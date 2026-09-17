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
    public function extractFromUrl(
        string $url,
        string $orgTypeTitle = 'University',
        int $orgTypeId = 1,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null,
        ?string $customPrompt = null
    ): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file.');
        }

        if (!empty($customPrompt)) {
            $prompt = $customPrompt;
        } else {
            // 1. Fetch initial content from the main URL
            $websiteContent = $this->fetchUrlContent($url);

            // 2. Fetch and combine content from any additional reference URLs
            $combinedContent = "=== PRIMARY OFFICIAL WEBSITE URL: {$url} ===\n" . $websiteContent;
            if (!empty($referenceUrls)) {
                $combinedContent .= "\n\n=== ADDITIONAL REFERENCE SOURCES PROVIDED BY ADMIN ===";
                foreach ($referenceUrls as $idx => $refUrl) {
                    $refNum = $idx + 1;
                    $refContent = $this->fetchUrlContent($refUrl);
                    $combinedContent .= "\n\n--- REFERENCE SOURCE #{$refNum}: {$refUrl} ---\n" . substr($refContent, 0, 10000);
                }
            }

            // 3. Build structured extraction prompt tailored to the selected Organisation Type and Mode
            $prompt = $this->buildPrompt($url, $combinedContent, $orgTypeTitle, $orgTypeId, $referenceUrls, $targetOrg);
        }

        // Candidate fallback models in case of high demand
        $modelsToTry = array_unique([
            $this->model,
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-3.7-flash',
            'gemini-3.1-flash-lite'
        ]);

        return $this->executePrompt($prompt, $modelsToTry);
    }

    /**
     * Public method to build the prompt for preview / customization
     */
    public function buildExtractionPrompt(
        string $url,
        string $orgTypeTitle = 'University',
        int $orgTypeId = 1,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null,
        string $content = ''
    ): string {
        return $this->buildPrompt($url, $content, $orgTypeTitle, $orgTypeId, $referenceUrls, $targetOrg);
    }

    /**
     * Extract scoped updates for an existing organisation strictly for enabled/selected fields
     */
    public function extractScopedUpdates(
        string $url,
        string $orgTypeTitle,
        int $orgTypeId,
        array $allowedFields,
        array $currentData
    ): array {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file.');
        }

        $websiteContent = $this->fetchUrlContent($url);
        $prompt = $this->buildScopedUpdatePrompt($url, $websiteContent, $orgTypeTitle, $allowedFields, $currentData);

        $modelsToTry = array_unique([
            $this->model,
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-3.7-flash',
            'gemini-3.1-flash-lite'
        ]);

        return $this->executePrompt($prompt, $modelsToTry);
    }

    /**
     * Execute a prompt against Gemini models with fallback
     */
    protected function executePrompt(string $prompt, array $modelsToTry): array
    {
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
                    if (is_array($decoded) && (isset($decoded['organisation']) || isset($decoded['campuses']) || isset($decoded['courses']) || isset($decoded['departments']))) {
                        $data = $decoded;
                        break;
                    }

                    // Try finding JSON substring { ... }
                    if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
                        $decodedSub = json_decode($matches[0], true);
                        if (is_array($decodedSub) && (isset($decodedSub['organisation']) || isset($decodedSub['campuses']) || isset($decodedSub['courses']) || isset($decodedSub['departments']))) {
                            $data = $decodedSub;
                            break;
                        }
                    }
                }

                if (is_array($data) && (isset($data['organisation']) || isset($data['campuses']) || isset($data['courses']) || isset($data['departments']))) {
                    if (!isset($data['organisation'])) {
                        $data['organisation'] = [];
                    }
                    if (!isset($data['campuses'])) {
                        $data['campuses'] = [];
                    }
                    if (!isset($data['departments'])) {
                        $data['departments'] = [];
                    }
                    if (!isset($data['courses'])) {
                        $data['courses'] = [];
                    }
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
     * Build prompt strictly scoped to allowed fields and current data comparison
     */
    protected function buildScopedUpdatePrompt(
        string $url,
        string $content,
        string $orgTypeTitle,
        array $allowedFields,
        array $currentData
    ): string {
        $allowedJson = json_encode($allowedFields, JSON_PRETTY_PRINT);
        $currentJson = json_encode($currentData, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are an expert institutional research and verification AI agent.
You are tasked with reviewing and fetching UPDATES for an existing educational institution.

INSTITUTION URL: {$url}
ORGANISATION TYPE: {$orgTypeTitle}

RAW WEBSITE / SEARCH CONTENT:
{$content}

CURRENT DATABASE RECORD (OLD DATA):
{$currentJson}

ALLOWED FIELDS FOR UPDATE:
Only the following fields are enabled by the admin for checking and updating:
{$allowedJson}

CRITICAL RULES:
1. ONLY return data for the keys explicitly listed in "ALLOWED FIELDS". Do NOT invent or include other field keys.
2. For every allowed field:
   - Search the website and Google Search Grounding to find the latest and most accurate current values.
   - If a field is already accurate in the database, keep the current value.
   - If there is a newer, updated, or previously missing value (e.g. new HOD, new NAAC grade, contact phone, website, accreditation), provide the NEW accurate value.
3. For departments, campuses, and courses:
   - For existing departments/campuses/courses provided in CURRENT DATABASE RECORD, provide their updated fields matching the allowed keys.
   - You may also include newly detected departments, campuses, or courses if verified on the official website.
4. Output MUST be ONLY valid JSON matching this structure:
{
  "organisation": {
    /* ONLY allowed organisation keys */
  },
  "campuses": [
    {
      "name": "...",
      /* ONLY allowed campus keys */
    }
  ],
  "departments": [
    {
      "name": "...",
      /* ONLY allowed department keys */
    }
  ],
  "courses": [
    {
      "academic_unit_name": "...",
      /* ONLY allowed course keys */
    }
  ]
}
PROMPT;
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
     * Build structured extraction prompt tailored to the selected Organisation Type and Mode
     */
    protected function buildPrompt(
        string $url,
        string $content,
        string $orgTypeTitle,
        int $orgTypeId,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null
    ): string
    {
        if ($targetOrg) {
            return $this->buildCampusesAndCoursesPrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg);
        }

        $titleLower = strtolower($orgTypeTitle);

        if (str_contains($titleLower, 'school') || $orgTypeId === 4) {
            return $this->buildSchoolPrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg);
        }

        if (str_contains($titleLower, 'exam') || str_contains($titleLower, 'conducting') || $orgTypeId === 5) {
            return $this->buildExamConductingBodyPrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg);
        }

        if (str_contains($titleLower, 'counselling') || $orgTypeId === 6) {
            return $this->buildCounsellingBodyPrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg);
        }

        if (str_contains($titleLower, 'regulatory') || str_contains($titleLower, 'agency') || in_array($orgTypeId, [7, 8])) {
            return $this->buildRegulatoryBodyPrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg);
        }

        if (str_contains($titleLower, 'institute') || $orgTypeId === 3) {
            return $this->buildInstitutePrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg);
        }

        // Default to University / College (Types 1, 2)
        return $this->buildOrganisationOnlyPrompt($url, $content, $orgTypeTitle, $orgTypeId, $referenceUrls);
    }

    protected function formatReferenceUrlsText(array $referenceUrls): string
    {
        if (empty($referenceUrls)) {
            return '';
        }
        return "\nADDITIONAL REFERENCE URLS PROVIDED BY ADMIN:\n" . implode("\n", array_map(fn($u) => "- " . $u, $referenceUrls)) . "\n";
    }

    /**
     * Specialized Prompt for Mode 2: Campuses, Departments, and Courses for an existing Target Organisation
     */
    public function buildCampusesAndCoursesPrompt(
        string $url,
        string $content,
        string $orgTypeTitle,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null
    ): string {
        $orgName = $targetOrg ? $targetOrg->name : 'the Selected Institution';
        $orgShort = $targetOrg ? $targetOrg->short_name : '';
        $orgSite = $targetOrg ? $targetOrg->official_website : $url;
        $orgId = $targetOrg ? $targetOrg->id : 0;

        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);
        $cleanContent = !empty(trim($content)) ? "\nPRIMARY WEBSITE CONTENT & REFERENCE PREVIEW:\n" . substr(trim($content), 0, 10000) : "";

        return <<<PROMPT
You are an expert higher education data extraction and research AI agent.
Your PRIMARY GOAL is to perform comprehensive, verified research and extract all physical CAMPUSES, academic DEPARTMENTS / FACULTIES, and ACADEMIC COURSES / DEGREES offered by the institution "{$orgName}".

TARGET INSTITUTION: {$orgName}
SHORT NAME: {$orgShort}
OFFICIAL SITE: {$orgSite}
PRIMARY URL: {$url}{$refUrlsText}
ORGANISATION TYPE: {$orgTypeTitle}
{$cleanContent}

CRITICAL RESEARCH & WRITING INSTRUCTIONS:
1. DEEP WEB SEARCH & FACT VERIFICATION:
   - The institution "{$orgName}" already exists in our database. DO NOT focus on basic organisation identity fields.
   - Carefully inspect the PRIMARY URL and any ADDITIONAL REFERENCE URLS provided above.
   - If courses, fee structures, eligibility criteria, campus details, or department information are missing or incomplete on the provided URLs, actively search Google Search Grounding, official admission portals, academic catalogues, Shiksha, Collegedunia, Wikipedia, and brochure PDFs for "{$orgName}" to discover all available physical campuses, academic faculties, and degree programs.
2. ORIGINAL & POLISHED DESCRIPTIONS (NO COPY-PASTE):
   - DO NOT copy-paste raw text or boilerplate disclaimers from websites.
   - Synthesize, summarize, and write original, clear, informative, professional, and SEO-friendly summaries in your own words for:
     * `about_department`: 1-2 paragraphs detailing the department's academic philosophy, faculty expertise, lab infrastructure, and focus areas.
     * `overview` (Course Overview): 1-2 engaging paragraphs explaining the degree curriculum, industry relevance, career pathways, and learning outcomes.
     * `eligibility`: Clear, concise criteria (e.g. "10+2 with minimum 50% aggregate marks in PCM from a recognized board").
     * `admission_process`: Clear step-by-step summary (e.g. "Online application followed by entrance exam score evaluation and personal interview").
     * `placement_details`: Comprehensive summary including average & highest package, top recruiters, and industry domains.
3. STRUCTURE & LINKING:
   - Link each course to its respective campus and department name.
   - If exact fees or dates are not publicly listed, provide accurate estimates/ranges based on verified sources or standard fees for this institution.
4. RETURN FORMAT:
   - Return ONLY valid, parseable JSON matching EXACTLY this structure:

{
  "target_organisation_id": {$orgId},
  "target_organisation_name": "{$orgName}",
  "campuses": [
    {
      "campus_name": "Main Campus",
      "campus_type": "Main",
      "established_year": 2005,
      "city": "Bengaluru",
      "state": "Karnataka",
      "country": "India",
      "pincode": "562106",
      "full_address": "Chikkahagade Cross, Chandapura - Anekal Main Road, Bengaluru, Karnataka",
      "google_map_url": "https://maps.google.com/?q=Alliance+University+Bangalore",
      "nearest_transport_hub": "Electronic City Metro / Chandapura Bus Stand",
      "campus_area_acres": 55,
      "academic_blocks_count": 6,
      "classrooms_count": 80,
      "smart_classrooms": true,
      "laboratories_count": 30,
      "library_available": true,
      "digital_library_access": true,
      "hostel_available": true,
      "hostel_type": "Both",
      "hostel_capacity": 2500,
      "medical_facility_available": true,
      "sports_facilities": [
        "Cricket Ground",
        "Football Turf",
        "Basketball Court",
        "Indoor Gymnasium",
        "Badminton Court"
      ],
      "transport_available": true,
      "cctv_coverage": true,
      "fire_safety_certified": true,
      "campus_email": "campus@alliance.edu.in",
      "campus_contact_numbers": [
        "+91 80 4619 9000"
      ]
    }
  ],
  "departments": [
    {
      "department_name": "Alliance School of Business",
      "department_code": "ASOB",
      "department_type": "Academic",
      "established_year": 2010,
      "head_of_department_name": "Dr. Ray Titus",
      "head_of_department_designation": "Dean & Professor of Marketing",
      "hod_email": "dean.asob@alliance.edu.in",
      "faculty_count": 45,
      "discipline_area": "Management & Business Administration",
      "specializations_supported": [
        "Marketing",
        "Finance",
        "Operations",
        "Human Resource Management",
        "Business Analytics"
      ],
      "education_levels_supported": [
        "Undergraduate",
        "Postgraduate",
        "Doctoral (Ph.D)"
      ],
      "department_labs_count": 4,
      "research_publications_count": 180,
      "funded_projects_count": 8,
      "patents_filed_count": 2,
      "phd_supervision_available": true,
      "industry_collaboration_supported": true,
      "is_interdisciplinary": true,
      "specialized_labs_available": true,
      "about_department": "Alliance School of Business is recognized for excellence in management education, emphasizing case-based pedagogy, corporate mentorship, global student exchange, and cutting-edge business research."
    }
  ],
  "courses": [
    {
      "course_name": "Master of Business Administration",
      "short_name": "MBA",
      "program_level": "Postgraduate",
      "department_name": "Alliance School of Business",
      "campus_name": "Main Campus",
      "stream": "Management",
      "discipline": "Business Administration",
      "specialization": "Business Analytics & Marketing",
      "duration": "2 Years",
      "mode": "Regular",
      "fees": "750000",
      "total_fees": "1500000",
      "annual_fee_range": "₹7,50,000 - ₹8,00,000",
      "admission_fee": "50000",
      "installment_available": true,
      "scholarship_available": true,
      "refund_policy_available": true,
      "roi": "High",
      "eligibility": "Bachelor's degree in any discipline with minimum 50% aggregate marks (45% for reserved categories).",
      "admission_process": "National entrance score (CAT/MAT/XAT/GMAT/AMAT) followed by Alliance Selection Process (Oral Presentation & Personal Interview).",
      "entrance_exams": "CAT, MAT, XAT, GMAT, NMAT, CMAT",
      "placement_details": "Average package ₹8.5 LPA, highest domestic package ₹21 LPA with 600+ recruiting partners including Deloitte, KPMG, Amazon, and EY.",
      "rating": "4.6",
      "overview": "The MBA program at Alliance University offers a globally benchmarked curriculum designed to develop strategic decision-making, ethical leadership, and data-driven management skills through experiential learning and corporate internships."
    }
  ]
}
PROMPT;
    }

    /**
     * Specialized Prompt for Mode 1: Organisation Profile Auto-Creation ONLY
     */
    public function buildOrganisationOnlyPrompt(
        string $url,
        string $content,
        string $orgTypeTitle,
        int $orgTypeId = 1,
        array $referenceUrls = []
    ): string {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);
        $cleanContent = !empty(trim($content)) ? "\nPRIMARY WEBSITE CONTENT & REFERENCE PREVIEW:\n" . substr(trim($content), 0, 10000) : "";

        return <<<PROMPT
You are an expert higher education data extraction and research AI agent.
Your PRIMARY GOAL is to perform comprehensive, verified research and extract institutional profile data for the educational institution at:
PRIMARY OFFICIAL URL: {$url}{$refUrlsText}
ORGANISATION TYPE: {$orgTypeTitle}

=== CRITICAL EXTRACTION SCOPE: ORGANISATION PROFILE ONLY ===
Extract ONLY the institutional profile for the "organisation" object.
{$cleanContent}

CRITICAL RESEARCH & WRITING INSTRUCTIONS:
1. DEEP WEB SEARCH & FACT VERIFICATION:
   - Carefully inspect the PRIMARY URL and any ADDITIONAL REFERENCE URLS provided above.
   - If any important institutional details (e.g. established year, UGC/AICTE approval, NAAC grade & cycle, NIRF ranking, Chancellor/VC names, managing trust, official contacts) are NOT found on the provided URLs, actively search Google Search Grounding, official regulatory directories (UGC, AICTE, NAAC, NIRF, AISHE), Wikipedia, and authoritative educational portals.
2. ORIGINAL & POLISHED DESCRIPTIONS (NO COPY-PASTE):
   - DO NOT copy-paste raw text or boilerplate disclaimers from websites.
   - Synthesize, rewrite, and write original, engaging, professionally structured, and SEO-friendly summaries in your own words for:
     * `about_university` / `about_organisation`: 2-3 well-written paragraphs covering history, academic standing, campus culture, infrastructure, and institutional achievements.
     * `vision_mission`: Clear, inspiring, and concise vision and mission statements.
     * `core_values`: Clean array of 3 to 6 key institutional values (e.g. ["Academic Rigor", "Innovation & Research", "Ethical Leadership", "Inclusivity"]).
3. COMPLETENESS & CLEAN DATA:
   - If a field is not applicable or genuinely cannot be found after deep search, use empty string "" for text/urls, null for numbers, false for booleans, or [] for lists.
4. RETURN FORMAT:
   - Return ONLY valid, parseable JSON matching EXACTLY this structure:

{
  "organisation": {
    "name": "Full Official Legal Name of Institution",
    "short_name": "Short Name / Abbreviation",
    "brand_name": "Brand Name / Popular Name",
    "organisation_type": "{$orgTypeTitle}",
    "brand_type": "Independent",
    "central_authority": "Governing Body / Sponsoring Trust / Society Name",
    "head_office_location": "City, State, Country",
    "official_website": "{$url}",
    "admission_portal_url": "https://admissions.example.edu.in",
    "student_portal_url": "https://portal.example.edu.in",
    "parent_portal_url": "",
    "established_year": 1985,
    "ownership_type": "Private",
    "university_type": "Private University",
    "about_university": "Write a polished, original 2-paragraph profile describing the institution's legacy, campus environment, and academic focus...",
    "about_organisation": "Write an executive overview highlighting key highlights, leadership, and recognitions...",
    "vision_mission": "To provide transformative education and foster ethical leadership, innovation, and global excellence...",
    "core_values": [
      "Academic Excellence",
      "Integrity & Ethics",
      "Innovation & Research",
      "Social Responsibility"
    ],
    "chancellor_name": "Chancellor / Founder Name",
    "vice_chancellor_name": "Vice Chancellor / Principal / Director Name",
    "governing_body_name": "Board of Governors / Management Trust Name",
    "autonomous_status": true,
    "degree_awarding_authority": true,
    "ugc_recognized": true,
    "ugc_approval_number": "F. No. 12-34/2005(CPP-I)",
    "aicte_approved": true,
    "naac_accredited": true,
    "naac_grade": "A+",
    "nirf_rank_overall": 45,
    "nirf_rank_category": "Ranked in Top 50 by NIRF",
    "international_accreditations": [
      "ABET",
      "AACSB",
      "QS 5-Star"
    ],
    "statutory_approvals": [
      "UGC",
      "AICTE",
      "NBA",
      "BCI",
      "PCI"
    ],
    "levels_offered": [
      "Undergraduate",
      "Postgraduate",
      "Doctoral (Ph.D)",
      "Diploma"
    ],
    "number_of_campuses": 2,
    "number_of_constituent_colleges": 4,
    "number_of_affiliated_colleges": 0,
    "email": "admissions@institution.edu.in",
    "phone": "+91 11 XXXXXXXX",
    "is_top": true
  }
}
PROMPT;
    }

    protected function buildUniversityPrompt(
        string $url,
        string $content,
        string $orgTypeTitle,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null
    ): string
    {
        if ($targetOrg) {
            return $this->buildCampusesAndCoursesPrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg);
        }
        return $this->buildOrganisationOnlyPrompt($url, $content, $orgTypeTitle, 1, $referenceUrls);
    }

    protected function buildInstitutePrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = []): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        return <<<PROMPT
You are an expert institutional data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the Institute / Academy at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
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

    protected function buildSchoolPrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = []): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        return <<<PROMPT
You are an expert K-12 school education data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the School / School Network at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
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

    protected function buildExamConductingBodyPrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = []): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        return <<<PROMPT
You are an expert exam authority and testing agency data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the Exam Conducting Body at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
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

    protected function buildCounsellingBodyPrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = []): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        return <<<PROMPT
You are an expert admission counselling and seat allocation authority research AI.
Extract comprehensive, highly accurate, and verified data for the Counselling Body at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
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

    protected function buildRegulatoryBodyPrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = []): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        return <<<PROMPT
You are an expert regulatory body and government education agency research AI.
Extract comprehensive, highly accurate, and verified data for the Regulatory Body / Government Agency at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
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
