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
        $this->model = config('services.gemini.model', 'gemini-3.6-flash');
    }

    /**
     * Extract organisation, campus, department, or course details from a given URL using Gemini + Search Grounding
     */
    public function extractFromUrl(
        string $url,
        string $orgTypeTitle = 'University',
        int $orgTypeId = 1,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null,
        ?string $customPrompt = null,
        string $mode = 'organisation',
        ?\App\Models\Campus $targetCampus = null,
        ?\App\Models\Department $targetDepartment = null,
        bool $searchGoogle = true
    ): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file.');
        }

        if (!empty($customPrompt)) {
            $prompt = $this->sanitizeUtf8($customPrompt);
        } else {
            // 1. Fetch initial content from the main URL (with smart automatic linked sub-page discovery)
            $websiteContent = $this->fetchUrlContent($url, $mode, true);

            // 2. Fetch and combine content from any additional reference URLs
            $combinedContent = "=== PRIMARY OFFICIAL WEBSITE URL: {$url} ===\n" . $websiteContent;
            if (!empty($referenceUrls)) {
                $combinedContent .= "\n\n=== ADDITIONAL REFERENCE SOURCES PROVIDED BY ADMIN ===";
                foreach ($referenceUrls as $idx => $refUrl) {
                    $refNum = $idx + 1;
                    $refContent = $this->fetchUrlContent($refUrl, $mode, false);
                    $combinedContent .= "\n\n--- REFERENCE SOURCE #{$refNum}: {$refUrl} ---\n" . mb_substr($refContent, 0, 10000, 'UTF-8');
                }
            }

            // 3. Build structured extraction prompt tailored to the selected Entity Mode
            $prompt = $this->buildPrompt($url, $combinedContent, $orgTypeTitle, $orgTypeId, $referenceUrls, $targetOrg, $mode, $targetCampus, $targetDepartment, $searchGoogle);
        }

        // Candidate fallback models in order of speed and stability
        $modelsToTry = array_values(array_filter(array_unique([
            $this->model,
            'gemini-3.6-flash',
            'gemini-3.7-flash',
            'gemini-3.1-flash-lite',
        ]), fn($m) => !empty($m) && !in_array($m, ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-2.5-flash'])));

        return $this->executePrompt($this->sanitizeUtf8($prompt), $modelsToTry, $searchGoogle);
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
        string $content = '',
        string $mode = 'organisation',
        ?\App\Models\Campus $targetCampus = null,
        ?\App\Models\Department $targetDepartment = null,
        bool $searchGoogle = true
    ): string {
        if (empty($content) && !empty($url) && filter_var($url, FILTER_VALIDATE_URL) && !str_contains($url, 'example.edu')) {
            $websiteContent = $this->fetchUrlContent($url, $mode, true);
            $content = "=== PRIMARY OFFICIAL WEBSITE URL: {$url} ===\n" . $websiteContent;
            if (!empty($referenceUrls)) {
                $content .= "\n\n=== ADDITIONAL REFERENCE SOURCES PROVIDED BY ADMIN ===";
                foreach ($referenceUrls as $idx => $refUrl) {
                    $refNum = $idx + 1;
                    $refContent = $this->fetchUrlContent($refUrl, $mode, false);
                    $content .= "\n\n--- REFERENCE SOURCE #{$refNum}: {$refUrl} ---\n" . mb_substr($refContent, 0, 10000, 'UTF-8');
                }
            }
        }
        return $this->sanitizeUtf8($this->buildPrompt($url, $content, $orgTypeTitle, $orgTypeId, $referenceUrls, $targetOrg, $mode, $targetCampus, $targetDepartment, $searchGoogle));
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

        $modelsToTry = array_values(array_filter(array_unique([
            $this->model,
            'gemini-3.6-flash',
            'gemini-3.7-flash',
            'gemini-3.1-flash-lite',
        ]), fn($m) => !empty($m) && !in_array($m, ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-2.5-flash'])));

        return $this->executePrompt($prompt, $modelsToTry, true);
    }

    /**
     * Execute a prompt against Gemini models with fallback
     */
    protected function executePrompt(string $prompt, array $modelsToTry, bool $searchGoogle = true): array
    {
        $lastError = null;
        $prompt = $this->sanitizeUtf8($prompt);

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
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 32768,
                    'thinkingConfig' => [
                        'thinkingBudget' => 0,
                    ],
                ]
            ];

            if ($searchGoogle) {
                $payload['tools'] = [
                    ['google_search' => (object)[]]
                ];
            }

            try {
                $response = Http::withoutVerifying()->timeout(120)->post($endpoint, $payload);

                if ($searchGoogle && !$response->successful()) {
                    // Fallback tool naming if needed
                    if ($response->status() === 400 && str_contains($response->body(), 'tools')) {
                        $payload['tools'] = [['googleSearch' => (object)[]]];
                        $response = Http::withoutVerifying()->timeout(120)->post($endpoint, $payload);
                    }

                    if (!$response->successful() && $response->status() !== 404) {
                        // Try without tool if tools conflict
                        unset($payload['tools']);
                        $response = Http::withoutVerifying()->timeout(120)->post($endpoint, $payload);
                    }
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
                    if (empty($text)) {
                        continue;
                    }
                    $text = $this->sanitizeUtf8($text);

                    // 1. Try direct JSON decode
                    $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));
                    $decoded = json_decode($clean, true);
                    if (is_array($decoded) && (isset($decoded['organisation']) || isset($decoded['campuses']) || isset($decoded['courses']) || isset($decoded['departments']))) {
                        $data = $decoded;
                        break;
                    }

                    // 2. Substring between first { and last }
                    $firstBrace = mb_strpos($text, '{', 0, 'UTF-8');
                    $lastBrace = mb_strrpos($text, '}', 0, 'UTF-8');
                    if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                        $candidate = mb_substr($text, $firstBrace, $lastBrace - $firstBrace + 1, 'UTF-8');
                        $decoded = json_decode($candidate, true);
                        if (is_array($decoded) && (isset($decoded['organisation']) || isset($decoded['campuses']) || isset($decoded['courses']) || isset($decoded['departments']))) {
                            $data = $decoded;
                            break;
                        }
                    }
                }

                if (is_array($data) && (isset($data['organisation']) || isset($data['campuses']) || isset($data['courses']) || isset($data['departments']))) {
                    return $this->normalizeExtractedPayload($data);
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Gemini model {$modelName} exception: {$lastError}, trying next fallback model...");
            }
        }

        throw new \Exception('Gemini API extraction failed: ' . ($lastError ?? 'Could not parse response from available AI models.'));
    }

    /**
     * Sanitize string to guarantee valid UTF-8 encoding without malformed byte sequences
     */
    public function sanitizeUtf8(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // 1. Check and convert encoding if needed
        if (!mb_check_encoding($text, 'UTF-8')) {
            $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII', 'UTF-16', 'UTF-32'], true) ?: 'ISO-8859-1';
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }

        // 2. Remove/replace any broken multi-byte sequences using iconv
        if (function_exists('iconv')) {
            $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
            if ($cleaned !== false) {
                $text = $cleaned;
            }
        }

        // 3. Clean invalid control chars while preserving valid UTF-8
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        return $sanitized !== null ? $sanitized : mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }

    /**
     * Recursively sanitize all strings in an array to valid UTF-8
     */
    public function sanitizeUtf8Recursive($data)
    {
        if (is_string($data)) {
            return $this->sanitizeUtf8($data);
        }
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $val) {
                $cleanKey = is_string($key) ? $this->sanitizeUtf8($key) : $key;
                $cleaned[$cleanKey] = $this->sanitizeUtf8Recursive($val);
            }
            return $cleaned;
        }
        return $data;
    }

    /**
     * Normalize and sanitize extracted payload keys
     */
    protected function normalizeExtractedPayload(array $data): array
    {
        $data = $this->sanitizeUtf8Recursive($data);

        if (!isset($data['organisation']) || !is_array($data['organisation'])) {
            $data['organisation'] = [];
        }
        if (!isset($data['campuses']) || !is_array($data['campuses'])) {
            $data['campuses'] = [];
        }
        if (!isset($data['departments']) || !is_array($data['departments'])) {
            $data['departments'] = [];
        }
        if (!isset($data['courses']) || !is_array($data['courses'])) {
            $data['courses'] = [];
        }

        // Normalize Organisation
        if (!empty($data['organisation'])) {
            $org = &$data['organisation'];

            // Normalize university_type
            $rawUniType = strtolower((string)($org['university_type'] ?? ''));
            if (str_contains($rawUniType, 'deemed')) {
                $org['university_type'] = 'Deemed';
            } elseif (str_contains($rawUniType, 'central')) {
                $org['university_type'] = 'Central';
            } elseif (str_contains($rawUniType, 'state')) {
                $org['university_type'] = 'State';
            } elseif (str_contains($rawUniType, 'international')) {
                $org['university_type'] = 'International';
            } elseif (str_contains($rawUniType, 'privat')) {
                $org['university_type'] = 'Private';
            }

            // Normalize ownership_type
            $rawOwnership = strtolower((string)($org['ownership_type'] ?? ''));
            if (str_contains($rawOwnership, 'gov') || str_contains($rawOwnership, 'public') || str_contains($rawOwnership, 'state')) {
                $org['ownership_type'] = 'Government';
            } elseif (str_contains($rawOwnership, 'trust') || str_contains($rawOwnership, 'society')) {
                $org['ownership_type'] = 'Trust';
            } elseif (str_contains($rawOwnership, 'minority')) {
                $org['ownership_type'] = 'Minority';
            } elseif (str_contains($rawOwnership, 'privat')) {
                $org['ownership_type'] = 'Private';
            }

            // Normalize university_category
            $rawCat = strtolower((string)($org['university_category'] ?? ''));
            if ((str_contains($rawCat, 'teach') && str_contains($rawCat, 'research')) || str_contains($rawCat, '+') || str_contains($rawCat, '&') || str_contains($rawCat, 'both')) {
                $org['university_category'] = 'Teaching + Research';
            } elseif (str_contains($rawCat, 'research')) {
                $org['university_category'] = 'Research';
            } elseif (str_contains($rawCat, 'teach')) {
                $org['university_category'] = 'Teaching';
            } else {
                if (!empty($org['naac_accredited']) || !empty($org['nirf_rank_overall'])) {
                    $org['university_category'] = 'Teaching + Research';
                }
            }

            // Normalize levels_offered
            if (!empty($org['levels_offered'])) {
                $rawLevels = is_array($org['levels_offered']) ? $org['levels_offered'] : explode(',', (string)$org['levels_offered']);
                $normalizedLevels = [];
                foreach ($rawLevels as $lvl) {
                    $lvlLower = strtolower(trim((string)$lvl));
                    if (str_contains($lvlLower, 'undergrad') || str_contains($lvlLower, 'bachelor') || $lvlLower === 'ug' || (str_contains($lvlLower, 'graduate') && !str_contains($lvlLower, 'post'))) {
                        $normalizedLevels[] = 'UG';
                    } elseif (str_contains($lvlLower, 'postgrad') || str_contains($lvlLower, 'master') || $lvlLower === 'pg') {
                        $normalizedLevels[] = 'PG';
                    } elseif (str_contains($lvlLower, 'phd') || str_contains($lvlLower, 'ph.d') || str_contains($lvlLower, 'doc') || str_contains($lvlLower, 'research')) {
                        $normalizedLevels[] = 'Doctoral';
                    } elseif (str_contains($lvlLower, 'diploma') || str_contains($lvlLower, 'polytechnic') || str_contains($lvlLower, 'cert')) {
                        $normalizedLevels[] = 'Diploma';
                    }
                }
                $org['levels_offered'] = array_values(array_unique($normalizedLevels));
            }

            unset($org);
        }

        // 1. Normalize & Deduplicate Campuses
        $dedupedCampuses = [];
        foreach ($data['campuses'] as $c) {
            if (!is_array($c)) continue;
            if (empty($c['campus_name']) && !empty($c['name'])) {
                $c['campus_name'] = $c['name'];
            }
            if (empty($c['full_address']) && !empty($c['address'])) {
                $c['full_address'] = $c['address'];
            }
            $rawCampus = strtolower($c['campus_name']);
            $isMain = str_contains($rawCampus, 'main') || strtolower($c['campus_type'] ?? '') === 'main';
            if ($isMain) {
                $normName = 'main';
            } else {
                $normName = strtolower(trim(preg_replace('/\b(campus|branch|location|centre|center|the|university|college|school)\b/i', '', $c['campus_name'])));
                $normName = preg_replace('/[^a-z0-9]/', '', $normName);
            }
            $normCity = strtolower(trim($c['city'] ?? ''));
            $key = ($normName !== '' ? $normName : 'campus') . '_' . $normCity;

            if (!isset($dedupedCampuses[$key])) {
                $dedupedCampuses[$key] = $c;
            } else {
                // Merge richer fields
                foreach ($c as $k => $val) {
                    if (!empty($val) && empty($dedupedCampuses[$key][$k])) {
                        $dedupedCampuses[$key][$k] = $val;
                    }
                }
            }
        }
        $data['campuses'] = array_values($dedupedCampuses);

        // 2. Normalize & Deduplicate Departments
        $dedupedDepts = [];
        foreach ($data['departments'] as $d) {
            if (!is_array($d)) continue;
            if (empty($d['department_name']) && !empty($d['name'])) {
                $d['department_name'] = $d['name'];
            }
            if (empty($d['about_department'])) {
                $d['about_department'] = $d['description'] ?? $d['overview'] ?? $d['about'] ?? '';
            }
            if (empty($d['department_name'])) continue;

            $rawName = $d['department_name'];
            $normName = strtolower(trim($rawName));
            $normName = preg_replace('/\b(department of|dept of|school of|faculty of|centre for|center for|division of|department|dept|school|faculty|centre|center|division)\b/i', '', $normName);
            $normName = preg_replace('/[^a-z0-9]/', '', $normName);
            if (empty($normName)) {
                $normName = strtolower(preg_replace('/[^a-z0-9]/', '', $rawName));
            }

            if (!isset($dedupedDepts[$normName])) {
                $dedupedDepts[$normName] = $d;
            } else {
                // Merge richer fields into existing
                $existing = &$dedupedDepts[$normName];
                if (strlen($d['department_name']) > strlen($existing['department_name'])) {
                    $existing['department_name'] = $d['department_name'];
                }
                if (empty($existing['about_department']) || (strlen($d['about_department'] ?? '') > strlen($existing['about_department']))) {
                    $existing['about_department'] = $d['about_department'] ?? $existing['about_department'];
                }
                if (empty($existing['faculty_count']) && !empty($d['faculty_count'])) {
                    $existing['faculty_count'] = $d['faculty_count'];
                }
                if (empty($existing['department_code']) && !empty($d['department_code'])) {
                    $existing['department_code'] = $d['department_code'];
                }
                if (empty($existing['discipline_area']) && !empty($d['discipline_area'])) {
                    $existing['discipline_area'] = $d['discipline_area'];
                }
                if (!empty($d['specializations_supported'])) {
                    $existing['specializations_supported'] = array_values(array_unique(array_merge(
                        (array)($existing['specializations_supported'] ?? []),
                        (array)$d['specializations_supported']
                    )));
                }
                if (!empty($d['education_levels_supported'])) {
                    $existing['education_levels_supported'] = array_values(array_unique(array_merge(
                        (array)($existing['education_levels_supported'] ?? []),
                        (array)$d['education_levels_supported']
                    )));
                }
                unset($existing);
            }
        }
        $data['departments'] = array_values($dedupedDepts);

        // 3. Normalize & Deduplicate Courses
        $dedupedCourses = [];
        foreach ($data['courses'] as $cr) {
            if (!is_array($cr)) continue;
            if (empty($cr['course_name'])) {
                $cr['course_name'] = $cr['name'] ?? $cr['academic_unit_name'] ?? $cr['program_name'] ?? $cr['title'] ?? 'Course';
            }
            if (empty($cr['overview'])) {
                $cr['overview'] = $cr['description'] ?? $cr['about_course'] ?? $cr['course_overview'] ?? '';
            }
            if (empty($cr['fees'])) {
                $cr['fees'] = $cr['fee_per_year'] ?? $cr['annual_fee'] ?? $cr['annual_fees'] ?? '';
            }
            if (empty($cr['total_fees'])) {
                $cr['total_fees'] = $cr['total_fee'] ?? $cr['course_fee'] ?? '';
            }
            if (empty($cr['entrance_exams'])) {
                $cr['entrance_exams'] = $cr['exams'] ?? $cr['entrance_exam'] ?? $cr['accepted_exams'] ?? '';
            }
            if (empty($cr['placement_details'])) {
                $cr['placement_details'] = $cr['placements'] ?? $cr['career_prospects'] ?? '';
            }

            $rawName = $cr['course_name'];
            $norm = strtolower(trim($rawName));
            $norm = preg_replace('/\b(b\.?tech|b\.?e\.?|bachelor of engineering)\b/i', 'bachelor of technology', $norm);
            $norm = preg_replace('/\b(m\.?tech|m\.?e\.?|master of engineering)\b/i', 'master of technology', $norm);
            $norm = preg_replace('/\b(b\.?sc|bsc)\b/i', 'bachelor of science', $norm);
            $norm = preg_replace('/\b(m\.?sc|msc)\b/i', 'master of science', $norm);
            $norm = preg_replace('/\b(bba)\b/i', 'bachelor of business administration', $norm);
            $norm = preg_replace('/\b(mba)\b/i', 'master of business administration', $norm);
            $norm = preg_replace('/\b(bca)\b/i', 'bachelor of computer applications', $norm);
            $norm = preg_replace('/\b(mca)\b/i', 'master of computer applications', $norm);
            $norm = preg_replace('/\b(b\.?com|bcom)\b/i', 'bachelor of commerce', $norm);
            $norm = preg_replace('/\b(m\.?com|mcom)\b/i', 'master of commerce', $norm);
            $norm = preg_replace('/\b(b\.?pharm|bpharm)\b/i', 'bachelor of pharmacy', $norm);
            $norm = preg_replace('/\b(m\.?pharm|mpharm)\b/i', 'master of pharmacy', $norm);
            $norm = preg_replace('/\b(b\.?a\.?|ba)\b/i', 'bachelor of arts', $norm);
            $norm = preg_replace('/\b(m\.?a\.?|ma)\b/i', 'master of arts', $norm);
            $norm = preg_replace('/\b(ph\.?d\.?|phd)\b/i', 'doctor of philosophy', $norm);
            $norm = preg_replace('/\b(cse|computer science & engineering|computer science and engineering)\b/i', 'computer science engineering', $norm);
            $norm = preg_replace('/\b(ece|electronics & communication engineering)\b/i', 'electronics communication engineering', $norm);
            $norm = preg_replace('/\b(me|mechanical engineering)\b/i', 'mechanical engineering', $norm);
            $norm = preg_replace('/\b(ce|civil engineering)\b/i', 'civil engineering', $norm);
            $norm = preg_replace('/\b(ai & ml|ai\/ml|artificial intelligence and machine learning)\b/i', 'artificial intelligence machine learning', $norm);
            $norm = preg_replace('/\b(in|of|and|&|the|for|with|a|an|program|course|degree|honors|hons)\b/i', '', $norm);
            $norm = preg_replace('/[^a-z0-9]/', '', $norm);

            $level = strtolower(trim($cr['program_level'] ?? ''));
            $campus = strtolower(trim($cr['campus_name'] ?? ''));
            $dept = strtolower(trim($cr['department_name'] ?? ''));

            $key = ($norm !== '' ? $norm : 'course') . '_' . $level . '_' . $campus . '_' . $dept;

            if (!isset($dedupedCourses[$key])) {
                $dedupedCourses[$key] = $cr;
            } else {
                $existing = &$dedupedCourses[$key];
                if (strlen($cr['course_name']) > strlen($existing['course_name'])) {
                    $existing['course_name'] = $cr['course_name'];
                }
                foreach (['fees', 'total_fees', 'admission_fee', 'annual_fee_range', 'entrance_exams', 'eligibility', 'admission_process', 'placement_details', 'overview', 'rating', 'roi', 'duration', 'mode', 'stream', 'discipline', 'specialization'] as $field) {
                    if (!empty($cr[$field]) && (empty($existing[$field]) || strlen((string)$cr[$field]) > strlen((string)$existing[$field]))) {
                        $existing[$field] = $cr[$field];
                    }
                }
                unset($existing);
            }
        }
        $data['courses'] = array_values($dedupedCourses);

        return $data;
    }

    /**
     * Fetch and clean text, meta images, structured academic navigation, and auto-crawl relevant linked sub-pages (fees, eligibility, programs, etc.)
     */
    protected function fetchUrlContent(string $url, string $mode = 'organisation', bool $autoCrawlSubPages = true): string
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                
                // Extract image candidates before stripping tags
                $imgCandidates = $this->extractImageCandidatesFromHtml($html, $url);
                $imgMeta = "";
                if (!empty($imgCandidates['logo'])) {
                    $imgMeta .= "\n- Detected Official Logo Candidate URL: " . $imgCandidates['logo'];
                }
                if (!empty($imgCandidates['cover'])) {
                    $imgMeta .= "\n- Detected Campus Cover Image Candidate URL: " . $imgCandidates['cover'];
                }

                // Extract structured academic navigation (departments, courses, academic hubs)
                $navData = $this->extractAcademicNavigationFromHtml($html, $url);
                // Remove scripts and styles for primary page
                $cleanHtml = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
                $cleanHtml = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleanHtml);
                $cleanHtml = preg_replace('/<\/(div|p|tr|li|h[1-6]|table|section|article|header|footer|nav)>/i', "\n", $cleanHtml);
                $cleanHtml = preg_replace('/<(br|hr)\s*\/?>/i', "\n", $cleanHtml);
                $cleanHtml = preg_replace('/<\/(td|th)>/i', " \t ", $cleanHtml);
                $cleanText = strip_tags($cleanHtml);
                $cleanText = preg_replace('/[ \t]+/', ' ', $cleanText);
                $cleanText = preg_replace('/\n\s*\n+/', "\n", $cleanText);
                $cleanText = $this->sanitizeUtf8($cleanText);
                
                $result = "";
                if (!empty($imgMeta)) {
                    $result .= "PAGE ASSET CANDIDATES:{$imgMeta}\n\n";
                }
                $result .= "=== MAIN BODY TEXT CONTENT ===\n" . mb_substr(trim($cleanText), 0, 70000, 'UTF-8');

                // Smart Automatic Linked Sub-page Crawler (Fast concurrent fetch with 5s timeout)
                if ($autoCrawlSubPages) {
                    // If this is a dedicated department list page or course list page where we already have navigation entries, skip crawling to stay fast
                    $hasRichDirectNav = ($mode === 'department' && count($navData['departments'] ?? []) >= 15)
                                     || ($mode === 'course' && count($navData['courses'] ?? []) >= 20);

                    if (!$hasRichDirectNav) {
                        $subPageCandidates = $this->discoverRelevantInternalPages($html, $url, $mode);
                        
                        if (!empty($subPageCandidates)) {
                            $maxSubpages = ($mode === 'organisation') ? 3 : (($mode === 'course') ? 3 : 2);
                            $subPageCandidates = array_slice($subPageCandidates, 0, $maxSubpages);

                            $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($subPageCandidates) {
                                $requests = [];
                                foreach ($subPageCandidates as $idx => $candidate) {
                                    $requests[$idx] = $pool->as((string)$idx)
                                        ->withoutVerifying()
                                        ->timeout(6)
                                        ->withHeaders([
                                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                                        ])
                                        ->get($candidate['url']);
                                }
                                return $requests;
                            });

                            foreach ($subPageCandidates as $idx => $candidate) {
                                $res = $responses[(string)$idx] ?? null;
                                if ($res instanceof \Illuminate\Http\Client\Response && $res->successful()) {
                                    $subHtml = $res->body();

                                    // Extract academic navigation from subpage too
                                    $subNav = $this->extractAcademicNavigationFromHtml($subHtml, $candidate['url']);
                                    if (!empty($subNav['departments'])) {
                                        foreach ($subNav['departments'] as $dName => $dUrl) {
                                            $navData['departments'][$dName] = $dUrl;
                                        }
                                    }
                                    if (!empty($subNav['courses'])) {
                                        foreach ($subNav['courses'] as $cName => $cUrl) {
                                            $navData['courses'][$cName] = $cUrl;
                                        }
                                    }

                                    $subClean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $subHtml);
                                    $subClean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $subClean);
                                    $subClean = strip_tags($subClean);
                                    $subClean = preg_replace('/\s+/', ' ', $subClean);
                                    $subClean = $this->sanitizeUtf8(trim($subClean));

                                    if (!empty($subClean)) {
                                        $result .= "\n\n=== AUTO-FETCHED INTERNAL LINKED PAGE: {$candidate['type']} - '{$candidate['title']}' (URL: {$candidate['url']}) ===\n" . mb_substr($subClean, 0, 15000, 'UTF-8');
                                    }
                                }
                            }
                        }
                    }
                }

                // Re-build structureMeta with all merged departments & courses
                $structureMeta = "";
                if (!empty($navData['departments'])) {
                    $structureMeta .= "\n=== DETECTED OFFICIAL DEPARTMENTS & SCHOOLS ON WEBSITE (" . count($navData['departments']) . ") ===\n";
                    $dIdx = 1;
                    foreach ($navData['departments'] as $dName => $dUrl) {
                        $structureMeta .= "{$dIdx}. {$dName} (URL: {$dUrl})\n";
                        $dIdx++;
                    }
                }
                if (!empty($navData['courses'])) {
                    $structureMeta .= "\n=== DETECTED OFFICIAL DEGREE COURSES & PROGRAMS ON WEBSITE (" . count($navData['courses']) . ") ===\n";
                    $cIdx = 1;
                    foreach ($navData['courses'] as $cName => $cUrl) {
                        $structureMeta .= "{$cIdx}. {$cName} (URL: {$cUrl})\n";
                        $cIdx++;
                    }
                }

                if (!empty($structureMeta) && !str_contains($result, '=== DETECTED OFFICIAL DEPARTMENTS')) {
                    $result = $structureMeta . "\n\n" . $result;
                }

                return $result;
            }
        } catch (\Exception $e) {
            Log::warning('Direct URL scrape failed, relying on Gemini search: ' . $e->getMessage());
        }

        return "Official Website URL: {$url}";
    }

    /**
     * Discover high-value linked internal sub-pages (e.g. Fees, Eligibility, Admissions, Courses, Placements)
     */
    protected function discoverRelevantInternalPages(string $html, string $baseUrl, string $mode = 'organisation'): array
    {
        $parsedBase = parse_url($baseUrl);
        $baseHost = strtolower($parsedBase['host'] ?? '');
        if (empty($baseHost)) {
            return [];
        }

        preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);
        $candidates = [];

        // Extract specific topic keywords from the URL slug (e.g. "aerospace", "mechanical")
        $urlPath = strtolower(parse_url($baseUrl, PHP_URL_PATH) ?? '');
        $urlPathParts = array_filter(explode('/', trim($urlPath, '/')));
        $genericKeywords = ['department', 'dept', 'course', 'courses', 'programs', 'faculty', 'school', 'academics', 'admissions', 'engineering', 'technology', 'studies', 'management', 'science', 'sciences'];
        $specificKeywords = [];

        foreach ($urlPathParts as $p) {
            $pClean = strtolower(trim(str_replace(['-', '_'], ' ', $p)));
            $words = explode(' ', $pClean);
            foreach ($words as $w) {
                $w = trim($w);
                if (strlen($w) >= 3 && !in_array($w, $genericKeywords)) {
                    $specificKeywords[] = $w;
                }
            }
        }
        $specificKeywords = array_unique($specificKeywords);

        foreach ($matches as $m) {
            $href = trim($m[1]);
            $rawText = trim(preg_replace('/\s+/', ' ', strip_tags($m[2])));

            if (empty($href) || str_starts_with($href, '#') || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }

            if (preg_match('/\.(jpg|jpeg|png|gif|svg|webp|mp4|mp3|zip|rar|exe|docx?)$/i', $href)) {
                continue;
            }

            $absUrl = $this->resolveAbsoluteUrl($href, $baseUrl);
            $parsedAbs = parse_url($absUrl);
            $absHost = strtolower($parsedAbs['host'] ?? '');

            // Only internal links (same host or same subdomain)
            if ($absHost !== $baseHost && !str_ends_with($absHost, '.' . $baseHost) && !str_ends_with($baseHost, '.' . $absHost)) {
                continue;
            }

            $cleanUrl = rtrim(explode('#', $absUrl)[0], '/');
            $cleanBaseUrl = rtrim(explode('#', $baseUrl)[0], '/');
            if (strtolower($cleanUrl) === strtolower($cleanBaseUrl)) {
                continue;
            }

            $combinedStr = strtolower($rawText . ' ' . $href);
            $score = 0;
            $type = 'General Reference';

            $hasSpecificMatch = false;
            if (!empty($specificKeywords)) {
                foreach ($specificKeywords as $sk) {
                    if (str_contains($combinedStr, $sk)) {
                        $hasSpecificMatch = true;
                        break;
                    }
                }
            }

            // --- PRIORITY 1: Direct Sub-page / Tab under the same URL path (e.g. /department/cse/faculty, /course/btech/fees) ---
            $isDirectChild = str_starts_with(strtolower($cleanUrl), strtolower($cleanBaseUrl) . '/');
            if ($isDirectChild) {
                if (preg_match('/(faculty|people|staff|teachers|professors|hod|members)/i', $combinedStr)) {
                    $score += 600;
                    $type = 'Faculty & Staff Members';
                } elseif (preg_match('/(course|program|degree|academic|offering|curriculum|syllabus)/i', $combinedStr)) {
                    $score += 580;
                    $type = 'Academic Programs & Syllabus';
                } elseif (preg_match('/(fee|tuition|cost|charge)/i', $combinedStr)) {
                    $score += 570;
                    $type = 'Fee Structure & Expenses';
                } elseif (preg_match('/(lab|facilit|infrastructure|research|project|center)/i', $combinedStr)) {
                    $score += 540;
                    $type = 'Laboratories & Facilities';
                } elseif (preg_match('/(about|overview|vision|mission|introduction|history)/i', $combinedStr)) {
                    $score += 520;
                    $type = 'Overview & Profile';
                } elseif (preg_match('/(admission|eligib|apply|criteria|intake)/i', $combinedStr)) {
                    $score += 500;
                    $type = 'Admissions & Eligibility';
                } elseif (preg_match('/(placement|career|recruiter)/i', $combinedStr)) {
                    $score += 480;
                    $type = 'Placements & Careers';
                } else {
                    $score += 450;
                    $type = 'Department / Course Section';
                }
            }

            // --- PRIORITY 2: Specific Program / Department Keywords Match ---
            if ($hasSpecificMatch) {
                if (preg_match('/(fee[s]?[\s_\-\/\.]|fee-structure|tuition|cost)/i', $combinedStr)) {
                    $score += 400;
                    $type = 'Specific Fee Structure';
                } elseif (preg_match('/(faculty|professors|teachers|people|staff|hod)/i', $combinedStr)) {
                    $score += 380;
                    $type = 'Faculty & Staff Directory';
                } elseif (preg_match('/(\/course\/|\/program\/|b\.?tech|m\.?tech|b\.?sc|m\.?sc|bba|mba|ph\.?d|bca|mca|b\.?com)/i', $combinedStr)) {
                    $score += 360;
                    $type = 'Specific Degree Program Page';
                } elseif (preg_match('/(syllabus|curriculum|scheme|course.*structure)/i', $combinedStr)) {
                    $score += 340;
                    $type = 'Syllabus & Course Structure';
                }
            }

            // --- PRIORITY 3: General Core Institutional Pages ---
            if ($score === 0) {
                if (preg_match('/(fee[s]?[\s_\-\/\.]|fee-structure|tuition|eligibility.*fee|fee.*eligibility|fee.*structure|cost.*study|annual.*fee)/i', $combinedStr)) {
                    $score += 260;
                    $type = 'Fee Structure & Tuition';
                } elseif (preg_match('/(\/faculty|\/people|\/staff-directory|faculty.*members)/i', $combinedStr)) {
                    $score += ($mode === 'department') ? 290 : 150;
                    $type = 'Faculty & Staff Directory';
                } elseif (preg_match('/(eligibility|admission[s]?|how.*to.*apply|admission.*process|entry.*requirement)/i', $combinedStr)) {
                    $score += 230;
                    $type = 'Eligibility & Admissions';
                } elseif (preg_match('/(\/course\/|\/program\/|programs.*offered|courses.*offered|curriculum|syllabus)/i', $combinedStr)) {
                    $score += ($mode === 'course') ? 280 : 180;
                    $type = 'Academic Program Details';
                } elseif (preg_match('/(scholarship|financial.*aid|fee.*concession|kaushal.*jyoti)/i', $combinedStr)) {
                    $score += 170;
                    $type = 'Scholarships & Financial Aid';
                } elseif (preg_match('/(placement[s]?|recruiter[s]?|highest.*package|average.*package)/i', $combinedStr)) {
                    $score += 160;
                    $type = 'Placements & Careers';
                } elseif (preg_match('/(hostel[s]?|infrastructure|campus.*facilit|sports|laborator)/i', $combinedStr)) {
                    $score += ($mode === 'campus') ? 280 : 140;
                    $type = 'Campus Facilities & Hostels';
                } elseif (preg_match('/(\/department[s]?|\/school[s]?|\/faculties|academic.*departments)/i', $combinedStr)) {
                    $score += ($mode === 'department' || $mode === 'organisation') ? 250 : 120;
                    $type = 'Academic Faculties & Departments';
                } elseif (preg_match('/(about.*us|overview|leadership|chancellor|accreditation|naac|nirf)/i', $combinedStr)) {
                    $score += 110;
                    $type = 'About & Accreditation';
                }
            }

            if ($score > 0) {
                if (!isset($candidates[$cleanUrl]) || $candidates[$cleanUrl]['score'] < $score) {
                    $candidates[$cleanUrl] = [
                        'url' => $cleanUrl,
                        'title' => $rawText ?: $type,
                        'type' => $type,
                        'score' => $score,
                    ];
                }
            }
        }

        // If candidate list is empty or sparse (e.g. Angular/SPA single-page homepages), probe root domain canonical directories
        if (count($candidates) < 2) {
            $parsed = parse_url($baseUrl);
            $host = strtolower($parsed['host'] ?? '');
            $parts = explode('.', $host);
            $rootDomain = (count($parts) >= 2) ? implode('.', array_slice($parts, -2)) : $host;

            if ($mode === 'department') {
                $probePaths = [
                    "https://www.{$rootDomain}/course-list.aspx",
                    "https://www.{$rootDomain}/department-list",
                    "https://www.{$rootDomain}/departments",
                    "https://www.{$rootDomain}/schools",
                    "https://www.{$rootDomain}/faculties",
                    "https://www.{$rootDomain}/institutes.aspx",
                    "https://www.{$rootDomain}/",
                    "https://{$host}/departments",
                ];
            } elseif ($mode === 'course') {
                $probePaths = [
                    "https://www.{$rootDomain}/course-list.aspx",
                    "https://www.{$rootDomain}/courses",
                    "https://www.{$rootDomain}/programs",
                    "https://www.{$rootDomain}/programe-list.aspx",
                    "https://{$host}/courses",
                ];
            } else {
                $probePaths = [
                    "https://www.{$rootDomain}/course-list.aspx",
                    "https://www.{$rootDomain}/department-list",
                    "https://www.{$rootDomain}/academics",
                    "https://www.{$rootDomain}/",
                ];
            }

            $probeResponses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($probePaths) {
                $reqs = [];
                foreach ($probePaths as $idx => $p) {
                    $reqs[$idx] = $pool->as((string)$idx)->withoutVerifying()->timeout(3.5)->get($p);
                }
                return $reqs;
            });

            foreach ($probePaths as $idx => $p) {
                $res = $probeResponses[(string)$idx] ?? null;
                if ($res instanceof \Illuminate\Http\Client\Response && $res->successful() && strlen($res->body()) > 2000) {
                    $cleanP = rtrim($p, '/');
                    $candidates[$cleanP] = [
                        'url' => $cleanP,
                        'title' => 'Official Academic Catalog Directory',
                        'type' => 'Academic Directory Catalog',
                        'score' => 950 - ($idx * 10),
                    ];
                }
            }
        }

        uasort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice(array_values($candidates), 0, 8);
    }

    /**
     * Helper to convert URL slug to human-readable department or academic title
     */
    protected function cleanDepartmentSlug(string $slug): string
    {
        $slug = trim($slug, '/');
        $parts = explode('/', $slug);
        $lastPart = end($parts);
        $lastPart = explode('?', $lastPart)[0];
        
        $title = ucwords(str_replace(['-', '_'], ' ', $lastPart));
        $title = preg_replace('/\bIt\b/', 'IT', $title);
        $title = preg_replace('/\bCse\b/', 'CSE', $title);
        $title = preg_replace('/\bEce\b/', 'ECE', $title);
        $title = preg_replace('/\bEee\b/', 'EEE', $title);
        $title = preg_replace('/\bAi\b/', 'AI', $title);
        $title = preg_replace('/\bMl\b/', 'ML', $title);
        return $title;
    }

    /**
     * Extract structured academic navigation (departments, courses, schools) from HTML
     */
    protected function extractAcademicNavigationFromHtml(string $html, string $baseUrl): array
    {
        $departmentsByUrl = [];
        $coursesByUrl = [];
        $academicHubs = [];

        $genericButtonPattern = '/^(know more|read more|click here|view more|view details|explore|details|learn more|visit|apply now|enquire now|more|link|website|check|view|browse)$/i';
        $nonAcademicPattern = '/^(home|contact|about|privacy|terms|login|register|portal|gallery|event|news|career|notice|placement|alumni|press|iqac|naac|nirf|grievance|fee|apply|admission|payment|download|blog|faqs?|help|sitemap|search)$/i';
        $socialPattern = '/(linkedin\.com|facebook\.com|twitter\.com|x\.com|instagram\.com|youtube\.com|pinterest\.com|whatsapp\.com)/i';

        // 1. First: Parse Table Rows (<tr>...<td>Department Name</td>...<td><a href="...">...</a></td>...</tr>)
        if (preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/is', $html, $trMatches)) {
            foreach ($trMatches[1] as $tr) {
                preg_match_all('/<td\b[^>]*>(.*?)<\/td>/is', $tr, $tdMatches);
                if (!empty($tdMatches[1])) {
                    $rowTexts = [];
                    $rowLinks = [];
                    foreach ($tdMatches[1] as $td) {
                        if (preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $td, $aMatches, PREG_SET_ORDER)) {
                            foreach ($aMatches as $am) {
                                $rowLinks[] = [
                                    'href' => trim($am[1]),
                                    'text' => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($am[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')))
                                ];
                            }
                        }
                        $plainCell = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($td), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                        if (!empty($plainCell) && !preg_match($genericButtonPattern, $plainCell)) {
                            $rowTexts[] = $plainCell;
                        }
                    }

                    if (!empty($rowTexts)) {
                        $possibleDeptName = implode(' - ', $rowTexts);
                        if (preg_match('/^(department of|school of|faculty of|centre for|center for|division of)/i', $possibleDeptName) ||
                            preg_match('/(engineering|technology|science|humanities|management|commerce|design|arts|law|medical|health|pharmacy|nursing)/i', $possibleDeptName)) {
                            
                            $linkUrl = !empty($rowLinks) ? $this->resolveAbsoluteUrl($rowLinks[0]['href'], $baseUrl) : $baseUrl;
                            if (!preg_match($socialPattern, $linkUrl)) {
                                $departmentsByUrl[$linkUrl] = $possibleDeptName;
                            }
                        }
                    }
                }
            }
        }

        // 2. Parse Select Dropdowns (Discipline, Department, Faculty, School options)
        if (preg_match_all('/<select\b[^>]*(?:name|id|class)=["\'][^"\']*(?:discipline|department|faculty|school|institute|branch|academic)[^"\']*["\'][^>]*>(.*?)<\/select>/is', $html, $selectMatches)) {
            foreach ($selectMatches[1] as $optionsBlock) {
                if (preg_match_all('/<option\s+[^>]*value=["\']([^"\']*)["\'][^>]*>(.*?)<\/option>/is', $optionsBlock, $optMatches, PREG_SET_ORDER)) {
                    foreach ($optMatches as $om) {
                        $val = trim($om[1]);
                        $label = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($om[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                        if (!empty($label) && $val !== '0' && $val !== '' && !preg_match('/^(select|all|choose|--|program type|program level)/i', $label) && strlen($label) >= 3 && strlen($label) <= 100) {
                            $optUrl = $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . 'discipline=' . urlencode($label);
                            if (!isset($departmentsByUrl[$optUrl])) {
                                $departmentsByUrl[$optUrl] = $label;
                            }
                        }
                    }
                }
            }
        }

        // 3. Third: Parse all <a> tags (cards, list items, navigation links, query parameters)
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);

        $parsedBasePath = strtolower(rtrim(parse_url($baseUrl, PHP_URL_PATH) ?? '', '/'));

        foreach ($matches as $m) {
            $href = trim($m[1]);
            $rawText = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

            if (empty($href) || str_starts_with($href, '#') || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }

            if (preg_match('/\.(jpg|jpeg|png|gif|svg|webp|pdf|zip|mp4|exe)$/i', $href)) {
                continue;
            }

            if (preg_match($socialPattern, $href)) {
                continue;
            }

            $absUrl = $this->resolveAbsoluteUrl($href, $baseUrl);
            $parsedAbsPath = strtolower(rtrim(parse_url($absUrl, PHP_URL_PATH) ?? '', '/'));

            // Check if link contains department or discipline query parameters (e.g. fd=Aerospace, dept=Biotech, discipline=Law)
            if (preg_match('/(?:[?&])(?:fd|dept|department|discipline|faculty)=([^&"\'\s]+)/i', $href, $paramMatch)) {
                $paramVal = urldecode($paramMatch[1]);
                $deptNameCandidate = (!empty($rawText) && !preg_match($genericButtonPattern, $rawText) && !preg_match($nonAcademicPattern, $rawText) && strlen($rawText) >= 3) ? $rawText : $paramVal;
                if (!empty($deptNameCandidate) && !in_array(strtolower($deptNameCandidate), ['all', 'select', '0', 'all programs', 'program type']) && strlen($deptNameCandidate) >= 3 && strlen($deptNameCandidate) <= 100) {
                    if (!isset($departmentsByUrl[$absUrl])) {
                        $departmentsByUrl[$absUrl] = $deptNameCandidate;
                    }
                    continue;
                }
            }

            // Ignore links pointing to department list / directory overview pages itself
            if ($parsedAbsPath === $parsedBasePath || preg_match('/(\/department-list|\/departments-list|\/all-departments|\/faculty-list)$/i', $parsedAbsPath)) {
                continue;
            }

            $isGenericButton = preg_match($genericButtonPattern, $rawText);

            // Check if URL indicates a Department / School / Faculty
            if (preg_match('/(\/department[s]?\/|\/school[s]?\/|\/faculty\/|\/dept[s]?\/|\/centres?\/|\/centers?\/)([a-z0-9\-_]+)/i', $parsedAbsPath, $slugMatch)) {
                $subSlug = $slugMatch[2];
                if (!in_array(strtolower($subSlug), ['list', 'all', 'index', 'home', 'overview', 'about', 'department-list'])) {
                    if (!isset($departmentsByUrl[$absUrl])) {
                        $deptTitle = '';
                        if (!$isGenericButton && strlen($rawText) >= 3 && !preg_match($nonAcademicPattern, $rawText) && !preg_match('/(department.*\(a to z\)|placement.*department)/i', $rawText)) {
                            $deptTitle = $rawText;
                        } else {
                            $deptTitle = $this->cleanDepartmentSlug($subSlug);
                        }

                        if (!empty($deptTitle)) {
                            $departmentsByUrl[$absUrl] = $deptTitle;
                        }
                    }
                    continue;
                }
            }

            if ($isGenericButton || empty($rawText) || strlen($rawText) < 2 || strlen($rawText) > 120) {
                continue;
            }

            if (preg_match($nonAcademicPattern, $rawText) || preg_match('/^(departments|department \(a to z\)|training and placement)/i', $rawText)) {
                continue;
            }

            if (preg_match('/^(department of|school of|faculty of|centre for|center for|division of|institute of)/i', $rawText) ||
                preg_match('/(school of [a-z\s]+|institute of [a-z\s]+|faculty of [a-z\s]+)/i', $rawText)) {
                if (!isset($departmentsByUrl[$absUrl])) {
                    $departmentsByUrl[$absUrl] = $rawText;
                }
            } elseif (preg_match('/^(b\.tech|m\.tech|b\.sc|m\.sc|bca|mca|bba|mba|b\.com|m\.com|b\.a\.|m\.a\.|b\.pharm|m\.pharm|b\.des|m\.des|ph\.d|diploma|bpt|mpt|gnm|bmlt|anm|integrated)/i', $rawText) ||
                preg_match('/(\/course\/|\/program\/|\/degree\/)/i', $href)) {
                $coursesByUrl[$absUrl] = $rawText;
            }
        }

        // Convert to name => URL map
        $departments = [];
        foreach ($departmentsByUrl as $url => $name) {
            $departments[$name] = $url;
        }

        $courses = [];
        foreach ($coursesByUrl as $url => $name) {
            $courses[$name] = $url;
        }

        return [
            'departments' => $departments,
            'courses' => $courses,
            'academic_hubs' => $academicHubs,
        ];
    }

    /**
     * Extract logo and cover image candidates from raw HTML
     */
    protected function extractImageCandidatesFromHtml(string $html, string $baseUrl): array
    {
        $images = [
            'logo' => null,
            'cover' => null,
        ];

        // 1. OpenGraph & Twitter Image
        if (preg_match('/<meta\s+(?:property|name)=["\'](?:og:image|twitter:image)["\']\s+content=["\']([^"\']+)["\']/i', $html, $m)) {
            $images['cover'] = $this->resolveAbsoluteUrl($m[1], $baseUrl);
        }

        // 2. Logo in img tags
        if (preg_match('/<img[^>]+(?:class|id|alt)=["\'][^"\']*(?:logo|brand|crest)[^"\']*["\'][^>]+src=["\']([^"\']+)["\']/i', $html, $m) ||
            preg_match('/<img[^>]+src=["\']([^"\']*(?:logo|brand|crest)[^"\']*)["\']/i', $html, $m)) {
            $images['logo'] = $this->resolveAbsoluteUrl($m[1], $baseUrl);
        } elseif (preg_match('/<link\s+rel=["\'](?:icon|shortcut icon|apple-touch-icon)["\']\s+href=["\']([^"\']+)["\']/i', $html, $m)) {
            $images['logo'] = $this->resolveAbsoluteUrl($m[1], $baseUrl);
        }

        // 3. Wikipedia Infobox Image Check
        if (str_contains($baseUrl, 'wikipedia.org')) {
            if (preg_match('/<table[^>]*class=["\'][^"\']*infobox[^"\']*["\'][^>]*>.*?<img[^>]+src=["\']([^"\']+)["\']/is', $html, $m)) {
                $images['logo'] = $this->resolveAbsoluteUrl($m[1], $baseUrl);
            }
        }

        return $images;
    }

    /**
     * Resolve relative URL to absolute URL
     */
    protected function resolveAbsoluteUrl(string $url, string $baseUrl): string
    {
        $url = trim($url);
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        if (str_starts_with($url, '/')) {
            return "{$scheme}://{$host}{$url}";
        }
        return "{$scheme}://{$host}/" . ltrim($url, '/');
    }

    /**
     * Build structured extraction prompt tailored to the selected Entity Mode
     */
    protected function buildPrompt(
        string $url,
        string $content,
        string $orgTypeTitle,
        int $orgTypeId,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null,
        string $mode = 'organisation',
        ?\App\Models\Campus $targetCampus = null,
        ?\App\Models\Department $targetDepartment = null,
        bool $searchGoogle = true
    ): string
    {
        if ($mode === 'campus') {
            return $this->buildCampusOnlyPrompt($url, $content, $referenceUrls, $targetOrg, $searchGoogle);
        }

        if ($mode === 'department') {
            return $this->buildDepartmentOnlyPrompt($url, $content, $referenceUrls, $targetOrg, $targetCampus, $searchGoogle);
        }

        if ($mode === 'course') {
            return $this->buildCourseOnlyPrompt($url, $content, $referenceUrls, $targetOrg, $targetCampus, $targetDepartment, $searchGoogle);
        }

        if ($targetOrg) {
            return $this->buildCampusesAndCoursesPrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg, $searchGoogle);
        }

        $titleLower = strtolower($orgTypeTitle);

        if (str_contains($titleLower, 'school') || $orgTypeId === 4) {
            return $this->buildSchoolPrompt($url, $content, $orgTypeTitle, $referenceUrls, $searchGoogle);
        }

        if (str_contains($titleLower, 'exam') || str_contains($titleLower, 'conducting') || $orgTypeId === 5) {
            return $this->buildExamConductingBodyPrompt($url, $content, $orgTypeTitle, $referenceUrls, $searchGoogle);
        }

        if (str_contains($titleLower, 'counselling') || $orgTypeId === 6) {
            return $this->buildCounsellingBodyPrompt($url, $content, $orgTypeTitle, $referenceUrls, $searchGoogle);
        }

        if (str_contains($titleLower, 'regulatory') || str_contains($titleLower, 'agency') || in_array($orgTypeId, [7, 8])) {
            return $this->buildRegulatoryBodyPrompt($url, $content, $orgTypeTitle, $referenceUrls, $searchGoogle);
        }

        if (str_contains($titleLower, 'institute') || $orgTypeId === 3) {
            return $this->buildInstitutePrompt($url, $content, $orgTypeTitle, $referenceUrls, $searchGoogle);
        }

        // Default to University / College (Types 1, 2)
        return $this->buildOrganisationOnlyPrompt($url, $content, $orgTypeTitle, $orgTypeId, $referenceUrls, $searchGoogle);
    }

    /**
     * Dedicated Prompt for Campus Only Extraction (Single Campus based on provided Campus URL)
     */
    public function buildCampusOnlyPrompt(
        string $url,
        string $content,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null,
        bool $searchGoogle = true
    ): string {
        $orgName = $targetOrg ? $targetOrg->name : 'the Target Organisation';
        $orgTypeTitle = $targetOrg && $targetOrg->organisationType ? $targetOrg->organisationType->title : 'University / College / School';
        $orgShort = $targetOrg ? $targetOrg->short_name : '';
        $orgSite = $targetOrg ? $targetOrg->official_website : $url;
        $orgId = $targetOrg ? $targetOrg->id : 0;
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);
        $cleanContent = !empty(trim($content)) ? "\nPRIMARY WEBSITE CONTENT & REFERENCE PREVIEW:\n" . substr(trim($content), 0, 12000) : "";

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
2. SEARCH GROUNDING & ACCURACY:
   - Search the official website, Google Maps, NIRF/school inspection reports, and institutional documents for accurate physical addresses, campus acreage, transport hubs, hostel capacities, and sports facilities for this specific campus.
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
2. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Extract information STRICTLY AND EXCLUSIVELY from the provided primary campus URL content and reference sources.
   - DO NOT search Google or fabricate data. If any field (e.g. exact acreage, pin code, or contact numbers) is not present in the provided sources, leave it null or empty.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert institutional infrastructure, geography, and campus research AI agent.
Your EXCLUSIVE MISSION is to extract exactly ONE specific physical CAMPUS / BRANCH LOCATION for the institution "{$orgName}" (Organisation Type: {$orgTypeTitle}) corresponding to the provided campus URL/webpage.
DO NOT extract multiple campuses or all branch centres of the institution. Focus strictly and exclusively on creating this single specific campus / branch.
(Note: If this institution is a School, extract this specific School branch / campus location).

TARGET INSTITUTION: {$orgName}
ORGANISATION TYPE: {$orgTypeTitle}
SHORT NAME: {$orgShort}
OFFICIAL SITE: {$orgSite}
CAMPUS SPECIFIC URL: {$url}{$refUrlsText}
{$cleanContent}

=== CRITICAL RESEARCH & EXTRACTION MANDATE ===
1. SINGLE CAMPUS EXTRACTION:
   - Extract exactly ONE campus object in the "campuses" array matching the specific campus location given in the URL.
   - Accurately determine the campus name (e.g. "Main Campus", "South Campus", "Kolkata Campus", "City Campus", etc.), address, city, state, country, pincode, facilities, and contact details.

{$searchInstructions}

3. RETURN ONLY VALID JSON MATCHING THIS EXACT STRUCTURE (Single campus in array):
{
  "target_organisation_id": {$orgId},
  "target_organisation_name": "{$orgName}",
  "mode": "campus",
  "campuses": [
    {
      "campus_name": "Main Campus",
      "campus_type": "Main",
      "established_year": 2005,
      "city": "Bengaluru",
      "state": "Karnataka",
      "country": "India",
      "pincode": "562106",
      "full_address": "Chikkahagade Cross, Chandapura - Anekal Main Road, Bengaluru, Karnataka 562106",
      "google_map_url": "https://maps.google.com/?q=Alliance+University+Bangalore",
      "nearest_transport_hub": "Chandapura Railway Station (3 km) / Electronic City Metro (10 km)",
      "campus_area_acres": 55.5,
      "academic_blocks_count": 6,
      "classrooms_count": 80,
      "smart_classrooms": true,
      "laboratories_count": 35,
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
        "Tennis Court",
        "Badminton Arena"
      ],
      "transport_available": true,
      "cctv_coverage": true,
      "fire_safety_certified": true,
      "campus_email": "campus@example.edu.in",
      "campus_contact_numbers": [
        "+91 80 4619 9000",
        "+91 80 4619 9001"
      ]
    }
  ]
}
PROMPT;
    }

    /**
     * Dedicated Prompt for Department Only Extraction
     */
    public function buildDepartmentOnlyPrompt(
        string $url,
        string $content,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null,
        ?\App\Models\Campus $targetCampus = null,
        bool $searchGoogle = true
    ): string {
        $orgName = $targetOrg ? $targetOrg->name : 'the Target Organisation';
        $orgTypeTitle = $targetOrg && $targetOrg->organisationType ? $targetOrg->organisationType->title : 'University / College / School';
        $orgId = $targetOrg ? $targetOrg->id : 0;
        $campusName = $targetCampus ? $targetCampus->campus_name : 'Main / Selected Campus';
        $campusId = $targetCampus ? $targetCampus->id : null;
        $campusIdJson = $campusId ? json_encode($campusId) : 'null';
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);
        $cleanContent = !empty(trim($content)) ? "\nPRIMARY WEBSITE CONTENT & STRUCTURED DIRECTORY:\n" . mb_substr(trim($content), 0, 80000, 'UTF-8') : "";

        // Check for structured detected departments
        $detectedDeptText = "";
        if (preg_match('/===\s*DETECTED OFFICIAL DEPARTMENTS & SCHOOLS ON WEBSITE \((\d+)\)\s*===\s*(.*?)(?=\n===|\nPRIMARY|\nPAGE ASSET|$)/s', $content, $m)) {
            $count = (int)$m[1];
            $deptList = trim($m[2]);
            $detectedDeptText = <<<DET_DEPT
MANDATORY TARGET DEPARTMENTS TO POPULATE ({$count} Departments detected directly from official website navigation):
{$deptList}

STRICT EXHAUSTIVE MANDATE:
- You MUST create an entry in the "departments" array for EVERY SINGLE ONE of the {$count} departments in the list above (including Aerospace, Anthropology, Biotechnology, Architecture, Artificial Intelligence, Forensic Sciences, Psychology, Law, etc.).
- DO NOT SKIP, SAMPLE, MERGE, OR STOP AT 20. If {$count} departments are listed, exactly {$count} distinct departmental objects must be returned in the JSON array.
DET_DEPT;
        } else {
            $detectedDeptText = <<<DET_DEPT
STRICT EXHAUSTIVE MANDATE (EXTRACT ALL 50 TO 120+ DEPARTMENTS/SCHOOLS):
- Large universities and institutions like "{$orgName}" have 50 to 120+ distinct academic departments, institutes, and schools across disciplines (Engineering & Technology, Management, Biotechnology, Law, Applied Sciences, Pharmacy, Architecture, Communication, Arts & Humanities, Commerce, Psychology, Forensic Sciences, Hospitality, Fashion, Nursing, Education, etc.).
- You MUST exhaustively extract and return EVERY SINGLE active academic department, institute, and faculty.
- DO NOT limit the output to 10 or 20 items. Output every single department offering programs across the institution.
DET_DEPT;
        }

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
2. DEEP WEB SEARCH & COMPLETE INSTITUTIONAL AUDIT:
   - Actively search Google Search Grounding across official institutional directories, "Institutes and Schools of {$orgName}", academic faculties, admission portals, Shiksha, Collegedunia, and NIRF disclosures.
   - Discover and extract ALL departments and schools across the entire institution.
   - Return every single discovered department in the "departments" array without skipping or truncating.
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
2. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Extract information STRICTLY AND EXCLUSIVELY from the provided primary department URL content and reference sources.
   - DO NOT search Google or fabricate data. If any field is not present in the provided sources, leave it null or empty.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert academic faculties, colleges, and university departmental research AI agent.
Your EXCLUSIVE MISSION is to research and extract ALL academic DEPARTMENTS, SCHOOLS, WINGS, and FACULTIES for the institution "{$orgName}" (Organisation Type: {$orgTypeTitle}, Campus: {$campusName}).
DO NOT extract general organisation profiles, campuses, or individual courses. ONLY extract Departments.
(Note: If this institution is a School, extract academic departments / wings such as Primary Wing, Middle Wing, Senior Secondary Wing, Science Department, Performing Arts, Sports, Humanities, etc.).

TARGET INSTITUTION: {$orgName}
ORGANISATION TYPE: {$orgTypeTitle}
TARGET CAMPUS: {$campusName}
PRIMARY URL: {$url}{$refUrlsText}
{$cleanContent}

=== CRITICAL EXHAUSTIVE & DEDUPLICATION MANDATE ===
1. EXHAUSTIVE EXTRACTION MANDATE:
{$detectedDeptText}

2. STRICT DEDUPLICATION RULE:
   - NEVER return duplicate or alias departments. Every item in the "departments" array MUST represent a unique academic department/school/wing.
   - If the sources refer to the same department under multiple variations (e.g. "Department of Physics" and "Physics"), output ONLY ONE canonical entry ("Department of Physics").
   - Merge all discovered faculty counts, lab numbers, and specializations from all sub-pages into that ONE canonical department object.

{$searchInstructions}

3. FAST & CONCISE WRITING (TO ENSURE RAPID GENERATION WITHOUT SERVER TIMEOUTS):
   - `about_department`: Write 1 crisp, high-impact sentence (e.g. "The department provides undergraduate and postgraduate education emphasizing research labs and practical industry training."). Keeping this to 1 sentence guarantees fast completion and avoids server timeouts.

4. RETURN ONLY VALID JSON MATCHING THIS EXACT STRUCTURE:
{
  "target_organisation_id": {$orgId},
  "target_organisation_name": "{$orgName}",
  "target_campus_id": {$campusIdJson},
  "target_campus_name": "{$campusName}",
  "mode": "department",
  "departments": [
    {
      "department_name": "Department of Computer Science and Engineering",
      "department_code": "CSE",
      "department_type": "Academic",
      "established_year": 2005,
      "discipline_area": "Engineering & Technology",
      "specializations_supported": [
        "Artificial Intelligence & Machine Learning",
        "Data Science",
        "Cyber Security",
        "Cloud Computing"
      ],
      "education_levels_supported": [
        "Undergraduate",
        "Postgraduate",
        "Doctoral (Ph.D)"
      ],
      "faculty_count": 32,
      "about_department": "The Department of Computer Science and Engineering offers industry-aligned academic programs emphasizing Artificial Intelligence, Cloud Computing, Cyber Security, and Software Engineering with state-of-the-art laboratory infrastructure.",
      "department_labs_count": 8,
      "specialized_labs_available": true,
      "phd_supervision_available": true,
      "industry_collaboration_supported": true
    }
  ]
}
PROMPT;
    }

    /**
     * Dedicated Prompt for Course Only Extraction
     */
    public function buildCourseOnlyPrompt(
        string $url,
        string $content,
        array $referenceUrls = [],
        ?\App\Models\Organisation $targetOrg = null,
        ?\App\Models\Campus $targetCampus = null,
        ?\App\Models\Department $targetDepartment = null,
        bool $searchGoogle = true
    ): string {
        $orgName = $targetOrg ? $targetOrg->name : 'the Target Organisation';
        $orgTypeTitle = $targetOrg && $targetOrg->organisationType ? $targetOrg->organisationType->title : 'University / College / School';
        $orgId = $targetOrg ? $targetOrg->id : 0;
        $campusName = $targetCampus ? $targetCampus->campus_name : 'Main / Selected Campus';
        $campusId = $targetCampus ? $targetCampus->id : null;
        $campusIdJson = $campusId ? json_encode($campusId) : 'null';
        $deptName = $targetDepartment ? $targetDepartment->department_name : 'All Departments / Selected Department';
        $deptId = $targetDepartment ? $targetDepartment->id : null;
        $deptIdJson = $deptId ? json_encode($deptId) : 'null';

        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);
        $cleanContent = !empty(trim($content)) ? "\nPRIMARY WEBSITE CONTENT & STRUCTURED DIRECTORY:\n" . substr(trim($content), 0, 60000) : "";

        // Check for structured detected courses
        $detectedCourseText = "";
        if (preg_match('/===\s*DETECTED OFFICIAL DEGREE COURSES & PROGRAMS ON WEBSITE \((\d+)\)\s*===\s*(.*?)(?=\n===|\nPRIMARY|\nPAGE ASSET|$)/s', $content, $m)) {
            $count = (int)$m[1];
            $courseList = trim(substr($m[2], 0, 5000));
            $detectedCourseText = <<<DET_COURSE
DETECTED DEGREE COURSES FROM OFFICIAL WEBSITE NAVIGATION ({$count} Programs detected):
{$courseList}

STRICT EXHAUSTIVE MULTI-DEGREE EXTRACTION RULE:
- Inspect the webpage's "Programs Offered", "Courses Offered", or "Academics" section very carefully.
- If this is a Department/Wing page (e.g. {$deptName}), you MUST extract EVERY degree program/curriculum offered by this department across all levels:
  * Undergraduate / Senior Secondary (e.g. B.Tech / B.Sc / BBA / Class 11-12 Science/Commerce)
  * Postgraduate / Middle & Secondary (e.g. M.Tech / M.Sc / MBA / Classes 6-10)
  * Doctoral / Primary / Pre-Primary programs
  * Diploma / Integrated programs / IB / Cambridge IGCSE
- NEVER omit programs. Return one distinct object in the `courses` array for EVERY single degree/program offered.
- DO NOT sample or merge programs.
DET_COURSE;
        } else {
            $detectedCourseText = <<<DET_COURSE
STRICT EXHAUSTIVE MULTI-DEGREE EXTRACTION RULE:
- Inspect the webpage's "Programs Offered", "Courses Offered", or "Academics" section very carefully.
- Extract ALL programs and courses offered by "{$orgName}" (Campus: {$campusName}, Department: {$deptName}) across all levels:
  * For Higher Ed: Undergraduate, Postgraduate, Doctoral (Ph.D), Diploma / Certificate
  * For Schools: Senior Secondary (Science, Commerce, Humanities), Secondary School, Middle School, Primary Wing, Kindergarten / IB DP / IGCSE
- Return one distinct object in the `courses` array for EVERY single program/course offered.
DET_COURSE;
        }

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
2. SEARCH GROUNDING & ACCURACY:
   - Actively use Google Search Grounding to verify full course catalogues, official syllabus brochures, fee tables, admission portals, and institutional prospectus PDFs.
   - If specific fees, eligibility, or entrance exams are not on the primary page, find verified data from the official admission prospectus or reliable institutional portals.
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
2. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Extract all course information, fees, entrance exams, eligibility, and placements directly from the provided PRIMARY WEBSITE CONTENT and AUTO-FETCHED LINKED PAGES (including Fee Structure tables and Degree Program pages).
   - Carefully compute and populate `fees`, `total_fees`, `admission_fee`, `annual_fee_range`, `entrance_exams`, and `placement_details` from the provided tables and pages.
   - Do NOT leave fee fields or entrance exam fields empty when fee structure tables or details are present in the provided text.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert educational admissions, academic programs, and curriculum research AI agent.
Your EXCLUSIVE MISSION is to perform an in-depth, exhaustive extraction of ALL COURSES, PROGRAMS, SPECIALIZATIONS, and CURRICULA offered by "{$orgName}" (Organisation Type: {$orgTypeTitle}, Campus: {$campusName}, Department: {$deptName}).
DO NOT extract organisation overviews, campuses, or department profiles. ONLY extract Courses and Programs.
(Note: If this institution is a School, extract school academic programs, grades/classes, streams, and curriculum offerings such as CBSE Class 11-12 Science/Commerce, IB Diploma Programme, IGCSE, Secondary School).

TARGET INSTITUTION: {$orgName}
ORGANISATION TYPE: {$orgTypeTitle}
TARGET CAMPUS: {$campusName}
TARGET DEPARTMENT: {$deptName}
PRIMARY URL: {$url}{$refUrlsText}
{$cleanContent}

=== CRITICAL EXHAUSTIVE & DEDUPLICATION MANDATE ===
1. EXHAUSTIVE COVERAGE - EXTRACT ALL PROGRAM OFFERINGS:
{$detectedCourseText}

2. STRICT DEDUPLICATION RULE:
   - NEVER return duplicate courses or duplicate degree programs. Every item in the "courses" array MUST be unique.
   - DO NOT create multiple entries for the same degree under different names/abbreviations (e.g., do NOT output both "B.Tech CSE" and "Bachelor of Technology in Computer Science and Engineering" - output only one canonical, fully populated object).
   - If a course has multiple specializations, output distinct named specialization programs (e.g. "B.Tech CSE (AI & ML)" and "B.Tech CSE (Cyber Security)") or consolidate them cleanly under the canonical program.

3. FEE STRUCTURE & FINANCIAL EXTRACTION MANDATE:
   - Carefully inspect all AUTO-FETCHED INTERNAL LINKED PAGES and AUTO-FETCHED DEDICATED FEE STRUCTURE PAGES provided in the scraped context above.
   - For every course extracted, you MUST populate:
     * `fees`: Annual or Per-Year Tuition/Academic Fee (e.g. "₹ 2,20,000 / Year" or "₹ 1,40,700 / Semester (₹ 2,81,400 / Year)").
     * `total_fees`: Numerical integer total course fee for the complete duration (e.g. 880000 or 1125600).
     * `admission_fee`: One-time admission/enrollment/caution deposit fee (e.g. "25000" or "10000").
     * `annual_fee_range`: Text range representation (e.g. "₹ 2.5 - 3.0 Lakhs / Year").
   - If fees vary with 12th/JEE scholarships, use the standard baseline 1st year fee or annual fee before scholarship.
   - Do NOT leave fee fields blank when fee tables or fee links are present in the provided webpage text or reference sources.

{$searchInstructions}

4. CONCISE, PROFESSIONAL WRITING:
   - `overview`: Write a concise 2-3 sentence overview explaining core curriculum focus, lab tools, and career pathways.
   - `eligibility`: State entry requirements (e.g. "10+2 with min 60% in PCM/CS").
   - `admission_process`: Step-by-step admission route.
   - `placement_details`: Key career stats and top recruiters.

5. RETURN ONLY VALID JSON MATCHING THIS EXACT STRUCTURE:
{
  "target_organisation_id": {$orgId},
  "target_organisation_name": "{$orgName}",
  "target_campus_id": {$campusIdJson},
  "target_campus_name": "{$campusName}",
  "target_department_id": {$deptIdJson},
  "target_department_name": "{$deptName}",
  "mode": "course",
  "courses": [
    {
      "course_name": "Bachelor of Technology in Computer Science and Engineering (Artificial Intelligence & Machine Learning)",
      "short_name": "B.Tech CSE (AI & ML)",
      "program_level": "Undergraduate",
      "stream": "Engineering",
      "discipline": "Computer Science & Engineering",
      "specialization": "Artificial Intelligence & Machine Learning",
      "duration": "4 Years",
      "mode": "Regular",
      "fees": "₹ 2,20,000 / Year",
      "total_fees": 880000,
      "annual_fee_range": "₹ 2.0 - 2.5 Lakhs / Year",
      "admission_fee": "25000",
      "rating": "4.6",
      "roi": "High",
      "entrance_exams": "JEE Main, CUET, State CET, Institution Entrance Test",
      "eligibility": "Passed 10+2 examination with Physics and Mathematics as compulsory subjects along with Chemistry/CS with at least 60% marks.",
      "admission_process": "Qualify in JEE Main / State CET followed by centralized counseling and document verification.",
      "placement_details": "Average package: 8.2 LPA, Highest package: 35 LPA. Top recruiters: Amazon, Microsoft, Infosys, TCS, Adobe, Wipro.",
      "installment_available": true,
      "scholarship_available": true,
      "refund_policy_available": true,
      "provisional_admission": true,
      "overview": "The B.Tech in Computer Science and Engineering with specialization in AI & ML is a 4-year undergraduate program designed to equip students with deep knowledge of machine learning algorithms, deep learning, neural networks, natural language processing, and big data technologies. Students gain practical experience through dedicated AI research labs and capstone industry projects."
    }
  ]
}
PROMPT;
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
        ?\App\Models\Organisation $targetOrg = null,
        bool $searchGoogle = true
    ): string {
        $orgName = $targetOrg ? $targetOrg->name : 'the Selected Institution';
        $orgShort = $targetOrg ? $targetOrg->short_name : '';
        $orgSite = $targetOrg ? $targetOrg->official_website : $url;
        $orgId = $targetOrg ? $targetOrg->id : 0;

        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);
        $cleanContent = !empty(trim($content)) ? "\nPRIMARY WEBSITE CONTENT & REFERENCE PREVIEW:\n" . substr(trim($content), 0, 10000) : "";

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
1. DEEP WEB SEARCH & FACT VERIFICATION:
   - The institution "{$orgName}" already exists in our database. DO NOT focus on basic organisation identity fields.
   - Carefully inspect the PRIMARY URL and any ADDITIONAL REFERENCE URLS provided above.
   - If courses, fee structures, eligibility criteria, campus details, or department information are missing or incomplete on the provided URLs, actively search Google Search Grounding, official admission portals, academic catalogues, Shiksha, Collegedunia, Wikipedia, and brochure PDFs for "{$orgName}" to discover all available physical campuses, academic faculties, and degree programs.
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
1. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - The institution "{$orgName}" already exists in our database. DO NOT focus on basic organisation identity fields.
   - Extract information STRICTLY AND EXCLUSIVELY from the provided primary URL content and reference sources.
   - DO NOT search Google or fabricate data. If any information is missing from the provided sources, leave it null or empty.
STRICT_URL_INSTRUCTIONS;

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
{$searchInstructions}
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
        array $referenceUrls = [],
        bool $searchGoogle = true
    ): string {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);
        $cleanContent = !empty(trim($content)) ? "\nPRIMARY WEBSITE CONTENT & REFERENCE PREVIEW:\n" . substr(trim($content), 0, 12000) : "";

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
1. DEEP WEB SEARCH & FACT VERIFICATION (GOOGLE GROUNDING):
   - Carefully inspect the PRIMARY URL and any ADDITIONAL REFERENCE URLS (e.g. Wikipedia, NAAC, NIRF) provided above.
   - If ANY institutional details (e.g. Established year, Ownership Type, University Type, University Category, Levels Offered, UGC/AICTE approval, NAAC grade & cycle, NIRF ranking, Chancellor/VC names, Logo URL, Cover Image URL, managing trust, official contacts) are NOT found in the provided preview text, ACTIVELY USE GOOGLE SEARCH GROUNDING, official regulatory directories (UGC, AICTE, NAAC, NIRF, AISHE), Wikipedia, and authoritative public educational portals to find the real verified facts. DO NOT LEAVE THEM BLANK.
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
1. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Carefully inspect the PRIMARY URL and any ADDITIONAL REFERENCE URLS provided above.
   - Extract institutional details STRICTLY AND EXCLUSIVELY from the provided preview text and reference sources.
   - DO NOT search Google or fabricate data. If any field is not found in the provided content, leave it empty/null/false.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert higher education data extraction and research AI agent.
Your PRIMARY GOAL is to perform comprehensive, verified research and extract institutional profile data for the educational institution at:
PRIMARY OFFICIAL URL: {$url}{$refUrlsText}
ORGANISATION TYPE: {$orgTypeTitle}

=== CRITICAL EXTRACTION SCOPE: ORGANISATION PROFILE ONLY ===
Extract ONLY the institutional profile for the "organisation" object.
{$cleanContent}

CRITICAL RESEARCH & WRITING INSTRUCTIONS:
{$searchInstructions}
2. STRICT ENUM VALUE MATCHING (CRITICAL FOR DATABASE MAPPING):
   - `university_type`: STRICT ENUM. Must be EXACTLY ONE of: "Central", "State", "Deemed", "Private", "International".
     * NOTE: If the institution is a Deemed-to-be-University (e.g. Thapar, BITS Pilani, NMIMS, Amrita, Manipal), return "Deemed".
     * If it is a State Private University, return "Private".
     * If it is a State Public University, return "State".
     * If it is a Central University, return "Central".
   - `ownership_type`: STRICT ENUM. Must be EXACTLY ONE of: "Government", "Private", "Trust", "Minority".
     * NOTE: For private or trust-founded institutions (e.g. Thapar Educational Trust), return "Private" or "Trust".
   - `university_category`: STRICT ENUM. Must be EXACTLY ONE of: "Teaching", "Research", "Teaching + Research".
     * NOTE: For universities offering undergraduate, postgraduate, and active Ph.D./research programs, return "Teaching + Research".
   - `levels_offered`: ARRAY. Must contain ONLY values from: ["Diploma", "UG", "PG", "Doctoral"].
     * Example: ["UG", "PG", "Doctoral"]
3. LOGO & COVER IMAGE URLS:
   - `logo_url`: Direct HTTPS URL to the official high-resolution logo / university crest (extract from website header, meta tags, or Wikipedia / Wikimedia Commons infobox image).
   - `cover_image_url`: Direct HTTPS URL to a high-quality campus landscape photo, main building facade, or banner image.
4. ORIGINAL & POLISHED DESCRIPTIONS (NO COPY-PASTE):
   - DO NOT copy-paste raw text or boilerplate disclaimers.
   - Synthesize, rewrite, and produce original, engaging, professionally structured, and SEO-friendly summaries:
     * `about_university` / `about_organisation`: 2-3 well-written paragraphs covering history, academic standing, campus culture, infrastructure, and institutional achievements.
     * `vision_mission`: Clear, inspiring, and concise vision and mission statements.
     * `core_values`: Clean array of 3 to 6 key institutional values (e.g. ["Academic Rigor", "Innovation & Research", "Ethical Leadership", "Inclusivity"]).
5. RETURN FORMAT:
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
    "established_year": 1956,
    "ownership_type": "Private",
    "university_type": "Deemed",
    "university_category": "Teaching + Research",
    "levels_offered": [
      "UG",
      "PG",
      "Doctoral"
    ],
    "logo_url": "https://upload.wikimedia.org/.../logo.png",
    "cover_image_url": "https://upload.wikimedia.org/.../campus.jpg",
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
        ?\App\Models\Organisation $targetOrg = null,
        bool $searchGoogle = true
    ): string
    {
        if ($targetOrg) {
            return $this->buildCampusesAndCoursesPrompt($url, $content, $orgTypeTitle, $referenceUrls, $targetOrg, $searchGoogle);
        }
        return $this->buildOrganisationOnlyPrompt($url, $content, $orgTypeTitle, 1, $referenceUrls, $searchGoogle);
    }

    protected function buildInstitutePrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = [], bool $searchGoogle = true): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
1. Search Google actively based on the institution name and official URL for:
   - Registration details, legal name, registered entity name, registration number, GST, PAN, ownership type (Private/Trust/LLP/Partnership).
   - Core details: brand name, short name, established year, head office, central authority, vision, mission, about organisation.
   - Campuses, departments/divisions, and courses/certifications offered.
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
1. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Extract information STRICTLY AND EXCLUSIVELY from the provided primary URL content and reference sources.
   - DO NOT search Google or fabricate data. If any field is not found in the provided sources, leave it empty or null.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert institutional data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the Institute / Academy at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
{$searchInstructions}
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

    protected function buildSchoolPrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = [], bool $searchGoogle = true): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
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
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
1. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Extract school information STRICTLY AND EXCLUSIVELY from the provided primary URL content and reference sources.
   - DO NOT search Google or fabricate data. If any field is not found in the provided content, leave it empty/null/false.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert K-12 school education data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the School / School Network at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
{$searchInstructions}
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
    "logo_url": "https://upload.wikimedia.org/.../school_logo.png",
    "cover_image_url": "https://upload.wikimedia.org/.../school_campus.jpg",
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

    protected function buildExamConductingBodyPrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = [], bool $searchGoogle = true): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
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
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
1. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Extract exam conducting body information STRICTLY AND EXCLUSIVELY from the provided primary URL content and reference sources.
   - DO NOT search Google or fabricate data. If any field is not found in the provided content, leave it empty/null/false.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert exam authority and testing agency data extraction and research AI.
Extract comprehensive, highly accurate, and verified data for the Exam Conducting Body at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
{$searchInstructions}
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

    protected function buildCounsellingBodyPrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = [], bool $searchGoogle = true): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
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
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
1. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Extract counselling body information STRICTLY AND EXCLUSIVELY from the provided primary URL content and reference sources.
   - DO NOT search Google or fabricate data. If any field is not found in the provided content, leave it empty/null/false.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert admission counselling and seat allocation authority research AI.
Extract comprehensive, highly accurate, and verified data for the Counselling Body at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
{$searchInstructions}
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

    protected function buildRegulatoryBodyPrompt(string $url, string $content, string $orgTypeTitle, array $referenceUrls = [], bool $searchGoogle = true): string
    {
        $refUrlsText = $this->formatReferenceUrlsText($referenceUrls);

        $searchInstructions = $searchGoogle ? <<<GOOGLE_INSTRUCTIONS
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
GOOGLE_INSTRUCTIONS
        : <<<STRICT_URL_INSTRUCTIONS
1. STRICT SOURCE EXTRACTION (NO GOOGLE SEARCH):
   - Extract regulatory body details STRICTLY AND EXCLUSIVELY from the provided primary URL content and reference sources.
   - DO NOT search Google or fabricate data. If any field is not found in the provided content, leave it empty/null/false.
STRICT_URL_INSTRUCTIONS;

        return <<<PROMPT
You are an expert regulatory body and government education agency research AI.
Extract comprehensive, highly accurate, and verified data for the Regulatory Body / Government Agency at PRIMARY URL: {$url}{$refUrlsText}
Organisation Type: {$orgTypeTitle}

RAW WEBSITE & REFERENCE CONTENT PREVIEW:
{$content}

IMPORTANT INSTRUCTIONS:
{$searchInstructions}
2. If any field cannot be found, return empty string "" or false for booleans, but always include every key in the JSON response.
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
    "headquarters_location": "New Delhi",
    "official_website": "{$url}",
    "mandate_description": "Apex regulatory authority maintaining educational standards...",
    "authority_type": "Statutory Body",
    "parent_ministry": "Ministry of Education",
    "parent_ministry_or_department": "Ministry of Education",
    "established_by": "Act of Parliament",
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
