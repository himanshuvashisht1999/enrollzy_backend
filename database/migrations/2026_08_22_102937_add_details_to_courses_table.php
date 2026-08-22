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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('full_form')->nullable();
            $table->unsignedBigInteger('course_type_id')->nullable();
            $table->json('available_modes')->nullable();
            $table->longText('overview')->nullable();
            $table->longText('generic_eligibility')->nullable();
            $table->json('common_entrance_exams')->nullable();
            $table->longText('core_curriculum')->nullable();
            $table->json('common_specializations')->nullable();
            $table->longText('skills_gained')->nullable();
            $table->longText('career_scope')->nullable();
            $table->string('average_salary_range')->nullable();
            $table->longText('higher_education_options')->nullable();
            $table->longText('course_comparison')->nullable();
            $table->longText('pros_cons')->nullable();
            $table->json('faqs')->nullable();
        });
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
