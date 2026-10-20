<?php

namespace App\Services\Quality;

use App\Models\Organisation;
use App\Models\Quality\EntityAccreditation;
use App\Models\Quality\EntityRanking;
use Illuminate\Support\Facades\DB;

/**
 * Splits a multi-university brand: moves chosen campuses (with their departments and programmes) from one organisation
 * to another. Nothing is copied or deleted, only organisation_id changes, and the exact ids moved are logged so the move can be undone.
 *
 * What does NOT move: organisation-level records (documents, awards, organisation-level rankings and accreditations) stay with the source
 * - they describe the legal entity, not the campus. Billing invoices pin a campus to an organisation, so a move that touches one is refused.
 */
class OrgMover
{
    /** Tables that carry organisation_id alongside a campus_id, and move with the campus. [table, campus column] */
    private const BY_CAMPUS = [
        'departments' => 'campus_id',
        'organisation_courses' => 'campus_id',
        'organisation_school_courses' => 'campus_id',
        'faculty_department_mapping' => 'campus_id',
    ];

    /** @param string[] $campusIds */
    public function preview(Organisation $from, ?Organisation $to, array $campusIds): array
    {
        $campusIds = array_values(array_unique($campusIds));
        $blockers = [];
        $own = DB::table('campuses')->where('organisation_id', $from->id)->whereIn('id', $campusIds)->pluck('id')->all();
        if (count($own) !== count($campusIds)) $blockers[] = 'Some selected campuses do not belong to ' . $from->name . '.';
        if (!$campusIds) $blockers[] = 'Select at least one campus.';
        if ($to && $to->id === $from->id) $blockers[] = 'Source and target are the same organisation.';

        $counts = ['campuses' => count($own)];
        foreach (self::BY_CAMPUS as $table => $col) {
            $counts[$table] = $own ? DB::table($table)->whereIn($col, $own)->where('organisation_id', $from->id)->count() : 0;
        }

        $invoices = $own ? DB::table('billing_invoices')->whereIn('campus_id', $own)->count() : 0;
        if ($invoices) $blockers[] = "$invoices billing invoice(s) reference these campuses. Billing history must not change under an invoice, so move these by hand.";

        // A programme in a moved campus must not sit under a department that stays behind (or the reverse).
        $split = $own ? DB::table('organisation_courses as oc')->join('departments as d', 'd.id', '=', 'oc.department_id')
            ->whereIn('oc.campus_id', $own)->whereColumn('d.campus_id', '!=', 'oc.campus_id')->count() : 0;
        if ($split) $blockers[] = "$split programme(s) sit in a department of a different campus. Fix those links first.";

        $remaining = DB::table('campuses')->where('organisation_id', $from->id)->count() - count($own);
        $warnings = [];
        if ($remaining === 0 && $own) $warnings[] = $from->name . ' would be left with no campuses.';
        $orgRecords = EntityAccreditation::forEntity('organisation', $from->id)->count() + EntityRanking::forEntity('organisation', $from->id)->count();
        if ($orgRecords) $warnings[] = "$orgRecords ranking / accreditation record(s) are recorded on {$from->name} itself and stay there. Re-record any that belong to the target legal entity.";

        return ['campus_ids' => $own, 'counts' => $counts, 'blockers' => $blockers, 'warnings' => $warnings];
    }

    /** @return int the organisation_moves id */
    public function apply(Organisation $from, Organisation $to, array $campusIds, ?int $userId, bool $createdTarget = false, ?string $note = null): int
    {
        $p = $this->preview($from, $to, $campusIds);
        if ($p['blockers']) throw new \RuntimeException(implode(' ', $p['blockers']));
        $own = $p['campus_ids'];

        return DB::transaction(function () use ($from, $to, $own, $userId, $createdTarget, $note) {
            $moved = ['campuses' => $own];
            foreach (self::BY_CAMPUS as $table => $col) {
                $moved[$table] = DB::table($table)->whereIn($col, $own)->where('organisation_id', $from->id)->pluck('id')->all();
            }
            DB::table('campuses')->whereIn('id', $own)->update(['organisation_id' => $to->id]);
            foreach (self::BY_CAMPUS as $table => $col) {
                foreach (array_chunk($moved[$table], 1000) as $chunk) DB::table($table)->whereIn('id', $chunk)->update(['organisation_id' => $to->id]);
            }
            return DB::table('organisation_moves')->insertGetId([
                'from_organisation_id' => $from->id, 'to_organisation_id' => $to->id, 'created_target' => $createdTarget,
                'moved' => json_encode($moved), 'status' => 'applied', 'note' => $note, 'created_by' => $userId, 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    /** Puts exactly the moved rows back. Refused if any of them has since been moved again. */
    public function undo(int $moveId): void
    {
        $move = DB::table('organisation_moves')->find($moveId) ?? throw new \RuntimeException('Move not found.');
        if ($move->status !== 'applied') throw new \RuntimeException('This move was already undone.');
        $moved = json_decode($move->moved, true);

        $later = DB::table('organisation_moves')->where('id', '>', $move->id)->where('status', 'applied')->get()
            ->contains(fn ($m) => array_intersect($moved['campuses'], json_decode($m->moved, true)['campuses'] ?? []));
        if ($later) throw new \RuntimeException('A later move touched some of these campuses. Undo that one first.');
        $stray = DB::table('campuses')->whereIn('id', $moved['campuses'])->where('organisation_id', '!=', $move->to_organisation_id)->count();
        if ($stray) throw new \RuntimeException("$stray campus(es) are no longer under the target organisation, so this move cannot be undone safely.");

        DB::transaction(function () use ($move, $moved) {
            DB::table('campuses')->whereIn('id', $moved['campuses'])->update(['organisation_id' => $move->from_organisation_id]);
            foreach (self::BY_CAMPUS as $table => $col) {
                foreach (array_chunk($moved[$table] ?? [], 1000) as $chunk) DB::table($table)->whereIn('id', $chunk)->update(['organisation_id' => $move->from_organisation_id]);
            }
            DB::table('organisation_moves')->where('id', $move->id)->update(['status' => 'undone', 'undone_at' => now(), 'updated_at' => now()]);
        });
    }
}
