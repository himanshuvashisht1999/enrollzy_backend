<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationSchoolCourse extends Model
{
    protected $fillable = [
        'organisation_id',

        // Core Academic Identity
        'school_name',
        'slug',
        'school_type',
        'established_year',
        'about_school',

        // Board & Affiliation
        'education_board',
        'board_affiliation_number',
        'affiliation_valid_from',
        'affiliation_valid_to',
        'medium_of_instruction',
        'grade_range',
        'streams_offered',

        // Faculty & Student Strength
        'total_teachers',
        'trained_teachers_percentage',
        'average_teacher_experience_years',
        'student_strength',
        'special_educator_available',
        'school_counsellor_available',

        // Academic Delivery
        'average_class_size',
        'assessment_pattern',
        'remedial_classes_available',

        // Reviews / SEO / Admin
        'average_rating',
        'total_reviews',
        'verified_reviews_only',
        'meta_title',
        'meta_description',
        'status',

        // Institute Specific Fields
        'exams_prepared_for',
        'target_classes',
        'total_batches',
        'average_batch_size',
        'min_batch_size',
        'max_batch_size',
        'integrated_schooling_available',
        'separate_batches_for_droppers',
        'merit_based_batching',
        'student_teacher_ratio',
        'delivery_mode',
    ];

    protected $casts = [
        'streams_offered' => 'array',
        'special_educator_available' => 'boolean',
        'school_counsellor_available' => 'boolean',
        'remedial_classes_available' => 'boolean',
        'verified_reviews_only' => 'boolean',
        'status' => 'boolean',
        'exams_prepared_for' => 'array',
        'target_classes' => 'array',
        'integrated_schooling_available' => 'boolean',
        'separate_batches_for_droppers' => 'boolean',
        'merit_based_batching' => 'boolean',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
