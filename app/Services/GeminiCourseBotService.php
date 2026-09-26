<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseType;
use App\Models\Discipline;
use App\Models\DynamicExam;
use App\Models\ProgramLevel;
use App\Models\ProgramType;
use App\Models\Specialization;
use App\Models\StreamOffered;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiCourseBotService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-3.6-flash');
    }

    /**
     * Fetch exhaustive course profile and details via Gemini AI Bot
     */
    public function fetchCourseData(string $courseName): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file.');
        }

        $courseName = trim($courseName);
        if (empty($courseName)) {
            throw new \Exception('Course name cannot be empty.');
        }

        // 1. Fetch available options from Database
        $programLevels = ProgramLevel::where('status', true)->pluck('title', 'id')->toArray();
        $streams = StreamOffered::where('status', true)->pluck('title', 'id')->toArray();
        $disciplines = Discipline::where('status', true)->pluck('title', 'id')->toArray();
        $courseTypes = CourseType::where('status', true)->orWhere('status', 1)->pluck('title', 'id')->toArray();
        $programTypes = ProgramType::where('status', true)->pluck('title', 'id')->toArray();
        $exams = DynamicExam::where('status', 'Active')->orWhere('status', 1)->orWhere('status', '1')->pluck('name', 'id')->toArray();
        $specializations = Specialization::where('status', true)->pluck('title', 'id')->toArray();
        $existingCourses = Course::where('status', 1)->pluck('name', 'id')->take(80)->toArray();

        // 2. Build structured extraction prompt
        $prompt = $this->buildPrompt($courseName, [
            'program_levels' => $programLevels,
            'streams' => $streams,
            'disciplines' => $disciplines,
            'course_types' => $courseTypes,
            'program_types' => $programTypes,
            'exams' => $exams,
            'specializations' => $specializations,
            'existing_courses' => $existingCourses,
        ]);

        // 3. Fallback models to try in sequence
        $modelsToTry = array_values(array_filter(array_unique([
            $this->model,
            'gemini-3.6-flash',
            'gemini-3.7-flash',
            'gemini-3.1-flash-lite',
        ])));

        $data = $this->executePrompt($prompt, $modelsToTry);

        // 4. Validate and sanitize returned IDs against DB catalogs
        return $this->validateAndFormatData($data, [
            'program_levels' => $programLevels,
            'streams' => $streams,
            'disciplines' => $disciplines,
            'course_types' => $courseTypes,
            'program_types' => $programTypes,
            'exams' => $exams,
            'specializations' => $specializations,
            'existing_courses' => $existingCourses,
        ]);
    }

    /**
     * Build prompt for Gemini with catalog mapping instructions
     */
    protected function buildPrompt(string $courseName, array $catalogs): string
    {
        $levelsJson = json_encode($catalogs['program_levels'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $streamsJson = json_encode($catalogs['streams'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $disciplinesJson = json_encode($catalogs['disciplines'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $courseTypesJson = json_encode($catalogs['course_types'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $programTypesJson = json_encode($catalogs['program_types'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $examsJson = json_encode($catalogs['exams'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $specializationsJson = json_encode($catalogs['specializations'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $coursesJson = json_encode($catalogs['existing_courses'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
You are an expert higher education academic counselor, university curriculum designer, and career guide for Indian and global academic programs.

TASK: Thoroughly research and generate an authoritative, exhaustive, and professionally written academic profile for the following course:
COURSE NAME: "{$courseName}"

MATCHING INSTRUCTIONS:
You must select matching numeric IDs strictly from our database catalogs below:

1. AVAILABLE PROGRAM LEVELS (ID => Title):
{$levelsJson}

2. AVAILABLE STREAMS OFFERED (ID => Title):
{$streamsJson}

3. AVAILABLE DISCIPLINES (ID => Title):
{$disciplinesJson}

4. AVAILABLE COURSE TYPES (ID => Title):
{$courseTypesJson}

5. AVAILABLE PROGRAM TYPES / MODES (ID => Title):
{$programTypesJson}

6. AVAILABLE COMMON ENTRANCE EXAMS (ID => Name):
{$examsJson}

7. AVAILABLE COMMON SPECIALIZATIONS (ID => Title):
{$specializationsJson}

8. AVAILABLE OTHER COURSES FOR RELATED COURSES (ID => Name):
{$coursesJson}

OUTPUT FORMAT RULES:
- Output MUST be valid JSON only. Do not add conversational text or markdown code fence tags if possible.
- Rich-text fields must contain clean, semantic HTML (<p>, <strong>, <em>, <ul>, <li>, etc.) ready for TinyMCE editors.
- Provide detailed, meaningful, high-quality content for every section.

Required JSON Structure:
{
  "name": "{$courseName}",
  "slug": "url-friendly-slug-for-course",
  "full_form": "Official complete expanded title of the course/degree (e.g. Master of Design in Communication Design)",
  "duration": "Typical standard duration, e.g. '2 Years', '4 Years', '3 Years', '1 Year'",
  "average_salary_range": "Realistic industry salary range in India, e.g. '₹6 LPA - ₹15 LPA'",
  "program_level_id": <best matching integer ID from AVAILABLE PROGRAM LEVELS or null>,
  "stream_offered_id": <best matching integer ID from AVAILABLE STREAMS OFFERED or null>,
  "discipline_id": <best matching integer ID from AVAILABLE DISCIPLINES or null>,
  "course_type_id": <best matching integer ID from AVAILABLE COURSE TYPES or null>,
  "program_types": [<array of matching integer IDs from AVAILABLE PROGRAM TYPES, e.g. Regular, Online, Hybrid>],
  "common_entrance_exams": [<array of matching integer IDs from AVAILABLE COMMON ENTRANCE EXAMS>],
  "common_specializations": [<array of matching integer IDs from AVAILABLE COMMON SPECIALIZATIONS>],
  "related_courses": [<array of matching integer IDs from AVAILABLE OTHER COURSES>],
  "overview": "<p>Comprehensive overview explaining what this program is, academic significance, core philosophies, and educational impact...</p>",
  "generic_eligibility": "<p>Detailed eligibility requirements: minimum qualifying degree/education, minimum percentage (general and reserved categories), subject requirements, and entrance prerequisites...</p>",
  "core_curriculum": "<p>Detailed semester-wise or module-wise curriculum, foundational subjects, laboratory/studio work, practical training, electives, and capstone thesis/project...</p>",
  "skills_gained": "<p>Key core technical, creative, software, analytical, and professional soft skills students develop during this course...</p>",
  "career_scope": "<p>Detailed career prospects, prime job profiles (e.g. Art Director, Brand Strategist), top hiring sectors, and prominent recruiters...</p>",
  "higher_education_options": "<p>Higher academic qualifications and research pathways (e.g. Ph.D., Postdoctoral research, international advanced credentials)...</p>",
  "course_comparison": "<p>Objective comparison with similar degrees or alternative academic paths, highlighting differentiating strengths...</p>",
  "pros_cons": "<p>Key benefits, potential challenges, rigorous demands, and guidance on who is ideally suited for this program...</p>",
  "faqs": [
    {
      "question": "What is the standard eligibility criteria for {$courseName}?",
      "answer": "Detailed answer explaining eligibility..."
    },
    {
      "question": "What are the key career opportunities after {$courseName}?",
      "answer": "Detailed answer covering roles and salary..."
    },
    {
      "question": "Which entrance exams are required for admission to {$courseName}?",
      "answer": "Detailed answer covering national and university-level exams..."
    },
    {
      "question": "Is {$courseName} available in online or part-time mode?",
      "answer": "Detailed answer regarding learning modes and flexibility..."
    },
    {
      "question": "What are the core subjects taught in {$courseName}?",
      "answer": "Detailed answer highlighting curriculum areas..."
    }
  ]
}
PROMPT;
    }

    /**
     * Call Gemini API with model fallback
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
                        'parts' => [['text' => $prompt]]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 8192,
                ],
                'tools' => [
                    ['google_search' => (object)[]]
                ]
            ];

            try {
                $response = Http::withoutVerifying()->timeout(90)->post($endpoint, $payload);

                if (!$response->successful()) {
                    // Try without search tool if tool error occurs
                    if ($response->status() === 400) {
                        unset($payload['tools']);
                        $response = Http::withoutVerifying()->timeout(90)->post($endpoint, $payload);
                    }
                }

                if (!$response->successful()) {
                    $lastError = $response->json('error.message', $response->body());
                    Log::warning("Gemini course bot {$modelName} failed: {$lastError}, trying fallback...");
                    continue;
                }

                $parts = $response->json('candidates.0.content.parts', []);
                if (empty($parts)) {
                    continue;
                }

                $rawText = '';
                foreach ($parts as $p) {
                    if (!empty($p['text'])) {
                        $rawText = $p['text'];
                        break;
                    }
                }

                $decoded = $this->extractJson($rawText);
                if ($decoded && is_array($decoded)) {
                    return $decoded;
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Gemini course bot exception with {$modelName}: {$lastError}");
            }
        }

        throw new \Exception('Failed to fetch course data from AI Bot: ' . ($lastError ?? 'No valid response received from AI models.'));
    }

    /**
     * Extract JSON from raw response text
     */
    protected function extractJson(string $text): ?array
    {
        $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));
        $decoded = json_decode($clean, true);
        if (is_array($decoded) && (isset($decoded['full_form']) || isset($decoded['overview']))) {
            return $decoded;
        }

        $firstBrace = mb_strpos($text, '{', 0, 'UTF-8');
        $lastBrace = mb_strrpos($text, '}', 0, 'UTF-8');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $candidate = mb_substr($text, $firstBrace, $lastBrace - $firstBrace + 1, 'UTF-8');
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Validate and format returned data against valid catalog IDs
     */
    protected function validateAndFormatData(array $data, array $catalogs): array
    {
        // Program Level ID
        $levelId = isset($data['program_level_id']) ? (int) $data['program_level_id'] : null;
        if ($levelId && !isset($catalogs['program_levels'][$levelId])) {
            $levelId = null;
        }

        // Stream Offered ID
        $streamId = isset($data['stream_offered_id']) ? (int) $data['stream_offered_id'] : null;
        if ($streamId && !isset($catalogs['streams'][$streamId])) {
            $streamId = null;
        }

        // Discipline ID
        $discId = isset($data['discipline_id']) ? (int) $data['discipline_id'] : null;
        if ($discId && !isset($catalogs['disciplines'][$discId])) {
            $discId = null;
        }

        // Course Type ID
        $typeId = isset($data['course_type_id']) ? (int) $data['course_type_id'] : null;
        if ($typeId && !isset($catalogs['course_types'][$typeId])) {
            $typeId = null;
        }

        // Program Types (Array of IDs)
        $progTypes = [];
        if (!empty($data['program_types']) && is_array($data['program_types'])) {
            foreach ($data['program_types'] as $ptId) {
                $ptId = (int) $ptId;
                if (isset($catalogs['program_types'][$ptId])) {
                    $progTypes[] = $ptId;
                }
            }
        }

        // Entrance Exams (Array of IDs)
        $examIds = [];
        if (!empty($data['common_entrance_exams']) && is_array($data['common_entrance_exams'])) {
            foreach ($data['common_entrance_exams'] as $exId) {
                $exId = (int) $exId;
                if (isset($catalogs['exams'][$exId])) {
                    $examIds[] = $exId;
                }
            }
        }

        // Specializations (Array of IDs)
        $specIds = [];
        if (!empty($data['common_specializations']) && is_array($data['common_specializations'])) {
            foreach ($data['common_specializations'] as $spId) {
                $spId = (int) $spId;
                if (isset($catalogs['specializations'][$spId])) {
                    $specIds[] = $spId;
                }
            }
        }

        // Related Courses (Array of IDs)
        $relCourseIds = [];
        if (!empty($data['related_courses']) && is_array($data['related_courses'])) {
            foreach ($data['related_courses'] as $rcId) {
                $rcId = (int) $rcId;
                if (isset($catalogs['existing_courses'][$rcId])) {
                    $relCourseIds[] = $rcId;
                }
            }
        }

        // FAQs
        $faqs = [];
        if (!empty($data['faqs']) && is_array($data['faqs'])) {
            foreach ($data['faqs'] as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $faqs[] = [
                        'question' => trim($faq['question']),
                        'answer' => trim($faq['answer']),
                    ];
                }
            }
        }

        return [
            'name' => $data['name'] ?? '',
            'slug' => $data['slug'] ?? '',
            'full_form' => $data['full_form'] ?? '',
            'duration' => $data['duration'] ?? '',
            'average_salary_range' => $data['average_salary_range'] ?? '',
            'program_level_id' => $levelId,
            'stream_offered_id' => $streamId,
            'discipline_id' => $discId,
            'course_type_id' => $typeId,
            'program_types' => array_values(array_unique($progTypes)),
            'common_entrance_exams' => array_values(array_unique($examIds)),
            'common_specializations' => array_values(array_unique($specIds)),
            'related_courses' => array_values(array_unique($relCourseIds)),
            'overview' => $data['overview'] ?? '',
            'generic_eligibility' => $data['generic_eligibility'] ?? '',
            'core_curriculum' => $data['core_curriculum'] ?? '',
            'skills_gained' => $data['skills_gained'] ?? '',
            'career_scope' => $data['career_scope'] ?? '',
            'higher_education_options' => $data['higher_education_options'] ?? '',
            'course_comparison' => $data['course_comparison'] ?? '',
            'pros_cons' => $data['pros_cons'] ?? '',
            'faqs' => $faqs,
        ];
    }
}
