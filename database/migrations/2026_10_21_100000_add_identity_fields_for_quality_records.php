<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Step 1 of the Ranking & Accreditation review: fix ENTITY IDENTITY first, because every later record
 * (NIRF, NAAC, approvals...) must attach to the right legal entity.
 *
 * Re-runnable: MySQL DDL is not transactional and this Laravel adds each column as its own statement, so a
 * failure part-way leaves some columns behind. Every column is therefore checked individually.
 * Nothing is removed or renamed: legacy columns (university_type, nirf_rank_overall...) stay as they are.
 *
 * Note: `organisations` is at MySQL's row-size limit (189 columns), so long values are TEXT, not VARCHAR.
 */
return new class extends Migration
{
    public const LEGAL_TYPES = ['central', 'state_public', 'state_private', 'deemed', 'institution_of_national_importance', 'autonomous_college', 'affiliated_college'];

    /** Add one column unless it exists: $define receives the table blueprint. */
    private function add(string $table, string $column, callable $define): void
    {
        if (!Schema::hasColumn($table, $column)) {
            Schema::table($table, fn (Blueprint $t) => $define($t));
        }
    }

    public function up(): void
    {
        // Groups legally separate universities under one brand (Amity, SRM...) WITHOUT merging them.
        if (!Schema::hasTable('organisation_groups')) {
            Schema::create('organisation_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('website')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // ---- organisations ----
        $this->add('organisations', 'aishe_code', fn ($t) => $t->string('aishe_code', 20)->nullable()->unique());      // official HEI identifier
        $this->add('organisations', 'nirf_institute_id', fn ($t) => $t->string('nirf_institute_id', 32)->nullable()->unique());   // matches NIRF results to the right entity
        $this->add('organisations', 'group_id', fn ($t) => $t->foreignId('group_id')->nullable()->constrained('organisation_groups')->nullOnDelete());
        $this->add('organisations', 'affiliating_organisation_id', fn ($t) => $t->unsignedBigInteger('affiliating_organisation_id')->nullable()->index());   // affiliated college -> its university
        $this->add('organisations', 'legal_entity_type', fn ($t) => $t->enum('legal_entity_type', self::LEGAL_TYPES)->nullable());   // replaces university_type / category / autonomous_status
        $this->add('organisations', 'ugc_recognition_date', fn ($t) => $t->date('ugc_recognition_date')->nullable());
        $this->add('organisations', 'deemed_status_notification_ref', fn ($t) => $t->text('deemed_status_notification_ref')->nullable());
        $this->add('organisations', 'identity_source_url', fn ($t) => $t->text('identity_source_url')->nullable());
        $this->add('organisations', 'identity_verified_at', fn ($t) => $t->dateTime('identity_verified_at')->nullable());
        $this->add('organisations', 'identity_verified_by', fn ($t) => $t->unsignedBigInteger('identity_verified_by')->nullable());

        // ---- campuses ----
        $this->add('campuses', 'campus_role', fn ($t) => $t->enum('campus_role', ['main', 'off_campus', 'constituent_college', 'study_centre'])->nullable());   // decides inherited vs separate rankings
        $this->add('campuses', 'aishe_code', fn ($t) => $t->string('aishe_code', 20)->nullable()->unique());
        $this->add('campuses', 'nirf_institute_id', fn ($t) => $t->string('nirf_institute_id', 32)->nullable()->unique());
        $this->add('campuses', 'board_affiliation_number', fn ($t) => $t->string('board_affiliation_number', 60)->nullable());   // school campuses (CBSE / CISCE / State)
        $this->add('campuses', 'affiliation_valid_to', fn ($t) => $t->date('affiliation_valid_to')->nullable());
        $this->add('campuses', 'inherits_org_accreditation', fn ($t) => $t->boolean('inherits_org_accreditation')->default(true));
        $this->add('campuses', 'identity_source_url', fn ($t) => $t->text('identity_source_url')->nullable());
        $this->add('campuses', 'identity_verified_at', fn ($t) => $t->dateTime('identity_verified_at')->nullable());
        $this->add('campuses', 'identity_verified_by', fn ($t) => $t->unsignedBigInteger('identity_verified_by')->nullable());

        // ---- departments ----
        // AACSB / EQUIS and NIRF category ranks attach to SCHOOLS, not to every department.
        $this->add('departments', 'academic_unit_type', fn ($t) => $t->enum('academic_unit_type', ['department', 'school', 'faculty', 'centre'])->default('department'));
        $this->add('departments', 'nirf_institute_id', fn ($t) => $t->string('nirf_institute_id', 32)->nullable()->unique());   // when a school is ranked as its own institute

        // ---- Backfills: only where the old value is unambiguous. Everything else goes to the identity review screen. ----
        $map = ['Central' => 'central', 'Deemed' => 'deemed', 'State' => 'state_public'];
        foreach ($map as $old => $new) {
            DB::table('organisations')->whereNull('legal_entity_type')->where('organisation_type_id', 1)->where('university_type', $old)->update(['legal_entity_type' => $new]);
        }

        // An organisation with exactly one campus has that campus as its main one.
        DB::statement("UPDATE campuses c JOIN (SELECT organisation_id FROM campuses GROUP BY organisation_id HAVING COUNT(*) = 1) one ON one.organisation_id = c.organisation_id
                       SET c.campus_role = 'main' WHERE c.campus_role IS NULL");
    }

    public function down(): void
    {
        $drop = function (string $table, array $cols, array $uniques = [], array $foreign = []) {
            Schema::table($table, function (Blueprint $t) use ($table, $cols, $uniques, $foreign) {
                foreach ($foreign as $f) { if (Schema::hasColumn($table, $f)) $t->dropForeign([$f]); }
                foreach ($uniques as $u) { if (Schema::hasColumn($table, $u)) $t->dropUnique([$u]); }
                $t->dropColumn(array_values(array_filter($cols, fn ($c) => Schema::hasColumn($table, $c))));
            });
        };
        $drop('departments', ['academic_unit_type', 'nirf_institute_id'], ['nirf_institute_id']);
        $drop('campuses', ['campus_role', 'aishe_code', 'nirf_institute_id', 'board_affiliation_number', 'affiliation_valid_to', 'inherits_org_accreditation',
            'identity_source_url', 'identity_verified_at', 'identity_verified_by'], ['aishe_code', 'nirf_institute_id']);
        $drop('organisations', ['aishe_code', 'nirf_institute_id', 'group_id', 'affiliating_organisation_id', 'legal_entity_type', 'ugc_recognition_date',
            'deemed_status_notification_ref', 'identity_source_url', 'identity_verified_at', 'identity_verified_by'], ['aishe_code', 'nirf_institute_id'], ['group_id']);
        Schema::dropIfExists('organisation_groups');
    }
};
