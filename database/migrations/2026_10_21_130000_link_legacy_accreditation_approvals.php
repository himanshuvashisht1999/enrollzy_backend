<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retires the old "Accreditation & Approval" master (accreditation_approvals) in favour of accreditation_bodies.
 * Each old title is linked to its new body (NAAC A++ => NAAC + grade A++, MCI => NMC); nothing is deleted.
 * Organisations that were linked through the old pivot become unverified organisation-level records.
 * Re-runnable.
 */
return new class extends Migration
{
    /** Old titles that are not simply a body's short name. */
    private const ALIASES = ['MCI' => 'NMC'];

    public function up(): void
    {
        if (!Schema::hasTable('accreditation_approvals') || !Schema::hasTable('accreditation_bodies')) return;

        if (!Schema::hasColumn('accreditation_approvals', 'accreditation_body_id')) {
            Schema::table('accreditation_approvals', fn (Blueprint $t) => $t->foreignId('accreditation_body_id')->nullable()->constrained('accreditation_bodies')->nullOnDelete());
        }
        if (!Schema::hasColumn('accreditation_approvals', 'grade')) {
            Schema::table('accreditation_approvals', fn (Blueprint $t) => $t->string('grade', 30)->nullable());
        }

        $bodies = DB::table('accreditation_bodies')->get()->keyBy(fn ($b) => strtolower($b->short_name));
        foreach (DB::table('accreditation_approvals')->whereNull('accreditation_body_id')->get() as $old) {
            $title = trim($old->title);
            $grade = null;
            $key = strtolower($title);
            if (preg_match('/^([A-Za-z]+)\s+(A\+\+|A\+|A|B\+\+|B\+|B|C|D)$/', $title, $m) && isset($bodies[strtolower($m[1])]) && $bodies[strtolower($m[1])]->has_grade) {
                $key = strtolower($m[1]); $grade = $m[2];
            }
            $key = strtolower(self::ALIASES[strtoupper($key)] ?? $key);

            if (!isset($bodies[$key])) {
                // Not in the new master yet: add it so nothing from the old list is lost.
                $id = DB::table('accreditation_bodies')->insertGetId([
                    'name' => $title, 'short_name' => mb_substr($title, 0, 40), 'kind' => 'accreditation', 'applicable_levels' => null,
                    'has_grade' => false, 'has_validity' => true, 'status' => (bool) $old->status, 'sort_order' => 100, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $bodies[$key] = DB::table('accreditation_bodies')->find($id);
            }
            DB::table('accreditation_approvals')->where('id', $old->id)->update(['accreditation_body_id' => $bodies[$key]->id, 'grade' => $grade]);
        }

        // Organisations linked through the old pivot become organisation-level, unverified records.
        if (Schema::hasTable('organisation_accreditations') && Schema::hasTable('entity_accreditations')) {
            $links = DB::table('organisation_accreditations as p')->join('accreditation_approvals as a', 'a.id', '=', 'p.accreditation_approval_id')
                ->whereNotNull('a.accreditation_body_id')->get(['p.organisation_id', 'a.accreditation_body_id', 'a.grade']);
            foreach ($links as $l) {
                $exists = DB::table('entity_accreditations')->where('entity_type', 'organisation')->where('entity_id', (string) $l->organisation_id)
                    ->where('accreditation_body_id', $l->accreditation_body_id)->where('legacy_source', 'organisation_accreditations')->where('grade', $l->grade)->exists();
                if (!$exists) {
                    DB::table('entity_accreditations')->insert([
                        'entity_type' => 'organisation', 'entity_id' => (string) $l->organisation_id, 'accreditation_body_id' => $l->accreditation_body_id, 'grade' => $l->grade,
                        'status' => 'active', 'verification_status' => 'unverified', 'legacy_source' => 'organisation_accreditations',
                        'notes' => 'Migrated from the old Accreditation & Approval master. Validity period never recorded.', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('accreditation_approvals')) {
            if (Schema::hasColumn('accreditation_approvals', 'accreditation_body_id')) {
                Schema::table('accreditation_approvals', function (Blueprint $t) { $t->dropForeign(['accreditation_body_id']); $t->dropColumn('accreditation_body_id'); });
            }
            if (Schema::hasColumn('accreditation_approvals', 'grade')) {
                Schema::table('accreditation_approvals', fn (Blueprint $t) => $t->dropColumn('grade'));
            }
        }
    }
};
