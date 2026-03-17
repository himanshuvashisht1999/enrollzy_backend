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
            // 1. Basic Identity
            $table->string('school_type')->nullable(); // Day, Boarding, etc.
            $table->string('gender_type')->nullable(); // Boys, Girls, Co-Ed
            $table->string('religious_affiliation')->nullable();
            $table->longText('about_school')->nullable();
            
            // 2. Board & Affiliation (JSON for arrays)
            $table->json('education_board')->nullable(); // CBSE, ICSE etc.
            $table->string('board_affiliation_number')->nullable();
            $table->date('affiliation_valid_upto')->nullable();
            $table->json('medium_of_instruction')->nullable(); // English, Hindi etc.
            $table->string('grade_range')->nullable(); // Pre-Primary to XII
            $table->json('streams_offered')->nullable(); // Science, Commerce etc.
            
            // 3. Academic Performance (Results in separate table)
            $table->string('result_verification_source')->nullable();
            
            // 4. Faculty
            $table->integer('total_teachers')->nullable();
            $table->integer('trained_teachers_percentage')->nullable();
            $table->integer('average_teacher_experience_years')->nullable();
            $table->string('student_teacher_ratio')->nullable();
            $table->integer('teacher_attrition_rate')->nullable(); // Percentage
            $table->text('teacher_training_programs')->nullable();
            $table->boolean('academic_counsellor_available')->default(false);
            
            // 5. Classroom & Infra
            $table->string('classroom_type')->nullable(); // AC, Non-AC etc.
            $table->boolean('digital_boards')->default(false);
            $table->json('teacher_aids')->nullable(); // Projectors, LMS etc.
            $table->integer('average_class_size')->nullable();
            
            // 6. Labs & Library
            $table->integer('science_labs_count')->nullable();
            $table->integer('computer_labs_count')->nullable();
            $table->boolean('robotics_lab')->default(false);
            $table->boolean('language_lab')->default(false);
            $table->boolean('library_available')->default(false);
            $table->integer('library_books_count')->nullable();
            $table->boolean('reading_rooms')->default(false);
            $table->boolean('e_library_access')->default(false);
            
            // 7. Sports & Co-Curricular
            $table->integer('sports_playgrounds_count')->nullable();
            $table->json('indoor_sports')->nullable();
            $table->json('outdoor_sports')->nullable();
            $table->boolean('sports_coaches_available')->default(false);
            $table->text('annual_sports_events')->nullable();
            $table->json('music_art_facilities')->nullable();
            $table->json('clubs_and_societies')->nullable();
            $table->text('inter_school_competitions_participation')->nullable();
            
            // 8. Safety & Health
            $table->boolean('cctv_coverage')->default(false);
            $table->integer('security_guards_count')->nullable();
            $table->boolean('visitor_management_system')->default(false);
            $table->boolean('medical_room_available')->default(false);
            $table->boolean('nurse_doctor_on_call')->default(false);
            $table->boolean('school_counsellor')->default(false);
            $table->boolean('fire_safety_certified')->default(false);
            $table->boolean('posco_compliance')->default(false);
            $table->boolean('anti_bullying_policy')->default(false);
            
            // 9. Hostel (If applicable)
            $table->boolean('hostel_available')->default(false);
            $table->string('hostel_type')->nullable(); // Boys, Girls, Both
            $table->integer('hostel_capacity')->nullable();
            $table->string('room_sharing_type')->nullable();
            $table->boolean('warden_available_24x7')->default(false);
            $table->string('food_provided')->nullable(); // Veg, Non-Veg
            $table->text('medical_support_in_hostel')->nullable();
            $table->boolean('leave_policy_defined')->default(false);
            
            // 10. Transport
            $table->boolean('transport_available')->default(false);
            $table->integer('bus_fleet_size')->nullable();
            $table->boolean('gps_enabled_buses')->default(false);
            $table->text('routes_covered')->nullable();
            $table->string('nearest_transport_hub')->nullable();
            
            // 11. Admissions
            $table->string('admission_start_month')->nullable();
            $table->string('admission_end_month')->nullable();
            $table->longText('admission_criteria')->nullable();
            $table->boolean('entrance_test_required')->default(false);
            $table->boolean('interview_required')->default(false);
            
            // 12. Parent Communication
            $table->string('parent_teacher_meet_frequency')->nullable();
            $table->boolean('parent_app_available')->default(false);
            $table->boolean('sms_email_alerts')->default(false);
            $table->boolean('homework_tracking_system')->default(false);
            
            // 13. Integrated Prep
            $table->boolean('integrated_coaching_available')->default(false);
            $table->json('competitive_exams_supported')->nullable(); // NEET, JEE etc.
            $table->string('coaching_partner_name')->nullable();
            $table->string('foundation_program_classes')->nullable();
            
            // 14. Location & Contact (Using existing address fields too)
            // campus_id, full_address covered by existing fields
            $table->string('pincode')->nullable();
            $table->string('google_map_url')->nullable();
            $table->json('contact_numbers')->nullable();
            $table->string('official_website')->nullable(); // distinct from existing? existing has no website field
            $table->string('email')->nullable(); // existing has no email field
            
            // 15. Reviews & Trust
            $table->integer('total_reviews')->default(0);
            $table->json('rating_sources')->nullable();
            $table->boolean('verified_reviews_only')->default(false);
            $table->json('parent_testimonials')->nullable(); // Simple text or IDs?
            $table->json('student_testimonials')->nullable();
            $table->json('awards_and_recognition')->nullable(); // separate from relation?
            $table->json('media_mentions')->nullable();
            
            // 16. SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('focus_keywords')->nullable();
            $table->string('schema_type')->default('School');
            $table->string('canonical_url')->nullable();
            $table->string('indexing_status')->default('index, follow');
            $table->string('breadcrumb_category')->nullable();
            
            // 17. Admin Control
            $table->boolean('claimed_by_school')->default(false);
            $table->string('verification_status')->default('Pending');
            $table->string('data_source')->nullable();
            $table->integer('confidence_score')->default(0);
            $table->integer('ranking_score')->default(0);
            $table->boolean('comparison_visibility')->default(true);
            $table->string('listing_type')->default('Free');
            $table->timestamp('last_updated_on')->nullable();
            
            // 18. Derived Scores
            $table->decimal('academic_score', 5, 2)->nullable();
            $table->decimal('faculty_score', 5, 2)->nullable();
            $table->decimal('infrastructure_score', 5, 2)->nullable();
            $table->decimal('safety_score', 5, 2)->nullable();
            $table->decimal('co_curricular_score', 5, 2)->nullable();
            $table->decimal('value_for_money_score', 5, 2)->nullable();
            $table->integer('overall_rank_city')->nullable();
            $table->integer('overall_rank_board')->nullable();
        });
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
