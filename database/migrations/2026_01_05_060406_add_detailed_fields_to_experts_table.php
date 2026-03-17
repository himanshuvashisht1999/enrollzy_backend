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
        Schema::table('experts', function (Blueprint $table) {
            // Authentication
            $table->string('email')->unique()->nullable()->after('name');
            $table->string('password')->nullable()->after('email');

            // Qualification & Credentials
            $table->string('highest_qualification')->nullable();
            $table->string('domain_certification')->nullable();
            $table->string('teaching_credentials')->nullable();
            $table->string('industry_licenses')->nullable();

            // Domain Expertise
            $table->string('primary_domain')->nullable();
            $table->string('sub_specialization')->nullable();
            $table->string('years_of_domain_experience')->nullable();
            $table->string('academic_vs_industry_expertise')->nullable();

            // Experience in Student Counseling
            $table->string('total_counseling_experience')->nullable();
            $table->string('no_of_students_counseled')->nullable();
            $table->json('counseling_specialization')->nullable(); // JSON for PG, UG, Study Abroad, Competitive Exams

            // Success Outcomes
            $table->text('students_admitted_to_top_university')->nullable();
            $table->string('exam_success_rate')->nullable();
            $table->string('scholarship_conversion_rate')->nullable();
            $table->text('career_placement_outcomes')->nullable();

            // Industry Experience
            $table->string('years_of_industry_experience')->nullable();
            $table->string('current_past_employer_quality')->nullable();
            $table->text('consulting_advisory_roles')->nullable();
            $table->text('live_industry_project_exposure')->nullable();

            // Counseling Approach
            $table->boolean('one_on_one_counseling')->default(false);
            $table->boolean('group_counseling')->default(false);
            $table->boolean('psychometric_based_counseling')->default(false);
            $table->boolean('data_driven_career_mapping')->default(false);
            $table->boolean('goal_oriented_planning')->default(false);

            // Availability & Accessibility
            $table->json('session_modes')->nullable(); // JSON for Online, Offline, etc.
            $table->json('languages_supported')->nullable();
            $table->string('average_wait_time')->nullable();
            $table->string('session_duration')->nullable();
            $table->boolean('flexible_scheduling')->default(false);

            // Network Strength
            $table->string('academic_network_reach')->nullable();
            $table->string('industry_connection')->nullable();
            $table->string('university_admission_office_access')->nullable();
            $table->string('alumni_recruiter_connections')->nullable();

            // Feedback & Student Ratings
            $table->string('feedback_sentiment_score')->nullable();
            $table->string('verified_counseling_reviews')->nullable();
            $table->string('repeat_counseling_rate')->nullable();

            // Research & Academic Knowledge
            $table->text('research_publications')->nullable();
            $table->text('patents')->nullable();
            $table->text('conference_talks')->nullable();
            $table->text('curriculum_design_experience')->nullable();
        });
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
