<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\Counselling;
use App\Models\Counselling\CounsellingExam;
use App\Models\ExamCategory;
use App\Models\ExamModule\AuditLog;
use App\Models\ExamModule\Exam;
use App\Models\Organisation;
use App\Models\ProgramLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CounsellingController extends Controller
{
    public function index(Request $request)
    {
        $counsellings = Counselling::with(['conductingBody', 'currentCycle', 'exams:id,name'])
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('short_name', 'like', "%$s%")->orWhere('slug', 'like', "%$s%")->orWhere('aliases', 'like', "%$s%")))
            ->when($request->level, fn ($q, $v) => $q->where('level', $v))
            ->when($request->state, fn ($q, $v) => $q->where('states', 'like', '%"' . $v . '"%'))
            ->when($request->program_level, fn ($q, $v) => $q->where('program_levels', 'like', '%' . (int) $v . '%'))
            ->when($request->visibility, fn ($q, $v) => $q->where('visibility', $v))
            ->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.counselling.counsellings.index', [
            'counsellings' => $counsellings,
            'levels' => ProgramLevel::orderBy('title')->pluck('title', 'id'),
        ]);
    }

    public function create(Request $request)
    {
        $counselling = new Counselling(['level' => 'national', 'mode' => 'online', 'visibility' => 'draft', 'is_active' => true]);
        return view('admin.counselling.counsellings.form', ['counselling' => $counselling, 'fromExam' => Exam::find($request->exam_id)] + $this->formData());
    }

    public function store(Request $request)
    {
        return $this->save($request, new Counselling(), 'created');
    }

    public function show(Counselling $counselling)
    {
        $counselling->load(['conductingBody', 'owningOrganisation', 'mappings.exam', 'cycles' => fn ($q) => $q->withCount('rounds')]);
        $cycleIds = $counselling->cycles->pluck('id');
        $logs = AuditLog::with('user')
            ->where(fn ($q) => $q->where(fn ($q) => $q->where('auditable_type', Counselling::class)->where('auditable_id', $counselling->id))
                ->orWhere(fn ($q) => $q->where('auditable_type', \App\Models\Counselling\CounsellingCycle::class)->whereIn('auditable_id', $cycleIds)))
            ->latest('id')->limit(15)->get();

        return view('admin.counselling.counsellings.show', [
            'counselling' => $counselling, 'logs' => $logs, 'missing' => $counselling->missingForPublish(),
            'levelNames' => ProgramLevel::pluck('title', 'id'), 'categoryNames' => ExamCategory::pluck('title', 'id'),
            'mappable' => Exam::whereNotIn('id', $counselling->mappings->pluck('exam_id'))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Counselling $counselling)
    {
        return view('admin.counselling.counsellings.form', ['counselling' => $counselling, 'fromExam' => null] + $this->formData($counselling));
    }

    public function update(Request $request, Counselling $counselling)
    {
        return $this->save($request, $counselling, 'updated');
    }

    public function destroy(Counselling $counselling)
    {
        $counselling->delete();   // soft delete: cycles and rounds are kept but hidden with it
        return redirect()->route('admin.counselling.counsellings.index')->with('success', 'Counselling deleted.');
    }

    public function visibility(Counselling $counselling)
    {
        $target = $counselling->visibility === 'public' ? 'draft' : 'public';
        if ($target === 'public' && ($missing = $counselling->missingForPublish())) {
            return back()->with('error', 'Cannot publish. Missing: ' . implode(', ', $missing));
        }
        $counselling->update(['visibility' => $target]);
        return back()->with('success', 'Counselling is now ' . $target . '.');
    }

    // ---- Exam mapping (one table, read from both sides) ----

    /** The same mapping fields wherever a mapping is added or edited (spec section 4). */
    private const MAPPING_RULES = [
        'valid_from_year' => 'nullable|integer|min:1990|max:2100',
        'valid_to_year' => 'nullable|integer|min:1990|max:2100|gte:valid_from_year',
        'seat_scope_note' => 'nullable|string|max:255',
    ];

    public function mapExam(Request $request, Counselling $counselling)
    {
        $data = $request->validate([
            'exam_id' => ['required', Rule::exists('exm_exams', 'id')->whereNull('deleted_at'),
                Rule::unique('cns_counselling_exams', 'exam_id')->where('counselling_id', $counselling->id)],
            'role' => ['required', Rule::in(['primary', 'additional'])],
        ] + self::MAPPING_RULES, ['exam_id.unique' => 'This exam is already mapped to this counselling.']);

        $counselling->mappings()->create($data + ['sort_order' => ($counselling->mappings()->max('sort_order') ?? 0) + 1]);
        return back()->with('success', 'Exam mapped.');
    }

    public function updateMapping(Request $request, CounsellingExam $mapping)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['primary', 'additional'])],
            'valid_from_year' => 'nullable|integer|min:1990|max:2100',
            'valid_to_year' => 'nullable|integer|min:1990|max:2100|gte:valid_from_year',
            'seat_scope_note' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $mapping->update($data + ['sort_order' => $data['sort_order'] ?? $mapping->sort_order]);
        return back()->with('success', 'Mapping updated.');
    }

    public function unmapExam(Request $request, CounsellingExam $mapping)
    {
        // Cutoffs must belong to a mapped exam, so removing the exam removes its cutoffs: only after confirmation.
        $cutoffs = \App\Models\Counselling\Cutoff::where('exam_id', $mapping->exam_id)
            ->whereIn('round_id', \App\Models\Counselling\Round::whereIn('cycle_id', $mapping->counselling->cycles()->withTrashed()->select('id'))->select('id'));
        $count = (clone $cutoffs)->count();
        if ($count && !$request->boolean('confirm')) {
            return back()->with('error', "$count cutoffs use this exam. Confirm the removal to delete the exam from this counselling together with those cutoffs.");
        }
        DB::transaction(function () use ($cutoffs, $mapping, $count) {
            if ($count) {
                \App\Models\ExamModule\AuditLog::create(['auditable_type' => \App\Models\Counselling\Cutoff::class, 'auditable_id' => $mapping->id, 'user_id' => auth('admin')->id(),
                    'action' => 'deleted', 'old_values' => ['exam_id' => $mapping->exam_id, 'counselling_id' => $mapping->counselling_id, 'cutoffs_deleted' => $count], 'new_values' => null]);
                $cutoffs->delete();
            }
            $mapping->delete();
        });
        return back()->with('success', 'Exam removed from this counselling' . ($count ? " with its $count cutoffs" : '') . '.');
    }

    /** Exam Module hook: counsellings mapped to one exam, with map-existing and create-new. */
    public function forExam(Exam $exam)
    {
        $mapped = CounsellingExam::with('counselling.conductingBody', 'counselling.currentCycle')->where('exam_id', $exam->id)->get();
        $available = Counselling::whereNotIn('id', $mapped->pluck('counselling_id'))->orderBy('name')->get(['id', 'name', 'level']);
        return view('admin.counselling.counsellings.for-exam', compact('exam', 'mapped', 'available'));
    }

    public function mapExistingFromExam(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'counselling_id' => 'required|exists:cns_counsellings,id',
            'role' => ['nullable', Rule::in(['primary', 'additional'])],
        ] + self::MAPPING_RULES);
        $data['role'] ??= 'primary';
        $counselling = Counselling::findOrFail($data['counselling_id']);
        if ($counselling->mappings()->where('exam_id', $exam->id)->exists()) {
            return back()->with('error', 'Already mapped.');
        }
        $counselling->mappings()->create(['exam_id' => $exam->id, 'role' => $data['role'],
            'valid_from_year' => $data['valid_from_year'] ?? null, 'valid_to_year' => $data['valid_to_year'] ?? null,
            'seat_scope_note' => $data['seat_scope_note'] ?? null,
            'sort_order' => ($counselling->mappings()->max('sort_order') ?? 0) + 1]);
        return back()->with('success', 'Counselling mapped to ' . $exam->name . '.');
    }

    // ---- internals ----

    private function save(Request $request, Counselling $counselling, string $verb)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('cns_counsellings', 'slug')->ignore($counselling->id)->withoutTrashed()],
            'aliases' => 'nullable|string',
            // Conducting body = an organisation of type "Counselling Body".
            'conducting_organisation_id' => ['required', Rule::exists('organisations', 'id')->where('organisation_type_id', Organisation::TYPE_COUNSELLING_BODY)],
            'level' => ['required', Rule::in(array_keys(Counselling::LEVELS))],
            'states' => 'required_if:level,state|nullable|array',
            'states.*' => ['string', Rule::in(Exam::STATES)],
            'owning_organisation_id' => ['required_if:level,university,institute', 'nullable', Rule::exists('organisations', 'id')],
            'program_levels' => 'nullable|array',
            'program_levels.*' => 'integer',
            'course_categories' => 'nullable|array',
            'course_categories.*' => 'integer',
            'mode' => ['required', Rule::in(array_keys(Counselling::MODES))],
            'official_website' => 'nullable|url|max:255',
            'registration_portal_url' => 'nullable|url|max:255',
            'helpline' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:4096',
            'about' => 'nullable|string',
            'visibility' => 'required|in:draft,public',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'exam_id' => 'nullable|exists:exm_exams,id',
        ], [
            'states.required_if' => 'Pick at least one state for a state-level counselling.',
            'owning_organisation_id.required_if' => 'An owning organisation is required for university or institute level.',
        ]);

        $examId = $data['exam_id'] ?? null;
        $data['aliases'] = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,\n]+/', (string) ($data['aliases'] ?? ''))))) ) ?: null;
        $data['is_active'] = $request->boolean('is_active');
        $data['is_archived'] = $request->boolean('is_archived');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['slug'] = ($data['slug'] ?? null) ?: null;
        unset($data['logo'], $data['cover_image'], $data['exam_id']);
        foreach (['logo' => 'cns/logos', 'cover_image' => 'cns/covers'] as $f => $dir) {
            if ($request->hasFile($f)) {
                $data[$f] = $request->file($f)->store($dir, 'public');
            }
        }

        $counselling->fill($data);
        if (blank($counselling->slug)) {
            $counselling->slug = Counselling::uniqueSlug($counselling->name, $counselling->id);
        }
        $notice = null;
        if ($counselling->visibility === 'public') {
            $missing = $counselling->exists ? $counselling->missingForPublish() : ['At least one mapped exam'];
            $canPublish = $request->user('admin')?->can('cns-counsellings-publish');
            if ($missing || !$canPublish) {
                $counselling->visibility = $counselling->getOriginal('visibility') ?? 'draft';
                $notice = $missing ? 'Saved as ' . $counselling->visibility . '. To publish, fill: ' . implode(', ', $missing) : 'Saved. You do not have permission to publish.';
            }
        }
        $counselling->save();

        // Created from an exam row: the mapping is pre-filled with that exam as primary.
        if ($verb === 'created' && $examId) {
            $counselling->mappings()->create(['exam_id' => $examId, 'role' => 'primary', 'sort_order' => 1]);
        }

        return redirect()->route('admin.counselling.counsellings.show', $counselling)
            ->with('success', "Counselling {$verb}.")->with($notice ? 'error' : 'noop', $notice);
    }

    private function formData(?Counselling $counselling = null): array
    {
        return [
            // Organisations of type "Counselling Body" (plus the one already chosen, in case its type changed later).
            'bodies' => Organisation::where('organisation_type_id', Organisation::TYPE_COUNSELLING_BODY)
                ->orWhere('id', $counselling?->conducting_organisation_id ?? 0)->orderBy('name')->get(['id', 'name', 'short_name', 'abbreviation']),
            'levels' => ProgramLevel::where('status', 1)->orderBy('title')->get(),
            'categories' => ExamCategory::where('status', 1)->orderBy('title')->get(),
            'organisations' => Organisation::orderBy('name')->get(['id', 'name']),
        ];
    }
}
