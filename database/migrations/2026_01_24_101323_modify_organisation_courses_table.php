<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisation_courses', function (Blueprint $table) {});
    }

    public function down(): void
    {
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->dropColumn(['course_languages', 'total_fees']);

            if (!Schema::hasColumn('organisation_courses', 'established_year')) $table->string('established_year')->nullable();
            if (!Schema::hasColumn('organisation_courses', 'about_academic_unit')) $table->text('about_academic_unit')->nullable();
            if (!Schema::hasColumn('organisation_courses', 'exams_prepared_for')) $table->json('exams_prepared_for')->nullable();
            if (!Schema::hasColumn('organisation_courses', 'courses_offered')) $table->json('courses_offered')->nullable();
            if (!Schema::hasColumn('organisation_courses', 'target_classes')) $table->json('target_classes')->nullable();
        });
    }
};
