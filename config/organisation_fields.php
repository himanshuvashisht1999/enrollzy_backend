<?php

/**
 * Rules for which organisation / campus / department / course fields the AI updater is allowed to read and write.
 * A field is usable only when ALL of these hold (see App\Services\OrganisationFields\OrganisationFieldPolicy):
 *   1. it is in the field catalogue,
 *   2. it is a real database column,
 *   3. it is on the real edit form that staff use for that organisation type (so a person can see and correct it),
 *   4. the admin ticked it for that organisation type on the Organisation Fields screen,
 *   5. it is not listed as "managed elsewhere" below (only when that switch is on).
 */
return [
    // Names that appear on the edit form under a different input name than the database column. [level => [column => input name]]
    'form_aliases' => [
        'organisation' => [],
        'campus' => [],
        'department' => [],
        'course' => [],
    ],

    // Catalogue names that never matched a column, pointed at the real column that holds the same thing (it is already on the edit form).
    // A saved Organisation Fields tick under the old name counts for the new one. [level => [old catalogue name => real column]]
    'catalogue_renames' => [
        'organisation' => [
            'trust_or_society_name' => 'managing_trust_or_society_name',
            'helpline_number' => 'helpdesk_contact_number',
            'support_email' => 'helpdesk_email',
            'act_name' => 'legal_act_reference',
            'nodal_ministry' => 'parent_ministry_or_department',
            'central_authority_type' => 'authority_type',
            'total_candidates_per_year' => 'average_candidates_per_year',
            'ai_proctoring_used' => 'remote_proctoring_supported',
            'gazette_notification_url' => 'legal_reference_document_url',
            'pocso_compliance_certified' => 'posco_compliance_policy',
        ],
        'campus' => [],
        'department' => [
            'name' => 'department_name',
            'code' => 'department_code',
            'hod_name' => 'head_of_department_name',
            'hod_designation' => 'head_of_department_designation',
        ],
        'course' => [],
    ],

    // Catalogue fields that cannot be saved anywhere today. They are hidden from the Organisation Fields screen and the AI updater
    // (and listed on the audit page) instead of being offered as options that silently do nothing. To bring one back: add the column
    // and an input on the edit form, then delete its line here. [level => [catalogue name => reason]]
    'unavailable' => [
        'organisation' => [
            // now kept as dated, verifiable records under Rankings & Accreditation
            'ugc_act_section' => 'records', 'naac_cgpa' => 'records', 'naac_validity_date' => 'records', 'nba_accredited' => 'records', 'approval_authority' => 'records',
            // set as a link to the real organisation on the Identity page
            'affiliating_university_name' => 'identity',
            // no column yet
            'registrar_name' => 'no_column', 'institute_category' => 'no_column',
            'system_type' => 'no_column', 'school_type' => 'no_column', 'gender_type' => 'no_column', 'day_boarding_type' => 'no_column', 'curriculum_type' => 'no_column',
            'management_type' => 'no_column', 'motto' => 'no_column', 'religious_affiliation' => 'no_column', 'founder_name' => 'no_column', 'leadership_structure' => 'no_column',
            'cbse_affiliation_number' => 'no_column', 'cisce_school_code' => 'no_column', 'ib_school_code' => 'no_column', 'cambridge_school_number' => 'no_column',
            'state_board_registration_number' => 'no_column', 'other_board_affiliations' => 'no_column', 'grade_range_offered' => 'no_column', 'special_education_support' => 'no_column',
            'gifted_student_program' => 'no_column', 'stem_steam_focus' => 'no_column', 'experiential_learning_programs' => 'no_column', 'anti_bullying_policy_url' => 'no_column',
            'cctv_coverage_in_campuses' => 'no_column', 'disciplinary_policy_summary' => 'no_column', 'uniform_mandatory' => 'no_column', 'transport_safety_certified' => 'no_column',
            'medical_room_available' => 'no_column', 'annual_medical_checkup' => 'no_column', 'student_insurance_coverage' => 'no_column', 'central_contact_email' => 'no_column',
            'central_helpline_number' => 'no_column', 'headquarters_city' => 'no_column', 'headquarters_state' => 'no_column', 'statutory_body' => 'no_column', 'act_year' => 'no_column',
            'autonomous_body' => 'no_column', 'curriculum_setting_authority' => 'no_column', 'admissions_counselling_handled' => 'no_column', 'result_declaration_authority' => 'no_column',
            'frequency_of_exams' => 'no_column', 'computer_based_test_centers_count' => 'no_column', 'biometric_verification_used' => 'no_column', 'grievance_redressal_portal_url' => 'no_column',
        ],
        'campus' => [],
        'department' => [],
        'course' => [
            // picked from a master list on the form (specialization_ids / entrance_exam_ids): the AI cannot choose master ids
            'specialization' => 'master', 'entrance_exams' => 'master',
        ],
    ],

    // The ranking / accreditation / approval / award / rating / placement values are now kept as dated, verifiable RECORDS
    // (Admin > Rankings & Accreditation). Turn this on to stop the AI updater writing the old typed columns for them.
    // Off by default so nothing changes until you decide; the Organisation Fields screen shows these fields either way.
    'block_quality_managed' => false,

    // Old typed columns that are superseded by records. [level => [column, ...]]
    'quality_managed' => [
        'organisation' => [
            'nirf_rank_overall', 'nirf_rank_category', 'naac_accredited', 'naac_grade', 'naac_cgpa', 'naac_validity_date', 'nba_accredited', 'aicte_approved',
            'ugc_recognized', 'international_accreditations', 'statutory_approvals', 'awards_and_recognition', 'awards_or_recognition', 'media_mentions',
            'is_top', 'average_rating', 'total_reviews', 'public_trust_score', 'confidence_score',
        ],
        'campus' => ['reviews'],
        'department' => ['college_reviews', 'placement_statistics', 'confidence_score'],
        'course' => ['roi', 'rating', 'internship_ranking', 'placement_details'],
    ],
];
