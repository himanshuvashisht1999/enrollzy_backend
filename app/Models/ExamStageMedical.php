<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamStageMedical extends Model
{
    protected $fillable = [
        'medical_stage_id',
        'exam_id',
        'exam_stage_id',
        'stage_name',
        'stage_order',
        'mandatory',
        'stage_contribution_type',
        'medical_conducting_authority',
        'medical_board_type',
        'medical_centres_scope',
        'medical_centres_list_url',
        'official_medical_guidelines_url',
        'general_health_required',
        'free_from_chronic_diseases',
        'physical_fitness_required',
        'height_requirement',
        'weight_standard_reference',
        'chest_measurement_required',
        'chest_expansion_required',
        'vision_test_required',
        'visual_acuity_standards',
        'color_vision_required',
        'night_blindness_disqualifying',
        'spectacles_allowed',
        'hearing_standard_required',
        'speech_standard_required',
        'cardiovascular_system_check',
        'respiratory_system_check',
        'neurological_system_check',
        'musculoskeletal_system_check',
        'mental_health_evaluation_required',
        'temporary_disqualifications',
        'permanent_disqualifications',
        'tattoo_policy',
        'surgical_history_rules',
        'pregnancy_rules',
        'medical_exam_procedure_steps',
        'tests_conducted',
        'fasting_required',
        'medical_exam_duration',
        'medical_review_allowed',
        'appeal_time_limit_days',
        'review_medical_board_details',
        'appeal_fee_required',
        'appeal_fee_amount',
        'final_decision_authority',
        'medical_result_type',
        'temporary_unfit_retest_allowed',
        'retest_timeline_days',
        'medical_result_visibility',
        'medical_documents_required',
        'medical_certificate_format_url',
        'medical_exam_start_date',
        'medical_exam_end_date',
        'slot_booking_required',
        'reporting_time_guidelines',
        'medical_fee_required',
        'medical_fee_amount',
        'fee_refundable',
        'payment_mode',
        'gender_based_relaxation_rules',
        'category_based_relaxation_rules',
        'ex_servicemen_relaxation',
        'pwd_medical_rules',
        'medical_disclaimer_text',
        'information_source',
        'last_verified_on',
        'stage_status',
        'visibility',
        'remarks',
        'last_updated_on'
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'general_health_required' => 'boolean',
        'free_from_chronic_diseases' => 'boolean',
        'physical_fitness_required' => 'boolean',
        'chest_measurement_required' => 'boolean',
        'chest_expansion_required' => 'boolean',
        'vision_test_required' => 'boolean',
        'color_vision_required' => 'boolean',
        'night_blindness_disqualifying' => 'boolean',
        'spectacles_allowed' => 'boolean',
        'hearing_standard_required' => 'boolean',
        'speech_standard_required' => 'boolean',
        'cardiovascular_system_check' => 'boolean',
        'respiratory_system_check' => 'boolean',
        'neurological_system_check' => 'boolean',
        'musculoskeletal_system_check' => 'boolean',
        'mental_health_evaluation_required' => 'boolean',
        'fasting_required' => 'boolean',
        'medical_review_allowed' => 'boolean',
        'appeal_fee_required' => 'boolean',
        'temporary_unfit_retest_allowed' => 'boolean',
        'slot_booking_required' => 'boolean',
        'medical_fee_required' => 'boolean',
        'fee_refundable' => 'boolean',
        'temporary_disqualifications' => 'array',
        'permanent_disqualifications' => 'array',
        'medical_exam_procedure_steps' => 'array',
        'tests_conducted' => 'array',
        'medical_documents_required' => 'array',
        'payment_mode' => 'array',
        'medical_exam_start_date' => 'date',
        'medical_exam_end_date' => 'date',
        'last_verified_on' => 'date',
        'last_updated_on' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->medical_stage_id)) {
                $model->medical_stage_id = (string) Str::uuid();
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
