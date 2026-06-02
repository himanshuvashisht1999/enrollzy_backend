<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrganisationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organisation::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('organisation_type_id')) {
            $query->where('organisation_type_id', $request->organisation_type_id);
        }

        $perPage = $request->input('per_page', 10);
        $organisations = $query->latest()->paginate($perPage)->withQueryString();
        
        $organisationTypes = \App\Models\OrganisationType::where('status', true)->get();

        return view('admin.organisations.index', compact('organisations', 'organisationTypes'));
    }

    public function create()
    {
        $organisationTypes = \App\Models\OrganisationType::where('status', true)->get();
        $brandTypes = Organisation::BRAND_TYPES;

        return view('admin.organisations.create', compact('organisationTypes', 'brandTypes'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'organisation_id_number' => 'nullable|string|max:255',
            'brand_type' => 'nullable|string|in:' . implode(',', Organisation::BRAND_TYPES),
            'central_authority' => 'nullable|string|max:255',
            'head_office_location' => 'nullable|string|max:255',
        ];

        // Type 1 & 2: University
        if (in_array($request->organisation_type_id, [1, 2])) {
            $rules += [
                'logo_url' => 'nullable|image|max:2048',
                'cover_image_url' => 'nullable|image|max:2048',
                'established_year' => 'nullable|integer',
                'core_values' => 'nullable|array',
                'international_accreditations' => 'nullable|array',
                'statutory_approvals' => 'nullable|array',
                'recognition_documents' => 'nullable|array',
                'levels_offered' => 'nullable|array',
            ];
        }

        // Type 3: Institute
        if ($request->organisation_type_id == 3) {
            $rules += [
                'logo_url' => 'nullable|image|max:2048',
                'cover_image_url' => 'nullable|image|max:2048',
                'established_year' => 'nullable|integer',
                'core_values' => 'nullable|array',
                'gst_registered' => 'nullable|boolean',
                'legal_documents_urls' => 'nullable|array',
                'legal_documents_urls.*' => 'file|max:5120',
            ];
        }

        // Type 4: School
        if ($request->organisation_type_id == 4) {
            $rules += [
                'logo_url' => 'nullable|image|max:2048',
                'cover_image_url' => 'nullable|image|max:2048',
                'established_year' => 'nullable|integer',
                'core_values' => 'nullable|array',
                'managing_trust_or_society_name' => 'nullable|string',
                'minority_status' => 'nullable|boolean',
                'education_boards_supported' => 'nullable|array',
                'medium_of_instruction_supported' => 'nullable|array',
                'international_curriculum_supported' => 'nullable|boolean',
                'education_levels_supported' => 'nullable|array',
                'streams_supported' => 'nullable|array',
                'focus_areas' => 'nullable|array',
                'centralized_curriculum_framework' => 'nullable|boolean',
                'centralized_teacher_training' => 'nullable|boolean',
                'centralized_assessment_policy' => 'nullable|boolean',
                'centralized_lms_available' => 'nullable|boolean',
                'centralized_parent_communication_system' => 'nullable|boolean',
                'child_safety_policy_available' => 'nullable|boolean',
                'posco_compliance_policy' => 'nullable|boolean',
                'anti_bullying_policy' => 'nullable|boolean',
                'mental_health_policy' => 'nullable|boolean',
                'teacher_background_verification_policy' => 'nullable|boolean',
                'total_schools_count' => 'nullable|integer',
                'cities_present_in' => 'nullable|array',
                'states_present_in' => 'nullable|array',
                'national_presence' => 'nullable|boolean',
                'international_presence' => 'nullable|boolean',
                'flagship_schools' => 'nullable|array',
                'official_website' => 'nullable|url',
                'admission_portal_url' => 'nullable|url',
                'parent_portal_url' => 'nullable|url',
                'student_portal_url' => 'nullable|url',
                'mobile_app_available' => 'nullable|boolean',
                'average_rating' => 'nullable|numeric|between:0,5',
                'total_reviews' => 'nullable|integer',
                'awards_and_recognition' => 'nullable|array',
                'claimed_by_organization' => 'nullable|boolean',
                'legal_documents_urls' => 'nullable|array',
                'legal_documents_urls.*' => 'file|max:5120',
            ];
        }

        // Type 5: Exam Conducting Body
        if ($request->organisation_type_id == 5) {
            $rules += [
                'abbreviation' => 'nullable|string|max:100',
                'mandate_description' => 'nullable|string',
                'authority_type' => 'nullable|string|in:Constitutional Body,Statutory Body,Government Agency,Autonomous Body',
                'parent_ministry' => 'nullable|string|in:Ministry of Education,DoPT,Ministry of Defence',
                'established_by' => 'nullable|string|in:Act of Parliament,Government Resolution',
                'legal_act_reference' => 'nullable|string',
                'headquarters_location' => 'nullable|string',
                'jurisdiction_scope' => 'nullable|string|in:National,State,Multi-State',
                'functions' => 'nullable|array',
                'exam_types_conducted' => 'nullable|array',
                'evaluation_methods' => 'nullable|array',
                'exams_conducted_ids' => 'nullable|array',
                'annual_exam_volume_estimate' => 'nullable|string',
                'average_candidates_per_year' => 'nullable|string',
                'exam_modes_supported' => 'nullable|array',
                'question_bank_managed' => 'nullable',
                'normalization_process_available' => 'nullable',
                'multi_language_support' => 'nullable',
                'remote_proctoring_supported' => 'nullable',
                'exam_centres_management_type' => 'nullable|string|in:In-house,Outsourced,Hybrid',
                'technology_partners' => 'nullable|array',
                'logistics_partners' => 'nullable|array',
                'data_security_standards' => 'nullable|string',
                'result_declaration_policy_summary' => 'nullable|string',
                'score_validity_period' => 'nullable|string',
                're_evaluation_allowed' => 'nullable|boolean',
                're_evaluation_process_summary' => 'nullable|string',
                'data_retention_policy' => 'nullable|string',
                'grievance_redressal_mechanism' => 'nullable|string',
                'candidate_portal_url' => 'nullable|string',
                'helpdesk_contact_number' => 'nullable|string',
                'helpdesk_email' => 'nullable|string',
                'official_notifications_urls' => 'nullable|array',
                'faq_url' => 'nullable|string',
                'rti_applicable' => 'nullable|boolean',
                'audit_conducted' => 'nullable|boolean',
                'exam_fairness_policy' => 'nullable|string',
                'anti_malpractice_measures' => 'nullable|array',
                'whistleblower_policy_available' => 'nullable|boolean',
                'awards_or_recognition' => 'nullable|array',
                'media_mentions' => 'nullable|array',
                'public_trust_score' => 'nullable|integer',
                'focus_keywords' => 'nullable|array',
                'claimed_by_authority' => 'nullable|boolean',
                'data_source' => 'nullable|string',
                'confidence_score' => 'nullable|integer',
                'verification_status' => 'nullable|string|in:Pending,Verified,Rejected',
                'status' => 'nullable|string|in:Active,Inactive,Archived',
            ];
        }

        // Type 6 & 7: Counselling Body & Regulatory Body
        if (in_array($request->organisation_type_id, [6, 7])) {
            $rules += [
                'logo_url' => 'nullable|image|max:2048',
                'abbreviation' => 'nullable|string|max:100',
                'established_year' => 'nullable|integer',
                'about_organisation' => 'nullable|string',
                'mandate_description' => 'nullable|string',
                'authority_type' => 'nullable|string|in:Statutory Body,Government Committee,Autonomous Body,Constitutional Body,Government Agency',
                'parent_ministry_or_department' => 'nullable|string|max:255',
                'established_by' => 'nullable|string|in:Government Notification,Act / Resolution,Act of Parliament,Government Resolution',
                'legal_reference_document_url' => 'nullable|string|max:255',
                'jurisdiction_scope' => 'nullable|string|in:National,State,Regional,Multi-State',
                'jurisdiction_states' => 'nullable|string',
                'counselling_functions' => 'nullable|array',
                'functions' => 'nullable|array',
                'counselling_types_supported' => 'nullable|array',
                'education_domains_supported' => 'nullable|array',
                'counselling_levels_supported' => 'nullable|array',
                'exams_used_for_counselling_ids' => 'nullable|string',
                'allocation_basis' => 'nullable|string|in:Rank,Score,Composite Merit',
                'rank_source_validation_required' => 'nullable',
                'multiple_exam_support' => 'nullable',
                'seat_matrix_management' => 'nullable',
                'seat_matrix_source' => 'nullable|string|in:Institutions,Regulatory Body,Combined',
                'quota_types_managed' => 'nullable|array',
                'reservation_policy_reference' => 'nullable|string',
                'seat_conversion_rules_supported' => 'nullable',
                'rounds_supported' => 'nullable|string',
                'round_types' => 'nullable|array',
                'choice_locking_mandatory' => 'nullable',
                'seat_upgradation_allowed' => 'nullable',
                'withdrawal_rules_summary' => 'nullable|string',
                'exit_rules_summary' => 'nullable|string',
                'counselling_fee_collection_supported' => 'nullable',
                'fee_collection_mode' => 'nullable|string|in:Direct to Authority,Through Exam Portal',
                'refund_processing_responsibility' => 'nullable|string|in:Authority,Exam Body',
                'security_deposit_handling' => 'nullable',
                'counselling_portal_url' => 'nullable|url',
                'candidate_login_system_available' => 'nullable',
                'choice_filling_system_available' => 'nullable',
                'auto_seat_allocation_engine' => 'nullable',
                'api_integration_supported' => 'nullable',
                'data_security_standards' => 'nullable|string',
                'institution_reporting_interface_available' => 'nullable',
                'document_verification_mode' => 'nullable|string|in:Online,Physical,Hybrid',
                'institution_confirmation_process_summary' => 'nullable|string',
                'mis_reporting_controls' => 'nullable|string',
                'grievance_redressal_mechanism' => 'nullable|string',
                'appeal_process_summary' => 'nullable|string',
                'grievance_contact_details' => 'nullable|string',
                'rti_applicable' => 'nullable',
                'audit_conducted' => 'nullable',
                'candidate_support_url' => 'nullable|url',
                'candidate_handbook_url' => 'nullable|url',
                'candidate_guidelines_url' => 'nullable|url',
                'helpdesk_toll_free_number' => 'nullable|string',
                'helpdesk_operational_hours' => 'nullable|string',
                'official_website' => 'nullable|url',
                'helpdesk_contact_number' => 'nullable|string',
                'helpdesk_email' => 'nullable|email',
                'faq_url' => 'nullable|url',
                'official_notifications_urls' => 'nullable|string',
                'social_media_handles' => 'nullable|string',
                'years_of_operation' => 'nullable|integer',
                'total_candidates_handled_estimate' => 'nullable|string',
                'total_seats_allocated_estimate' => 'nullable|string',
                'annual_candidate_volume' => 'nullable|string',
                'institutions_covered_count' => 'nullable|integer',
                'states_covered_count' => 'nullable|integer',
                'media_mentions' => 'nullable|string',
                'public_trust_rating' => 'nullable|numeric|between:0,10',
                'schema_type' => 'nullable|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'canonical_url' => 'nullable|url',
                'focus_keywords' => 'nullable|string',
                'data_source' => 'nullable|string',
                'confidence_score' => 'nullable|integer',
                'verification_status' => 'nullable|string|in:Pending,Verified,Rejected',
                'status' => 'nullable|string|in:Active,Inactive,Archived',
            ];
        }

        $request->validate($rules);

        $data = $request->except(['logo_url', 'cover_image_url', 'legal_documents_urls', 'recognition_documents', '_token']);

        // Handle Array fields (comma separated strings -> arrays)
        if (in_array($request->organisation_type_id, [5, 6, 7])) {
            $arrayFields = [
                'exams_conducted_ids',
                'technology_partners',
                'logistics_partners',
                'official_notifications_urls',
                'anti_malpractice_measures',
                'awards_or_recognition',
                'media_mentions',
                'focus_keywords',
                'core_values', // Added core_values here
                // Counselling/Regulatory Body fields
                'jurisdiction_states',
                'exams_used_for_counselling_ids',
                'social_media_handles'
            ];
            foreach ($arrayFields as $field) {
                if ($request->has($field) && is_string($request->$field)) {
                    $data[$field] = array_map('trim', explode(',', $request->$field));
                }
            }
        }

        // Handle Single File Uploads
        if ($request->hasFile('logo_url')) {
            $name = time() . '_logo.' . $request->logo_url->extension();
            $path = match ($request->organisation_type_id) {
                '3' => 'media/institutes',
                '4' => 'media/schools',
                '6' => 'media/counselling_bodies',
                '7' => 'media/regulatory_bodies',
                default => 'media/universities'
            };
            $request->logo_url->move(public_path($path), $name);
            $data['logo_url'] = $path . '/' . $name;
        }

        if ($request->hasFile('cover_image_url')) {
            $name = time() . '_cover.' . $request->cover_image_url->extension();
            $path = match ($request->organisation_type_id) {
                '3' => 'media/institutes',
                '4' => 'media/schools',
                '6' => 'media/counselling_bodies',
                '7' => 'media/regulatory_bodies',
                default => 'media/universities'
            };
            $request->cover_image_url->move(public_path($path), $name);
            $data['cover_image_url'] = $path . '/' . $name;
        }

        // Handle Multiple Files Upload (Legal Documents)
        if ($request->hasFile('legal_documents_urls')) {
            $legalDocs = [];
            $docPath = $request->organisation_type_id == 4 ? 'media/schools/legal' : 'media/institutes/legal';
            foreach ($request->file('legal_documents_urls') as $file) {
                $name = time() . '_' . uniqid() . '.' . $file->extension();
                $file->move(public_path($docPath), $name);
                $legalDocs[] = $docPath . '/' . $name;
            }
            $data['legal_documents_urls'] = $legalDocs;
        }

        // Handle Recognition Documents (Universities)
        if ($request->hasFile('recognition_documents')) {
            $recDocs = [];
            $docPath = 'media/universities/recognition';
            foreach ($request->file('recognition_documents') as $file) {
                $name = time() . '_' . uniqid() . '.' . $file->extension();
                $file->move(public_path($docPath), $name);
                $recDocs[] = $docPath . '/' . $name;
            }
            $data['recognition_documents'] = $recDocs;
        }

        // Handle Booleans
        $booleans = [
            'degree_awarding_authority',
            'ugc_recognized',
            'aicte_approved',
            'naac_accredited',
            'autonomous_status',
            'gst_registered',
            'minority_status',
            'international_curriculum_supported',
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
            'national_presence',
            'international_presence',
            'mobile_app_available',
            'claimed_by_organization',
            // Exam Conducting Body Booleans
            'question_bank_managed',
            'normalization_process_available',
            'multi_language_support',
            'remote_proctoring_supported',
            're_evaluation_allowed',
            'rti_applicable',
            'audit_conducted',
            'whistleblower_policy_available',
            'claimed_by_authority',
            // Counselling Body Booleans
            'rank_source_validation_required',
            'multiple_exam_support',
            'seat_matrix_management',
            'seat_conversion_rules_supported',
            'choice_locking_mandatory',
            'seat_upgradation_allowed',
            'counselling_fee_collection_supported',
            'security_deposit_handling',
            'candidate_login_system_available',
            'choice_filling_system_available',
            'auto_seat_allocation_engine',
            'api_integration_supported',
            'institution_reporting_interface_available'
        ];
        foreach ($booleans as $boolField) {
            $data[$boolField] = $request->has($boolField);
        }

        Organisation::create($data);

        return redirect()->route('admin.organisations.index')->with('success', 'Organisation added successfully.');
    }

    public function edit(Organisation $organisation)
    {
        $organisationTypes = \App\Models\OrganisationType::where('status', true)->get();
        $brandTypes = Organisation::BRAND_TYPES;

        return view('admin.organisations.edit', compact('organisation', 'organisationTypes', 'brandTypes'));
    }

    public function update(Request $request, Organisation $organisation)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'organisation_id_number' => 'nullable|string|max:255',
            'brand_type' => 'nullable|string|in:' . implode(',', Organisation::BRAND_TYPES),
            'central_authority' => 'nullable|string|max:255',
            'head_office_location' => 'nullable|string|max:255',
        ];

        if (in_array($request->organisation_type_id, [1, 2])) {
            $rules += [
                'logo_url' => 'nullable|image|max:2048',
                'cover_image_url' => 'nullable|image|max:2048',
                'established_year' => 'nullable|integer',
                'core_values' => 'nullable|array',
                'international_accreditations' => 'nullable|array',
                'statutory_approvals' => 'nullable|array',
                'recognition_documents' => 'nullable|array',
                'levels_offered' => 'nullable|array',
            ];
        }

        if ($request->organisation_type_id == 3) {
            $rules += [
                'logo_url' => 'nullable|image|max:2048',
                'cover_image_url' => 'nullable|image|max:2048',
                'established_year' => 'nullable|integer',
                'core_values' => 'nullable|array',
                'gst_registered' => 'nullable|boolean',
                'legal_documents_urls' => 'nullable|array',
                'legal_documents_urls.*' => 'file|max:5120',
            ];
        }

        if ($request->organisation_type_id == 4) {
            $rules += [
                'logo_url' => 'nullable|image|max:2048',
                'cover_image_url' => 'nullable|image|max:2048',
                'established_year' => 'nullable|integer',
                'core_values' => 'nullable|array',
                'managing_trust_or_society_name' => 'nullable|string',
                'minority_status' => 'nullable|boolean',
                'education_boards_supported' => 'nullable|array',
                'medium_of_instruction_supported' => 'nullable|array',
                'international_curriculum_supported' => 'nullable|boolean',
                'education_levels_supported' => 'nullable|array',
                'streams_supported' => 'nullable|array',
                'focus_areas' => 'nullable|array',
                'centralized_curriculum_framework' => 'nullable|boolean',
                'centralized_teacher_training' => 'nullable|boolean',
                'centralized_assessment_policy' => 'nullable|boolean',
                'centralized_lms_available' => 'nullable|boolean',
                'centralized_parent_communication_system' => 'nullable|boolean',
                'child_safety_policy_available' => 'nullable|boolean',
                'posco_compliance_policy' => 'nullable|boolean',
                'anti_bullying_policy' => 'nullable|boolean',
                'mental_health_policy' => 'nullable|boolean',
                'teacher_background_verification_policy' => 'nullable|boolean',
                'total_schools_count' => 'nullable|integer',
                'cities_present_in' => 'nullable|array',
                'states_present_in' => 'nullable|array',
                'national_presence' => 'nullable|boolean',
                'international_presence' => 'nullable|boolean',
                'flagship_schools' => 'nullable|array',
                'official_website' => 'nullable|url',
                'admission_portal_url' => 'nullable|url',
                'parent_portal_url' => 'nullable|url',
                'student_portal_url' => 'nullable|url',
                'mobile_app_available' => 'nullable|boolean',
                'average_rating' => 'nullable|numeric|between:0,5',
                'total_reviews' => 'nullable|integer',
                'awards_and_recognition' => 'nullable|array',
                'claimed_by_organization' => 'nullable|boolean',
                'legal_documents_urls' => 'nullable|array',
                'legal_documents_urls.*' => 'file|max:5120',
            ];
        }

        // Type 5: Exam Conducting Body
        if ($request->organisation_type_id == 5) {
            $rules += [
                'abbreviation' => 'nullable|string|max:100',
                'mandate_description' => 'nullable|string',
                'authority_type' => 'nullable|string|in:Constitutional Body,Statutory Body,Government Agency,Autonomous Body',
                'parent_ministry' => 'nullable|string|in:Ministry of Education,DoPT,Ministry of Defence',
                'established_by' => 'nullable|string|in:Act of Parliament,Government Resolution',
                'legal_act_reference' => 'nullable|string',
                'headquarters_location' => 'nullable|string',
                'jurisdiction_scope' => 'nullable|string|in:National,State,Multi-State',
                'functions' => 'nullable|array',
                'exam_types_conducted' => 'nullable|array',
                'evaluation_methods' => 'nullable|array',
                'exams_conducted_ids' => 'nullable|array',
                'annual_exam_volume_estimate' => 'nullable|string',
                'average_candidates_per_year' => 'nullable|string',
                'exam_modes_supported' => 'nullable|array',
                'question_bank_managed' => 'nullable',
                'normalization_process_available' => 'nullable',
                'multi_language_support' => 'nullable',
                'remote_proctoring_supported' => 'nullable',
                'exam_centres_management_type' => 'nullable|string|in:In-house,Outsourced,Hybrid',
                'technology_partners' => 'nullable|array',
                'logistics_partners' => 'nullable|array',
                'data_security_standards' => 'nullable|string|in:ISO,Government Norms',
                'result_declaration_policy_summary' => 'nullable|string',
                'score_validity_period' => 'nullable|string',
                're_evaluation_allowed' => 'nullable',
                're_evaluation_process_summary' => 'nullable|string',
                'data_retention_policy' => 'nullable|string',
                'grievance_redressal_mechanism' => 'nullable|string',
                'candidate_portal_url' => 'nullable|string',
                'helpdesk_contact_number' => 'nullable|string',
                'helpdesk_email' => 'nullable|string',
                'official_notifications_urls' => 'nullable|array',
                'faq_url' => 'nullable|string',
                'rti_applicable' => 'nullable',
                'audit_conducted' => 'nullable',
                'exam_fairness_policy' => 'nullable|string',
                'anti_malpractice_measures' => 'nullable|array',
                'whistleblower_policy_available' => 'nullable',
                'awards_or_recognition' => 'nullable|array',
                'media_mentions' => 'nullable|array',
                'public_trust_score' => 'nullable|integer',
                'focus_keywords' => 'nullable|array',
                'claimed_by_authority' => 'nullable',
                'data_source' => 'nullable|string',
                'confidence_score' => 'nullable|integer',
                'verification_status' => 'nullable|string|in:Pending,Verified,Rejected',
                'status' => 'nullable|string|in:Active,Inactive,Archived',
            ];
        }

        // Type 6 & 7: Counselling Body & Regulatory Body
        if (in_array($request->organisation_type_id, [6, 7])) {
            $rules += [
                'logo_url' => 'nullable|image|max:2048',
                'abbreviation' => 'nullable|string|max:100',
                'established_year' => 'nullable|integer',
                'about_organisation' => 'nullable|string',
                'mandate_description' => 'nullable|string',
                'authority_type' => 'nullable|string|in:Statutory Body,Government Committee,Autonomous Body,Constitutional Body,Government Agency',
                'parent_ministry_or_department' => 'nullable|string|max:255',
                'established_by' => 'nullable|string|in:Government Notification,Act / Resolution,Act of Parliament,Government Resolution',
                'legal_reference_document_url' => 'nullable|string|max:255',
                'jurisdiction_scope' => 'nullable|string|in:National,State,Regional,Multi-State',
                'jurisdiction_states' => 'nullable|string',
                'counselling_functions' => 'nullable|array',
                'functions' => 'nullable|array',
                'counselling_types_supported' => 'nullable|array',
                'education_domains_supported' => 'nullable|array',
                'counselling_levels_supported' => 'nullable|array',
                'exams_used_for_counselling_ids' => 'nullable|string',
                'allocation_basis' => 'nullable|string|in:Rank,Score,Composite Merit',
                'rank_source_validation_required' => 'nullable',
                'multiple_exam_support' => 'nullable',
                'seat_matrix_management' => 'nullable',
                'seat_matrix_source' => 'nullable|string|in:Institutions,Regulatory Body,Combined',
                'quota_types_managed' => 'nullable|array',
                'reservation_policy_reference' => 'nullable|string',
                'seat_conversion_rules_supported' => 'nullable',
                'rounds_supported' => 'nullable|string',
                'round_types' => 'nullable|array',
                'choice_locking_mandatory' => 'nullable',
                'seat_upgradation_allowed' => 'nullable',
                'withdrawal_rules_summary' => 'nullable|string',
                'exit_rules_summary' => 'nullable|string',
                'counselling_fee_collection_supported' => 'nullable',
                'fee_collection_mode' => 'nullable|string|in:Direct to Authority,Through Exam Portal',
                'refund_processing_responsibility' => 'nullable|string|in:Authority,Exam Body',
                'security_deposit_handling' => 'nullable',
                'counselling_portal_url' => 'nullable|url',
                'candidate_login_system_available' => 'nullable',
                'choice_filling_system_available' => 'nullable',
                'auto_seat_allocation_engine' => 'nullable',
                'api_integration_supported' => 'nullable',
                'data_security_standards' => 'nullable|string',
                'institution_reporting_interface_available' => 'nullable|boolean',
                'document_verification_mode' => 'nullable|string|in:Online,Physical,Hybrid',
                'institution_confirmation_process_summary' => 'nullable|string',
                'mis_reporting_controls' => 'nullable|string',
                'grievance_redressal_mechanism' => 'nullable|string',
                'appeal_process_summary' => 'nullable|string',
                'grievance_contact_details' => 'nullable|string',
                'rti_applicable' => 'nullable|boolean',
                'audit_conducted' => 'nullable|boolean',
                'candidate_support_url' => 'nullable|url',
                'candidate_handbook_url' => 'nullable|url',
                'candidate_guidelines_url' => 'nullable|url',
                'helpdesk_toll_free_number' => 'nullable|string',
                'helpdesk_operational_hours' => 'nullable|string',
                'official_website' => 'nullable|url',
                'helpdesk_contact_number' => 'nullable|string',
                'helpdesk_email' => 'nullable|email',
                'faq_url' => 'nullable|url',
                'official_notifications_urls' => 'nullable|string',
                'social_media_handles' => 'nullable|string',
                'years_of_operation' => 'nullable|integer',
                'total_candidates_handled_estimate' => 'nullable|string',
                'total_seats_allocated_estimate' => 'nullable|string',
                'annual_candidate_volume' => 'nullable|string',
                'institutions_covered_count' => 'nullable|integer',
                'states_covered_count' => 'nullable|integer',
                'media_mentions' => 'nullable|string',
                'public_trust_rating' => 'nullable|numeric|between:0,10',
                'schema_type' => 'nullable|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'canonical_url' => 'nullable|url',
                'focus_keywords' => 'nullable|string',
                'data_source' => 'nullable|string',
                'confidence_score' => 'nullable|integer',
                'verification_status' => 'nullable|string|in:Pending,Verified,Rejected',
                'status' => 'nullable|string|in:Active,Inactive,Archived',
            ];
        }

        $request->validate($rules);

        $data = $request->except(['logo_url', 'cover_image_url', 'legal_documents_urls', 'recognition_documents', '_token', '_method']);

        // Handle Single File Uploads
        if ($request->hasFile('logo_url')) {
            if ($organisation->logo_url && file_exists(public_path($organisation->logo_url))) {
                @unlink(public_path($organisation->logo_url));
            }
            $name = time() . '_logo.' . $request->logo_url->extension();
            $path = match ($request->organisation_type_id) {
                '3' => 'media/institutes',
                '4' => 'media/schools',
                '6' => 'media/counselling_bodies',
                '7' => 'media/regulatory_bodies',
                default => 'media/universities'
            };
            $request->logo_url->move(public_path($path), $name);
            $data['logo_url'] = $path . '/' . $name;
        }

        if ($request->hasFile('cover_image_url')) {
            if ($organisation->cover_image_url && file_exists(public_path($organisation->cover_image_url))) {
                @unlink(public_path($organisation->cover_image_url));
            }
            $name = time() . '_cover.' . $request->cover_image_url->extension();
            $path = match ($request->organisation_type_id) {
                '3' => 'media/institutes',
                '4' => 'media/schools',
                '6' => 'media/counselling_bodies',
                '7' => 'media/regulatory_bodies',
                default => 'media/universities'
            };
            $request->cover_image_url->move(public_path($path), $name);
            $data['cover_image_url'] = $path . '/' . $name;
        }

        // Handle Multiple Files Upload (Legal Documents)
        if ($request->hasFile('legal_documents_urls')) {
            $legalDocs = $organisation->legal_documents_urls ?? [];
            $docPath = $request->organisation_type_id == 4 ? 'media/schools/legal' : 'media/institutes/legal';
            foreach ($request->file('legal_documents_urls') as $file) {
                $name = time() . '_' . uniqid() . '.' . $file->extension();
                $file->move(public_path($docPath), $name);
                $legalDocs[] = $docPath . '/' . $name;
            }
            $data['legal_documents_urls'] = $legalDocs;
        }

        // Handle Recognition Documents (Universities)
        if ($request->hasFile('recognition_documents')) {
            $recDocs = $organisation->recognition_documents ?? [];
            $docPath = 'media/universities/recognition';
            foreach ($request->file('recognition_documents') as $file) {
                $name = time() . '_' . uniqid() . '.' . $file->extension();
                $file->move(public_path($docPath), $name);
                $recDocs[] = $docPath . '/' . $name;
            }
            $data['recognition_documents'] = $recDocs;
        }

        // Handle Booleans
        $booleans = [
            'degree_awarding_authority',
            'ugc_recognized',
            'aicte_approved',
            'naac_accredited',
            'autonomous_status',
            'gst_registered',
            'minority_status',
            'international_curriculum_supported',
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
            'national_presence',
            'international_presence',
            'mobile_app_available',
            'claimed_by_organization',
            // Exam Conducting Body Booleans
            'question_bank_managed',
            'normalization_process_available',
            'multi_language_support',
            'remote_proctoring_supported',
            're_evaluation_allowed',
            'rti_applicable',
            'audit_conducted',
            'whistleblower_policy_available',
            'claimed_by_authority',
            // Counselling Body Booleans
            'rank_source_validation_required',
            'multiple_exam_support',
            'seat_matrix_management',
            'seat_conversion_rules_supported',
            'choice_locking_mandatory',
            'seat_upgradation_allowed',
            'counselling_fee_collection_supported',
            'security_deposit_handling',
            'candidate_login_system_available',
            'choice_filling_system_available',
            'auto_seat_allocation_engine',
            'api_integration_supported',
            'institution_reporting_interface_available',
            'rti_applicable',
            'audit_conducted',
            'claimed_by_authority'
        ];
        foreach ($booleans as $boolField) {
            $data[$boolField] = $request->has($boolField);
        }

        // Handle Array fields
        $arrayFields = [
            'exams_conducted_ids',
            'technology_partners',
            'logistics_partners',
            'official_notifications_urls',
            'anti_malpractice_measures',
            'awards_or_recognition',
            'media_mentions',
            'focus_keywords',
            'core_values', // Added core_values here
            // Counselling Body fields
            'jurisdiction_states',
            'exams_used_for_counselling_ids',
            'social_media_handles'
        ];
        foreach ($arrayFields as $field) {
            if ($request->has($field) && is_string($request->$field)) {
                $data[$field] = array_map('trim', explode(',', $request->$field));
            }
        }

        $organisation->update($data);

        return redirect()->route('admin.organisations.index')->with('success', 'Organisation updated successfully.');
    }

    public function destroy(Organisation $organisation)
    {
        $organisation->delete();
        return redirect()->route('admin.organisations.index')->with('success', 'Organisation deleted successfully.');
    }

    public function storeDraft(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'organisation_type_id' => 'required|exists:organisation_types,id',
        ]);

        $organisation = Organisation::create([
            'name' => $request->name,
            'organisation_type_id' => $request->organisation_type_id,
        ]);

        return response()->json([
            'status' => 'success',
            'organisation_id' => $organisation->id,
            'message' => 'Draft created successfully'
        ]);
    }

    public function autosave(Request $request, Organisation $organisation)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        // Handle File Uploads for Autosave
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            if ($organisation->$field && file_exists(public_path($organisation->$field))) {
                @unlink(public_path($organisation->$field));
            }
            $name = time() . '_' . $field . '.' . $file->extension();
            $path = match ((string) $organisation->organisation_type_id) {
                '3' => 'media/institutes',
                '4' => 'media/schools',
                '6' => 'media/counselling_bodies',
                '7' => 'media/regulatory_bodies',
                default => 'media/universities'
            };
            $file->move(public_path($path), $name);
            $value = $path . '/' . $name;
        }

        // Handle Array Fields for Autosave
        $casts = [
            'core_values', 'international_accreditations', 'statutory_approvals', 'recognition_documents',
            'levels_offered', 'legal_documents_urls', 'education_boards_supported',
            'medium_of_instruction_supported', 'education_levels_supported', 'streams_supported',
            'focus_areas', 'cities_present_in', 'states_present_in', 'flagship_schools',
            'awards_and_recognition', 'functions', 'exam_types_conducted', 'evaluation_methods',
            'exams_conducted_ids', 'exam_modes_supported', 'technology_partners', 'logistics_partners',
            'official_notifications_urls', 'anti_malpractice_measures', 'awards_or_recognition',
            'media_mentions', 'focus_keywords', 'jurisdiction_states', 'counselling_functions',
            'counselling_types_supported', 'education_domains_supported', 'counselling_levels_supported',
            'exams_used_for_counselling_ids', 'quota_types_managed', 'round_types'
        ];

        if (in_array($field, $casts) && is_string($value)) {
            // Fallback for cases where it's still sent as a comma-separated string
            $value = array_map('trim', explode(',', $value));
            $value = array_filter($value); // Remove empty values
        }

        $organisation->update([$field => $value]);

        return response()->json(['status' => 'success']);
    }

    public function getCampusesJson($id)
    {
        $campuses = \App\Models\Campus::where('organisation_id', $id)
            ->where('status', true)
            ->select('id', 'campus_name')
            ->get();
        return response()->json($campuses);
    }

    public function toggleStatus(Organisation $organisation)
    {
        $organisation->status = !$organisation->status;
        $organisation->save();

        $statusMessage = $organisation->status ? 'published' : 'unpublished';
        return back()->with('success', "Organisation {$statusMessage} successfully.");
    }
}
