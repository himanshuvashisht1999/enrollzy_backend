<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Quality\AccreditationBody;
use App\Models\Quality\EntityAccreditation;
use App\Models\Quality\EntityRanking;
use App\Models\Quality\RankingBody;
use App\Models\Quality\RankingCategory;
use App\Support\Quality\Levels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Rankings and accreditations of one entity (organisation / campus / department / course), plus the verification queue. */
class RecordController extends Controller
{
    // ---------- entity page ----------

    public function entity(string $type, string $id)
    {
        $entity = Levels::find($type, $id) ?? abort(404);
        $rankings = EntityRanking::forEntity($type, $id)->with(['body', 'category'])->orderByDesc('ranking_year')->orderBy('ranking_body_id')->get();
        $accreditations = EntityAccreditation::forEntity($type, $id)->with('body')->orderBy('accreditation_body_id')->orderByDesc('valid_to')->get();

        // Records of the entities above, shown read-only with a label.
        $inherited = [];
        foreach (Levels::ancestors($entity) as [$aType, $anc]) {
            $inherited[] = [
                'label' => Levels::LABELS[$aType], 'name' => Levels::name($anc), 'url' => route('admin.quality.entity', [$aType, $anc->getKey()]),
                'accreditations' => EntityAccreditation::forEntity($aType, $anc->getKey())->current()->with('body')->get(),
                'rankings' => EntityRanking::forEntity($aType, $anc->getKey())->with(['body', 'category'])->orderByDesc('ranking_year')->limit(5)->get(),
            ];
        }

        return view('admin.quality.entity', [
            'type' => $type, 'entity' => $entity, 'name' => Levels::name($entity), 'context' => Levels::describe($entity), 'editUrl' => Levels::editUrl($entity),
            'rankings' => $rankings, 'accreditations' => $accreditations, 'inherited' => $inherited,
            'rankingBodies' => RankingBody::where('status', true)->orderBy('sort_order')->get(),
            'categories' => RankingCategory::where('status', true)->orderBy('sort_order')->get()->filter(fn ($c) => $c->allowsLevel($type))->values(),
            'accBodies' => AccreditationBody::where('status', true)->orderBy('sort_order')->get()->filter(fn ($b) => $b->allowsLevel($type))->values(),
        ]);
    }

    // ---------- rankings ----------

    public function storeRanking(Request $request, string $type, string $id)
    {
        Levels::find($type, $id) ?? abort(404);
        $data = $this->rankingData($request, $type, $id);
        EntityRanking::create($data + ['entity_type' => $type, 'entity_id' => (string) $id, 'created_by' => auth('admin')->id()]);
        return back()->with('success', 'Ranking added. It stays unverified until someone verifies it.');
    }

    public function updateRanking(Request $request, EntityRanking $ranking)
    {
        $ranking->update($this->rankingData($request, $ranking->entity_type, $ranking->entity_id, $ranking));
        return back()->with('success', 'Ranking updated.' . ($ranking->wasChanged('verification_status') ? ' It needs to be verified again.' : ''));
    }

    public function destroyRanking(EntityRanking $ranking)
    {
        $ranking->delete();
        return back()->with('success', 'Ranking removed.');
    }

    private function rankingData(Request $request, string $type, string $id, ?EntityRanking $row = null): array
    {
        $d = $request->validate([
            'ranking_body_id' => ['required', Rule::exists('ranking_bodies', 'id')],
            'ranking_category_id' => ['required', Rule::exists('ranking_categories', 'id')],
            'ranking_year' => ['required', 'integer', 'min:1990', 'max:' . (now()->year + 1)],
            'rank_exact' => ['nullable', 'integer', 'min:1'],
            'rank_band_from' => ['nullable', 'integer', 'min:1', 'required_with:rank_band_to'],
            'rank_band_to' => ['nullable', 'integer', 'min:1', 'required_with:rank_band_from', 'gte:rank_band_from'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'total_ranked' => ['nullable', 'integer', 'min:1'],
            'external_institute_id' => ['nullable', 'string', 'max:40'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'published_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $cat = RankingCategory::find($d['ranking_category_id']);
        if ($cat->ranking_body_id != $d['ranking_body_id']) {
            throw ValidationException::withMessages(['ranking_category_id' => 'That category belongs to a different ranking body.']);
        }
        if (!$cat->allowsLevel($type)) {
            throw ValidationException::withMessages(['ranking_category_id' => 'This category cannot be recorded at ' . strtolower(Levels::LABELS[$type]) . ' level.']);
        }
        if (filled($d['rank_exact'] ?? null) && filled($d['rank_band_from'] ?? null)) {
            throw ValidationException::withMessages(['rank_exact' => 'Give either an exact rank or a band, not both.']);
        }
        if (!filled($d['rank_exact'] ?? null) && !filled($d['rank_band_from'] ?? null) && !filled($d['score'] ?? null)) {
            throw ValidationException::withMessages(['rank_exact' => 'Give a rank, a band or a score.']);
        }
        $dup = EntityRanking::forEntity($type, $id)->where('ranking_body_id', $d['ranking_body_id'])->where('ranking_category_id', $d['ranking_category_id'])
            ->where('ranking_year', $d['ranking_year'])->when($row, fn ($q) => $q->where('id', '!=', $row->id))->exists();
        if ($dup) {
            throw ValidationException::withMessages(['ranking_year' => 'This ranking is already recorded for that year. Edit the existing one instead.']);
        }

        $out = collect($d)->except('evidence')->map(fn ($v) => $v === '' ? null : $v)->all();
        // A band and an exact rank are mutually exclusive: clear the unused side explicitly so an edit can switch between them.
        if (filled($out['rank_exact'] ?? null)) { $out['rank_band_from'] = $out['rank_band_to'] = null; }
        if (filled($out['rank_band_from'] ?? null)) { $out['rank_exact'] = null; }
        if ($request->hasFile('evidence')) {
            $out['evidence_file'] = $request->file('evidence')->store('quality-evidence', 'local');
        }
        return $out;
    }

    // ---------- accreditations ----------

    public function storeAccreditation(Request $request, string $type, string $id)
    {
        Levels::find($type, $id) ?? abort(404);
        $data = $this->accreditationData($request, $type, $id);
        EntityAccreditation::create($data + ['entity_type' => $type, 'entity_id' => (string) $id, 'created_by' => auth('admin')->id()]);
        return back()->with('success', 'Accreditation added. It stays unverified until someone verifies it.');
    }

    public function updateAccreditation(Request $request, EntityAccreditation $accreditation)
    {
        $accreditation->update($this->accreditationData($request, $accreditation->entity_type, $accreditation->entity_id, $accreditation));
        return back()->with('success', 'Accreditation updated.' . ($accreditation->wasChanged('verification_status') ? ' It needs to be verified again.' : ''));
    }

    public function destroyAccreditation(EntityAccreditation $accreditation)
    {
        $accreditation->delete();
        return back()->with('success', 'Accreditation removed.');
    }

    private function accreditationData(Request $request, string $type, string $id, ?EntityAccreditation $row = null): array
    {
        $d = $request->validate([
            'accreditation_body_id' => ['required', Rule::exists('accreditation_bodies', 'id')],
            'grade' => ['nullable', 'string', 'max:30'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cycle' => ['nullable', 'integer', 'min:1', 'max:20'],
            'valid_from' => ['nullable', 'date'],
            // Only compare against valid_from when it was given.
            'valid_to' => array_filter(['nullable', 'date', $request->filled('valid_from') ? 'after_or_equal:valid_from' : null]),
            'status' => ['required', Rule::in(array_keys(EntityAccreditation::STATUSES))],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $body = AccreditationBody::find($d['accreditation_body_id']);
        if (!$body->allowsLevel($type)) {
            throw ValidationException::withMessages(['accreditation_body_id' => $body->short_name . ' cannot be recorded at ' . strtolower(Levels::LABELS[$type]) . ' level.']);
        }
        if (filled($d['grade'] ?? null) && !$body->has_grade) {
            throw ValidationException::withMessages(['grade' => $body->short_name . ' has no grade. Leave it empty.']);
        }
        if (filled($d['grade'] ?? null) && !empty($body->grade_options) && !in_array($d['grade'], $body->grade_options, true)) {
            throw ValidationException::withMessages(['grade' => 'Grade must be one of: ' . implode(', ', $body->grade_options) . '.']);
        }
        if (!$body->has_validity && (filled($d['valid_to'] ?? null))) {
            throw ValidationException::withMessages(['valid_to' => $body->short_name . ' has no validity period.']);
        }
        $dup = EntityAccreditation::forEntity($type, $id)->where('accreditation_body_id', $body->id)
            ->where('grade', $d['grade'] ?? null)->where('valid_from', $d['valid_from'] ?? null)->where('valid_to', $d['valid_to'] ?? null)
            ->when($row, fn ($q) => $q->where('id', '!=', $row->id))->exists();
        if ($dup) {
            throw ValidationException::withMessages(['accreditation_body_id' => 'An identical record already exists for ' . $body->short_name . '.']);
        }

        $out = collect($d)->except('evidence')->map(fn ($v) => $v === '' ? null : $v)->all();
        if ($request->hasFile('evidence')) {
            $out['evidence_file'] = $request->file('evidence')->store('quality-evidence', 'local');
        }
        return $out;
    }

    /** Evidence is kept on the private disk and only served to admins who may browse quality records (no public storage link needed). */
    public function evidence(string $kind, int $id)
    {
        $path = $this->row($kind, $id)->evidence_file;
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->response($path);
    }

    // ---------- verification ----------

    public function verify(string $kind, int $id)
    {
        $row = $this->row($kind, $id);
        $this->guardVerifiable($row);
        $row->verify(auth('admin')->id());
        return back()->with('success', 'Marked as verified.');
    }

    public function reject(string $kind, int $id)
    {
        $this->row($kind, $id)->reject(auth('admin')->id());
        return back()->with('success', 'Marked as rejected.');
    }

    /** A record can only be verified if it names its year (rankings) and has some evidence of where it came from. */
    private function guardVerifiable($row): void
    {
        if ($row instanceof EntityRanking && !$row->ranking_year) {
            throw ValidationException::withMessages(['verify' => 'Set the ranking year before verifying (migrated records have none).']);
        }
        if (!$row->source_url && !$row->evidence_file) {
            throw ValidationException::withMessages(['verify' => 'Add a source link or evidence file before verifying.']);
        }
    }

    private function row(string $kind, int $id)
    {
        return match ($kind) {
            'ranking' => EntityRanking::findOrFail($id),
            'accreditation' => EntityAccreditation::findOrFail($id),
            default => abort(404),
        };
    }

    // ---------- review queue ----------

    public function review(Request $request)
    {
        $kind = $request->query('kind', 'ranking') === 'accreditation' ? 'accreditation' : 'ranking';
        $status = in_array($request->query('status'), ['unverified', 'verified', 'rejected'], true) ? $request->query('status') : 'unverified';
        $q = $kind === 'ranking' ? EntityRanking::with(['body', 'category']) : EntityAccreditation::with('body');
        $q->where('verification_status', $status);
        if ($request->filled('body')) $q->where($kind === 'ranking' ? 'ranking_body_id' : 'accreditation_body_id', (int) $request->query('body'));
        if ($request->query('origin') === 'legacy') $q->whereNotNull('legacy_source');
        if ($request->query('origin') === 'manual') $q->whereNull('legacy_source');
        if ($request->filled('type') && Levels::valid($request->query('type'))) $q->where('entity_type', $request->query('type'));
        $rows = $q->orderBy('id')->paginate(50)->withQueryString();

        // Resolve names for the page in one query per level.
        $names = [];
        foreach ($rows->groupBy('entity_type') as $t => $group) {
            $model = Levels::MAP[$t];
            $names[$t] = $model::whereIn((new $model)->getKeyName(), $group->pluck('entity_id'))->get()->mapWithKeys(fn ($m) => [(string) $m->getKey() => Levels::name($m)])->all();
        }
        return view('admin.quality.review', [
            'kind' => $kind, 'status' => $status, 'rows' => $rows, 'names' => $names,
            'bodies' => $kind === 'ranking' ? RankingBody::orderBy('name')->pluck('name', 'id') : AccreditationBody::orderBy('name')->pluck('short_name', 'id'),
            'counts' => [
                'ranking' => EntityRanking::where('verification_status', 'unverified')->count(),
                'accreditation' => EntityAccreditation::where('verification_status', 'unverified')->count(),
            ],
        ]);
    }

    /** Bulk verify. Rows that cannot be verified (no year / no source) are skipped and reported, not silently verified. */
    public function bulkVerify(Request $request)
    {
        $d = $request->validate(['kind' => ['required', Rule::in(['ranking', 'accreditation'])], 'ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer']]);
        $done = 0; $skipped = 0;
        foreach ($d['ids'] as $id) {
            $row = $this->row($d['kind'], (int) $id);
            if ($row->verification_status === 'verified') continue;
            try { $this->guardVerifiable($row); } catch (ValidationException) { $skipped++; continue; }
            $row->verify(auth('admin')->id());
            $done++;
        }
        return back()->with($skipped ? 'error' : 'success', "$done verified" . ($skipped ? ", $skipped skipped (need a year and a source link or evidence file first)." : '.'));
    }
}
