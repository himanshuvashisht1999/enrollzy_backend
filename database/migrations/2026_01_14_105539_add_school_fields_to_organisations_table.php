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
        Schema::table('organisations', function (Blueprint $table) {
            // School Specific Fields (Type 4)

            // A. Core Identity (distinct ID)
            $table->uuid('school_id')->nullable();
            
            // B. Ownership & Legal
            // Note: reusing ownership_type, registered_entity_name, registration_number, legal_documents_urls
            $table->string('managing_trust_or_society_name')->nullable();
            $table->boolean('minority_status')->nullable();
            $table->string('minority_type')->nullable();

            // C. Board Scope
            $table->json('education_boards_supported')->nullable(); // CBSE, ICSE, etc.
            $table->json('medium_of_instruction_supported')->nullable();
            $table->boolean('international_curriculum_supported')->nullable();

            // D. Academic Philosophy
            $table->json('education_levels_supported')->nullable();
            $table->json('streams_supported')->nullable();
            $table->string('pedagogy_model')->nullable();
            $table->json('focus_areas')->nullable();

            // E. Central Policies & Systems
            $table->boolean('centralized_curriculum_framework')->nullable();
            $table->boolean('centralized_teacher_training')->nullable();
            $table->boolean('centralized_assessment_policy')->nullable();
            $table->boolean('centralized_lms_available')->nullable();
            $table->boolean('centralized_parent_communication_system')->nullable();

            // F. Safety Policies
            $table->boolean('child_safety_policy_available')->nullable();
            $table->boolean('posco_compliance_policy')->nullable();
            $table->boolean('anti_bullying_policy')->nullable();
            $table->boolean('mental_health_policy')->nullable();
            $table->boolean('teacher_background_verification_policy')->nullable();

            // G. Brand Footprint
            $table->integer('total_schools_count')->nullable();
            $table->json('cities_present_in')->nullable();
            $table->json('states_present_in')->nullable();
            $table->boolean('national_presence')->nullable();
            $table->boolean('international_presence')->nullable();
            $table->json('flagship_schools')->nullable();

            // H. Digital Presence
            $table->string('official_website')->nullable();
            $table->string('admission_portal_url')->nullable();
            $table->string('parent_portal_url')->nullable();
            $table->string('student_portal_url')->nullable();
            $table->boolean('mobile_app_available')->nullable();

            // I. Trust, SEO & Admin
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('total_reviews')->nullable();
            $table->json('awards_and_recognition')->nullable();
            $table->string('schema_type')->nullable(); // e.g. "School"
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('claimed_by_organization')->nullable();
            $table->string('verification_status')->default('Pending'); // Verified, Pending, Rejected
        });
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
