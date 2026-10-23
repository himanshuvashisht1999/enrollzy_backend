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
                'school_type', 'gender_type', 'religious_affiliation', 'about_school',
                'education_board', 'board_affiliation_number', 'affiliation_valid_upto',
                'medium_of_instruction', 'grade_range', 'streams_offered',
                'result_verification_source',
                'total_teachers', 'trained_teachers_percentage', 'average_teacher_experience_years',
                'student_teacher_ratio', 'teacher_attrition_rate', 'teacher_training_programs',
                'academic_counsellor_available',
                'classroom_type', 'digital_boards', 'teacher_aids', 'average_class_size',
                'science_labs_count', 'computer_labs_count', 'robotics_lab', 'language_lab',
                'library_available', 'library_books_count', 'reading_rooms', 'e_library_access',
                'sports_playgrounds_count', 'indoor_sports', 'outdoor_sports', 'sports_coaches_available',
                'annual_sports_events', 'music_art_facilities', 'clubs_and_societies',
                'inter_school_competitions_participation',
                'cctv_coverage', 'security_guards_count', 'visitor_management_system',
                'medical_room_available', 'nurse_doctor_on_call', 'school_counsellor',
                'fire_safety_certified', 'posco_compliance', 'anti_bullying_policy',
                'hostel_available', 'hostel_type', 'hostel_capacity', 'room_sharing_type',
                'warden_available_24x7', 'food_provided', 'medical_support_in_hostel',
                'leave_policy_defined',
                'transport_available', 'bus_fleet_size', 'gps_enabled_buses', 'routes_covered',
                'nearest_transport_hub',
                'admission_start_month', 'admission_end_month', 'admission_criteria',
                'entrance_test_required', 'interview_required',
                'parent_teacher_meet_frequency', 'parent_app_available', 'sms_email_alerts',
                'homework_tracking_system',
                'integrated_coaching_available', 'competitive_exams_supported', 'coaching_partner_name',
                'foundation_program_classes',
                'pincode', 'google_map_url', 'contact_numbers', 'official_website', 'email',
                'total_reviews', 'rating_sources', 'verified_reviews_only', 'parent_testimonials',
                'student_testimonials', 'awards_and_recognition', 'media_mentions',
                'meta_title', 'meta_description', 'focus_keywords', 'schema_type',
                'canonical_url', 'indexing_status', 'breadcrumb_category',
                'claimed_by_school', 'verification_status', 'data_source', 'confidence_score',
                'ranking_score', 'comparison_visibility', 'listing_type', 'last_updated_on',
                'academic_score', 'faculty_score', 'infrastructure_score', 'safety_score',
                'co_curricular_score', 'value_for_money_score', 'overall_rank_city', 'overall_rank_board'
            ]);
        });
    }
};
