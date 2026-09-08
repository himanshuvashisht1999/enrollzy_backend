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
        Schema::table('experts', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            $table->dropColumn([
                'email', 'password',
                'highest_qualification', 'domain_certification', 'teaching_credentials', 'industry_licenses',
                'primary_domain', 'sub_specialization', 'years_of_domain_experience', 'academic_vs_industry_expertise',
                'total_counseling_experience', 'no_of_students_counseled', 'counseling_specialization',
                'students_admitted_to_top_university', 'exam_success_rate', 'scholarship_conversion_rate', 'career_placement_outcomes',
                'years_of_industry_experience', 'current_past_employer_quality', 'consulting_advisory_roles', 'live_industry_project_exposure',
                'one_on_one_counseling', 'group_counseling', 'psychometric_based_counseling', 'data_driven_career_mapping', 'goal_oriented_planning',
                'session_modes', 'languages_supported', 'average_wait_time', 'session_duration', 'flexible_scheduling',
                'academic_network_reach', 'industry_connection', 'university_admission_office_access', 'alumni_recruiter_connections',
                'feedback_sentiment_score', 'verified_counseling_reviews', 'repeat_counseling_rate',
                'research_publications', 'patents', 'conference_talks', 'curriculum_design_experience'
            ]);
        });
    }
};
