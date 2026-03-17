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
        Schema::table('alumnis', function (Blueprint $table) {
            // Authentication
            $table->string('email')->unique()->nullable()->after('name');
            $table->string('password')->nullable()->after('email');

            // Alumni Network Size & Density
            $table->string('total_alumni_count')->nullable();
            $table->string('alumni_per_graduation_batch')->nullable();
            $table->string('alumni_growth_rate')->nullable();

            // Alumni Global Presence
            $table->string('active_alumni_countries_count')->nullable();
            $table->json('top_alumni_geographies')->nullable(); // country/city wise
            $table->string('percent_alumni_working_abroad')->nullable();

            // Alumni Career Outcomes
            $table->boolean('placed_in_top_companies')->default(false);
            $table->string('leadership_roles_count')->nullable();
            $table->string('average_salary_bands')->nullable();
            $table->boolean('alumni_in_govt_civil_services')->default(false);

            // Alumni Industry Distribution
            $table->string('tech_industry_percent')->nullable();
            $table->string('finance_industry_percent')->nullable();
            $table->string('healthcare_industry_percent')->nullable();
            $table->string('law_industry_percent')->nullable();
            $table->string('consulting_industry_percent')->nullable();
            $table->string('entrepreneurship_industry_percent')->nullable();
            $table->string('sports_arts_industry_percent')->nullable();

            // Alumni Engagement Level
            $table->boolean('is_mentor')->default(false);
            $table->string('alumni_interaction_frequency')->nullable();
            $table->string('participation_rate')->nullable();
            $table->string('student_mentorship_ratio')->nullable();

            // Alumni Mentorship & Guidance
            $table->boolean('formal_mentorship_available')->default(false);
            $table->boolean('career_guidance_sessions')->default(false);
            $table->boolean('academic_mentoring')->default(false);
            $table->boolean('startup_mentoring')->default(false);

            // Alumni Placement & Referral Effort
            $table->string('alumni_driven_placements_count')->nullable();
            $table->boolean('referral_programs_active')->default(false);
            $table->boolean('internship_support_via_alumni')->default(false);
            $table->boolean('campus_hiring_initiated_by_alumni')->default(false);

            // Alumni Entrepreneurship & Startups
            $table->string('alumni_founded_startups_count')->nullable();
            $table->string('unicorn_funded_startups_count')->nullable();
            $table->string('alumni_investors_angels_count')->nullable();
            $table->boolean('startup_incubators_led_by_alumni')->default(false);

            // Alumni Accessibility
            $table->boolean('directory_access')->default(false);
            $table->boolean('network_platform_available')->default(false);
            $table->boolean('linkedin_integration_active')->default(false);
            $table->boolean('contact_via_portal_active')->default(false);

            // Alumni Contribution & Giving Back
            $table->string('total_alumni_donations')->nullable();
            $table->string('scholarships_funded_by_alumni')->nullable();
            $table->string('infrastructure_funded_by_alumni')->nullable();
            $table->string('endowment_contributions')->nullable();

            // Alumni Network Strength Score
            $table->string('network_strength_score')->nullable(); // Weak/Moderate/Strong/Elite
            $table->string('mentorship_effectiveness_score')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnis', function (Blueprint $table) {
            $table->dropColumn([
                'email', 'password',
                'total_alumni_count', 'alumni_per_graduation_batch', 'alumni_growth_rate',
                'active_alumni_countries_count', 'top_alumni_geographies', 'percent_alumni_working_abroad',
                'placed_in_top_companies', 'leadership_roles_count', 'average_salary_bands', 'alumni_in_govt_civil_services',
                'tech_industry_percent', 'finance_industry_percent', 'healthcare_industry_percent', 'law_industry_percent', 'consulting_industry_percent', 'entrepreneurship_industry_percent', 'sports_arts_industry_percent',
                'is_mentor', 'alumni_interaction_frequency', 'participation_rate', 'student_mentorship_ratio',
                'formal_mentorship_available', 'career_guidance_sessions', 'academic_mentoring', 'startup_mentoring',
                'alumni_driven_placements_count', 'referral_programs_active', 'internship_support_via_alumni', 'campus_hiring_initiated_by_alumni',
                'alumni_founded_startups_count', 'unicorn_funded_startups_count', 'alumni_investors_angels_count', 'startup_incubators_led_by_alumni',
                'directory_access', 'network_platform_available', 'linkedin_integration_active', 'contact_via_portal_active',
                'total_alumni_donations', 'scholarships_funded_by_alumni', 'infrastructure_funded_by_alumni', 'endowment_contributions',
                'network_strength_score', 'mentorship_effectiveness_score'
            ]);
        });
    }
};
