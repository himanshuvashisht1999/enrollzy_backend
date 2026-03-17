<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $fillable = [
        'name',
        'organisation_type_id',
        'organisation_id_number',
        'brand_type',
        'franchise_partner_name',
        'franchise_start_year',
        'brand_compliance_verified',
        'central_authority',
        'head_office_location',
        'slug',
        'status',
        // University Specific (Core)
        'university_id',
        'brand_name',
        'short_name',
        'logo_url',
        'cover_image_url',
        'established_year',
        'university_type',
        'ownership_type',
        'about_university',
        'vision_mission',
        'core_values',
        // Legal & Regulatory
        'degree_awarding_authority',
        'ugc_recognized',
        'ugc_approval_number',
        'aicte_approved',
        'naac_accredited',
        'naac_grade',
        'nirf_rank_overall',
        'nirf_rank_category',
        'international_accreditations',
        'statutory_approvals',
        'recognition_documents',
        // Governance
        'governing_body_name',
        'chancellor_name',
        'vice_chancellor_name',
        'autonomous_status',
        'university_category',
        'number_of_campuses',
        'number_of_constituent_colleges',
        'number_of_affiliated_colleges',
        // Academic
        'levels_offered',
        // Institute Specific
        'institute_id',
        'about_organisation',
        'registered_entity_name',
        'registration_number',
        'gst_registered',
        'gst_number',
        'pan_number',
        'legal_documents_urls',
        // School Specific (Type 4)
        'school_id',
        'managing_trust_or_society_name',
        'minority_status',
        'minority_type',
        'education_boards_supported',
        'medium_of_instruction_supported',
        'international_curriculum_supported',
        'education_levels_supported',
        'streams_supported',
        'pedagogy_model',
        'focus_areas',
        'centralized_curriculum_framework',
        'centralized_teacher_training',
        'centralized_assessment_policy',
        'centralized_lms_available',
        'centralized_parent_communication_system',
        'child_safety_policy_available',
        'posco_compliance_policy',
        'anti_bullying_policy',
        'mental_health_policy',
        'teacher_background_verification_policy',
        'total_schools_count',
        'cities_present_in',
        'states_present_in',
        'national_presence',
        'international_presence',
        'flagship_schools',
        'official_website',
        'admission_portal_url',
        'parent_portal_url',
        'student_portal_url',
        'mobile_app_available',
        'average_rating',
        'total_reviews',
        'awards_and_recognition',
        'schema_type',
        'meta_title',
        'meta_description',
        'canonical_url',
        'claimed_by_organization',
        'verification_status',
        // Exam Conducting Body Specific
        'exam_conducting_body_id',
        'abbreviation',
        'mandate_description',
        'authority_type',
        'parent_ministry',
        'established_by',
        'legal_act_reference',
        'headquarters_location',
        'jurisdiction_scope',
        'functions',
        'exam_types_conducted',
        'evaluation_methods',
        'exams_conducted_ids',
        'annual_exam_volume_estimate',
        'average_candidates_per_year',
        'exam_modes_supported',
        'question_bank_managed',
        'normalization_process_available',
        'multi_language_support',
        'remote_proctoring_supported',
        'exam_centres_management_type',
        'technology_partners',
        'logistics_partners',
        'data_security_standards',
        'result_declaration_policy_summary',
        'score_validity_period',
        're_evaluation_allowed',
        're_evaluation_process_summary',
        'data_retention_policy',
        'grievance_redressal_mechanism',
        'candidate_portal_url',
        'helpdesk_contact_number',
        'helpdesk_email',
        'official_notifications_urls',
        'faq_url',
        'rti_applicable',
        'audit_conducted',
        'exam_fairness_policy',
        'anti_malpractice_measures',
        'whistleblower_policy_available',
        'awards_or_recognition',
        'media_mentions',
        'public_trust_score',
        'focus_keywords',
        'claimed_by_authority',
        'data_source',
        'confidence_score',
        'last_updated_on',
        // Counselling Body Specific
        'counselling_body_id',
        'parent_ministry_or_department',
        'legal_reference_document_url',
        'jurisdiction_states',
        'counselling_functions',
        'counselling_types_supported',
        'education_domains_supported',
        'counselling_levels_supported',
        'exams_used_for_counselling_ids',
        'allocation_basis',
        'rank_source_validation_required',
        'multiple_exam_support',
        'seat_matrix_management',
        'seat_matrix_source',
        'quota_types_managed',
        'reservation_policy_reference',
        'seat_conversion_rules_supported',
        'rounds_supported',
        'round_types',
        'choice_locking_mandatory',
        'seat_upgradation_allowed',
        'withdrawal_rules_summary',
        'exit_rules_summary',
        'counselling_fee_collection_supported',
        'fee_collection_mode',
        'refund_processing_responsibility',
        'security_deposit_handling',
        'candidate_login_system_available',
        'choice_filling_system_available',
        'auto_seat_allocation_engine',
        'api_integration_supported',
        'institution_reporting_interface_available',
        'document_verification_mode',
        'institution_confirmation_process_summary',
        'mis_reporting_controls',
        'appeal_process_summary',
        'grievance_contact_details',
        'candidate_guidelines_url',
        'years_of_operation',
        'annual_candidate_volume',
        'institutions_covered_count',
        'states_covered_count'
    ];

    public const BRAND_TYPES = [
        'Independent',
        'Chain',
        'Franchise'
    ];

    protected $casts = [
        'status' => 'boolean',
        'core_values' => 'array',
        'degree_awarding_authority' => 'boolean',
        'ugc_recognized' => 'boolean',
        'aicte_approved' => 'boolean',
        'naac_accredited' => 'boolean',
        'autonomous_status' => 'boolean',
        'international_accreditations' => 'array',
        'statutory_approvals' => 'array',
        'recognition_documents' => 'array',
        'levels_offered' => 'array',
        // Institute Casts
        'gst_registered' => 'boolean',
        'legal_documents_urls' => 'array',
        // School Casts
        'minority_status' => 'boolean',
        'education_boards_supported' => 'array',
        'medium_of_instruction_supported' => 'array',
        'international_curriculum_supported' => 'boolean',
        'education_levels_supported' => 'array',
        'streams_supported' => 'array',
        'focus_areas' => 'array',
        'centralized_curriculum_framework' => 'boolean',
        'centralized_teacher_training' => 'boolean',
        'centralized_assessment_policy' => 'boolean',
        'centralized_lms_available' => 'boolean',
        'centralized_parent_communication_system' => 'boolean',
        'child_safety_policy_available' => 'boolean',
        'posco_compliance_policy' => 'boolean',
        'anti_bullying_policy' => 'boolean',
        'mental_health_policy' => 'boolean',
        'teacher_background_verification_policy' => 'boolean',
        'cities_present_in' => 'array',
        'states_present_in' => 'array',
        'national_presence' => 'boolean',
        'international_presence' => 'boolean',
        'flagship_schools' => 'array',
        'mobile_app_available' => 'boolean',
        'awards_and_recognition' => 'array',
        'claimed_by_organization' => 'boolean',
        'average_rating' => 'decimal:2',
        // Exam Conducting Body Casts
        'functions' => 'array',
        'exam_types_conducted' => 'array',
        'evaluation_methods' => 'array',
        'exams_conducted_ids' => 'array',
        'exam_modes_supported' => 'array',
        'question_bank_managed' => 'boolean',
        'normalization_process_available' => 'boolean',
        'multi_language_support' => 'boolean',
        'remote_proctoring_supported' => 'boolean',
        'technology_partners' => 'array',
        'logistics_partners' => 'array',
        're_evaluation_allowed' => 'boolean',
        'official_notifications_urls' => 'array',
        'rti_applicable' => 'boolean',
        'audit_conducted' => 'boolean',
        'anti_malpractice_measures' => 'array',
        'whistleblower_policy_available' => 'boolean',
        'awards_or_recognition' => 'array',
        'media_mentions' => 'array',
        'focus_keywords' => 'array',
        'claimed_by_authority' => 'boolean',
        'last_updated_on' => 'datetime',
        // Counselling Body Casts
        'jurisdiction_states' => 'array',
        'counselling_functions' => 'array',
        'counselling_types_supported' => 'array',
        'education_domains_supported' => 'array',
        'counselling_levels_supported' => 'array',
        'exams_used_for_counselling_ids' => 'array',
        'rank_source_validation_required' => 'boolean',
        'multiple_exam_support' => 'boolean',
        'seat_matrix_management' => 'boolean',
        'quota_types_managed' => 'array',
        'seat_conversion_rules_supported' => 'boolean',
        'round_types' => 'array',
        'choice_locking_mandatory' => 'boolean',
        'seat_upgradation_allowed' => 'boolean',
        'counselling_fee_collection_supported' => 'boolean',
        'security_deposit_handling' => 'boolean',
        'candidate_login_system_available' => 'boolean',
        'choice_filling_system_available' => 'boolean',
        'auto_seat_allocation_engine' => 'boolean',
        'api_integration_supported' => 'boolean',
        'institution_reporting_interface_available' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organisation) {
            if (empty($organisation->slug)) {
                $slug = \Illuminate\Support\Str::slug($organisation->name);
                $originalSlug = $slug;
                $count = 1;
                while (\App\Models\Organisation::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                $organisation->slug = $slug;
            }

            if (empty($organisation->organisation_id_number)) {
                $organisation->organisation_id_number = 'ORG-' . strtoupper(\Illuminate\Support\Str::random(8));
            }

            // Auto-generate UUID for university_id if empty and type is University (1, 2)
            if (in_array($organisation->organisation_type_id, [1, 2]) && empty($organisation->university_id)) {
                $organisation->university_id = (string) \Illuminate\Support\Str::uuid();
            }

            // Auto-generate UUID for institute_id if empty and type is Institute (3)
            if ($organisation->organisation_type_id == 3 && empty($organisation->institute_id)) {
                $organisation->institute_id = (string) \Illuminate\Support\Str::uuid();
            }

            // Auto-generate UUID for school_id if empty and type is School (4)
            if ($organisation->organisation_type_id == 4 && empty($organisation->school_id)) {
                $organisation->school_id = (string) \Illuminate\Support\Str::uuid();
            }

            // Auto-generate UUID for exam_conducting_body_id if empty and type is Exam Conducting Body (5)
            if ($organisation->organisation_type_id == 5 && empty($organisation->exam_conducting_body_id)) {
                $organisation->exam_conducting_body_id = (string) \Illuminate\Support\Str::uuid();
            }

            // Auto-generate UUID for counselling_body_id if empty and type is Counselling Body (6)
            if ($organisation->organisation_type_id == 6 && empty($organisation->counselling_body_id)) {
                $organisation->counselling_body_id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function courses()
    {
        return $this->hasMany(OrganisationCourse::class);
    }

    public function organisationType()
    {
        return $this->belongsTo(OrganisationType::class);
    }

    public function campusType()
    {
        return $this->belongsTo(CampusType::class);
    }

    public function accreditations()
    {
        return $this->belongsToMany(AccreditationApproval::class, 'organisation_accreditations');
    }

    public function awards()
    {
        return $this->hasMany(OrganisationAward::class);
    }

    public function sports()
    {
        return $this->hasMany(OrganisationSport::class);
    }

    public function organisationSubType()
    {
        return $this->belongsTo(OrganisationSubType::class);
    }

    public function academicResults()
    {
        return $this->hasMany(OrganisationAcademicResult::class);
    }

    public function feeStructures()
    {
        return $this->hasMany(OrganisationFee::class);
    }

    public function admissionRoutes()
    {
        return $this->hasMany(AdmissionRoute::class);
    }

    public function campuses()
    {
        return $this->hasMany(Campus::class);
    }


}
