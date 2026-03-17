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
        Schema::table('organisation_courses', function (Blueprint $table) {
            // Core Identity (Shared/School/Institute)
            $table->string('academic_unit_name')->nullable()->after('course_id');
            $table->string('slug')->nullable()->after('academic_unit_name');

            // School Specific
            $table->string('school_type')->nullable(); // Day School, Boarding, etc.
            $table->string('established_year')->nullable();
            $table->text('about_academic_unit')->nullable(); // Shared for School/Institute

            $table->string('education_board')->nullable();
            $table->string('board_affiliation_number')->nullable();
            $table->date('affiliation_valid_from')->nullable();
            $table->date('affiliation_valid_to')->nullable();
            $table->string('medium_of_instruction')->nullable();
            $table->string('grade_range')->nullable();

            $table->json('streams_offered')->nullable(); // JSON
            $table->string('student_strength')->nullable();
            $table->string('total_teachers')->nullable();
            $table->string('trained_teachers_percentage')->nullable();
            $table->string('student_teacher_ratio')->nullable();

            $table->boolean('special_educator_available')->default(false);
            $table->boolean('school_counsellor_available')->default(false);

            $table->string('average_class_size')->nullable();
            $table->string('assessment_pattern')->nullable();
            $table->string('homework_policy')->nullable();
            $table->string('parent_teacher_meet_frequency')->nullable();
            $table->boolean('remedial_classes_available')->default(false);

            $table->json('board_result_classes')->nullable(); // JSON
            $table->string('average_board_result_percentage')->nullable();
            $table->string('highest_score')->nullable();
            $table->string('distinction_percentage')->nullable();
            $table->boolean('olympiad_participation')->default(false);
            $table->boolean('competitive_exam_preparation_support')->default(false);

            $table->string('annual_fee_range')->nullable();
            $table->string('admission_fee')->nullable();
            $table->string('transport_fee')->nullable();
            $table->string('hostel_fee')->nullable();
            $table->string('fee_payment_frequency')->nullable();
            $table->boolean('parent_app_available')->default(false);
            $table->boolean('attendance_tracking_available')->default(false);

            $table->json('sports_offered')->nullable(); // JSON
            $table->boolean('arts_music_programs_available')->default(false);
            $table->json('clubs_and_societies')->nullable(); // JSON
            $table->json('annual_events')->nullable(); // JSON

            // Institute Specific
            $table->string('delivery_mode')->nullable(); // Offline, Online, Hybrid
            $table->json('exams_prepared_for')->nullable(); // JSON
            $table->json('target_classes')->nullable(); // JSON
            $table->json('courses_offered')->nullable(); // JSON (Course IDs)

            $table->boolean('integrated_schooling_available')->default(false);
            $table->integer('total_batches')->nullable();
            $table->integer('average_batch_size')->nullable();
            $table->integer('min_batch_size')->nullable();
            $table->integer('max_batch_size')->nullable();
            $table->boolean('separate_batches_for_droppers')->default(false);
            $table->boolean('merit_based_batching')->default(false);

            $table->integer('total_faculty_count')->nullable();
            $table->integer('senior_faculty_count')->nullable();
            $table->integer('average_faculty_experience_years')->nullable();
            $table->string('full_time_faculty_percentage')->nullable();
            $table->boolean('visiting_faculty_available')->default(false);

            $table->string('doubt_solving_mode')->nullable();
            $table->boolean('personal_mentorship_available')->default(false);
            $table->boolean('extra_classes_for_weak_students')->default(false);
            $table->boolean('parent_counselling_available')->default(false);

            $table->string('study_material_type')->nullable(); // Printed, Digital
            $table->boolean('dpp_provided')->default(false);
            $table->boolean('test_series_available')->default(false);
            $table->integer('tests_per_month')->nullable();
            $table->integer('full_syllabus_tests_count')->nullable();
            $table->boolean('online_test_platform_available')->default(false);

            $table->json('results_years_available')->nullable(); // JSON
            $table->string('total_selections_all_time')->nullable();
            $table->string('selections_last_year')->nullable();
            $table->string('highest_rank_achieved')->nullable();
            $table->string('average_selection_rate')->nullable();
            $table->string('result_verification_status')->nullable();

            $table->string('average_course_fee_range')->nullable();
            $table->boolean('installment_available')->default(false);
            $table->boolean('scholarship_available')->default(false);
            $table->boolean('refund_policy_available')->default(false);

            // Common Admin
            $table->boolean('verified_reviews_only')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
        });
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
