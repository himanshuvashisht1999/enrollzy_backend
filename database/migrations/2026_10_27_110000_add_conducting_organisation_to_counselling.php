<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A counselling's conducting body is now an organisation of type "Counselling Body" (organisation_type_id 6) instead of
 * a row of the separate exm_conducting_bodies master. Institute codes (a college's code at that body) follow it.
 * The old conducting_body_id columns and their data are kept; they only become optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['cns_counsellings', 'cns_institute_codes'] as $table) {
            DB::statement("ALTER TABLE `$table` MODIFY `conducting_body_id` BIGINT UNSIGNED NULL");
            if (!Schema::hasColumn($table, 'conducting_organisation_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->foreignId('conducting_organisation_id')->nullable()->after('conducting_body_id')
                        ->constrained('organisations')->{$table === 'cns_institute_codes' ? 'cascadeOnDelete' : 'nullOnDelete'}();
                });
            }
        }
        Schema::table('cns_institute_codes', function (Blueprint $t) {
            $t->unique(['conducting_organisation_id', 'code'], 'cns_institute_codes_org_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cns_institute_codes', fn (Blueprint $t) => $t->dropUnique('cns_institute_codes_org_code_unique'));
        foreach (['cns_counsellings', 'cns_institute_codes'] as $table) {
            if (Schema::hasColumn($table, 'conducting_organisation_id')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropConstrainedForeignId('conducting_organisation_id'));
            }
        }
    }
};
