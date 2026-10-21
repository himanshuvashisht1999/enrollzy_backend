<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An exam's conducting body is now an organisation of type "Exam Conducting Body" (organisation_type_id 5)
 * instead of a row of the separate exm_conducting_bodies master. The old column and its data are kept untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('exm_exams', 'conducting_organisation_id')) {
            return;
        }
        Schema::table('exm_exams', function (Blueprint $table) {
            $table->foreignId('conducting_organisation_id')->nullable()->after('conducting_body_id')
                ->constrained('organisations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('exm_exams', 'conducting_organisation_id')) {
            return;
        }
        Schema::table('exm_exams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conducting_organisation_id');
        });
    }
};
