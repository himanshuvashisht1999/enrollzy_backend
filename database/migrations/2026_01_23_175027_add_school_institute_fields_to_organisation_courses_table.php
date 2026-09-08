<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisation_courses', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->dropColumn([
                'academic_unit_name',
                'slug',
                'school_type',
                'established_year',
                'about_academic_unit',
                'education_board',
                'board_affiliation_number',
                'affiliation_valid_from',
                'affiliation_valid_to',
                'medium_of_instruction',
                'grade_range',
                'streams_offered',
                'student_strength',
                'total_teachers',
                'trained_teachers_percentage',
                'student_teacher_ratio',
                'special_educator_available',
                'school_counsellor_available',
                'average_class_size',
                'assessment_pattern',
                'homework_policy',
                'parent_teacher_meet_frequency',
                'remedial_classes_available',
                'board_result_classes',
                'average_board_result_percentage',
                'highest_score',
                'distinction_percentage',
                'olympiad_participation',
                'competitive_exam_preparation_support',
                'annual_fee_range',
                'admission_fee',
                'transport_fee',
                'hostel_fee',
                'fee_payment_frequency',
                'parent_app_available',
                'attendance_tracking_available',
                'sports_offered',
                'arts_music_programs_available',
                'clubs_and_societies',
                'annual_events',
                'delivery_mode',
                'exams_prepared_for',
                'target_classes',
                'courses_offered',
                'integrated_schooling_available',
                'total_batches',
                'average_batch_size',
                'min_batch_size',
                'max_batch_size',
                'separate_batches_for_droppers',
                'merit_based_batching',
                'total_faculty_count',
                'senior_faculty_count',
                'average_faculty_experience_years',
                'full_time_faculty_percentage',
                'visiting_faculty_available',
                'doubt_solving_mode',
                'personal_mentorship_available',
                'extra_classes_for_weak_students',
                'parent_counselling_available',
                'study_material_type',
                'dpp_provided',
                'test_series_available',
                'tests_per_month',
                'full_syllabus_tests_count',
                'online_test_platform_available',
                'results_years_available',
                'total_selections_all_time',
                'selections_last_year',
                'highest_rank_achieved',
                'average_selection_rate',
                'result_verification_status',
                'average_course_fee_range',
                'installment_available',
                'scholarship_available',
                'refund_policy_available',
                'verified_reviews_only',
                'meta_title',
                'meta_description'
            ]);
        });
    }
};
