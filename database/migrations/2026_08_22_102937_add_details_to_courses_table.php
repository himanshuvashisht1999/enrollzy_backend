<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'full_form', 'course_type_id', 'available_modes', 'overview',
                'generic_eligibility', 'common_entrance_exams', 'core_curriculum',
                'common_specializations', 'skills_gained', 'career_scope',
                'average_salary_range', 'higher_education_options', 'course_comparison',
                'pros_cons', 'faqs'
            ]);
        });
    }
};
