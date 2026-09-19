<?php

namespace App\Services;

class OrganisationFieldCatalogService
{
    /**
     * Get entity tabs and fields for a specific organisation type.
     */
    public function getOrganisationTabs(int $typeId): array
    {
        $tabs = [];

        // Common Core Institutional Identity fields
        $tabs[] = [
            'id' => 'org-common',
            'title' => 'Core Institutional Identity',
            'icon' => 'fas fa-id-card',
            'fields' => [
                ['name' => 'name', 'label' => 'Organisation Name', 'required' => true],
                ['name' => 'organisation_type_id', 'label' => 'Organisation Type', 'required' => true],
                ['name' => 'central_authority', 'label' => 'Central Authority / Trust'],
                ['name' => 'head_office_location', 'label' => 'Head Office Location'],
                ['name' => 'is_top', 'label' => 'Is Top Institution Flag'],
                ['name' => 'core_values', 'label' => 'Core Values'],
                ['name' => 'established_year', 'label' => 'Established Year'],
                ['name' => 'ownership_type', 'label' => 'Ownership Type'],
                ['name' => 'about_organisation', 'label' => 'About Organisation Overview'],
            ]
        ];

        // Type-specific sections matching edit.blade.php
        if (in_array($typeId, [1, 2])) { // University / College
            $tabs[] = [
                'id' => 'org-uni-core',
                'title' => 'Core Identity',
                'icon' => 'fas fa-university',
                'fields' => [
                    ['name' => 'brand_name', 'label' => 'Brand Name'],
                    ['name' => 'short_name', 'label' => 'Short Name / Acronym'],
                    ['name' => 'university_type', 'label' => 'University Type'],
                    ['name' => 'official_website', 'label' => 'Official Website URL'],
                    ['name' => 'admission_portal_url', 'label' => 'Admission Portal URL'],
                    ['name' => 'student_portal_url', 'label' => 'Student Portal URL'],
                    ['name' => 'logo_url', 'label' => 'Logo Image'],
                    ['name' => 'cover_image_url', 'label' => 'Cover Image'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-uni-legal',
                'title' => 'Legal & Regulatory',
                'icon' => 'fas fa-balance-scale',
                'fields' => [
                    ['name' => 'degree_awarding_authority', 'label' => 'Degree Awarding Authority'],
                    ['name' => 'ugc_act_section', 'label' => 'UGC Act Section (2f/12B)'],
                    ['name' => 'naac_grade', 'label' => 'NAAC Grade'],
                    ['name' => 'naac_cgpa', 'label' => 'NAAC CGPA Score'],
                    ['name' => 'naac_validity_date', 'label' => 'NAAC Validity Date'],
                    ['name' => 'nirf_rank_overall', 'label' => 'NIRF Rank (Overall)'],
                    ['name' => 'nirf_rank_category', 'label' => 'NIRF Rank (Category)'],
                    ['name' => 'nba_accredited', 'label' => 'NBA Accredited Status'],
                    ['name' => 'international_accreditations', 'label' => 'International Accreditations (QS/THE/AACSB)'],
                    ['name' => 'statutory_approvals', 'label' => 'Statutory Approvals (AICTE, BCI, PCI, NMC)'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-uni-gov',
                'title' => 'Governance & Administration',
                'icon' => 'fas fa-sitemap',
                'fields' => [
                    ['name' => 'governing_body_name', 'label' => 'Governing Body / Trust Name'],
                    ['name' => 'chancellor_name', 'label' => 'Chancellor Name'],
                    ['name' => 'vice_chancellor_name', 'label' => 'Vice Chancellor Name'],
                    ['name' => 'university_category', 'label' => 'University Category (Teaching / Research / Teaching + Research)'],
                    ['name' => 'registrar_name', 'label' => 'Registrar Name'],
                    ['name' => 'number_of_campuses', 'label' => 'Number of Campuses'],
                    ['name' => 'number_of_constituent_colleges', 'label' => 'Number of Constituent Colleges'],
                    ['name' => 'number_of_affiliated_colleges', 'label' => 'Number of Affiliated Colleges'],
                    ['name' => 'autonomous_status', 'label' => 'Autonomous Status Flag'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-uni-academic',
                'title' => 'Academic Scope',
                'icon' => 'fas fa-graduation-cap',
                'fields' => [
                    ['name' => 'levels_offered', 'label' => 'Levels Offered (Diploma, UG, PG, Doctoral)'],
                ]
            ];
        } elseif ($typeId === 3) { // Institute
            $tabs[] = [
                'id' => 'org-inst-core',
                'title' => 'Core Identity',
                'icon' => 'fas fa-landmark',
                'fields' => [
                    ['name' => 'brand_name', 'label' => 'Brand Name'],
                    ['name' => 'short_name', 'label' => 'Short Name'],
                    ['name' => 'institute_category', 'label' => 'Institute Category'],
                    ['name' => 'affiliating_university_name', 'label' => 'Affiliating University Name'],
                    ['name' => 'approval_authority', 'label' => 'Approval Authority (AICTE/UGC)'],
                    ['name' => 'official_website', 'label' => 'Official Website URL'],
                    ['name' => 'logo_url', 'label' => 'Logo Image'],
                    ['name' => 'cover_image_url', 'label' => 'Cover Image'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-inst-legal',
                'title' => 'Legal & Regulatory',
                'icon' => 'fas fa-balance-scale',
                'fields' => [
                    ['name' => 'aicte_approved', 'label' => 'AICTE Approved Flag'],
                    ['name' => 'naac_grade', 'label' => 'NAAC Grade'],
                    ['name' => 'nirf_rank_overall', 'label' => 'NIRF Rank Overall'],
                    ['name' => 'nba_accredited', 'label' => 'NBA Accredited Status'],
                ]
            ];
        } elseif ($typeId === 4) { // School
            $tabs[] = [
                'id' => 'org-sch-core',
                'title' => 'Core Identity',
                'icon' => 'fas fa-school',
                'fields' => [
                    ['name' => 'brand_name', 'label' => 'Brand Name'],
                    ['name' => 'short_name', 'label' => 'Short Name'],
                    ['name' => 'system_type', 'label' => 'System Type (Standalone / Chain)'],
                    ['name' => 'school_type', 'label' => 'School Category Type'],
                    ['name' => 'gender_type', 'label' => 'Gender Policy (Co-ed / Boys / Girls)'],
                    ['name' => 'day_boarding_type', 'label' => 'Boarding Policy (Day / Boarding / Mixed)'],
                    ['name' => 'education_boards_supported', 'label' => 'Education Boards Supported (CBSE, ICSE, IB)'],
                    ['name' => 'medium_of_instruction_supported', 'label' => 'Medium of Instruction Supported'],
                    ['name' => 'education_levels_supported', 'label' => 'Education Levels Supported'],
                    ['name' => 'streams_supported', 'label' => 'Senior Secondary Streams Supported'],
                    ['name' => 'focus_areas', 'label' => 'Institutional Focus Areas'],
                    ['name' => 'curriculum_type', 'label' => 'Curriculum Framework'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-sch-ownership',
                'title' => 'Ownership & Philosophy',
                'icon' => 'fas fa-hands-helping',
                'fields' => [
                    ['name' => 'management_type', 'label' => 'Management Type (Trust/Society/Pvt)'],
                    ['name' => 'trust_or_society_name', 'label' => 'Registered Trust / Society Name'],
                    ['name' => 'motto', 'label' => 'School Motto / Tagline'],
                    ['name' => 'religious_affiliation', 'label' => 'Religious / Minority Affiliation'],
                    ['name' => 'founder_name', 'label' => 'Founder Name'],
                    ['name' => 'leadership_structure', 'label' => 'Leadership Structure'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-sch-boards',
                'title' => 'Boards & Academic Offerings',
                'icon' => 'fas fa-certificate',
                'fields' => [
                    ['name' => 'cbse_affiliation_number', 'label' => 'CBSE Affiliation Number'],
                    ['name' => 'cisce_school_code', 'label' => 'CISCE School Code'],
                    ['name' => 'ib_school_code', 'label' => 'IB World School Code'],
                    ['name' => 'cambridge_school_number', 'label' => 'Cambridge School Number'],
                    ['name' => 'state_board_registration_number', 'label' => 'State Board Registration No'],
                    ['name' => 'other_board_affiliations', 'label' => 'Other Board Affiliations'],
                    ['name' => 'grade_range_offered', 'label' => 'Grade Range Offered'],
                    ['name' => 'special_education_support', 'label' => 'Special Education Support Flag'],
                    ['name' => 'gifted_student_program', 'label' => 'Gifted Student Programs Flag'],
                    ['name' => 'stem_steam_focus', 'label' => 'STEM / STEAM Focus Flag'],
                    ['name' => 'experiential_learning_programs', 'label' => 'Experiential Learning Programs'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-sch-policies',
                'title' => 'Safety, Policies & Welfare',
                'icon' => 'fas fa-shield-alt',
                'fields' => [
                    ['name' => 'anti_bullying_policy_url', 'label' => 'Anti-Bullying Policy URL'],
                    ['name' => 'pocso_compliance_certified', 'label' => 'POCSO Compliance Certified'],
                    ['name' => 'cctv_coverage_in_campuses', 'label' => 'CCTV Surveillance in Campuses'],
                    ['name' => 'disciplinary_policy_summary', 'label' => 'Disciplinary Policy Summary'],
                    ['name' => 'uniform_mandatory', 'label' => 'Uniform Mandatory Flag'],
                    ['name' => 'transport_safety_certified', 'label' => 'Transport Safety Certified'],
                    ['name' => 'medical_room_available', 'label' => 'Medical Infirmary Available'],
                    ['name' => 'annual_medical_checkup', 'label' => 'Annual Medical Checkup Provided'],
                    ['name' => 'student_insurance_coverage', 'label' => 'Student Insurance Coverage'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-sch-footprint',
                'title' => 'Geographic Footprint & Outreach',
                'icon' => 'fas fa-globe-asia',
                'fields' => [
                    ['name' => 'total_schools_count', 'label' => 'Total Schools Count in Chain'],
                    ['name' => 'cities_present_in', 'label' => 'Cities Present In'],
                    ['name' => 'states_present_in', 'label' => 'States Present In'],
                    ['name' => 'national_presence', 'label' => 'National Presence Flag'],
                    ['name' => 'international_presence', 'label' => 'International Presence Flag'],
                    ['name' => 'flagship_schools', 'label' => 'Flagship / Premier Campuses'],
                    ['name' => 'central_contact_email', 'label' => 'Central Admissions Email'],
                    ['name' => 'central_helpline_number', 'label' => 'Central Helpline Number'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-sch-seo',
                'title' => 'SEO, Ratings & Discovery',
                'icon' => 'fas fa-search',
                'fields' => [
                    ['name' => 'meta_title', 'label' => 'SEO Meta Title'],
                    ['name' => 'meta_description', 'label' => 'SEO Meta Description'],
                    ['name' => 'canonical_url', 'label' => 'Canonical URL'],
                    ['name' => 'average_rating', 'label' => 'Average User Rating'],
                    ['name' => 'total_reviews', 'label' => 'Total Verified Reviews Count'],
                    ['name' => 'awards_and_recognition', 'label' => 'Awards & Recognitions'],
                ]
            ];
        } elseif ($typeId === 5) { // Exam Conducting Body
            $tabs[] = [
                'id' => 'org-ecb-identity',
                'title' => 'Core Identity & Portals',
                'icon' => 'fas fa-id-badge',
                'fields' => [
                    ['name' => 'brand_name', 'label' => 'Brand Name / Short Title'],
                    ['name' => 'short_name', 'label' => 'Abbreviation (e.g. NTA, UPSC)'],
                    ['name' => 'headquarters_city', 'label' => 'Headquarters City'],
                    ['name' => 'headquarters_state', 'label' => 'Headquarters State'],
                    ['name' => 'jurisdiction_scope', 'label' => 'Jurisdiction Scope (National/State)'],
                    ['name' => 'official_website', 'label' => 'Official Website URL'],
                    ['name' => 'candidate_portal_url', 'label' => 'Candidate Application Portal URL'],
                    ['name' => 'helpline_number', 'label' => 'Candidate Helpline Number'],
                    ['name' => 'support_email', 'label' => 'Support / Helpdesk Email'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-ecb-legal',
                'title' => 'Legal Status & Governance',
                'icon' => 'fas fa-gavel',
                'fields' => [
                    ['name' => 'statutory_body', 'label' => 'Statutory Body Status'],
                    ['name' => 'act_name', 'label' => 'Governing Legislative Act Name'],
                    ['name' => 'act_year', 'label' => 'Legislative Act Enactment Year'],
                    ['name' => 'nodal_ministry', 'label' => 'Nodal Government Ministry / Dept'],
                    ['name' => 'central_authority_type', 'label' => 'Central Authority Type'],
                    ['name' => 'gazette_notification_url', 'label' => 'Official Gazette Notification URL'],
                    ['name' => 'autonomous_body', 'label' => 'Autonomous Body Flag'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-ecb-resp',
                'title' => 'Roles & Responsibilities',
                'icon' => 'fas fa-tasks',
                'fields' => [
                    ['name' => 'functions', 'label' => 'Key Statutory Functions'],
                    ['name' => 'exam_types_conducted', 'label' => 'Types of Examinations Conducted'],
                    ['name' => 'evaluation_methods', 'label' => 'Evaluation & Scoring Methods'],
                    ['name' => 'curriculum_setting_authority', 'label' => 'Curriculum / Syllabus Setting Authority'],
                    ['name' => 'admissions_counselling_handled', 'label' => 'Admissions / Counselling Handled'],
                    ['name' => 'result_declaration_authority', 'label' => 'Result Declaration Authority'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-ecb-cap',
                'title' => 'Testing Capabilities & Security',
                'icon' => 'fas fa-fingerprint',
                'fields' => [
                    ['name' => 'exam_modes_supported', 'label' => 'Exam Delivery Modes (CBT/OMR/Hybrid)'],
                    ['name' => 'frequency_of_exams', 'label' => 'Examination Cycle Frequency'],
                    ['name' => 'total_candidates_per_year', 'label' => 'Total Candidates Tested Annually'],
                    ['name' => 'computer_based_test_centers_count', 'label' => 'Verified CBT Test Centers Count'],
                    ['name' => 'biometric_verification_used', 'label' => 'Biometric & Aadhaar Verification Used'],
                    ['name' => 'ai_proctoring_used', 'label' => 'AI Remote Proctoring Used'],
                    ['name' => 'anti_malpractice_measures', 'label' => 'Anti-Malpractice Security Measures'],
                ]
            ];
        } elseif ($typeId === 6) { // Counselling Body
            $tabs[] = [
                'id' => 'org-cb-core',
                'title' => 'Core Identity & Portals',
                'icon' => 'fas fa-user-check',
                'fields' => [
                    ['name' => 'brand_name', 'label' => 'Brand Name / Acronym (MCC, JoSAA)'],
                    ['name' => 'official_website', 'label' => 'Official Counselling Portal URL'],
                    ['name' => 'candidate_login_system_available', 'label' => 'Candidate Portal Available'],
                    ['name' => 'choice_filling_system_available', 'label' => 'Choice Filling System Online'],
                    ['name' => 'auto_seat_allocation_engine', 'label' => 'Automated Seat Allocation Engine'],
                    ['name' => 'helpline_number', 'label' => 'Central Helpline Contact'],
                    ['name' => 'support_email', 'label' => 'Counselling Support Email'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-cb-scope',
                'title' => 'Jurisdiction & Seat Matrix',
                'icon' => 'fas fa-th-list',
                'fields' => [
                    ['name' => 'counselling_types_supported', 'label' => 'Counselling Types Supported (All India / State)'],
                    ['name' => 'education_domains_supported', 'label' => 'Education Domains (Medical, Engg, Law)'],
                    ['name' => 'seat_matrix_management', 'label' => 'Seat Matrix Management Handled'],
                    ['name' => 'seat_conversion_rules_supported', 'label' => 'Category Seat Conversion Rules Supported'],
                    ['name' => 'choice_locking_mandatory', 'label' => 'Choice Locking Mandatory Flag'],
                    ['name' => 'seat_upgradation_allowed', 'label' => 'Seat Upgradation (Floating) Allowed'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-cb-finance',
                'title' => 'Fees, Security & Grievance',
                'icon' => 'fas fa-money-check-alt',
                'fields' => [
                    ['name' => 'counselling_fee_collection_supported', 'label' => 'Online Fee Collection Supported'],
                    ['name' => 'security_deposit_handling', 'label' => 'Security Deposit Escrow Handling'],
                    ['name' => 'grievance_redressal_portal_url', 'label' => 'Online Grievance Redressal Portal URL'],
                    ['name' => 'public_trust_score', 'label' => 'Public Trust & Transparency Score'],
                ]
            ];
        } elseif (in_array($typeId, [7, 8])) { // Regulatory Body / Govt agency
            $tabs[] = [
                'id' => 'org-rb-core',
                'title' => 'Regulatory Scope & Authority',
                'icon' => 'fas fa-landmark',
                'fields' => [
                    ['name' => 'brand_name', 'label' => 'Regulator Name / Acronym (UGC, AICTE, NMC)'],
                    ['name' => 'statutory_body', 'label' => 'Statutory Regulatory Authority Flag'],
                    ['name' => 'nodal_ministry', 'label' => 'Nodal Ministry / Department'],
                    ['name' => 'official_website', 'label' => 'Official Regulatory Website URL'],
                    ['name' => 'act_name', 'label' => 'Governing Parliamentary / State Act'],
                    ['name' => 'act_year', 'label' => 'Act Year'],
                ]
            ];
            $tabs[] = [
                'id' => 'org-rb-monitoring',
                'title' => 'Audits, Compliance & Affiliations',
                'icon' => 'fas fa-clipboard-check',
                'fields' => [
                    ['name' => 'institutions_covered_count', 'label' => 'Regulated Institutions Covered Count'],
                    ['name' => 'states_covered_count', 'label' => 'Jurisdiction States Count'],
                    ['name' => 'audit_conducted', 'label' => 'Routine Institutional Audits Conducted'],
                    ['name' => 'whistleblower_policy_available', 'label' => 'Whistleblower & RTI Policy Available'],
                    ['name' => 'grievance_redressal_portal_url', 'label' => 'Grievance & Public Safety Portal URL'],
                ]
            ];
        }

        return $tabs;
    }

    /**
     * Get all database fields for Campuses grouped into logical categories.
     */
    public function getCampusSections(): array
    {
        return [
            [
                'id' => 'campus-basic',
                'title' => 'Basic Details & Location',
                'icon' => 'fas fa-map-marked-alt',
                'fields' => [
                    ['name' => 'campus_name', 'label' => 'Campus Name', 'required' => true],
                    ['name' => 'campus_type', 'label' => 'Campus Type (Main / Regional / Satellite)'],
                    ['name' => 'established_year', 'label' => 'Established Year'],
                    ['name' => 'city', 'label' => 'City'],
                    ['name' => 'state', 'label' => 'State'],
                    ['name' => 'country', 'label' => 'Country'],
                    ['name' => 'pincode', 'label' => 'Pincode'],
                    ['name' => 'full_address', 'label' => 'Full Physical Address'],
                    ['name' => 'google_map_url', 'label' => 'Google Maps Location URL'],
                    ['name' => 'nearest_transport_hub', 'label' => 'Nearest Transport Hub / Metro / Airport'],
                ]
            ],
            [
                'id' => 'campus-infra',
                'title' => 'Physical Infrastructure & Acreage',
                'icon' => 'fas fa-archway',
                'fields' => [
                    ['name' => 'campus_area_acres', 'label' => 'Campus Area (in Acres)'],
                    ['name' => 'academic_blocks_count', 'label' => 'Academic Blocks Count'],
                    ['name' => 'classrooms_count', 'label' => 'Total Classrooms Count'],
                    ['name' => 'smart_classrooms', 'label' => 'Smart / ICT Enabled Classrooms Available'],
                    ['name' => 'laboratories_count', 'label' => 'Total Laboratories Count'],
                    ['name' => 'research_centers_count', 'label' => 'Research Centers Count'],
                ]
            ],
            [
                'id' => 'campus-academic-fac',
                'title' => 'Academic Facilities & Library',
                'icon' => 'fas fa-book-reader',
                'fields' => [
                    ['name' => 'library_available', 'label' => 'Central Library Available'],
                    ['name' => 'library_books_count', 'label' => 'Library Books & Volumes Count'],
                    ['name' => 'digital_library_access', 'label' => 'Digital Library & Journal Subscriptions'],
                ]
            ],
            [
                'id' => 'campus-hostel',
                'title' => 'Residential & Student Life',
                'icon' => 'fas fa-bed',
                'fields' => [
                    ['name' => 'hostel_available', 'label' => 'Hostel Accommodation Available'],
                    ['name' => 'hostel_type', 'label' => 'Hostel Type (Boys / Girls / Both)'],
                    ['name' => 'hostel_capacity', 'label' => 'Hostel Bed Capacity'],
                    ['name' => 'food_facility', 'label' => 'Cafeteria & Mess Dining Facility'],
                    ['name' => 'medical_facility_available', 'label' => 'On-Campus Medical Clinic & Doctor'],
                    ['name' => 'sports_facilities', 'label' => 'Sports & Recreation Facilities (Indoor/Outdoor)'],
                ]
            ],
            [
                'id' => 'campus-transport',
                'title' => 'Transport, Security & Safety',
                'icon' => 'fas fa-bus-alt',
                'fields' => [
                    ['name' => 'transport_available', 'label' => 'College/School Bus Transport Available'],
                    ['name' => 'bus_routes_count', 'label' => 'Bus Routes Count'],
                    ['name' => 'bus_fleet_size', 'label' => 'Total Bus Fleet Size'],
                    ['name' => 'gps_enabled_buses', 'label' => 'GPS Tracking Enabled on Buses'],
                    ['name' => 'parking_available', 'label' => 'Student & Staff Parking Available'],
                    ['name' => 'cctv_coverage', 'label' => '24x7 CCTV Surveillance Coverage'],
                    ['name' => 'security_staff_count', 'label' => 'Security Staff Personnel Count'],
                    ['name' => 'fire_safety_certified', 'label' => 'Fire Safety NOC Certified'],
                    ['name' => 'disaster_management_plan', 'label' => 'Disaster Management Plan in Place'],
                    ['name' => 'visitor_management_system', 'label' => 'Digital Visitor Gate Management System'],
                ]
            ],
            [
                'id' => 'campus-school-amenities',
                'title' => 'School Specific Campus Amenities',
                'icon' => 'fas fa-shapes',
                'fields' => [
                    ['name' => 'science_labs_available', 'label' => 'Science Laboratories (Physics/Chem/Bio)'],
                    ['name' => 'computer_labs_available', 'label' => 'Computer Coding Labs Available'],
                    ['name' => 'playground_available', 'label' => 'Outdoor Athletics Playground Available'],
                ]
            ]
        ];
    }

    /**
     * Get all database fields for Departments grouped into logical categories.
     */
    public function getDepartmentSections(): array
    {
        return [
            [
                'id' => 'dept-identity',
                'title' => 'Department Identity & Type',
                'icon' => 'fas fa-building',
                'fields' => [
                    ['name' => 'name', 'label' => 'Department Name', 'required' => true],
                    ['name' => 'code', 'label' => 'Department Code (e.g. CSE, MED)'],
                    ['name' => 'department_type', 'label' => 'Department Type (Academic/Clinical/Research)'],
                    ['name' => 'established_year', 'label' => 'Established Year'],
                    ['name' => 'status', 'label' => 'Operational Status (Active/Inactive)'],
                    ['name' => 'visibility', 'label' => 'Visibility (Public / Internal)'],
                ]
            ],
            [
                'id' => 'dept-leadership',
                'title' => 'Leadership & Administration',
                'icon' => 'fas fa-user-tie',
                'fields' => [
                    ['name' => 'hod_name', 'label' => 'Head of Department (HOD) Name'],
                    ['name' => 'hod_designation', 'label' => 'HOD Designation / Rank'],
                    ['name' => 'hod_email', 'label' => 'HOD Official Email'],
                    ['name' => 'department_office_contact', 'label' => 'Department Contact Phone Number'],
                ]
            ],
            [
                'id' => 'dept-faculty',
                'title' => 'Faculty & Academics',
                'icon' => 'fas fa-chalkboard-teacher',
                'fields' => [
                    ['name' => 'faculty_count', 'label' => 'Total Teaching Faculty Count'],
                    ['name' => 'curriculum_design_responsibility', 'label' => 'Curriculum Design Handled by Dept'],
                    ['name' => 'exam_setting_responsibility', 'label' => 'Internal Exam Setting Responsibility'],
                    ['name' => 'classrooms_count', 'label' => 'Dedicated Department Classrooms'],
                ]
            ],
            [
                'id' => 'dept-research',
                'title' => 'Research, Labs & Patents',
                'icon' => 'fas fa-flask',
                'fields' => [
                    ['name' => 'research_programs_managed', 'label' => 'Ph.D & Research Programs Managed'],
                    ['name' => 'phd_supervision_available', 'label' => 'Ph.D Doctoral Supervision Available'],
                    ['name' => 'industry_collaboration_supported', 'label' => 'Industry Collaboration Supported'],
                    ['name' => 'department_labs_count', 'label' => 'Department Practical Labs Count'],
                    ['name' => 'specialized_labs_available', 'label' => 'Specialized High-End Research Labs'],
                    ['name' => 'research_centers_under_department', 'label' => 'Research Centers Under Department'],
                    ['name' => 'department_library_section', 'label' => 'Departmental Library Section'],
                    ['name' => 'research_publications_count', 'label' => 'Research Publications Count'],
                    ['name' => 'funded_projects_count', 'label' => 'Funded External Projects Count'],
                    ['name' => 'patents_filed_count', 'label' => 'Patents Filed / Granted Count'],
                    ['name' => 'industry_projects_count', 'label' => 'Industry Consulting Projects Count'],
                ]
            ],
            [
                'id' => 'dept-online',
                'title' => 'Online Portals & Communication',
                'icon' => 'fas fa-globe',
                'fields' => [
                    ['name' => 'department_website_url', 'label' => 'Department Web Page URL'],
                    ['name' => 'department_email', 'label' => 'Department General Inquiries Email'],
                    ['name' => 'department_notice_board_url', 'label' => 'Online Digital Notice Board URL'],
                ]
            ]
        ];
    }

    /**
     * Get all database fields for Courses grouped into logical categories.
     */
    public function getCourseSections(): array
    {
        return [
            [
                'id' => 'course-identity',
                'title' => 'Course Identity & Masters',
                'icon' => 'fas fa-graduation-cap',
                'fields' => [
                    ['name' => 'academic_unit_name', 'label' => 'Course / Degree Name', 'required' => true],
                    ['name' => 'course_id', 'label' => 'Master Course Reference'],
                    ['name' => 'program_level_id', 'label' => 'Program Level (UG/PG/Diploma/Ph.D)'],
                    ['name' => 'stream_offered_id', 'label' => 'Academic Stream Master'],
                    ['name' => 'discipline_id', 'label' => 'Discipline Master'],
                    ['name' => 'specialization', 'label' => 'Specialization / Major'],
                    ['name' => 'mode', 'label' => 'Study Mode (Regular/Online/Distance/Part-time)'],
                    ['name' => 'duration', 'label' => 'Course Duration (e.g. 4 Years, 2 Years)'],
                    ['name' => 'delivery_mode', 'label' => 'Delivery Mode (Classroom / Online / Hybrid)'],
                ]
            ],
            [
                'id' => 'course-fees',
                'title' => 'Fees, Pricing & Scholarships',
                'icon' => 'fas fa-receipt',
                'fields' => [
                    ['name' => 'fees', 'label' => 'Annual Tuition Fee Display (₹)'],
                    ['name' => 'total_fees', 'label' => 'Total Course Program Fee (₹)'],
                    ['name' => 'fees_structure', 'label' => 'Detailed Fees Structure Breakdown'],
                    ['name' => 'annual_fee_range', 'label' => 'Annual Fee Range (e.g. ₹1.5L - ₹2.5L)'],
                    ['name' => 'admission_fee', 'label' => 'One-time Admission / Registration Fee (₹)'],
                    ['name' => 'fee_payment_frequency', 'label' => 'Fee Payment Frequency (Annual/Semester/Monthly)'],
                    ['name' => 'transport_fee', 'label' => 'Transport Facility Fee (₹)'],
                    ['name' => 'hostel_fee', 'label' => 'Hostel & Food Accommodation Fee (₹)'],
                    ['name' => 'average_course_fee_range', 'label' => 'Average Course Fee Range'],
                    ['name' => 'installment_available', 'label' => 'Installment / EMI Payment Option Available'],
                    ['name' => 'scholarship_available', 'label' => 'Merit / Need Scholarships Available'],
                    ['name' => 'refund_policy_available', 'label' => 'Fee Refund Policy Available'],
                ]
            ],
            [
                'id' => 'course-admission',
                'title' => 'Admissions & Eligibility',
                'icon' => 'fas fa-door-open',
                'fields' => [
                    ['name' => 'eligibility', 'label' => 'Eligibility Criteria & Minimum Marks'],
                    ['name' => 'admission_process', 'label' => 'Admission Selection Process Description'],
                    ['name' => 'entrance_exams', 'label' => 'Accepted Entrance Exams (JEE, NEET, CAT, etc.)'],
                    ['name' => 'provisional_admission', 'label' => 'Provisional Admission Allowed'],
                ]
            ],
            [
                'id' => 'course-academics',
                'title' => 'Curriculum, Placements & Rankings',
                'icon' => 'fas fa-briefcase',
                'fields' => [
                    ['name' => 'curriculum', 'label' => 'Detailed Syllabus & Course Modules'],
                    ['name' => 'career_prospects', 'label' => 'Career Prospects & Job Roles'],
                    ['name' => 'placement_details', 'label' => 'Placement Statistics & Average Package'],
                    ['name' => 'rating', 'label' => 'Student Course Rating (out of 5.0)'],
                    ['name' => 'roi', 'label' => 'Return on Investment Rating (High/Medium/Low)'],
                    ['name' => 'industrial_collaboration', 'label' => 'Industry Tie-ups & Corporate Partnerships'],
                    ['name' => 'internship_ranking', 'label' => 'Mandatory Internship & Ranking Highlights'],
                    ['name' => 'course_languages', 'label' => 'Medium of Instruction Languages'],
                ]
            ],
            [
                'id' => 'course-school-coaching',
                'title' => 'School & Coaching Specific Program Details',
                'icon' => 'fas fa-chalkboard',
                'fields' => [
                    ['name' => 'school_type', 'label' => 'School Program Category Type'],
                    ['name' => 'education_board', 'label' => 'Education Board (CBSE/ICSE/State)'],
                    ['name' => 'board_affiliation_number', 'label' => 'Board Affiliation / Code Number'],
                    ['name' => 'medium_of_instruction', 'label' => 'Medium of Instruction'],
                    ['name' => 'grade_range', 'label' => 'Grade Range (e.g. Nursery to 12th)'],
                    ['name' => 'student_strength', 'label' => 'Total Enrolled Student Strength'],
                    ['name' => 'total_teachers', 'label' => 'Total Faculty / Teachers Assigned'],
                    ['name' => 'student_teacher_ratio', 'label' => 'Student Teacher Ratio (e.g. 20:1)'],
                    ['name' => 'average_class_size', 'label' => 'Average Class Size'],
                    ['name' => 'total_batches', 'label' => 'Total Batches Running'],
                    ['name' => 'average_batch_size', 'label' => 'Average Batch Size'],
                    ['name' => 'separate_batches_for_droppers', 'label' => 'Separate Batches for Droppers / Repeaters'],
                    ['name' => 'merit_based_batching', 'label' => 'Merit-Based Batch Shuffling'],
                    ['name' => 'total_faculty_count', 'label' => 'Total Coaching Faculty Count'],
                    ['name' => 'senior_faculty_count', 'label' => 'Senior / Star Faculty Count'],
                    ['name' => 'average_faculty_experience_years', 'label' => 'Average Faculty Experience (Years)'],
                    ['name' => 'visiting_faculty_available', 'label' => 'Visiting / Guest Faculty Available'],
                    ['name' => 'doubt_solving_mode', 'label' => 'Doubt Solving Mode (One-on-one / Desk)'],
                    ['name' => 'personal_mentorship_available', 'label' => 'Personal 1-on-1 Mentorship Available'],
                    ['name' => 'extra_classes_for_weak_students', 'label' => 'Remedial / Extra Classes for Weak Students'],
                    ['name' => 'parent_counselling_available', 'label' => 'Parent Counselling & PTMs Available'],
                    ['name' => 'study_material_type', 'label' => 'Comprehensive Study Material Provided'],
                    ['name' => 'dpp_provided', 'label' => 'Daily Practice Problems (DPP) Provided'],
                    ['name' => 'test_series_available', 'label' => 'All India Test Series (AITS) Available'],
                    ['name' => 'tests_per_month', 'label' => 'Mock Tests Count per Month'],
                    ['name' => 'online_test_platform_available', 'label' => 'Online CBT Testing Portal Available'],
                    ['name' => 'total_selections_all_time', 'label' => 'Total Selections All-Time (NEET/JEE)'],
                    ['name' => 'selections_last_year', 'label' => 'Total Selections in Previous Year'],
                    ['name' => 'highest_rank_achieved', 'label' => 'Highest All India Rank (AIR) Achieved'],
                    ['name' => 'average_selection_rate', 'label' => 'Average Selection Success Rate (%)'],
                    ['name' => 'verified_reviews_only', 'label' => 'Verified Student Reviews Only Flag'],
                    ['name' => 'meta_title', 'label' => 'Course SEO Meta Title'],
                    ['name' => 'meta_description', 'label' => 'Course SEO Meta Description'],
                ]
            ]
        ];
    }

    /**
     * Get a key => label mapping for all fields of a specific entity type (and optional organisation type id).
     */
    public function getFieldLabels(string $entityType, int $orgTypeId = 1): array
    {
        $map = [];
        if ($entityType === 'organisation') {
            $tabs = $this->getOrganisationTabs($orgTypeId);
            foreach ($tabs as $tab) {
                foreach ($tab['fields'] as $f) {
                    $map[$f['name']] = $f['label'];
                }
            }
        } elseif ($entityType === 'campuses' || $entityType === 'campus') {
            $sections = $this->getCampusSections();
            foreach ($sections as $sec) {
                foreach ($sec['fields'] as $f) {
                    $map[$f['name']] = $f['label'];
                }
            }
        } elseif ($entityType === 'departments' || $entityType === 'department') {
            $sections = $this->getDepartmentSections();
            foreach ($sections as $sec) {
                foreach ($sec['fields'] as $f) {
                    $map[$f['name']] = $f['label'];
                }
            }
        } elseif ($entityType === 'courses' || $entityType === 'course') {
            $sections = $this->getCourseSections();
            foreach ($sections as $sec) {
                foreach ($sec['fields'] as $f) {
                    $map[$f['name']] = $f['label'];
                }
            }
        }
        return $map;
    }
}