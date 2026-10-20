<?php

namespace App\Services\Quality;

use App\Models\OrganisationCourse;
use App\Models\Quality\EntityPlacement;
use App\Models\Quality\EntityRanking;
use App\Models\Quality\EntityReview;
use App\Models\Quality\RankingBody;
use App\Models\Quality\RankingCategory;
use App\Support\Quality\Levels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Everything here is COMPUTED from the raw records, never typed:
 *  - ratings: the average of individual reviews at an entity and everything below it (course -> department -> campus -> organisation);
 *  - placements: totals from the COURSE-level rows below an entity, so the same students are never counted twice. A row recorded at a higher level is
 *    shown as "reported by the institution" next to the computed figures, but is not added to them.
 */
class Aggregates
{
    private const COLL = 'COLLATE utf8mb4_unicode_ci';

    /** Entity types below $type with the SQL that selects their ids. [childType, sql, bindings] */
    private function below(string $type, $id): array
    {
        return match ($type) {
            'organisation' => [
                ['campus', 'SELECT id FROM campuses WHERE organisation_id = ? AND deleted_at IS NULL', [$id]],
                ['department', 'SELECT id FROM departments WHERE organisation_id = ? AND deleted_at IS NULL', [$id]],
                ['course', 'SELECT id FROM organisation_courses WHERE organisation_id = ?', [$id]],
            ],
            'campus' => [
                ['department', 'SELECT id FROM departments WHERE campus_id = ? AND deleted_at IS NULL', [$id]],
                ['course', 'SELECT id FROM organisation_courses WHERE campus_id = ?', [$id]],
            ],
            'department' => [['course', 'SELECT id FROM organisation_courses WHERE department_id = ?', [$id]]],
            default => [],
        };
    }

    /** Restrict a record query to the entity itself and (optionally only some of) the levels below it. */
    private function within(Builder $q, string $type, $id, ?array $onlyBelow = null): Builder
    {
        return $q->where(function ($w) use ($type, $id, $onlyBelow) {
            $w->where(fn ($x) => $x->where('entity_type', $type)->where('entity_id', (string) $id));
            foreach ($this->below($type, $id) as [$childType, $sql, $bind]) {
                if ($onlyBelow !== null && !in_array($childType, $onlyBelow, true)) continue;
                $col = $childType === 'course' ? 'entity_id' : 'entity_id ' . self::COLL;
                $inner = $childType === 'course' ? $sql : preg_replace('/^SELECT id /', 'SELECT id ' . self::COLL . ' ', $sql);
                $w->orWhere(fn ($x) => $x->where('entity_type', $childType)->whereRaw("$col IN ($inner)", $bind));
            }
        });
    }

    // ---------------------------------------------------------------- ratings

    /**
     * @return array{count:int, verified_count:int, unverified_count:int, own_count:int, below_count:int, average:?float, subs:array<string,?float>, basis:string}
     */
    public function ratings(string $type, $id, bool $verifiedOnly = true): array
    {
        $all = $this->within(EntityReview::query()->counted(), $type, $id);
        $row = (clone $all)->selectRaw("COUNT(*) n, SUM(verification_status = 'verified') v")->first();
        $n = (int) $row->n; $v = (int) $row->v;

        $basis = $verifiedOnly ? (clone $all)->where('verification_status', 'verified') : (clone $all);
        // own / below are counted on the SAME basis as the average, so "includes N reviews from below" never counts reviews the average ignores.
        $own = (clone $basis)->where('entity_type', $type)->where('entity_id', (string) $id)->count();
        $cols = array_keys(EntityReview::SUB_RATINGS);
        $sel = 'COUNT(*) c, AVG(rating) a' . implode('', array_map(fn ($c) => ", AVG($c) s_$c", $cols));
        $agg = $basis->selectRaw($sel)->first();
        $count = (int) $agg->c;
        $min = (int) config('quality.min_reviews_to_show_average', 1);
        $subs = [];
        foreach ($cols as $c) $subs[$c] = $count >= $min && $agg->{"s_$c"} !== null ? round((float) $agg->{"s_$c"}, 2) : null;

        return ['count' => $verifiedOnly ? $count : $n, 'verified_count' => $v, 'unverified_count' => $n - $v, 'own_count' => $own, 'below_count' => $count - $own,
            'average' => $count >= $min && $agg->a !== null ? round((float) $agg->a, 2) : null, 'subs' => $subs, 'basis' => $verifiedOnly ? 'verified reviews' : 'all published reviews'];
    }

    // ------------------------------------------------------------- placements

    /**
     * Per academic year. A course gets its own rows; any other level gets totals computed from the course rows below it.
     *
     * @return array{rows:array, reported:\Illuminate\Support\Collection, programmes_total:int, basis:string}
     */
    public function placements(string $type, $id, bool $verifiedOnly = true): array
    {
        $q = $this->within(EntityPlacement::query(), $type, $id, ['course']);
        if ($type !== 'course') $q->where('entity_type', 'course');     // roll up from programmes only
        if ($verifiedOnly) $q->where('verification_status', 'verified'); else $q->where('verification_status', '!=', 'rejected');

        // Impossible rows (placed > graduating) never enter a total.
        $ok = '(students_placed IS NULL OR students_graduating IS NULL OR students_placed <= students_graduating)';
        $both = 'students_placed IS NOT NULL AND students_graduating IS NOT NULL';
        $w = 'median_salary IS NOT NULL AND students_placed IS NOT NULL AND students_placed > 0';
        $raw = $q->whereRaw($ok)->selectRaw("academic_year,
            COUNT(DISTINCT entity_id) programmes,
            SUM(CASE WHEN $both THEN students_placed END) placed,
            SUM(CASE WHEN $both THEN students_graduating END) graduating,
            SUM(students_higher_studies) higher,
            MAX(highest_salary) highest,
            SUM(CASE WHEN $w THEN median_salary * students_placed END) / NULLIF(SUM(CASE WHEN $w THEN students_placed END), 0) median_weighted,
            SUM(recruiters_count) recruiters")
            ->groupBy('academic_year')->get()
            ->sortByDesc(fn ($r) => $r->academic_year ?? '0000')->values();

        $rows = [];
        foreach ($raw as $r) {
            $rows[] = ['academic_year' => $r->academic_year, 'programmes' => (int) $r->programmes,
                'placed' => $r->placed !== null ? (int) $r->placed : null, 'graduating' => $r->graduating !== null ? (int) $r->graduating : null,
                'percentage' => $r->graduating ? round($r->placed / $r->graduating * 100, 1) : null,
                'higher_studies' => $r->higher !== null ? (int) $r->higher : null, 'highest_salary' => $r->highest !== null ? (int) $r->highest : null,
                'median_salary' => $r->median_weighted !== null ? (int) round($r->median_weighted) : null, 'recruiters' => $r->recruiters !== null ? (int) $r->recruiters : null];
        }

        $total = $this->programmeCount($type, $id);

        $reported = $type === 'course' ? collect()
            : EntityPlacement::where('entity_type', $type)->where('entity_id', (string) $id)->where('verification_status', '!=', 'rejected')->orderByDesc('academic_year')->get();

        return ['rows' => $rows, 'reported' => $reported, 'programmes_total' => $total,
            'basis' => $type === 'course' ? 'this programme' : 'verified programme-level figures below' ];
    }

    public function programmeCount(string $type, $id): int
    {
        return match ($type) {
            'organisation' => DB::table('organisation_courses')->where('organisation_id', $id)->count(),
            'campus' => DB::table('organisation_courses')->where('campus_id', $id)->count(),
            'department' => DB::table('organisation_courses')->where('department_id', $id)->count(),
            default => 1,
        };
    }

    // ----------------------------------------------------- computed labels

    /** "Top" = the latest VERIFIED NIRF Overall ranking is within config('quality.top_nirf_overall_rank_max'). Replaces the typed is_top flag. */
    public function isTop($organisationId): bool
    {
        $body = RankingBody::where('short_name', 'NIRF')->value('id');
        $cat = $body ? RankingCategory::where('ranking_body_id', $body)->where('name', 'Overall')->value('id') : null;
        if (!$cat) return false;
        $latest = EntityRanking::where('entity_type', 'organisation')->where('entity_id', (string) $organisationId)->where('ranking_category_id', $cat)
            ->where('verification_status', 'verified')->whereNotNull('ranking_year')->orderByDesc('ranking_year')->first();
        if (!$latest) return false;
        $best = $latest->rank_exact ?? $latest->rank_band_to;
        return $best !== null && $best <= (int) config('quality.top_nirf_overall_rank_max', 50);
    }

    /**
     * ROI label from payback time = total course fee / median annual salary, using the latest VERIFIED placement row of that programme.
     * Returns null when either number is missing. Replaces the typed roi select.
     *
     * @return array{label:string, payback_years:float, academic_year:string}|null
     */
    public function roi(OrganisationCourse $course): ?array
    {
        $fees = (float) $course->total_fees;
        if ($fees <= 0) return null;
        $p = EntityPlacement::where('entity_type', 'course')->where('entity_id', (string) $course->id)->where('verification_status', 'verified')
            ->whereNotNull('median_salary')->where('median_salary', '>', 0)->whereNotNull('academic_year')->orderByDesc('academic_year')->first();
        if (!$p) return null;
        $years = round($fees / $p->median_salary, 2);
        $t = config('quality.roi_payback_years');
        return ['label' => $years <= $t['high'] ? 'High' : ($years <= $t['medium'] ? 'Medium' : 'Low'), 'payback_years' => $years, 'academic_year' => $p->academic_year];
    }

    /** Fully formatted for the admin: "Rs 6.5 LPA". */
    public static function money(?int $inr): string
    {
        if ($inr === null) return '-';
        return $inr >= 100000 ? 'Rs ' . rtrim(rtrim(number_format($inr / 100000, 2), '0'), '.') . ' LPA' : 'Rs ' . number_format($inr);
    }
}
