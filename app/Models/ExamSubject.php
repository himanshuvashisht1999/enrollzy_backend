<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamSubject extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'exam_id',
        'exam_stage_id',
        'subject_name',
        'subject_code',
        'slug',
        'subject_type',
        'subject_group',
        'display_order',
        'max_subjects_allowed',
        'subject_choice_required',
        'subject_combination_rules',
        'applicable_categories',
        'subject_mediums_available',
        'syllabus_structure',
        'syllabus_description',
        'official_syllabus_pdf_url',
        'reference_books',
        'syllabus_version',
        'syllabus_effective_year',
        'number_of_papers',
        'paper_names',
        'total_marks',
        'marks_per_paper',
        'negative_marking',
        'qualifying_marks',
        'normalization_applied',
        'applicable_exam_stages',
        'stage_weightage_override',
        'minimum_qualification_required',
        'background_subject_required',
        'subject_specific_eligibility_notes',
        'subject_contributes_to_merit',
        'subject_weightage_percentage',
        'subject_result_type',
        'subject_registration_required',
        'subject_change_allowed_till_date',
        'schema_type',
        'meta_title',
        'meta_description',
        'focus_keywords',
        'canonical_url',
        'status',
        'created_by',
        'last_updated_on',
        'information_source',
    ];

    protected $casts = [
        'subject_choice_required' => 'boolean',
        'applicable_categories' => 'array',
        'subject_mediums_available' => 'array',
        'syllabus_structure' => 'array',
        'reference_books' => 'array',
        'paper_names' => 'array',
        'marks_per_paper' => 'array',
        'negative_marking' => 'boolean',
        'normalization_applied' => 'boolean',
        'applicable_exam_stages' => 'array',
        'stage_weightage_override' => 'array',
        'background_subject_required' => 'boolean',
        'subject_contributes_to_merit' => 'boolean',
        'subject_registration_required' => 'boolean',
        'subject_change_allowed_till_date' => 'date',
        'focus_keywords' => 'array',
        'last_updated_on' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->subject_name . '-' . Str::random(5));
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function examStage()
    {
        return $this->belongsTo(ExamStage::class, 'exam_stage_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
