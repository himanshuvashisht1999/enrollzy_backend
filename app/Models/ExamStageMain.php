<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamStageMain extends Model
{
    protected $fillable = [
        'main_stage_id',
        'exam_id',
        'exam_stage_id',
        'stage_name',
        'stage_order',
        'mandatory',
        'subjects_required',
        'attempt_limit',
        'gap_year_allowed',
        'eligibility_notes',
        'exam_mode',
        'exam_format',
        'total_questions',
        'total_marks',
        'duration_minutes',
        'negative_marking',
        'negative_marking_scheme',
        'syllabus_url',
        'difficulty_level',
        'syllabus_source',
        'subjects_covered',
        'sessions_data',
        'admit_card_download_procedure',
        'result_check_procedure',
        'score_type',
        'rank_type',
        'normalization_applied',
        'tie_breaking_rules',
        'score_validity_period',
        'result_format_url',
        'cutoff_type',
        'cutoff_year_wise',
        'cutoff_reference_note',
        'registration_fee_required',
        'registration_fee_structure',
        'late_registration_allowed',
        'late_fee_rules',
        'security_deposit_required',
        'security_deposit_structure',
        'payment_modes_allowed',
        'transaction_charges_applicable',
        'transaction_charge_borne_by',
        'last_updated_on'
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'gap_year_allowed' => 'boolean',
        'negative_marking' => 'boolean',
        'normalization_applied' => 'boolean',
        'registration_fee_required' => 'boolean',
        'late_registration_allowed' => 'boolean',
        'security_deposit_required' => 'boolean',
        'transaction_charges_applicable' => 'boolean',
        'subjects_required' => 'array',
        'subjects_covered' => 'array',
        'sessions_data' => 'array',
        'cutoff_year_wise' => 'array',
        'registration_fee_structure' => 'array',
        'late_fee_rules' => 'array',
        'security_deposit_structure' => 'array',
        'payment_modes_allowed' => 'array',
        'last_updated_on' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->main_stage_id)) {
                $model->main_stage_id = (string) Str::uuid();
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function masterStage()
    {
        return $this->belongsTo(ExamStage::class, 'exam_stage_id');
    }
}
