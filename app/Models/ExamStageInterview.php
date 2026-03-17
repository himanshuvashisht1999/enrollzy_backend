<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStageInterview extends Model
{
    protected $fillable = [
        'interview_stage_id',
        'exam_id',
        'exam_stage_id',
        'stage_name',
        'stage_order',
        'mandatory',
        'stage_contribution_type',
        'interview_conducting_body',
        'interview_panel_type',
        'panel_constitution_guidelines',
        'interview_centres_scope',
        'official_interview_guidelines_url',
        'interview_mode',
        'interview_duration_minutes',
        'number_of_panellists',
        'language_options',
        'medium_switch_allowed',
        'evaluation_criteria',
        'criteria_weightage_defined',
        'marks_applicable',
        'maximum_marks',
        'minimum_qualifying_marks',
        'category_wise_cutoff_applicable',
        'weightage_percentage',
        'normalization_applied',
        'previous_stage_qualification_required',
        'shortlisting_basis',
        'documents_required_for_interview_call',
        'interview_process_steps',
        'identity_verification_required',
        'biometric_verification_required',
        'slot_booking_required',
        'slot_allocation_method',
        'rescheduling_allowed',
        'rescheduling_conditions',
        'late_reporting_policy',
        'interview_result_type',
        'interview_result_visibility',
        'interview_result_declaration_date',
        'appeal_allowed',
        'appeal_process_description',
        'appeal_time_limit_days',
        'appeal_fee_required',
        'appeal_fee_amount',
        'final_decision_authority',
        'category_relaxations',
        'pwd_accommodations_available',
        'ex_servicemen_relaxations',
        'gender_specific_guidelines',
        'interview_fee_required',
        'interview_fee_amount',
        'fee_refundable',
        'payment_modes',
        'interview_disclaimer_text',
        'information_source',
        'last_verified_on',
        'stage_status',
        'visibility',
        'remarks',
        'last_updated_on'
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'medium_switch_allowed' => 'boolean',
        'criteria_weightage_defined' => 'boolean',
        'marks_applicable' => 'boolean',
        'category_wise_cutoff_applicable' => 'boolean',
        'normalization_applied' => 'boolean',
        'previous_stage_qualification_required' => 'boolean',
        'identity_verification_required' => 'boolean',
        'biometric_verification_required' => 'boolean',
        'slot_booking_required' => 'boolean',
        'rescheduling_allowed' => 'boolean',
        'appeal_allowed' => 'boolean',
        'appeal_fee_required' => 'boolean',
        'pwd_accommodations_available' => 'boolean',
        'interview_fee_required' => 'boolean',
        'fee_refundable' => 'boolean',
        'language_options' => 'array',
        'evaluation_criteria' => 'array',
        'documents_required_for_interview_call' => 'array',
        'interview_process_steps' => 'array',
        'category_relaxations' => 'array',
        'payment_modes' => 'array',
        'interview_result_declaration_date' => 'date',
        'last_verified_on' => 'date',
        'last_updated_on' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->interview_stage_id)) {
                $model->interview_stage_id = (string) \Illuminate\Support\Str::uuid();
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
