<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganisationInstituteCourse extends Model
{
    use HasFactory;

    protected $table = 'organisation_institute_courses';   // agar table ka naam institutes hai

    /**
     * Mass assignable fields
     */
    protected $fillable = [

        // Core Academic Identity
        'academic_unit_id',
        'organisation_id',
        'location_unit_id',
        'academic_unit_type',
        'academic_unit_name',
        'slug',
        'delivery_mode',
        'established_year',
        'about_academic_unit',

        // Exam & Course Focus
        'exams_prepared_for',
        'courses_offered',
        'target_classes',
        'medium_of_instruction',
        'integrated_schooling_available',

        // Batch Structure
        'total_batches',
        'average_batch_size',
        'min_batch_size',
        'max_batch_size',
        'student_teacher_ratio',
        'separate_batches_for_droppers',
        'merit_based_batching',

        // Faculty Strength
        'total_faculty_count',
        'senior_faculty_count',
        'average_faculty_experience_years',
        'full_time_faculty_percentage',
        'visiting_faculty_available',

        // Academic Support System
        'doubt_solving_mode',
        'personal_mentorship_available',
        'extra_classes_for_weak_students',
        'parent_counselling_available',

        // Study Material & Testing
        'study_material_type',
        'dpp_provided',
        'test_series_available',
        'tests_per_month',
        'full_syllabus_tests_count',
        'online_test_platform_available',

        // Results & Outcomes
        'results_years_available',
        'total_selections_all_time',
        'selections_last_year',
        'highest_rank_achieved',
        'average_selection_rate',
        'result_verification_status',

        // Fees & Affordability
        'average_course_fee_range',
        'installment_available',
        'scholarship_available',
        'refund_policy_available',

        // Reviews, SEO & Admin
        'average_rating',
        'total_reviews',
        'verified_reviews_only',
        'schema_type',
        'meta_title',
        'meta_description',
        'status',
        'last_updated_on',
    ];

    /**
     * Attribute casting (VERY IMPORTANT)
     * JSON / array fields yahin define hote hain
     */
    protected $casts = [

        // Arrays
        'exams_prepared_for'       => 'array',
        'courses_offered'          => 'array',
        'target_classes'           => 'array',
        'medium_of_instruction'    => 'array',
        'results_years_available'  => 'array',

        // Booleans
        'integrated_schooling_available' => 'boolean',
        'separate_batches_for_droppers'  => 'boolean',
        'merit_based_batching'           => 'boolean',
        'visiting_faculty_available'     => 'boolean',
        'personal_mentorship_available'  => 'boolean',
        'extra_classes_for_weak_students'=> 'boolean',
        'parent_counselling_available'   => 'boolean',
        'dpp_provided'                   => 'boolean',
        'test_series_available'          => 'boolean',
        'online_test_platform_available' => 'boolean',
        'installment_available'          => 'boolean',
        'scholarship_available'          => 'boolean',
        'refund_policy_available'        => 'boolean',
        'verified_reviews_only'          => 'boolean',
        'status'                         => 'boolean',
    ];

    /**
     * Relationships (optional but recommended)
     */

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
