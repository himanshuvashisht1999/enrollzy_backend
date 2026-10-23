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
        Schema::table('alumnis', function (Blueprint $table) {});
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
