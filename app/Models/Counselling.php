<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Counselling extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'exam_id',
        'counselling_name',
        'slug',
        'counselling_type',
        'counselling_mode',
        'conducting_authority_name',
        'conducting_authority_type',
        'official_counselling_website',
        // Scope
        'applicable_course_levels',
        'applicable_quotas',
        'applicable_categories',
        'domicile_required',
        'state_applicability',
        // Eligibility
        'minimum_exam_qualification_required',
        'minimum_score_or_rank_required',
        'category_wise_eligibility',
        'attempts_allowed',
        'age_criteria_for_counselling',
        'eligibility_notes',
        // Rounds
        'number_of_rounds',
        'rounds',
        // Process
        'registration_process_steps',
        'choice_filling_process',
        'choice_locking_required',
        'seat_allotment_process',
        'reporting_process',
        'document_verification_process',
        'upgradation_rules',
        'exit_and_refund_rules',
        // Dates
        'counselling_year',
        'registration_start_date',
        'registration_end_date',
        'choice_filling_start_date',
        'choice_filling_end_date',
        'seat_allotment_result_date',
        'reporting_start_date',
        'reporting_end_date',
        'round_wise_schedule',
        'important_dates',
        // Seat Allocation
        'seat_allocation_basis',
        'tie_breaking_rules',
        'reservation_policy_reference',
        'seat_matrix_source',
        'seat_conversion_rules',
        // Documents
        'documents_required',
        'document_format_requirements',
        'original_documents_required_at_reporting',
        // Advanced Fees
        'registration_fee_required',
        'registration_fee_structure',
        'late_registration_allowed',
        'late_fee_rules',
        'security_deposit_required',
        'security_deposit_structure',
        'round_specific_fee_rules',
        'refund_policy_summary',
        'refund_timeline',
        'refund_mode',
        'forfeiture_scenarios',
        'payment_modes_allowed',
        'transaction_charges_applicable',
        'transaction_charge_borne_by',
        'payment_gateway_name',
        // Institutions
        'participating_institutions_count',
        'participating_institutions_note',
        'institution_type_supported',
        // Help
        'helpdesk_contact_number',
        'helpdesk_email',
        'faq_url',
        'grievance_redressal_process',
        'official_notifications_urls',
        // Verification & SEO
        'information_source',
        'last_verified_on',
        'data_confidence_score',
        'disclaimer_text',
        'meta_title',
        'meta_description',
        'focus_keywords',
        'schema_type',
        'canonical_url',
        'indexing_status',
        // System
        'status',
        'visibility',
        'created_by',
        'last_updated_by',
        'partial_refund_allowed',
    ];

    protected $casts = [
        'applicable_course_levels' => 'array',
        'applicable_quotas' => 'array',
        'applicable_categories' => 'array',
        'domicile_required' => 'boolean',
        // 'state_applicability' => 'array', // Assuming text or json needed, usually array is safer if json
        'category_wise_eligibility' => 'array',
        'minimum_exam_qualification_required' => 'boolean',
        'rounds' => 'array',
        'registration_process_steps' => 'array',
        'choice_locking_required' => 'boolean',
        'important_dates' => 'array',
        'round_wise_schedule' => 'array',
        'documents_required' => 'array',
        'original_documents_required_at_reporting' => 'boolean',
        'registration_fee_required' => 'boolean',
        'registration_fee_structure' => 'array',
        'late_registration_allowed' => 'boolean',
        'late_fee_rules' => 'array',
        'security_deposit_required' => 'boolean',
        'security_deposit_structure' => 'array',
        'round_specific_fee_rules' => 'array',
        'forfeiture_scenarios' => 'array',
        'payment_modes_allowed' => 'array',
        'transaction_charges_applicable' => 'boolean',
        'partial_refund_allowed' => 'boolean',
        'institution_type_supported' => 'array',
        'official_notifications_urls' => 'array',
        'last_verified_on' => 'datetime',
        'focus_keywords' => 'array',
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'choice_filling_start_date' => 'date',
        'choice_filling_end_date' => 'date',
        'seat_allotment_result_date' => 'date',
        'reporting_start_date' => 'date',
        'reporting_end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->counselling_name . '-' . Str::random(6));
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
