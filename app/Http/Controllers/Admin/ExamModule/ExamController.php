<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamCategory;
use App\Models\ExamModule\AuditLog;
use App\Models\ExamModule\ConductingBody;
use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\Exam;
use App\Models\Organisation;
use App\Models\ProgramLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with(['conductingBody', 'currentCycle'])
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('short_name', 'like', "%{$s}%")
                    ->orWhere('slug', 'like', "%{$s}%")->orWhere('aliases', 'like', "%{$s}%");
            }))
            ->when($request->exam_type, fn ($q, $t) => $q->where('exam_type', $t))
            ->when($request->visibility, fn ($q, $v) => $q->where('visibility', $v))
            ->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.exam-module.exams.index', compact('exams'));
    }

    public function create()
    {
        return view('admin.exam-module.exams.form', ['exam' => new Exam(['source_type' => 'external', 'visibility' => 'draft', 'status' => true])] + $this->formData());
    }

    public function store(Request $request)
    {
        $exam = new Exam();
        return $this->save($request, $exam, 'created');
    }

    public function show(Exam $exam)
    {
        $exam->load(['conductingBody', 'owningOrganisation', 'feederExam', 'cycles' => fn ($q) => $q->withCount(['events', 'stages'])]);
        $cycleIds = $exam->cycles->pluck('id');
        $logs = AuditLog::with('user')
            ->where(fn ($q) => $q->where(fn ($q) => $q->where('auditable_type', Exam::class)->where('auditable_id', $exam->id))
                ->orWhere(fn ($q) => $q->where('auditable_type', Cycle::class)->whereIn('auditable_id', $cycleIds)))
            ->latest('id')->limit(15)->get();

        // "Counselling after this exam": the same mapping table the counselling page reads, from the exam's side.
        $counsellings = \App\Models\Counselling\CounsellingExam::with(['counselling.conductingBody', 'counselling.currentCycle'])
            ->where('exam_id', $exam->id)->whereHas('counselling')->get()->sortBy(fn ($m) => [$m->role === 'primary' ? 0 : 1, $m->counselling->name])->values();

        return view('admin.exam-module.exams.show', ['exam' => $exam, 'logs' => $logs, 'missing' => $exam->missingForPublish(), 'counsellings' => $counsellings]
            + ['categories' => ExamCategory::pluck('title', 'id'), 'levels' => ProgramLevel::pluck('title', 'id'),
                'languageNames' => \App\Models\Language::pluck('title', 'id')]);
    }

    public function edit(Exam $exam)
    {
        return view('admin.exam-module.exams.form', ['exam' => $exam] + $this->formData($exam));
    }

    public function update(Request $request, Exam $exam)
    {
        return $this->save($request, $exam, 'updated');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('admin.exam-module.exams.index')->with('success', 'Exam deleted.');
    }

    public function visibility(Request $request, Exam $exam)
    {
        $target = $exam->visibility === 'public' ? 'draft' : 'public';
        if ($target === 'public' && ($missing = $exam->missingForPublish())) {
            return back()->with('error', 'Cannot publish. Missing: ' . implode(', ', $missing));
        }
        $exam->update(['visibility' => $target]);
        return back()->with('success', 'Exam is now ' . $target . '.');
    }

    private function save(Request $request, Exam $exam, string $verb)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('exm_exams', 'slug')->ignore($exam->id)->withoutTrashed()],
            'aliases' => 'nullable|string',
            'source_type' => 'required|in:external,internal',
            'owning_organisation_id' => 'required_if:source_type,internal|nullable|integer',
            'conducting_body_id' => 'nullable|exists:exm_conducting_bodies,id',
            'exam_type' => ['nullable', Rule::in(array_keys(Exam::TYPES))],
            'exam_categories' => 'nullable|array',
            'exam_categories.*' => 'integer',
            'frequency' => ['nullable', Rule::in(array_keys(Exam::FREQUENCIES))],
            'program_levels' => 'nullable|array',
            'program_levels.*' => 'integer',
            'states' => 'nullable|array',
            'states.*' => 'string',
            'feeder_exam_id' => array_filter(['nullable', 'exists:exm_exams,id', $exam->exists ? Rule::notIn([$exam->id]) : null]),
            'languages' => 'nullable|array',
            'languages.*' => ['integer', Rule::exists('languages', 'id')],
            'about' => 'nullable|string',
            'official_website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:4096',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'has_stages' => 'nullable|boolean',
            'visibility' => 'required|in:draft,public',
            'status' => 'nullable|boolean',
        ]);

        $data['aliases'] = $this->csv($data['aliases'] ?? null);
        $data['languages'] = array_map('intval', $data['languages'] ?? []) ?: null;
        $data['has_stages'] = $request->boolean('has_stages');

        // Turning stages off while cycles still define them would hide data that events depend on.
        if ($exam->exists && $exam->has_stages && !$data['has_stages']
            && \App\Models\ExamModule\Stage::whereIn('cycle_id', $exam->cycles()->withTrashed()->pluck('id'))->exists()) {
            return back()->withInput()->withErrors(['has_stages' => 'Delete the stages from all cycles before turning stages off.']);
        }
        $data['status'] = $request->boolean('status');
        $data['slug'] = $data['slug'] ?? null ?: null;
        $data['featured'] = $request->boolean('featured');
        $data['is_archived'] = $request->boolean('is_archived');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        unset($data['logo'], $data['cover_image']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('exm/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('exm/covers', 'public');
        }

        $exam->fill($data);
        if (blank($exam->slug)) {
            $exam->slug = Exam::uniqueSlug($exam->name, $exam->id);
        }

        $notice = null;
        if ($exam->visibility === 'public') {
            $missing = $exam->missingForPublish();
            $canPublish = $request->user('admin')?->can('exm-exams-publish');
            if ($missing || !$canPublish) {
                $exam->visibility = $exam->getOriginal('visibility') ?? 'draft';
                $notice = $missing ? 'Saved as ' . $exam->visibility . '. To publish, fill: ' . implode(', ', $missing) : 'Saved. You do not have permission to publish.';
            }
        }
        $exam->save();

        return redirect()->route('admin.exam-module.exams.show', $exam)
            ->with('success', "Exam {$verb}.")->with($notice ? 'error' : 'noop', $notice);
    }

    private function csv(?string $value): ?array
    {
        $items = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,\n]+/', (string) $value)))));
        return $items ?: null;
    }

    private function formData(?Exam $exam = null): array
    {
        return [
            'bodies' => ConductingBody::where('status', true)->orderBy('name')->get(),
            'languageList' => \App\Models\Language::where('status', 1)->orderBy('title')->get(['id', 'title']),
            'categories' => ExamCategory::where('status', 1)->orderBy('title')->get(),
            'levels' => ProgramLevel::where('status', 1)->orderBy('title')->get(),
            'organisations' => Organisation::orderBy('name')->get(['id', 'name']),
            'feederExams' => Exam::where('id', '!=', $exam?->id ?? 0)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
