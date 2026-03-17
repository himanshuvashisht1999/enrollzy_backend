<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationCourse extends Model
{
    protected $fillable = [
        'organisation_id',
        'course_id',
        'mode',
        'fees',
        'duration',
        'status',
        'sort_order',
        // New Fields
        'admission_process',
        'provisional_admission',
        'eligibility',
        'fees_structure',
        'roi',
        'curriculum',
        'career_prospects',
        'placement_details',
        'program_level_id',
        'stream_offered_id',
        'discipline_id',
        'specialization_id',
        'rating',
        'industrial_collaboration',
        'internship_ranking',

        'campus_id',
        'department_id',
        'entrance_exam_id',
        'entrance_exam_ids',

        // Core Identity
        'academic_unit_name',
        'slug',
        'school_type',
        'course_languages',
        'total_fees',

        // School Specific
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

        // Institute Specific
        'delivery_mode',
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

        // Admin
        'verified_reviews_only',
        'meta_title',
        'meta_description',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function entranceExam()
    {
        return $this->belongsTo(Exam::class, 'entrance_exam_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    // Master Relationships
    public function programLevel()
    {
        return $this->belongsTo(ProgramLevel::class);
    }

    public function streamOffered()
    {
        return $this->belongsTo(StreamOffered::class);
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    protected $casts = [
        'streams_offered' => 'array',
        'board_result_classes' => 'array',
        'sports_offered' => 'array',
        'clubs_and_societies' => 'array',
        'annual_events' => 'array',
        'course_languages' => 'array',
        'results_years_available' => 'array',
        'entrance_exam_ids' => 'array',

        'special_educator_available' => 'boolean',
        'school_counsellor_available' => 'boolean',
        'remedial_classes_available' => 'boolean',
        'olympiad_participation' => 'boolean',
        'competitive_exam_preparation_support' => 'boolean',
        'parent_app_available' => 'boolean',
        'attendance_tracking_available' => 'boolean',
        'arts_music_programs_available' => 'boolean',
        'integrated_schooling_available' => 'boolean',
        'separate_batches_for_droppers' => 'boolean',
        'merit_based_batching' => 'boolean',
        'visiting_faculty_available' => 'boolean',
        'personal_mentorship_available' => 'boolean',
        'extra_classes_for_weak_students' => 'boolean',
        'parent_counselling_available' => 'boolean',
        'dpp_provided' => 'boolean',
        'test_series_available' => 'boolean',
        'online_test_platform_available' => 'boolean',
        'installment_available' => 'boolean',
        'scholarship_available' => 'boolean',
        'refund_policy_available' => 'boolean',
        'verified_reviews_only' => 'boolean',
        'status' => 'boolean',
        'provisional_admission' => 'boolean',
    ];
}
