-- =======================================================
-- Real Scholarship Data Seed File for Enrollzy
-- Includes 7 Realistic Indian Scholarship Programs with Eligibilities and Dates
-- =======================================================

-- Clear test data if any
DELETE FROM `scholarship_dates` WHERE `scholarship_id` IN (SELECT `id` FROM `scholarships` WHERE `slug` IN (
    'pm-yasasvi-scheme-2026', 'inspire-she-scholarship-2026', 'pragati-scholarship-girls-2026',
    'ntpc-utkarsh-scholarship-2026', 'lic-hfl-vidyadhan-scholarship-2026', 'kotak-kanya-scholarship-2026',
    'sbi-asha-scholarship-2026'
));

DELETE FROM `scholarship_eligibilities` WHERE `scholarship_id` IN (SELECT `id` FROM `scholarships` WHERE `slug` IN (
    'pm-yasasvi-scheme-2026', 'inspire-she-scholarship-2026', 'pragati-scholarship-girls-2026',
    'ntpc-utkarsh-scholarship-2026', 'lic-hfl-vidyadhan-scholarship-2026', 'kotak-kanya-scholarship-2026',
    'sbi-asha-scholarship-2026'
));

DELETE FROM `scholarships` WHERE `slug` IN (
    'pm-yasasvi-scheme-2026', 'inspire-she-scholarship-2026', 'pragati-scholarship-girls-2026',
    'ntpc-utkarsh-scholarship-2026', 'lic-hfl-vidyadhan-scholarship-2026', 'kotak-kanya-scholarship-2026',
    'sbi-asha-scholarship-2026'
);

-- -------------------------------------------------------
-- 1. PM Yasasvi Central Sector Scheme 2026
-- -------------------------------------------------------
INSERT INTO `scholarships` (
    `title`, `slug`, `short_name`, `scholarship_code`, `short_description`, `overview`,
    `about_scholarship`, `why_apply`, `selection_process`, `terms_conditions`, `scholarship_type`,
    `category`, `max_amount`, `amount_prefix`, `amount_suffix`, `provider_name`, `application_mode`,
    `status`, `featured`, `featured_on_homepage`, `sort_order`, `cta_text`, `cta_url`, `created_at`, `updated_at`
) VALUES (
    'PM Young Achievers Scholarship Award Scheme for Vibrant India (YASASVI) 2026',
    'pm-yasasvi-scheme-2026',
    'PM YASASVI',
    'PM-YASASVI-2026',
    'Financial assistance up to Rs. 1,25,000 per year for meritorious OBC, EBC, and DNT students studying in Top Identified Schools across India.',
    'PM Young Achievers Scholarship Award Scheme for Vibrant India (YASASVI) is a major initiative by the Ministry of Social Justice and Empowerment, Govt. of India, aimed at offering financial aid to students belonging to OBC, EBC, and DNT categories.',
    'The scheme awards scholarships for school education from Class 9 to 12 in top-rated schools identified by the government. Selected Class 9 & 10 students receive Rs. 75,000/year while Class 11 & 12 students get Rs. 1,25,000/year.',
    'Covers tuition fees, hostel fees, and educational equipment. Directly transferred to student bank accounts.',
    'Selection is based on merit in the YASASVI Entrance Test (YET) conducted by NTA or school merit ranking.',
    'Annual family income must not exceed Rs 2.50 Lakhs.',
    'Government / Merit-cum-Means',
    'School Education',
    125000.00,
    'Up to',
    '/year',
    'Ministry of Social Justice & Empowerment, Govt. of India',
    'Online',
    1, 1, 1, 1,
    'Apply on NSP Portal',
    'https://scholarships.gov.in',
    NOW(), NOW()
);

SET @s1_id = LAST_INSERT_ID();

INSERT INTO `scholarship_eligibilities` (
    `scholarship_id`, `minimum_class`, `maximum_class`, `minimum_percentage`, `gender`, `nationality`,
    `state`, `category`, `annual_family_income`, `course_level`, `created_at`, `updated_at`
) VALUES (
    @s1_id, '9', '12', 60.00, 'Any', 'Indian',
    'All India', 'OBC / EBC / DNT', 'Up to INR 2,50,000', 'Class 9 to 12', NOW(), NOW()
);

INSERT INTO `scholarship_dates` (
    `scholarship_id`, `application_start_date`, `application_end_date`, `exam_date`, `result_date`, `created_at`, `updated_at`
) VALUES (
    @s1_id, '2026-07-01', '2026-09-30', '2026-10-15', '2026-11-20', NOW(), NOW()
);


-- -------------------------------------------------------
-- 2. INSPIRE Scholarship for Higher Education (SHE) 2026
-- -------------------------------------------------------
INSERT INTO `scholarships` (
    `title`, `slug`, `short_name`, `scholarship_code`, `short_description`, `overview`,
    `about_scholarship`, `why_apply`, `selection_process`, `terms_conditions`, `scholarship_type`,
    `category`, `max_amount`, `amount_prefix`, `amount_suffix`, `provider_name`, `application_mode`,
    `status`, `featured`, `featured_on_homepage`, `sort_order`, `cta_text`, `cta_url`, `created_at`, `updated_at`
) VALUES (
    'INSPIRE Scholarship for Higher Education (SHE) 2026',
    'inspire-she-scholarship-2026',
    'DST INSPIRE',
    'INSPIRE-SHE-2026',
    'Scholarship worth Rs. 80,000 per annum for top 1% meritorious students pursuing Basic and Natural Sciences at B.Sc. and M.Sc. level.',
    'Innovation in Science Pursuit for Inspired Research (INSPIRE) is an innovative program sponsored and managed by the Department of Science & Technology (DST) for attraction of talent to Science.',
    'The scheme offers 10,000 scholarships every year for students pursuing B.Sc., B.S., and Integrated M.Sc. courses in Basic and Natural Sciences. Annual scholarship amount is Rs 60,000 cash plus Rs 20,000 mentorship grant for research projects.',
    'Prestige scholarship for science aspirants fostering research careers at premier research labs and institutes in India.',
    'Top 1% ranking in Class 12 State or Central Board examinations or JEE / NEET top rankers.',
    'Must be enrolled in natural or basic science degree program.',
    'Government / Science Research',
    'Higher Education / Science',
    80000.00,
    'Up to',
    '/year',
    'Department of Science & Technology (DST), Govt. of India',
    'Online',
    1, 1, 1, 2,
    'Apply on INSPIRE Portal',
    'https://online-inspire.gov.in',
    NOW(), NOW()
);

SET @s2_id = LAST_INSERT_ID();

INSERT INTO `scholarship_eligibilities` (
    `scholarship_id`, `minimum_class`, `maximum_class`, `minimum_percentage`, `gender`, `nationality`,
    `state`, `category`, `annual_family_income`, `course_level`, `created_at`, `updated_at`
) VALUES (
    @s2_id, '12', 'higher', 85.00, 'Any', 'Indian',
    'All India', 'General / All Categories', 'No Income Limit', 'higher', NOW(), NOW()
);

INSERT INTO `scholarship_dates` (
    `scholarship_id`, `application_start_date`, `application_end_date`, `exam_date`, `result_date`, `created_at`, `updated_at`
) VALUES (
    @s2_id, '2026-08-01', '2026-10-31', NULL, '2026-12-15', NOW(), NOW()
);


-- -------------------------------------------------------
-- 3. AICTE Pragati Scholarship Scheme for Girl Students 2026
-- -------------------------------------------------------
INSERT INTO `scholarships` (
    `title`, `slug`, `short_name`, `scholarship_code`, `short_description`, `overview`,
    `about_scholarship`, `why_apply`, `selection_process`, `terms_conditions`, `scholarship_type`,
    `category`, `max_amount`, `amount_prefix`, `amount_suffix`, `provider_name`, `application_mode`,
    `status`, `featured`, `featured_on_homepage`, `sort_order`, `cta_text`, `cta_url`, `created_at`, `updated_at`
) VALUES (
    'AICTE Pragati Scholarship Scheme for Girl Students (Degree & Diploma) 2026',
    'pragati-scholarship-girls-2026',
    'AICTE Pragati',
    'AICTE-PRAGATI-2026',
    'Financial aid of Rs. 50,000 per annum to empowering female students admitted to technical degree (B.Tech/BE) and diploma programs.',
    'Pragati is a scheme implemented by AICTE aimed at providing assistance to meritorious girl students to pursue technical education.',
    'A total of 10,000 scholarships are awarded per annum. Selected candidates receive Rs 50,000 per year for every year of study towards college fees, purchase of books, equipment, laptops, and soft wares.',
    'Dedicated support for women in STEM, promoting female representation in engineering and polytechnic institutes.',
    'Merit list based on qualifying exam marks for admission to AICTE-approved institutions.',
    'Maximum two girl children per family. Family income should be less than Rs 8 Lakhs per annum.',
    'Government / Technical Education',
    'Girls / Women STEM',
    50000.00,
    'Fixed',
    '/year',
    'All India Council for Technical Education (AICTE)',
    'Online',
    1, 1, 1, 3,
    'Apply via National Scholarship Portal',
    'https://scholarships.gov.in',
    NOW(), NOW()
);

SET @s3_id = LAST_INSERT_ID();

INSERT INTO `scholarship_eligibilities` (
    `scholarship_id`, `minimum_class`, `maximum_class`, `minimum_percentage`, `gender`, `nationality`,
    `state`, `category`, `annual_family_income`, `course_level`, `created_at`, `updated_at`
) VALUES (
    @s3_id, '12', 'higher', 60.00, 'Female', 'Indian',
    'All India', 'Girls / Female Students', 'Up to INR 8,000,000', 'higher', NOW(), NOW()
);

INSERT INTO `scholarship_dates` (
    `scholarship_id`, `application_start_date`, `application_end_date`, `exam_date`, `result_date`, `created_at`, `updated_at`
) VALUES (
    @s3_id, '2026-08-15', '2026-11-30', NULL, '2027-01-10', NOW(), NOW()
);


-- -------------------------------------------------------
-- 4. Kotak Kanya Scholarship 2026
-- -------------------------------------------------------
INSERT INTO `scholarships` (
    `title`, `slug`, `short_name`, `scholarship_code`, `short_description`, `overview`,
    `about_scholarship`, `why_apply`, `selection_process`, `terms_conditions`, `scholarship_type`,
    `category`, `max_amount`, `amount_prefix`, `amount_suffix`, `provider_name`, `application_mode`,
    `status`, `featured`, `featured_on_homepage`, `sort_order`, `cta_text`, `cta_url`, `created_at`, `updated_at`
) VALUES (
    'Kotak Kanya Scholarship Program 2026 for Female Professional Course Students',
    'kotak-kanya-scholarship-2026',
    'Kotak Kanya',
    'KOTAK-KANYA-2026',
    'Scholarship up to Rs. 1,50,000 per year for meritorious girl students pursuing professional degree courses (Engineering, MBBS, Architecture, Design, Law).',
    'Kotak Education Foundation invites applications for Kotak Kanya Scholarship to financial support deserving female students from low-income families.',
    'Covering tuition fees, hostel charges, internet, laptop, and learning materials up to Rs 1,50,000 per year throughout the duration of the professional course.',
    'One of India\'s largest private CSR scholarships providing complete handholding, career guidance, and financial independence to young women leaders.',
    'Academic evaluation followed by telephonic and personal interviews by Kotak Education panel.',
    'Minimum 85% marks in Class 12 board exams. Annual family income must be Rs 6 Lakhs or less.',
    'Private / Corporate CSR',
    'Girls / Professional Degrees',
    150000.00,
    'Up to',
    '/year',
    'Kotak Education Foundation',
    'Online',
    1, 1, 1, 4,
    'Apply Now',
    'https://kotakeducation.org',
    NOW(), NOW()
);

SET @s4_id = LAST_INSERT_ID();

INSERT INTO `scholarship_eligibilities` (
    `scholarship_id`, `minimum_class`, `maximum_class`, `minimum_percentage`, `gender`, `nationality`,
    `state`, `category`, `annual_family_income`, `course_level`, `created_at`, `updated_at`
) VALUES (
    @s4_id, '12', 'higher', 85.00, 'Female', 'Indian',
    'All India', 'Girls / Women', 'Up to INR 6,000,000', 'higher', NOW(), NOW()
);

INSERT INTO `scholarship_dates` (
    `scholarship_id`, `application_start_date`, `application_end_date`, `exam_date`, `result_date`, `created_at`, `updated_at`
) VALUES (
    @s4_id, '2026-07-15', '2026-10-31', NULL, '2026-12-01', NOW(), NOW()
);


-- -------------------------------------------------------
-- 5. LIC HFL Vidyadhan Scholarship 2026
-- -------------------------------------------------------
INSERT INTO `scholarships` (
    `title`, `slug`, `short_name`, `scholarship_code`, `short_description`, `overview`,
    `about_scholarship`, `why_apply`, `selection_process`, `terms_conditions`, `scholarship_type`,
    `category`, `max_amount`, `amount_prefix`, `amount_suffix`, `provider_name`, `application_mode`,
    `status`, `featured`, `featured_on_homepage`, `sort_order`, `cta_text`, `cta_url`, `created_at`, `updated_at`
) VALUES (
    'LIC HFL Vidyadhan Scholarship 2026 for Class 11, 12, UG & PG Students',
    'lic-hfl-vidyadhan-scholarship-2026',
    'LIC HFL Vidyadhan',
    'LICHFL-2026',
    'Financial support up to Rs. 25,000 per year for underprivileged students pursuing Class 11-12, Graduation, and Post-Graduation degrees.',
    'LIC Housing Finance Limited (LIC HFL) CSR initiative empowering students from economically weaker sections to continue their higher education uninterrupted.',
    'Provides Rs 15,000/year for Class 11 & 12, Rs 25,000/year for Graduation, and up to Rs 25,000/year for Post-Graduation students.',
    'Direct bank transfer and renewable scholarship for multi-year academic programs.',
    'Screening based on academic record, family income proof, and telephonic interview.',
    'Minimum 60% marks in previous qualifying exam. Family annual income less than Rs 3,60,000.',
    'Corporate CSR / Merit-cum-Means',
    'School & College Education',
    25000.00,
    'Up to',
    '/year',
    'LIC Housing Finance Limited (LIC HFL)',
    'Online',
    1, 0, 1, 5,
    'Apply for LIC Scholarship',
    'https://www.lichousing.com',
    NOW(), NOW()
);

SET @s5_id = LAST_INSERT_ID();

INSERT INTO `scholarship_eligibilities` (
    `scholarship_id`, `minimum_class`, `maximum_class`, `minimum_percentage`, `gender`, `nationality`,
    `state`, `category`, `annual_family_income`, `course_level`, `created_at`, `updated_at`
) VALUES (
    @s5_id, '11', 'higher', 60.00, 'Any', 'Indian',
    'All India', 'General / Low Income', 'Up to INR 3,60,000', '11', NOW(), NOW()
);

INSERT INTO `scholarship_dates` (
    `scholarship_id`, `application_start_date`, `application_end_date`, `exam_date`, `result_date`, `created_at`, `updated_at`
) VALUES (
    @s5_id, '2026-06-01', '2026-09-30', NULL, '2026-11-15', NOW(), NOW()
);


-- -------------------------------------------------------
-- 6. SBI Asha Scholarship Program 2026
-- -------------------------------------------------------
INSERT INTO `scholarships` (
    `title`, `slug`, `short_name`, `scholarship_code`, `short_description`, `overview`,
    `about_scholarship`, `why_apply`, `selection_process`, `terms_conditions`, `scholarship_type`,
    `category`, `max_amount`, `amount_prefix`, `amount_suffix`, `provider_name`, `application_mode`,
    `status`, `featured`, `featured_on_homepage`, `sort_order`, `cta_text`, `cta_url`, `created_at`, `updated_at`
) VALUES (
    'SBI Asha Scholarship Program 2026 for School & College Students',
    'sbi-asha-scholarship-2026',
    'SBI Asha',
    'SBI-ASHA-2026',
    'Scholarship assistance from Rs. 15,000 to Rs. 50,000 for bright students from low-income families across Class 6 to 12 and Undergraduate degrees.',
    'SBI Foundation under its Integrated Learning Program launched the SBI Asha Scholarship to support education of meritorious students from low-income backgrounds across India.',
    'Financial grant of Rs 15,000 for Class 6 to 12 students and up to Rs 50,000 for undergraduate students at premier institutions like IITs and IIMs.',
    'Backed by State Bank of India with quick document verification and direct bank account credit.',
    'Initial shortlist on academic merit (75%+ marks), followed by document verification.',
    'Family income must be below Rs 3.00 Lakhs per annum across all categories.',
    'Corporate CSR / Merit-cum-Means',
    'School & Undergraduate',
    50000.00,
    'Up to',
    '/year',
    'SBI Foundation',
    'Online',
    1, 1, 1, 6,
    'Apply via Buddy4Study',
    'https://www.sbifoundation.in',
    NOW(), NOW()
);

SET @s6_id = LAST_INSERT_ID();

INSERT INTO `scholarship_eligibilities` (
    `scholarship_id`, `minimum_class`, `maximum_class`, `minimum_percentage`, `gender`, `nationality`,
    `state`, `category`, `annual_family_income`, `course_level`, `created_at`, `updated_at`
) VALUES (
    @s6_id, '9', 'higher', 75.00, 'Any', 'Indian',
    'All India', 'General / Low Income', 'Up to INR 3,00,000', '10', NOW(), NOW()
);

INSERT INTO `scholarship_dates` (
    `scholarship_id`, `application_start_date`, `application_end_date`, `exam_date`, `result_date`, `created_at`, `updated_at`
) VALUES (
    @s6_id, '2026-07-01', '2026-10-15', NULL, '2026-11-30', NOW(), NOW()
);


-- -------------------------------------------------------
-- 7. NTPC Utkarsh Merit Scholarship Scheme 2026
-- -------------------------------------------------------
INSERT INTO `scholarships` (
    `title`, `slug`, `short_name`, `scholarship_code`, `short_description`, `overview`,
    `about_scholarship`, `why_apply`, `selection_process`, `terms_conditions`, `scholarship_type`,
    `category`, `max_amount`, `amount_prefix`, `amount_suffix`, `provider_name`, `application_mode`,
    `status`, `featured`, `featured_on_homepage`, `sort_order`, `cta_text`, `cta_url`, `created_at`, `updated_at`
) VALUES (
    'NTPC Utkarsh Merit Scholarship Scheme 2026 for Engineering & Medical Students',
    'ntpc-utkarsh-scholarship-2026',
    'NTPC Utkarsh',
    'NTPC-UTKARSH-2026',
    'Merit scholarship of Rs. 60,000 per year for students pursuing B.Tech, BE, and MBBS in government-recognized engineering and medical colleges.',
    'NTPC Limited, India\'s largest power utility, awards Utkarsh Merit Scholarships to support engineering and medical education for deserving students.',
    'Selected candidates receive Rs 5,000 per month (Rs 60,000 per year) for 4 years of Engineering or 4.5 years of MBBS study.',
    'High prestige PSUs scholarship award with priority internships at NTPC units.',
    'Based on JEE Advanced / NEET Ranks and 12th Board marks.',
    'Must maintain minimum 65% aggregate or 6.5 CGPA in university semester exams for annual renewal.',
    'Public Sector Undertaking (PSU) Merit',
    'Engineering & Medical',
    60000.00,
    'Fixed',
    '/year',
    'NTPC Limited',
    'Online',
    1, 0, 1, 7,
    'Apply on NTPC Career Portal',
    'https://www.ntpc.co.in',
    NOW(), NOW()
);

SET @s7_id = LAST_INSERT_ID();

INSERT INTO `scholarship_eligibilities` (
    `scholarship_id`, `minimum_class`, `maximum_class`, `minimum_percentage`, `gender`, `nationality`,
    `state`, `category`, `annual_family_income`, `course_level`, `created_at`, `updated_at`
) VALUES (
    @s7_id, '12', 'higher', 65.00, 'Any', 'Indian',
    'All India', 'Engineering & Medical', 'No Income Limit', 'higher', NOW(), NOW()
);

INSERT INTO `scholarship_dates` (
    `scholarship_id`, `application_start_date`, `application_end_date`, `exam_date`, `result_date`, `created_at`, `updated_at`
) VALUES (
    @s7_id, '2026-09-01', '2026-11-30', NULL, '2027-01-15', NOW(), NOW()
);
