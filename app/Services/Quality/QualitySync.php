<?php

namespace App\Services\Quality;

use App\Models\Quality\AccreditationBody;
use App\Models\Quality\RankingBody;
use App\Models\Quality\RankingCategory;
use Database\Seeders\QualitySeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Fills in the DATA of the Rankings & Accreditation module on an environment whose tables were created by the SQL file.
 * Works on whatever is in the database at that moment (so a database that has moved on since the SQL was prepared is fine),
 * only ever ADDS missing rows, never overwrites an edit, and can be run again at any time to pick up newer data.
 */
class QualitySync
{
    /** Structure that must exist first (created by the SQL file or the migrations). */
    private const REQUIRED = [
        'tables' => ['organisation_groups', 'ranking_bodies', 'ranking_categories', 'accreditation_bodies', 'entity_rankings', 'entity_accreditations', 'organisation_moves', 'accreditation_approvals', 'permissions', 'roles'],
        'columns' => [
            'organisations' => ['aishe_code', 'nirf_institute_id', 'group_id', 'legal_entity_type', 'ugc_recognition_date'],
            'campuses' => ['campus_role', 'aishe_code', 'nirf_institute_id', 'inherits_org_accreditation'],
            'departments' => ['academic_unit_type', 'nirf_institute_id'],
            'accreditation_approvals' => ['accreditation_body_id', 'grade'],
        ],
    ];

    /** Tables that are missing, as "table" or "table.column". Empty = ready. */
    public function missingStructure(): array
    {
        $missing = [];
        foreach (self::REQUIRED['tables'] as $t) if (!Schema::hasTable($t)) $missing[] = $t;
        foreach (self::REQUIRED['columns'] as $t => $cols) {
            if (!Schema::hasTable($t)) { $missing[] = $t; continue; }
            foreach ($cols as $c) if (!Schema::hasColumn($t, $c)) $missing[] = "$t.$c";
        }
        return array_values(array_unique($missing));
    }

    /** What a run would do right now. Changes nothing. */
    public function preview(): array
    {
        $steps = [];
        $steps[] = ['key' => 'masters', 'title' => 'Starter masters and permissions',
            'detail' => sprintf('Currently %d ranking bodies, %d categories, %d accreditation bodies. Any missing starter entries and the quality-* permissions (admin and superadmin roles) will be added; existing entries are left as they are.',
                RankingBody::count(), RankingCategory::count(), AccreditationBody::count())];

        $steps[] = ['key' => 'identity', 'title' => 'Identity backfill',
            'detail' => sprintf('%d universities will get a legal entity type from their old university type, and %d single-campus organisations will have their campus marked as the main campus. Everything ambiguous is left for the Identity review screen.',
                $this->legalBackfillQuery()->count(), $this->campusRoleQuery()->count())];

        $unlinked = DB::table('accreditation_approvals')->whereNull('accreditation_body_id')->pluck('title')->all();
        $steps[] = ['key' => 'approvals', 'title' => 'Link the old Accreditation & Approval master',
            'detail' => $unlinked ? count($unlinked) . ' old entries will be linked to the new bodies: ' . implode(', ', $unlinked) . '.' : 'Nothing to link: every old entry is already linked.'];

        $legacy = RankingBody::where('short_name', 'NIRF')->exists() ? (new LegacyMigrator)->run(false) : null;
        $steps[] = ['key' => 'legacy', 'title' => 'Copy old NAAC / UGC / AICTE / NIRF values into the new records',
            'detail' => $legacy && $legacy['ok']
                ? "{$legacy['acc_rows']} accreditation and {$legacy['rank_rows']} ranking records will be created (unverified, year unknown). Already copied: {$legacy['counts']['already migrated']}."
                : 'Counted after the masters step has run (the starter masters are needed first).',
            'warnings' => $legacy['warnings'] ?? []];
        return $steps;
    }

    /** Runs every step. Returns one result line per step. */
    public function run(?int $userId = null): array
    {
        $out = [];

        $before = [RankingBody::count(), RankingCategory::count(), AccreditationBody::count()];
        (new QualitySeeder)->run();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $out[] = ['title' => 'Starter masters and permissions', 'result' => sprintf('Added %d ranking bodies, %d categories, %d accreditation bodies (all quality-* permissions present).',
            RankingBody::count() - $before[0], RankingCategory::count() - $before[1], AccreditationBody::count() - $before[2])];

        $n1 = 0;
        foreach (['Central' => 'central', 'Deemed' => 'deemed', 'State' => 'state_public'] as $old => $new) {
            $n1 += DB::table('organisations')->whereNull('legal_entity_type')->where('organisation_type_id', 1)->where('university_type', $old)->update(['legal_entity_type' => $new]);
        }
        $n2 = DB::affectingStatement("UPDATE campuses c JOIN (SELECT organisation_id FROM campuses GROUP BY organisation_id HAVING COUNT(*) = 1) one ON one.organisation_id = c.organisation_id SET c.campus_role = 'main' WHERE c.campus_role IS NULL");
        $out[] = ['title' => 'Identity backfill', 'result' => "$n1 universities given a legal entity type, $n2 campuses marked as main campus."];

        $out[] = ['title' => 'Link the old Accreditation & Approval master', 'result' => $this->linkApprovals()];

        $legacy = (new LegacyMigrator)->run(true);
        $out[] = ['title' => 'Copy old NAAC / UGC / AICTE / NIRF values',
            'result' => $legacy['ok'] ? "Created {$legacy['acc_rows']} accreditation and {$legacy['rank_rows']} ranking records (unverified). Already copied earlier: {$legacy['counts']['already migrated']}."
                . ($legacy['counts']['not a plain rank (left in old column)'] ? " {$legacy['counts']['not a plain rank (left in old column)']} rank value(s) were not plain numbers and stay in the old column." : '')
                : $legacy['error'],
            'warnings' => $legacy['warnings']];

        return $out;
    }

    private function legalBackfillQuery()
    {
        return DB::table('organisations')->whereNull('legal_entity_type')->where('organisation_type_id', 1)->whereIn('university_type', ['Central', 'Deemed', 'State']);
    }

    private function campusRoleQuery()
    {
        return DB::table('campuses as c')->join(DB::raw('(SELECT organisation_id FROM campuses GROUP BY organisation_id HAVING COUNT(*) = 1) one'), 'one.organisation_id', '=', 'c.organisation_id')->whereNull('c.campus_role');
    }

    /** NAAC A++ => NAAC + grade A++, MCI => NMC. Titles that match nothing become new bodies so nothing is lost. Pivot rows become records. */
    private function linkApprovals(): string
    {
        $aliases = ['MCI' => 'NMC'];
        $bodies = DB::table('accreditation_bodies')->get()->keyBy(fn ($b) => strtolower($b->short_name));
        $linked = 0; $created = 0;
        foreach (DB::table('accreditation_approvals')->whereNull('accreditation_body_id')->get() as $old) {
            $title = trim($old->title);
            $grade = null;
            $key = strtolower($title);
            if (preg_match('/^([A-Za-z]+)\s+(A\+\+|A\+|A|B\+\+|B\+|B|C|D)$/', $title, $m) && isset($bodies[strtolower($m[1])]) && $bodies[strtolower($m[1])]->has_grade) {
                $key = strtolower($m[1]); $grade = $m[2];
            }
            $key = strtolower($aliases[strtoupper($key)] ?? $key);
            if (!isset($bodies[$key])) {
                $id = DB::table('accreditation_bodies')->insertGetId([
                    'name' => $title, 'short_name' => mb_substr($title, 0, 40), 'kind' => 'accreditation', 'applicable_levels' => null,
                    'has_grade' => false, 'has_validity' => true, 'status' => (bool) $old->status, 'sort_order' => 100, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $bodies[$key] = DB::table('accreditation_bodies')->find($id);
                $created++;
            }
            DB::table('accreditation_approvals')->where('id', $old->id)->update(['accreditation_body_id' => $bodies[$key]->id, 'grade' => $grade]);
            $linked++;
        }

        $records = 0;
        if (Schema::hasTable('organisation_accreditations')) {
            $links = DB::table('organisation_accreditations as p')->join('accreditation_approvals as a', 'a.id', '=', 'p.accreditation_approval_id')
                ->whereNotNull('a.accreditation_body_id')->get(['p.organisation_id', 'a.accreditation_body_id', 'a.grade']);
            foreach ($links as $l) {
                $exists = DB::table('entity_accreditations')->where('entity_type', 'organisation')->where('entity_id', (string) $l->organisation_id)
                    ->where('accreditation_body_id', $l->accreditation_body_id)->where('legacy_source', 'organisation_accreditations')
                    ->when($l->grade === null, fn ($q) => $q->whereNull('grade'), fn ($q) => $q->where('grade', $l->grade))->exists();
                if (!$exists) {
                    DB::table('entity_accreditations')->insert([
                        'entity_type' => 'organisation', 'entity_id' => (string) $l->organisation_id, 'accreditation_body_id' => $l->accreditation_body_id, 'grade' => $l->grade,
                        'status' => 'active', 'verification_status' => 'unverified', 'legacy_source' => 'organisation_accreditations',
                        'notes' => 'Migrated from the old Accreditation & Approval master. Validity period never recorded.', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $records++;
                }
            }
        }
        return "$linked old entries linked" . ($created ? " ($created new bodies added for titles that matched nothing)" : '') . ", $records organisation links converted to records.";
    }
}
