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
        Schema::table('organisation_courses', function (Blueprint $table) {
            // Remove redundant fields
            $table->dropColumn([
                'established_year',
                'about_academic_unit',
                'exams_prepared_for',
                'courses_offered',
                'target_classes'
            ]);

            // Add new fields
            $table->json('course_languages')->nullable()->after('language_id');
            $table->decimal('total_fees', 10, 2)->nullable()->after('course_languages');
        });
    }

    public function down(): void
    {
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->dropColumn(['course_languages', 'total_fees']);

            $table->string('established_year')->nullable();
            $table->text('about_academic_unit')->nullable();
            $table->json('exams_prepared_for')->nullable();
            $table->json('courses_offered')->nullable();
            $table->json('target_classes')->nullable();
        });
    }
};
