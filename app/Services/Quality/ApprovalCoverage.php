<?php

namespace App\Services\Quality;

use App\Models\Quality\AccreditationBody;
use Illuminate\Support\Facades\DB;

/**
 * Which programmes fall under a regulator (by discipline) and which of those have no current approval.
 * A programme's discipline is its own, else its course master's. Mapping lives on each body.
 * Query builders use the alias `oc` for organisation_courses and `c` for courses.
 */
class ApprovalCoverage
{
    /** Programmes whose effective discipline is one this body governs. */
    public static function scope(AccreditationBody $b)
    {
        return DB::table('organisation_courses as oc')->leftJoin('courses as c', 'c.id', '=', 'oc.course_id')
            ->whereIn(DB::raw('COALESCE(oc.discipline_id, c.discipline_id)'), $b->disciplines()->pluck('disciplines.id')->all());
    }

    /** ...that have no current, non-rejected approval from this body at course level. */
    public static function missing($query, AccreditationBody $b)
    {
        return $query->whereNotExists(function ($q) use ($b) {
            $q->select(DB::raw(1))->from('entity_approvals as e')
                ->where('e.entity_type', 'course')->whereColumn('e.entity_id', 'oc.id')
                ->where('e.accreditation_body_id', $b->id)->whereNull('e.deleted_at')
                ->where('e.verification_status', '!=', 'rejected')->where('e.status', 'active')
                ->where(fn ($w) => $w->whereNull('e.valid_to')->orWhere('e.valid_to', '>=', today()->toDateString()));
        });
    }
}
