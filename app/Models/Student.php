<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'full_name',
        'slug',
        'profile_photo_url',
        'gender',
        'year_of_birth',
        'short_intro',
        'city',
        'state',
        'current_class',
        'school_name',
        'board',
        'previous_year_percentage',
        'stream',
        'competitive_exam_target',
        'attempt_type',
        'year_of_admission',
        'organisation_id',
        'institute_name',
        'course_enrolled',
        'batch_type',
        'mode_of_study',
        'admission_through',
        'test_scores_summary',
        'average_test_score',
        'rank_trend',
        'attendance_percentage',
        'academic_improvement_indicator',
        'strengths',
        'weak_areas',
        'exam_attempted',
        'exam_year',
        'exam_score',
        'exam_rank',
        'selection_status',
        'college_allotted',
        'category_rank',
        'result_verified',
        'student_testimonial',
        'rating_for_institute',
        'rating_for_faculty',
        'would_recommend',
        'preparation_duration_months',
        'study_hours_per_day',
        'study_groups_joined',
        'discussion_forum_participation',
        'mentor_assigned',
        'doubt_sessions_attended',
        'profile_visibility',
        'fields_visible_public',
        'contact_visible',
        'testimonial_visible',
        'consent_for_data_use',
        'profile_indexing_allowed',
        'schema_type',
        'canonical_url',
        'profile_verified',
        'verification_source',
        'data_source',
        'confidence_score',
        'status',
    ];

    protected $casts = [
        'year_of_birth' => 'integer',
        'previous_year_percentage' => 'decimal:2',
        'year_of_admission' => 'integer',
        'average_test_score' => 'decimal:2',
        'rank_trend' => 'array',
        'attendance_percentage' => 'decimal:2',
        'strengths' => 'array',
        'weak_areas' => 'array',
        'exam_year' => 'integer',
        'result_verified' => 'boolean',
        'rating_for_institute' => 'decimal:1',
        'rating_for_faculty' => 'decimal:1',
        'would_recommend' => 'boolean',
        'study_hours_per_day' => 'decimal:1',
        'study_groups_joined' => 'array',
        'discussion_forum_participation' => 'boolean',
        'fields_visible_public' => 'array',
        'contact_visible' => 'boolean',
        'testimonial_visible' => 'boolean',
        'consent_for_data_use' => 'boolean',
        'profile_indexing_allowed' => 'boolean',
        'profile_verified' => 'boolean',
        'confidence_score' => 'decimal:1',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->student_id)) {
                $model->student_id = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->full_name) . '-' . Str::random(6);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('full_name') && empty($model->slug)) {
                 $model->slug = Str::slug($model->full_name) . '-' . Str::random(6);
            }
        });
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
