<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamStageSkill extends Model
{
    protected $fillable = [
        'skill_stage_id',
        'exam_id',
        'exam_stage_id',
        'stage_name',
        'stage_order',
        'mandatory',
        'stage_contribution_type',
        'skill_test_category',
        'skill_test_purpose',
        'skills_evaluated',
        'typing_language_options',
        'minimum_typing_speed',
        'accuracy_required_percentage',
        'error_tolerance_percentage',
        'backspace_allowed',
        'software_tools_tested',
        'task_based_evaluation',
        'task_completion_time_minutes',
        'marks_applicable',
        'maximum_marks',
        'minimum_qualifying_score',
        'pass_fail_only',
        'normalization_applied',
        'previous_stage_qualification_required',
        'shortlisting_basis',
        'post_wise_skill_requirements',
        'category_wise_relaxations',
        'test_mode',
        'test_environment',
        'assistive_devices_allowed',
        'pwd_accommodations_available',
        'skill_test_centres_scope',
        'lab_infrastructure_required',
        'reporting_time_guidelines',
        'identity_verification_required',
        'attempts_allowed',
        'retest_allowed',
        'retest_conditions',
        'temporary_failure_recovery_allowed',
        'skill_test_result_type',
        'result_visibility',
        'result_declaration_date',
        'appeal_allowed',
        'appeal_process',
        'appeal_time_limit_days',
        'appeal_fee_required',
        'appeal_fee_amount',
        'documents_required',
        'instruction_guidelines_url',
        'mock_test_available',
        'demo_environment_available',
        'skill_test_fee_required',
        'skill_test_fee_amount',
        'fee_refundable',
        'payment_modes',
        'skill_test_disclaimer_text',
        'information_source',
        'last_verified_on',
        'stage_status',
        'visibility',
        'remarks',
        'last_updated_on'
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'backspace_allowed' => 'boolean',
        'task_based_evaluation' => 'boolean',
        'marks_applicable' => 'boolean',
        'pass_fail_only' => 'boolean',
        'normalization_applied' => 'boolean',
        'previous_stage_qualification_required' => 'boolean',
        'assistive_devices_allowed' => 'boolean',
        'pwd_accommodations_available' => 'boolean',
        'identity_verification_required' => 'boolean',
        'retest_allowed' => 'boolean',
        'temporary_failure_recovery_allowed' => 'boolean',
        'appeal_allowed' => 'boolean',
        'appeal_fee_required' => 'boolean',
        'mock_test_available' => 'boolean',
        'demo_environment_available' => 'boolean',
        'skill_test_fee_required' => 'boolean',
        'fee_refundable' => 'boolean',
        'skills_evaluated' => 'array',
        'typing_language_options' => 'array',
        'software_tools_tested' => 'array',
        'post_wise_skill_requirements' => 'array',
        'category_wise_relaxations' => 'array',
        'documents_required' => 'array',
        'payment_modes' => 'array',
        'result_declaration_date' => 'date',
        'last_verified_on' => 'date',
        'last_updated_on' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->skill_stage_id)) {
                $model->skill_stage_id = (string) Str::uuid();
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
