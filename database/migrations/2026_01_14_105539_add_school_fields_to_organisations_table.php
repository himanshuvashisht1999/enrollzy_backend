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
        Schema::table('organisations', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'school_id',
                'managing_trust_or_society_name', 'minority_status', 'minority_type',
                'education_boards_supported', 'medium_of_instruction_supported', 'international_curriculum_supported',
                'education_levels_supported', 'streams_supported', 'pedagogy_model', 'focus_areas',
                'centralized_curriculum_framework', 'centralized_teacher_training', 'centralized_assessment_policy', 'centralized_lms_available', 'centralized_parent_communication_system',
                'child_safety_policy_available', 'posco_compliance_policy', 'anti_bullying_policy', 'mental_health_policy', 'teacher_background_verification_policy',
                'total_schools_count', 'cities_present_in', 'states_present_in', 'national_presence', 'international_presence', 'flagship_schools',
                'official_website', 'admission_portal_url', 'parent_portal_url', 'student_portal_url', 'mobile_app_available',
                'average_rating', 'total_reviews', 'awards_and_recognition', 'schema_type', 'meta_title', 'meta_description', 'canonical_url',
                'claimed_by_organization', 'verification_status'
            ]);
        });
    }
};
