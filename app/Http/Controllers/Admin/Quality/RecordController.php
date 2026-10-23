<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Quality\AccreditationBody;
use App\Models\Quality\EntityAccreditation;
use App\Models\Quality\EntityApproval;
use App\Models\Quality\EntityAward;
use App\Models\Quality\EntityPlacement;
use App\Models\Quality\EntityReview;
use App\Services\Quality\Aggregates;
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
        $approvals = EntityApproval::forEntity($type, $id)->with('body')->orderBy('accreditation_body_id')->orderByDesc('academic_year')->get();
        $agg = app(Aggregates::class);
        $placements = EntityPlacement::forEntity($type, $id)->orderByDesc('academic_year')->get();
        $awards = EntityAward::forEntity($type, $id)->orderByRaw("FIELD(kind, 'award', 'membership', 'ranking_mention', 'accreditation_mention', 'affiliation', 'other')")->orderByDesc('award_year')->orderBy('id')->get();
        $reviews = EntityReview::forEntity($type, $id)->orderByDesc('id')->get();
        $legacy = match ($type) {
            'organisation' => ['rating' => $entity->average_rating, 'rating_count' => $entity->total_reviews, 'is_top' => (bool) $entity->is_top],
            'course' => ['rating' => $entity->rating, 'roi' => $entity->roi],
            default => [],
        };

        // Records of the entities above, shown read-only with a label.
        $inherited = [];
        foreach (Levels::ancestors($entity) as [$aType, $anc]) {
            $inherited[] = [
                'label' => Levels::LABELS[$aType], 'name' => Levels::name($anc), 'url' => route('admin.quality.entity', [$aType, $anc->getKey()]),
                'accreditations' => EntityAccreditation::forEntity($aType, $anc->getKey())->current()->with('body')->get(),
                'approvals' => EntityApproval::forEntity($aType, $anc->getKey())->current()->with('body')->orderByDesc('academic_year')->get(),
                'rankings' => EntityRanking::forEntity($aType, $anc->getKey())->with(['body', 'category'])->orderByDesc('ranking_year')->limit(5)->get(),
            ];
        }

        return view('admin.quality.entity', [
            'type' => $type, 'entity' => $entity, 'name' => Levels::name($entity), 'context' => Levels::describe($entity), 'editUrl' => Levels::editUrl($entity),
            'rankings' => $rankings, 'accreditations' => $accreditations, 'approvals' => $approvals, 'inherited' => $inherited,
            'placements' => $placements, 'reviews' => $reviews, 'awards' => $awards, 'legacy' => $legacy,
            'ratings' => $agg->ratings($type, $id, true), 'ratingsAll' => $agg->ratings($type, $id, false),
            'placementSummary' => $agg->placements($type, $id, true), 'placementSummaryAll' => $agg->placements($type, $id, false),
            'computedTop' => $type === 'organisation' ? $agg->isTop($entity->id) : null,
            'roi' => $type === 'course' ? $agg->roi($entity) : null,
            'rankingBodies' => RankingBody::where('status', true)->orderBy('sort_order')->get(),
            'categories' => RankingCategory::where('status', true)->orderBy('sort_order')->get()->filter(fn ($c) => $c->allowsLevel($type))->values(),
            // New accreditations are NAAC / NBA / AMBA...; statutory approvals go in the Approvals panel. A body already used on this entity stays selectable so old rows can be edited.
            'accBodies' => AccreditationBody::where('status', true)->orderBy('sort_order')->get()
                ->filter(fn ($b) => $b->allowsLevel($type) && (!$b->isApprovalKind() || $accreditations->contains('accreditation_body_id', $b->id)))->values(),
            'approvalBodies' => AccreditationBody::where('status', true)->orderBy('sort_order')->get()->filter(fn ($b) => $b->isApprovalKind() && $b->allowsLevel($type))->values(),
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
        if ($body->isApprovalKind() && (!$row || $row->accreditation_body_id != $body->id)) {
            throw ValidationException::withMessages(['accreditation_body_id' => $body->short_name . ' is a ' . strtolower(AccreditationBody::KINDS[$body->kind]) . ', not an accreditation. Record it in the Approvals panel (it needs an academic year and intake).']);
        }
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

    // ---------- awards & recognitions ----------

    public function storeAward(Request $request, string $type, string $id)
    {
        Levels::find($type, $id) ?? abort(404);
        $data = $this->awardData($request, $type, $id);
        EntityAward::create($data + ['entity_type' => $type, 'entity_id' => (string) $id, 'created_by' => auth('admin')->id()]);
        return back()->with('success', 'Award added. It stays unverified until someone verifies it.');
    }

    public function updateAward(Request $request, EntityAward $award)
    {
        $award->update($this->awardData($request, $award->entity_type, $award->entity_id, $award));
        return back()->with('success', 'Award updated.' . ($award->wasChanged('verification_status') ? ' It needs to be verified again.' : ''));
    }

    public function destroyAward(EntityAward $award)
    {
        $award->delete();
        return back()->with('success', 'Award removed.');
    }

    private function awardData(Request $request, string $type, string $id, ?EntityAward $row = null): array
    {
        $d = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'kind' => ['required', Rule::in(array_keys(EntityAward::KINDS))],
            'issuer' => ['nullable', 'string', 'max:255'],
            'award_year' => ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:3000'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);
        if ($d['kind'] === 'award' && !filled($d['award_year'] ?? null)) {
            throw ValidationException::withMessages(['award_year' => 'An award needs the year it was received.']);
        }
        $norm = EntityAward::normalise($d['title']);
        $dup = EntityAward::forEntity($type, $id)->when($row, fn ($q) => $q->where('id', '!=', $row->id))->get(['title', 'award_year'])
            ->contains(fn ($a) => EntityAward::normalise($a->title) === $norm && (int) $a->award_year === (int) ($d['award_year'] ?? 0));
        if ($dup) {
            throw ValidationException::withMessages(['title' => 'This award is already recorded for the same year. Edit the existing one instead.']);
        }
        $out = collect($d)->except('evidence')->map(fn ($v) => $v === '' ? null : $v)->all();
        if ($request->hasFile('evidence')) {
            $out['evidence_file'] = $request->file('evidence')->store('quality-evidence', 'local');
        }
        return $out;
    }

    /** Bulk reject any records, or change the kind of awards (to sort out the old text list quickly). */
    public function bulkAction(Request $request)
    {
        $d = $request->validate([
            'kind' => ['required', Rule::in(['ranking', 'accreditation', 'approval', 'placement', 'review', 'award'])],
            'action' => ['required', Rule::in(['reject', 'set_kind'])],
            'value' => ['nullable', Rule::in(array_keys(EntityAward::KINDS))],
            'ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer'],
        ]);
        $user = auth('admin')->user();
        if ($d['action'] === 'reject') {
            abort_unless($user->can('quality-verify'), 403);
            $n = 0;
            foreach ($d['ids'] as $id) { $this->row($d['kind'], (int) $id)->reject($user->id); $n++; }
            return back()->with('success', "$n record(s) rejected.");
        }
        if ($d['kind'] !== 'award' || empty($d['value'])) {
            return back()->with('error', 'Choose a type to apply (awards only).');
        }
        $n = 0;
        foreach ($d['ids'] as $id) { $a = EntityAward::findOrFail((int) $id); if ($a->kind !== $d['value']) { $a->update(['kind' => $d['value']]); $n++; } }
        return back()->with('success', "$n award(s) changed to \"" . EntityAward::SHORT_KINDS[$d['value']] . '".');
    }

    // ---------- placements (raw figures; totals are computed) ----------

    public function storePlacement(Request $request, string $type, string $id)
    {
        Levels::find($type, $id) ?? abort(404);
        $data = $this->placementData($request, $type, $id);
        EntityPlacement::create($data + ['entity_type' => $type, 'entity_id' => (string) $id, 'created_by' => auth('admin')->id()]);
        return back()->with('success', 'Placement figures added. They stay unverified until someone verifies them.');
    }

    public function updatePlacement(Request $request, EntityPlacement $placement)
    {
        $placement->update($this->placementData($request, $placement->entity_type, $placement->entity_id, $placement));
        return back()->with('success', 'Placement figures updated.' . ($placement->wasChanged('verification_status') ? ' They need to be verified again.' : ''));
    }

    public function destroyPlacement(EntityPlacement $placement)
    {
        $placement->delete();
        return back()->with('success', 'Placement figures removed.');
    }

    private function placementData(Request $request, string $type, string $id, ?EntityPlacement $row = null): array
    {
        $d = $request->validate([
            'academic_year' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'students_graduating' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'students_placed' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'students_higher_studies' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'median_salary' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'highest_salary' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'average_salary' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'recruiters_count' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'placement_source' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);
        [$from, $to] = array_map('intval', explode('-', $d['academic_year']));
        if (($from + 1) % 100 !== $to) {
            throw ValidationException::withMessages(['academic_year' => 'Use consecutive years, like 2024-25.']);
        }
        $nums = ['students_graduating', 'students_placed', 'students_higher_studies', 'median_salary', 'highest_salary', 'average_salary', 'recruiters_count'];
        if (collect($nums)->every(fn ($f) => !filled($d[$f] ?? null))) {
            throw ValidationException::withMessages(['students_placed' => 'Enter at least one figure.']);
        }
        $g = $d['students_graduating'] ?? null; $p = $d['students_placed'] ?? null;
        if (filled($g) && filled($p) && (int) $p > (int) $g) {
            throw ValidationException::withMessages(['students_placed' => 'More students placed (' . number_format($p) . ') than graduating (' . number_format($g) . '). Check the figures.']);
        }
        if (filled($d['students_higher_studies'] ?? null) && filled($g) && (int) $d['students_higher_studies'] > (int) $g) {
            throw ValidationException::withMessages(['students_higher_studies' => 'More students in higher studies than graduating.']);
        }
        if (filled($d['median_salary'] ?? null) && filled($d['highest_salary'] ?? null) && (int) $d['median_salary'] > (int) $d['highest_salary']) {
            throw ValidationException::withMessages(['median_salary' => 'Median salary is higher than the highest salary.']);
        }
        $dup = EntityPlacement::forEntity($type, $id)->where('academic_year', $d['academic_year'])->when($row, fn ($q) => $q->where('id', '!=', $row->id))->exists();
        if ($dup) {
            throw ValidationException::withMessages(['academic_year' => 'Figures for ' . $d['academic_year'] . ' are already recorded. Edit the existing row instead.']);
        }
        $out = collect($d)->except('evidence')->map(fn ($v) => $v === '' ? null : $v)->all();
        if ($request->hasFile('evidence')) {
            $out['evidence_file'] = $request->file('evidence')->store('quality-evidence', 'local');
        }
        return $out;
    }

    // ---------- reviews (individual ratings; averages are computed) ----------

    public function storeReview(Request $request, string $type, string $id)
    {
        Levels::find($type, $id) ?? abort(404);
        EntityReview::create($this->reviewData($request) + ['entity_type' => $type, 'entity_id' => (string) $id, 'created_by' => auth('admin')->id()]);
        return back()->with('success', 'Review added. It counts towards averages once it is published, and towards the public figure once verified.');
    }

    public function updateReview(Request $request, EntityReview $review)
    {
        $review->update($this->reviewData($request));
        return back()->with('success', 'Review updated.' . ($review->wasChanged('verification_status') ? ' It needs to be verified again.' : ''));
    }

    public function destroyReview(EntityReview $review)
    {
        $review->delete();
        return back()->with('success', 'Review removed.');
    }

    private function reviewData(Request $request): array
    {
        $star = ['nullable', 'numeric', 'min:1', 'max:5', 'regex:/^\d(\.\d)?$/'];
        $d = $request->validate([
            'reviewer_name' => ['nullable', 'string', 'max:150'],
            'reviewer_type' => ['required', Rule::in(array_keys(EntityReview::REVIEWER_TYPES))],
            'rating' => ['required', 'numeric', 'min:1', 'max:5', 'regex:/^\d(\.\d)?$/'],
            'infrastructure' => $star, 'campus_life' => $star, 'academics' => $star, 'placements' => $star, 'value_for_money' => $star,
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:5000'],
            'reviewed_on' => ['nullable', 'date', 'before_or_equal:today'],
            'review_year' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],
            'source' => ['nullable', 'string', 'max:100'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $out = collect($d)->map(fn ($v) => $v === '' ? null : $v)->all();
        $out['is_published'] = $request->boolean('is_published', true);
        if (!empty($out['reviewed_on'])) $out['review_year'] = (int) substr($out['reviewed_on'], 0, 4);
        return $out;
    }

    // ---------- approvals (statutory approvals with sanctioned intake) ----------

    public function storeApproval(Request $request, string $type, string $id)
    {
        Levels::find($type, $id) ?? abort(404);
        $data = $this->approvalData($request, $type, $id);
        EntityApproval::create($data + ['entity_type' => $type, 'entity_id' => (string) $id, 'created_by' => auth('admin')->id()]);
        return back()->with('success', 'Approval added. It stays unverified until someone verifies it.');
    }

    public function updateApproval(Request $request, EntityApproval $approval)
    {
        $approval->update($this->approvalData($request, $approval->entity_type, $approval->entity_id, $approval));
        return back()->with('success', 'Approval updated.' . ($approval->wasChanged('verification_status') ? ' It needs to be verified again.' : ''));
    }

    public function destroyApproval(EntityApproval $approval)
    {
        $approval->delete();
        return back()->with('success', 'Approval removed.');
    }

    private function approvalData(Request $request, string $type, string $id, ?EntityApproval $row = null): array
    {
        $d = $request->validate([
            'accreditation_body_id' => ['required', Rule::exists('accreditation_bodies', 'id')],
            'academic_year' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'approval_number' => ['nullable', 'string', 'max:100'],
            'sanctioned_intake' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'valid_from' => ['nullable', 'date'],
            // Only compare against valid_from when it was given.
            'valid_to' => array_filter(['nullable', 'date', $request->filled('valid_from') ? 'after_or_equal:valid_from' : null]),
            'status' => ['required', Rule::in(array_keys(EntityApproval::STATUSES))],
            'source_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        [$from, $to] = array_map('intval', explode('-', $d['academic_year']));
        if (($from + 1) % 100 !== $to) {
            throw ValidationException::withMessages(['academic_year' => 'Use consecutive years, like 2025-26.']);
        }
        $body = AccreditationBody::find($d['accreditation_body_id']);
        if (!$body->isApprovalKind()) {
            throw ValidationException::withMessages(['accreditation_body_id' => $body->short_name . ' is an accreditation. Record it in the Accreditations panel.']);
        }
        if (!$body->allowsLevel($type)) {
            throw ValidationException::withMessages(['accreditation_body_id' => $body->short_name . ' cannot be recorded at ' . strtolower(Levels::LABELS[$type]) . ' level.']);
        }
        if (!$body->has_validity && filled($d['valid_to'] ?? null)) {
            throw ValidationException::withMessages(['valid_to' => $body->short_name . ' has no validity period.']);
        }
        $dup = EntityApproval::forEntity($type, $id)->where('accreditation_body_id', $body->id)->where('academic_year', $d['academic_year'])
            ->when($row, fn ($q) => $q->where('id', '!=', $row->id))->exists();
        if ($dup) {
            throw ValidationException::withMessages(['academic_year' => $body->short_name . ' is already recorded for ' . $d['academic_year'] . '. Edit the existing one instead.']);
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
        if ($row instanceof EntityPlacement) {
            if (!$row->academic_year) throw ValidationException::withMessages(['verify' => 'Set the academic year before verifying (migrated figures have none).']);
            if ($problems = $row->problems()) throw ValidationException::withMessages(['verify' => implode(' ', $problems)]);
        }
        if ($row instanceof EntityAward) {
            if (!in_array($row->kind, ['award', 'membership'], true)) {
                throw ValidationException::withMessages(['verify' => 'This is a "' . EntityAward::SHORT_KINDS[$row->kind] . '". Ranking mentions belong under Rankings, affiliations under Approvals, accreditation statements under Accreditations: record them there and reject this one, or change its type if it really is an award.']);
            }
            if (!$row->issuer) throw ValidationException::withMessages(['verify' => 'Say who gave it (issuer) before verifying.']);
            if ($row->kind === 'award' && !$row->award_year) throw ValidationException::withMessages(['verify' => 'Set the year before verifying.']);
        }
        if ($row instanceof EntityReview) {
            if (!$row->source && !$row->source_url) throw ValidationException::withMessages(['verify' => 'Say where the review was collected (source or link) before verifying.']);
            return;
        }
        if (!$row->source_url && !$row->evidence_file) {
            throw ValidationException::withMessages(['verify' => 'Add a source link or evidence file before verifying.']);
        }
    }

    private function row(string $kind, int $id)
    {
        return match ($kind) {
            'ranking' => EntityRanking::findOrFail($id),
            'approval' => EntityApproval::findOrFail($id),
            'placement' => EntityPlacement::findOrFail($id),
            'review' => EntityReview::findOrFail($id),
            'award' => EntityAward::findOrFail($id),
            'accreditation' => EntityAccreditation::findOrFail($id),
            default => abort(404),
        };
    }

    // ---------- review queue ----------

    public function review(Request $request)
    {
        $kind = in_array($request->query('kind'), ['accreditation', 'approval', 'placement', 'review', 'award'], true) ? $request->query('kind') : 'ranking';
        $status = in_array($request->query('status'), ['unverified', 'verified', 'rejected'], true) ? $request->query('status') : 'unverified';
        $q = match ($kind) { 'ranking' => EntityRanking::with(['body', 'category']), 'accreditation' => EntityAccreditation::with('body'), 'approval' => EntityApproval::with('body'), 'placement' => EntityPlacement::query(), 'review' => EntityReview::query(), 'award' => EntityAward::query() };
        if ($kind === 'award' && array_key_exists($request->query('akind'), EntityAward::KINDS)) $q->where('kind', $request->query('akind'));
        $q->where('verification_status', $status);
        if ($request->filled('body') && !in_array($kind, ['placement', 'review', 'award'], true)) $q->where($kind === 'ranking' ? 'ranking_body_id' : 'accreditation_body_id', (int) $request->query('body'));
        if ($request->query('origin') === 'legacy') $q->whereNotNull('legacy_source');
        if ($request->query('origin') === 'manual') { $q->whereNull('legacy_source'); if (in_array($kind, ['approval', 'placement', 'review'], true)) $q->whereNull('import_batch'); }
        if (in_array($kind, ['approval', 'placement', 'review'], true) && $request->query('origin') === 'import') $q->whereNotNull('import_batch');
        if (in_array($kind, ['approval', 'placement', 'review'], true) && $request->filled('batch')) $q->where('import_batch', $request->query('batch'));
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
            'bodies' => $kind === 'ranking' ? RankingBody::orderBy('name')->pluck('name', 'id') : (in_array($kind, ['placement', 'review', 'award'], true) ? collect() : AccreditationBody::orderBy('name')->pluck('short_name', 'id')),
            'counts' => [
                'ranking' => EntityRanking::where('verification_status', 'unverified')->count(),
                'accreditation' => EntityAccreditation::where('verification_status', 'unverified')->count(),
                'approval' => EntityApproval::where('verification_status', 'unverified')->count(),
                'placement' => EntityPlacement::where('verification_status', 'unverified')->count(),
                'review' => EntityReview::where('verification_status', 'unverified')->count(),
                'award' => EntityAward::where('verification_status', 'unverified')->where('kind', 'award')->count(),
            ],
        ]);
    }

    /** Bulk verify. Rows that cannot be verified (no year / no source) are skipped and reported, not silently verified. */
    public function bulkVerify(Request $request)
    {
        $d = $request->validate(['kind' => ['required', Rule::in(['ranking', 'accreditation', 'approval', 'placement', 'review', 'award'])], 'ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer']]);
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
