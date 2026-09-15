<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiOrganisationScraperService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Extract organisation, campus, department, and course details from a given URL using Gemini + Search Grounding
     *
     * @param string $url
     * @param string $orgTypeTitle
     * @param int $orgTypeId
     * @return array
     * @throws \Exception
     */
    public function extractFromUrl(string $url, string $orgTypeTitle = 'University', int $orgTypeId = 1): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file.');
        }

        // 1. Fetch initial content from the URL
        $websiteContent = $this->fetchUrlContent($url);

        // 2. Build structured extraction prompt tailored to the selected Organisation Type
        $prompt = $this->buildPrompt($url, $websiteContent, $orgTypeTitle, $orgTypeId);

        // Candidate fallback models in case of high demand
        $modelsToTry = array_unique([
            $this->model,
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-3.7-flash',
            'gemini-3.1-flash-lite'
        ]);

        $lastError = null;

        foreach ($modelsToTry as $modelName) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}";

            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'tools' => [
                    ['google_search' => (object)[]]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                ]
            ];

            try {
                $response = Http::timeout(90)->post($endpoint, $payload);

                // Fallback tool naming if needed
                if ($response->status() === 400 && str_contains($response->body(), 'tools')) {
                    $payload['tools'] = [['googleSearch' => (object)[]]];
                    $response = Http::timeout(90)->post($endpoint, $payload);
                }

                if (!$response->successful()) {
                    // Try without tool if tools conflict
                    unset($payload['tools']);
                    $response = Http::timeout(90)->post($endpoint, $payload);
                }

                if (!$response->successful()) {
                    $errMsg = $response->json('error.message', $response->body());
                    $lastError = $errMsg;
                    Log::warning("Gemini model {$modelName} failed: {$errMsg}, trying next fallback model...");
                    continue;
                }

                $parts = $response->json('candidates.0.content.parts', []);
                if (empty($parts)) {
                    continue;
                }

                $data = null;
                foreach ($parts as $part) {
                    $text = $part['text'] ?? '';
                    // Try direct JSON decode
                    $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));
                    $decoded = json_decode($clean, true);
                    if (is_array($decoded) && isset($decoded['organisation'])) {
                        $data = $decoded;
                        break;
                    }

                    // Try finding JSON substring { ... }
                    if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
                        $decodedSub = json_decode($matches[0], true);
                        if (is_array($decodedSub) && isset($decodedSub['organisation'])) {
                            $data = $decodedSub;
                            break;
                        }
                    }
                }

                if (is_array($data) && isset($data['organisation'])) {
                    return $data;
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Gemini model {$modelName} exception: {$lastError}, trying next fallback model...");
            }
        }

        throw new \Exception('Gemini API extraction failed: ' . ($lastError ?? 'Could not parse response from available AI models.'));
    }

    /**
     * Fetch and clean text from given URL
     */
    protected function fetchUrlContent(string $url): string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                // Remove scripts and styles
                $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
                $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
                $cleanText = strip_tags($html);
                $cleanText = preg_replace('/\s+/', ' ', $cleanText);
                return substr(trim($cleanText), 0, 15000);
            }
        } catch (\Exception $e) {
            Log::warning('Direct URL scrape failed, relying on Gemini search: ' . $e->getMessage());
        }

        return "Official Website URL: {$url}";
    }

    /**
     * Build structured extraction prompt tailored to the selected Organisation Type
     */
    protected function buildPrompt(string $url, string $content, string $orgTypeTitle, int $orgTypeId): string
    {
        $titleLower = strtolower($orgTypeTitle);

        if (str_contains($titleLower, 'school') || $orgTypeId === 4) {
            return $this->buildSchoolPrompt($url, $content, $orgTypeTitle);
        }

        if (str_contains($titleLower, 'exam') || str_contains($titleLower, 'conducting') || $orgTypeId === 5) {
            return $this->buildExamConductingBodyPrompt($url, $content, $orgTypeTitle);
        }

        if (str_contains($titleLower, 'counselling') || $orgTypeId === 6) {
            return $this->buildCounsellingBodyPrompt($url, $content, $orgTypeTitle);
        }

        if (str_contains($titleLower, 'regulatory') || str_contains($titleLower, 'agency') || in_array($orgTypeId, [7, 8])) {
            return $this->buildRegulatoryBodyPrompt($url, $content, $orgTypeTitle);
        }

        if (str_contains($titleLower, 'institute') || $orgTypeId === 3) {
            return $this->buildInstitutePrompt($url, $content, $orgTypeTitle);
        }

        // Default to University / College (Types 1, 2)
        return $this->buildUniversityPrompt($url, $content, $orgTypeTitle);
    }

    protected function buildUniversityPrompt(string $url, string $content, string $orgTypeTitle): string
    {
        return <<<PROMPT
You are an expert higher education data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the educational institution at URL: {$url}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
1. Extract or research the full exhaustive hierarchy:
   - "organisation": Main institutional details covering Core, Governance, Legal/Regulatory, Contacts, Portals, Trust/Society details.
   - "campuses": All campus locations with Infrastructure, Amenities, Hostel, Transport, Safety, and Contact fields.
   - "departments": All faculties/schools/departments with HOD, Faculty stats, Labs, Research, and Contact fields.
   - "courses": All degree/diploma programs with Levels, Streams, Fees, Durations, Eligibility, Admissions, Placements, and Facilities.
2. Cross-verify and fill all fields possible using Google Search Grounding based on institution name and URL. If any field is not found anywhere, return empty string "" or false for booleans, but always include the key in JSON.
3. Return ONLY valid JSON matching EXACTLY this structure:

{
  "organisation": {
    "name": "Full Legal Name of Institution",
    "short_name": "Short Name / Abbreviation",
    "brand_name": "Brand Name",
    "organisation_type": "{$orgTypeTitle}",
    "brand_type": "Independent",
    "central_authority": "Governing Body / Trust",
    "head_office_location": "City, State",
    "official_website": "{$url}",
    "admission_portal_url": "",
    "student_portal_url": "",
    "parent_portal_url": "",
    "established_year": 2005,
    "ownership_type": "Private",
    "university_type": "Private University",
    "about_university": "Detailed overview...",
    "about_organisation": "Detailed overview...",
    "vision_mission": "Vision & Mission...",
    "core_values": ["Excellence", "Innovation", "Integrity"],
    "chancellor_name": "Chancellor / Founder",
    "vice_chancellor_name": "Vice Chancellor / Principal",
    "governing_body_name": "Board of Governors / Management Trust",
    "autonomous_status": true,
    "degree_awarding_authority": true,
    "ugc_recognized": true,
    "ugc_approval_number": "",
    "aicte_approved": true,
    "naac_accredited": true,
    "naac_grade": "A+",
    "nirf_rank_overall": null,
    "nirf_rank_category": null,
    "international_accreditations": ["ABET", "AACSB", "QS 5-Star"],
    "statutory_approvals": ["UGC", "AICTE", "NBA", "BCI", "PCI"],
    "levels_offered": ["Undergraduate", "Postgraduate", "Doctoral", "Diploma"],
    "number_of_campuses": 2,
    "number_of_constituent_colleges": 5,
    "number_of_affiliated_colleges": 0,
    "email": "info@institution.edu",
    "phone": "+91 XXXXXXXXXX",
    "is_top": false
  },
  "campuses": [
    {
      "campus_name": "Main Campus",
      "campus_type": "Main",
      "established_year": 2005,
      "city": "",
      "state": "",
      "country": "India",
      "pincode": "",
      "full_address": "",
      "google_map_url": "",
      "nearest_transport_hub": "",
      "campus_area_acres": 50,
      "academic_blocks_count": 5,
      "classrooms_count": 60,
      "smart_classrooms": true,
      "laboratories_count": 25,
      "library_available": true,
      "digital_library_access": true,
      "hostel_available": true,
      "hostel_type": "Both",
      "hostel_capacity": 2000,
      "medical_facility_available": true,
      "sports_facilities": ["Cricket Ground", "Football Field", "Gymnasium"],
      "transport_available": true,
      "cctv_coverage": true,
      "fire_safety_certified": true,
      "campus_email": "",
      "campus_contact_numbers": []
    }
  ],
  "departments": [
    {
      "department_name": "Department of Computer Science & Engineering",
      "department_code": "CSE",
      "department_type": "Academic",
      "established_year": 2005,
      "head_of_department_name": "",
      "head_of_department_designation": "Professor & Head",
      "hod_email": "",
      "faculty_count": 40,
      "discipline_area": "Engineering & Technology",
      "specializations_supported": ["AI & ML", "Data Science"],
      "education_levels_supported": ["Undergraduate", "Postgraduate"],
      "department_labs_count": 8,
      "research_publications_count": 120,
      "funded_projects_count": 5,
      "patents_filed_count": 3,
      "phd_supervision_available": true,
      "industry_collaboration_supported": true,
      "is_interdisciplinary": true,
      "specialized_labs_available": true,
      "about_department": ""
    }
  ],
  "courses": [
    {
      "course_name": "Bachelor of Technology in Computer Science and Engineering",
      "short_name": "B.Tech CSE",
      "program_level": "Undergraduate",
      "department_name": "Department of Computer Science & Engineering",
      "campus_name": "Main Campus",
      "stream": "Engineering",
      "discipline": "Computer Science & Engineering",
      "specialization": "Artificial Intelligence",
      "duration": "4 Years",
      "mode": "Regular",
      "fees": "200000",
      "total_fees": "800000",
      "annual_fee_range": "₹2,00,000 - ₹2,50,000",
      "admission_fee": "25000",
      "installment_available": true,
      "scholarship_available": true,
      "refund_policy_available": true,
      "roi": "High",
      "eligibility": "10+2 with 60% PCM",
      "admission_process": "Merit / Entrance exam",
      "entrance_exams": "JEE Main",
      "placement_details": "Average package ₹8-10 LPA",
      "rating": "4.5",
      "overview": ""
    }
  ]
}
PROMPT;
    }

    protected function buildInstitutePrompt(string $url, string $content, string $orgTypeTitle): string
    {
        return <<<PROMPT
You are an expert institutional data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the Institute / Academy at URL: {$url}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
1. Search Google actively based on the institution name and official URL for:
   - Registration details, legal name, registered entity name, registration number, GST, PAN, ownership type (Private/Trust/LLP/Partnership).
   - Core details: brand name, short name, established year, head office, central authority, vision, mission, about organisation.
   - Campuses, departments/divisions, and courses/certifications offered.
2. If any field cannot be found, return empty string "" or false for booleans, but always include every key in the JSON response.
3. Return ONLY valid JSON matching EXACTLY this structure:

{
  "organisation": {
    "name": "Full Legal Name of Institute",
    "short_name": "Short Name",
    "brand_name": "Brand Name",
    "organisation_type": "{$orgTypeTitle}",
    "brand_type": "Independent",
    "central_authority": "Trust / Society / Parent Body",
    "head_office_location": "City, State",
    "official_website": "{$url}",
    "established_year": 2010,
    "ownership_type": "Private",
    "registered_entity_name": "",
    "registration_number": "",
    "gst_registered": false,
    "gst_number": "",
    "pan_number": "",
    "about_organisation": "Comprehensive description...",
    "vision_mission": "Vision & Mission...",
    "core_values": ["Excellence", "Integrity"],
    "email": "info@institute.org",
    "phone": "+91 XXXXXXXXXX",
    "is_top": false
  },
  "campuses": [
    {
      "campus_name": "Main Campus",
      "campus_type": "Main",
      "established_year": 2010,
      "city": "",
      "state": "",
      "country": "India",
      "pincode": "",
      "full_address": "",
      "google_map_url": "",
      "nearest_transport_hub": "",
      "campus_area_acres": 10,
      "classrooms_count": 20,
      "smart_classrooms": true,
      "laboratories_count": 10,
      "library_available": true,
      "hostel_available": false,
      "transport_available": true,
      "cctv_coverage": true,
      "fire_safety_certified": true,
      "campus_email": "",
      "campus_contact_numbers": []
    }
  ],
  "departments": [
    {
      "department_name": "Academic Division",
      "department_code": "AD",
      "department_type": "Academic",
      "established_year": 2010,
      "head_of_department_name": "",
      "head_of_department_designation": "Director / Head",
      "hod_email": "",
      "faculty_count": 15,
      "discipline_area": "",
      "specializations_supported": [],
      "education_levels_supported": [],
      "department_labs_count": 4,
      "specialized_labs_available": true,
      "about_department": ""
    }
  ],
  "courses": [
    {
      "course_name": "Professional Certificate / Diploma Program",
      "short_name": "",
      "program_level": "Diploma",
      "department_name": "Academic Division",
      "campus_name": "Main Campus",
      "stream": "",
      "discipline": "",
      "specialization": "",
      "duration": "1 Year",
      "mode": "Regular",
      "fees": "",
      "total_fees": "",
      "eligibility": "",
      "admission_process": "",
      "rating": "4.5",
      "overview": ""
    }
  ]
}
PROMPT;
    }

    protected function buildSchoolPrompt(string $url, string $content, string $orgTypeTitle): string
    {
        return <<<PROMPT
You are an expert K-12 school education data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the School / School Network at URL: {$url}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
1. Search Google actively for this School or School Chain:
   - Education boards supported (CBSE, ICSE, State Board, IB, IGCSE/Cambridge).
   - Medium of instruction (English, Hindi, Regional languages).
   - Education levels (Pre-Primary, Primary, Middle, Secondary, Senior Secondary).
   - Senior secondary streams supported (Science, Commerce, Humanities, Vocational).
   - Pedagogy model, focus areas (Holistic, STEM, Sports, Performing Arts).
   - Child safety & policies (Child Safety Policy, POSCO Compliance, Anti-Bullying, Mental Health, Teacher Background Verification).
   - Centralized systems: Curriculum Framework, Teacher Training, Assessment Policy, LMS, Parent Communication System.
   - Footprint: total schools count, national presence, international presence, cities present in, states present in, flagship schools.
   - Portals: Official Website, Admission Portal, Parent Portal, Student Portal, Mobile App Available.
   - Reputation: Average rating, total reviews, awards and recognitions, meta title, description.
   - Core details: Brand name, short name, established year, ownership type, managing trust or society name, minority status & type.
2. If any field cannot be found, return empty string "" or false for booleans, but include every key in the JSON response.
3. Return ONLY valid JSON matching EXACTLY this structure:

{
  "organisation": {
    "name": "Full Legal Name of School",
    "short_name": "Short Name",
    "brand_name": "Brand Name",
    "organisation_type": "{$orgTypeTitle}",
    "brand_type": "Independent",
    "central_authority": "Managing Trust / Society / Group",
    "head_office_location": "City, State",
    "official_website": "{$url}",
    "admission_portal_url": "",
    "parent_portal_url": "",
    "student_portal_url": "",
    "established_year": 1995,
    "ownership_type": "Private",
    "registered_entity_name": "",
    "registration_number": "",
    "managing_trust_or_society_name": "",
    "minority_status": false,
    "minority_type": "",
    "about_organisation": "Overview of the school...",
    "vision_mission": "Vision & Mission statements...",
    "core_values": ["Integrity", "Excellence", "Compassion", "Inclusivity"],
    "education_boards_supported": ["CBSE", "ICSE", "IB"],
    "medium_of_instruction_supported": ["English"],
    "international_curriculum_supported": false,
    "education_levels_supported": ["Pre-Primary", "Primary", "Middle", "Secondary", "Senior Secondary"],
    "streams_supported": ["Science", "Commerce", "Humanities"],
    "pedagogy_model": "Experiential & Inquiry-Based Learning",
    "focus_areas": ["STEM", "Sports", "Arts", "Holistic Development"],
    "centralized_curriculum_framework": true,
    "centralized_teacher_training": true,
    "centralized_assessment_policy": true,
    "centralized_lms_available": true,
    "centralized_parent_communication_system": true,
    "child_safety_policy_available": true,
    "posco_compliance_policy": true,
    "anti_bullying_policy": true,
    "mental_health_policy": true,
    "teacher_background_verification_policy": true,
    "total_schools_count": 1,
    "national_presence": false,
    "international_presence": false,
    "cities_present_in": [],
    "states_present_in": [],
    "flagship_schools": [],
    "mobile_app_available": true,
    "average_rating": 4.5,
    "total_reviews": 150,
    "awards_and_recognition": ["Top School Award"],
    "meta_title": "",
    "meta_description": "",
    "canonical_url": "{$url}",
    "schema_type": "School",
    "claimed_by_organization": false,
    "email": "info@school.edu",
    "phone": "+91 XXXXXXXXXX",
    "is_top": false
  },
  "campuses": [
    {
      "campus_name": "Main Campus",
      "campus_type": "Main",
      "established_year": 1995,
      "city": "",
      "state": "",
      "country": "India",
      "pincode": "",
      "full_address": "",
      "google_map_url": "",
      "nearest_transport_hub": "",
      "campus_area_acres": 15,
      "classrooms_count": 50,
      "smart_classrooms": true,
      "laboratories_count": 6,
      "library_available": true,
      "hostel_available": false,
      "sports_facilities": ["Playground", "Basketball Court", "Skating Rink", "Swimming Pool"],
      "transport_available": true,
      "cctv_coverage": true,
      "fire_safety_certified": true,
      "campus_email": "",
      "campus_contact_numbers": []
    }
  ],
  "departments": [],
  "courses": []
}
PROMPT;
    }

    protected function buildExamConductingBodyPrompt(string $url, string $content, string $orgTypeTitle): string
    {
        return <<<PROMPT
You are an expert exam authority and testing agency data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the Exam Conducting Body at URL: {$url}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
1. Search Google actively for this Exam Conducting Body (e.g. NTA, UPSC, SSC, CBSE Exam Wing, State Public Service Commission, etc.):
   - Abbreviation, mandate description, public trust score (1-100), focus keywords.
   - Legal status: authority type (Constitutional Body, Statutory Body, Government Agency, Autonomous Body), parent ministry (e.g. Ministry of Education, DoPT, etc.), established by (Act of Parliament, Government Resolution), legal act reference, headquarters location, jurisdiction scope (National, State, Multi-State).
   - Functions: Exam Conduct, Question Paper Design, Result Declaration, Scorecard Issuance, Rank List Preparation.
   - Exam types conducted: Entrance, Recruitment, Eligibility, Board Examination.
   - Evaluation methods: CBT, OMR, Descriptive, Hybrid.
   - Exams owned/conducted: list names of major examinations conducted (e.g. JEE Main, NEET UG, CUET, UGC NET).
   - Volumes: Annual exam volume estimate, average candidates per year.
   - Capabilities: Exam modes supported (Online, Offline), question bank managed, normalization process available, multi-language support, remote proctoring supported.
   - Partners & Security: Exam centres management type (In-house, Outsourced, Hybrid), technology partners, logistics partners, data security standards.
   - Policies & Data: Result declaration policy, score validity period, re-evaluation allowed, re-evaluation process, data retention policy.
   - Transparency & Support: Grievance redressal mechanism, candidate portal URL, helpdesk contact number, helpdesk email, official notifications URL, FAQ URL, RTI applicable, audit conducted.
   - Reputation: Exam fairness policy, anti-malpractice measures, whistleblower policy, awards/recognition, media mentions.
2. If any field cannot be found, return empty string "" or false for booleans, but include every key in the JSON response.
3. Return ONLY valid JSON matching EXACTLY this structure:

{
  "organisation": {
    "name": "Full Name of Exam Conducting Body",
    "abbreviation": "NTA / UPSC / SSC",
    "short_name": "",
    "brand_name": "",
    "organisation_type": "{$orgTypeTitle}",
    "established_year": 2017,
    "central_authority": "Ministry of Education / Government of India",
    "head_office_location": "New Delhi",
    "headquarters_location": "New Delhi",
    "official_website": "{$url}",
    "mandate_description": "Premier testing organization conducting entrance and recruitment examinations...",
    "public_trust_score": 85,
    "focus_keywords": ["Entrance Exams", "CBT", "National Testing"],
    "authority_type": "Autonomous Body",
    "parent_ministry": "Ministry of Education",
    "established_by": "Government Resolution",
    "legal_act_reference": "",
    "jurisdiction_scope": "National",
    "claimed_by_authority": false,
    "functions": ["Exam Conduct", "Question Paper Design", "Result Declaration", "Scorecard Issuance", "Rank List Preparation"],
    "exam_types_conducted": ["Entrance", "Eligibility", "Recruitment"],
    "evaluation_methods": ["CBT", "OMR"],
    "exams_conducted_ids": ["JEE Main", "NEET UG", "CUET UG", "UGC NET"],
    "annual_exam_volume_estimate": "10+ Million Candidates",
    "average_candidates_per_year": "10000000",
    "exam_modes_supported": ["Online", "Offline"],
    "question_bank_managed": true,
    "normalization_process_available": true,
    "multi_language_support": true,
    "remote_proctoring_supported": false,
    "exam_centres_management_type": "Hybrid",
    "technology_partners": ["TCS iON", "NIC"],
    "logistics_partners": [],
    "data_security_standards": "ISO 27001 / CERT-In Certified",
    "result_declaration_policy_summary": "Standardized percentile and normalization based declaration",
    "score_validity_period": "1 Year / As per exam regulations",
    "re_evaluation_allowed": false,
    "re_evaluation_process_summary": "Challenge of answer keys allowed with fee refund on verification",
    "data_retention_policy": "Retained for 180 days post result",
    "grievance_redressal_mechanism": "Online portal and helpdesk ticketing",
    "candidate_portal_url": "",
    "helpdesk_contact_number": "+91 XXXXXXXXXX",
    "helpdesk_email": "helpdesk@exam.org",
    "official_notifications_urls": [],
    "faq_url": "",
    "rti_applicable": true,
    "audit_conducted": true,
    "exam_fairness_policy": "Strict protocols, CCTV surveillance, jammers, and biometric authentication",
    "anti_malpractice_measures": ["Biometric Verification", "Signal Jammers", "Live CCTV AI Monitoring", "Frisking"],
    "whistleblower_policy_available": true,
    "awards_or_recognition": [],
    "media_mentions": [],
    "data_source": "Official Portal & Gazette",
    "confidence_score": 90,
    "verification_status": "Verified",
    "status": "Active",
    "is_top": false
  },
  "campuses": [],
  "departments": [],
  "courses": []
}
PROMPT;
    }

    protected function buildCounsellingBodyPrompt(string $url, string $content, string $orgTypeTitle): string
    {
        return <<<PROMPT
You are an expert admission counselling and seat allocation authority research AI.
Extract comprehensive, highly accurate, and verified data for the Counselling Body at URL: {$url}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
1. Search Google actively for this Counselling Body (e.g. JoSAA, CSAB, MCC, State Counselling Authorities like CET/KEA/UPTAC, etc.):
   - Core identity: Name, short name, abbreviation, established year, about organisation, mandate description.
   - Legal status: authority type (Statutory Body, Government Committee, Autonomous Body), parent ministry or department, established by, legal reference document URL, jurisdiction scope (National, State, Regional), jurisdiction states.
   - Counselling functions: Seat Allotment, Document Verification, Merit List Preparation, Choice Filling.
   - Counselling types supported: Centralized, State-level, Institutional.
   - Education domains supported: Engineering, Medical, Architecture, Management, etc.
   - Counselling levels: Undergraduate, Postgraduate, Super Speciality.
   - Exams used for counselling: e.g. JEE Main, JEE Advanced, NEET UG, NEET PG.
   - Allocation basis: Rank, Score, Composite Merit; rank source validation, multiple exam support.
   - Seat matrix: seat matrix management, seat matrix source, quota types managed (AIQ, State Quota, NRI, Management), reservation policy reference, seat conversion rules supported.
   - Governance: rounds supported (e.g. 6 Rounds), round types (Mock, Regular, Special/Spot), choice locking mandatory, seat upgradation allowed, withdrawal rules, exit rules.
   - Fees: counselling fee collection, fee collection mode, refund processing responsibility, security deposit handling.
   - Technical: candidate login system, choice filling system, auto seat allocation engine, API integration, data security standards, institution reporting interface.
   - Reporting & Grievance: document verification mode (Online, Physical, Hybrid), institution confirmation process, mis reporting controls, appeal process summary, grievance contact details, RTI applicable, audit conducted.
   - Support & Scale: candidate support URL, candidate handbook/guidelines URL, helpdesk toll free number, operational hours, email, phone, FAQ, notifications, years of operation, candidate volume estimate, institutions covered, states covered.
2. If any field cannot be found, return empty string "" or false for booleans, but include every key in the JSON response.
3. Return ONLY valid JSON matching EXACTLY this structure:

{
  "organisation": {
    "name": "Full Name of Counselling Authority",
    "short_name": "Short Name",
    "abbreviation": "",
    "organisation_type": "{$orgTypeTitle}",
    "established_year": 2015,
    "central_authority": "Ministry of Education / State Higher Education Dept",
    "head_office_location": "",
    "official_website": "{$url}",
    "about_organisation": "Apex counselling and seat allocation authority...",
    "mandate_description": "Single-window online seat allocation...",
    "authority_type": "Government Committee",
    "parent_ministry_or_department": "",
    "established_by": "Government Notification",
    "legal_reference_document_url": "",
    "jurisdiction_scope": "National",
    "jurisdiction_states": "",
    "counselling_functions": ["Seat Allotment", "Document Verification", "Choice Filling"],
    "counselling_types_supported": ["Centralized"],
    "education_domains_supported": ["Engineering", "Architecture"],
    "counselling_levels_supported": ["Undergraduate"],
    "exams_used_for_counselling_ids": "JEE Main, JEE Advanced",
    "allocation_basis": "Rank",
    "rank_source_validation_required": true,
    "multiple_exam_support": true,
    "seat_matrix_management": true,
    "seat_matrix_source": "Institutions",
    "quota_types_managed": ["AIQ", "State Quota", "Home State"],
    "reservation_policy_reference": "Central reservation rules for SC/ST/OBC-NCL/EWS/PwD",
    "seat_conversion_rules_supported": true,
    "rounds_supported": "6 Rounds + Spot Round",
    "round_types": ["Mock Round", "Regular Rounds", "Special Round"],
    "choice_locking_mandatory": true,
    "seat_upgradation_allowed": true,
    "withdrawal_rules_summary": "Allowed up to pre-final round with nominal processing charge deduction",
    "exit_rules_summary": "",
    "counselling_fee_collection_supported": true,
    "fee_collection_mode": "Direct to Authority",
    "refund_processing_responsibility": "Authority",
    "security_deposit_handling": true,
    "counselling_portal_url": "{$url}",
    "candidate_login_system_available": true,
    "choice_filling_system_available": true,
    "auto_seat_allocation_engine": true,
    "api_integration_supported": true,
    "data_security_standards": "NIC / CERT-In compliant",
    "institution_reporting_interface_available": true,
    "document_verification_mode": "Online",
    "institution_confirmation_process_summary": "Online provisional seat allotment letter followed by reporting",
    "mis_reporting_controls": "Real-time seat acceptance and vacancy matrix dashboard",
    "appeal_process_summary": "",
    "grievance_contact_details": "",
    "candidate_portal_url": "",
    "candidate_support_url": "",
    "candidate_handbook_url": "",
    "candidate_guidelines_url": "",
    "helpdesk_toll_free_number": "",
    "helpdesk_operational_hours": "10:00 AM - 5:30 PM",
    "helpdesk_contact_number": "",
    "helpdesk_email": "",
    "faq_url": "",
    "official_notifications_urls": "",
    "social_media_handles": "",
    "rti_applicable": true,
    "audit_conducted": true,
    "years_of_operation": 10,
    "total_candidates_handled_estimate": "300,000+",
    "annual_candidate_volume": "300000",
    "institutions_covered_count": 120,
    "states_covered_count": 36,
    "meta_title": "",
    "meta_description": "",
    "canonical_url": "{$url}",
    "claimed_by_authority": false,
    "is_top": false
  },
  "campuses": [],
  "departments": [],
  "courses": []
}
PROMPT;
    }

    protected function buildRegulatoryBodyPrompt(string $url, string $content, string $orgTypeTitle): string
    {
        return <<<PROMPT
You are an expert regulatory body and government education agency research AI.
Extract comprehensive, highly accurate, and verified data for the Regulatory Body / Government Agency at URL: {$url}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
1. Search Google actively for this Regulatory Body / Government Agency (e.g. UGC, AICTE, NMC, PCI, BCI, NCTE, State Education Directorate, etc.):
   - Core identity: Name, abbreviation (e.g. UGC, AICTE), short name, established year, about organisation, mandate description.
   - Legal status: authority type (Statutory Body, Constitutional Body, Government Agency, Autonomous Body), parent ministry or department, established by (Act of Parliament, Government Resolution, Government Notification), legal reference document URL, jurisdiction scope (National, State, Multi-State, Regional), jurisdiction states.
   - Regulatory functions: Policy Making, Accreditation, Funding, Inspection, Standard Setting, Curriculum Framework.
   - Education domains supported: Higher Education, Technical, Medical, Teacher Training, Vocational.
   - Counselling roles: Regulation, Advisory, Direct Control, Grievance Redressal.
   - Standards & Compliance: Allocation basis, rank validation, multi-exam support, seat matrix source, data security standards.
   - Scope & Scale: Institutions covered count, states covered count, quota types managed, reservation policy reference.
   - Reporting & Grievance: Monitoring/inspection mechanisms, audit conducted, RTI applicable, grievance redressal mechanism, appeal process summary, grievance contact details.
   - Support & Links: Official website, helpdesk email, phone, notifications, FAQ URL, years of operation.
2. If any field cannot be found, return empty string "" or false for booleans, but include every key in the JSON response.
3. Return ONLY valid JSON matching EXACTLY this structure:

{
  "organisation": {
    "name": "Full Name of Regulatory Authority",
    "abbreviation": "UGC / AICTE / NMC",
    "short_name": "",
    "organisation_type": "{$orgTypeTitle}",
    "established_year": 1956,
    "central_authority": "Government of India / Ministry of Education",
    "head_office_location": "New Delhi",
    "official_website": "{$url}",
    "about_organisation": "Apex statutory body responsible for standards, coordination, and recognition...",
    "mandate_description": "Formulation of minimum standards, regulation, inspection, and accreditation...",
    "authority_type": "Statutory Body",
    "parent_ministry_or_department": "Ministry of Education",
    "established_by": "Act of Parliament",
    "legal_reference_document_url": "",
    "jurisdiction_scope": "National",
    "jurisdiction_states": "",
    "functions": ["Policy Making", "Standard Setting", "Accreditation", "Inspection", "Funding", "Curriculum Framework"],
    "education_domains_supported": ["Higher Education", "Technical", "Vocational"],
    "counselling_functions": ["Regulation", "Advisory"],
    "allocation_basis": "Composite Merit",
    "rank_source_validation_required": true,
    "multiple_exam_support": true,
    "seat_matrix_source": "Regulatory Body",
    "data_security_standards": "ISO 27001",
    "institutions_covered_count": 1200,
    "states_covered_count": 36,
    "quota_types_managed": ["AIQ", "State Quota"],
    "reservation_policy_reference": "Statutory reservation mandates",
    "document_verification_mode": "Online",
    "mis_reporting_controls": "Centralized compliance reporting portal",
    "grievance_redressal_mechanism": "Online grievance portal and Ombudsman regulations",
    "appeal_process_summary": "Statutory Appellate Committee review",
    "grievance_contact_details": "",
    "rti_applicable": true,
    "audit_conducted": true,
    "candidate_support_url": "",
    "candidate_handbook_url": "",
    "candidate_guidelines_url": "",
    "helpdesk_toll_free_number": "",
    "helpdesk_operational_hours": "09:30 AM - 06:00 PM",
    "helpdesk_contact_number": "",
    "helpdesk_email": "",
    "faq_url": "",
    "official_notifications_urls": "",
    "social_media_handles": "",
    "years_of_operation": 68,
    "meta_title": "",
    "meta_description": "",
    "canonical_url": "{$url}",
    "claimed_by_authority": false,
    "is_top": false
  },
  "campuses": [],
  "departments": [],
  "courses": []
}
PROMPT;
    }
}
