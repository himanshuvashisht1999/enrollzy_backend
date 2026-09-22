<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GeminiOrganisationScraperService;
use App\Services\OrganisationImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiOrganisationImportController extends Controller
{
    protected GeminiOrganisationScraperService $scraper;
    protected OrganisationImportService $importer;

    public function __construct(GeminiOrganisationScraperService $scraper, OrganisationImportService $importer)
    {
        $this->scraper = $scraper;
        $this->importer = $importer;
    }

    /**
     * Dedicated full-page view for AI Organisation Auto-Creation
     */
    public function create()
    {
        $organisationTypes = \App\Models\OrganisationType::where('status', true)->orderBy('sort_order')->orderBy('title')->get();
        $organisations = \App\Models\Organisation::select('id', 'name', 'organisation_type_id', 'short_name', 'official_website')
            ->orderBy('name')
            ->get();
        $programLevels = \App\Models\ProgramLevel::where('status', true)->orderBy('title')->get();
        $streams = \App\Models\StreamOffered::where('status', true)->orderBy('title')->get();
        $disciplines = \App\Models\Discipline::where('status', true)->orderBy('title')->get();
        $courses = \App\Models\Course::select('id', 'name', 'program_level_id', 'stream_offered_id', 'discipline_id', 'duration')->orderBy('name')->get();
        $organisation = new \App\Models\Organisation();

        return view('admin.organisations.ai_create', compact(
            'organisation',
            'organisationTypes',
            'organisations',
            'programLevels',
            'streams',
            'disciplines',
            'courses'
        ));
    }

    /**
     * AJAX endpoint to get cascading options for Campuses and Departments
     */
    public function cascadingOptions(Request $request)
    {
        $organisationId = $request->query('organisation_id');
        $campusId = $request->query('campus_id');

        $campuses = [];
        $departments = [];

        if ($organisationId) {
            $campuses = \App\Models\Campus::where('organisation_id', $organisationId)
                ->select('id', 'campus_name', 'city')
                ->orderBy('campus_name')
                ->get();
        }

        if ($campusId) {
            $departments = \App\Models\Department::where('campus_id', $campusId)
                ->select('id', 'department_name', 'department_code')
                ->orderBy('department_name')
                ->get();
        } elseif ($organisationId) {
            $departments = \App\Models\Department::where('organisation_id', $organisationId)
                ->select('id', 'department_name', 'department_code')
                ->orderBy('department_name')
                ->get();
        }

        return response()->json([
            'success' => true,
            'campuses' => $campuses,
            'departments' => $departments
        ]);
    }

    /**
     * AJAX endpoint to preview/generate the AI prompt before extraction
     */
    public function previewPrompt(Request $request)
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $mode = $request->input('mode', 'organisation');

        $rules = [
            'url' => 'nullable|string',
            'mode' => 'required|in:organisation,campus,department,course',
            'reference_urls' => 'nullable|array',
            'reference_urls.*' => 'nullable|string',
            'search_google' => 'nullable',
        ];

        if ($mode === 'organisation') {
            $rules['organisation_type_id'] = 'required|exists:organisation_types,id';
        } elseif ($mode === 'campus') {
            $rules['target_organisation_id'] = 'required|exists:organisations,id';
        } elseif ($mode === 'department') {
            $rules['target_organisation_id'] = 'required|exists:organisations,id';
            $rules['target_campus_id'] = 'nullable|exists:campuses,id';
        } elseif ($mode === 'course') {
            $rules['target_organisation_id'] = 'required|exists:organisations,id';
            $rules['target_campus_id'] = 'nullable|exists:campuses,id';
            $rules['target_department_id'] = 'nullable|exists:departments,id';
        }

        $request->validate($rules);

        try {
            $url = $request->input('url') ?: 'https://www.example.edu';
            $orgType = $request->filled('organisation_type_id') ? \App\Models\OrganisationType::find($request->input('organisation_type_id')) : null;
            $orgTypeId = $orgType ? $orgType->id : 1;
            $orgTypeTitle = $orgType ? $orgType->title : 'University';

            $targetOrgId = $request->input('target_organisation_id');
            $targetOrg = $targetOrgId ? \App\Models\Organisation::find($targetOrgId) : null;
            $targetCampus = $request->filled('target_campus_id') ? \App\Models\Campus::find($request->input('target_campus_id')) : null;
            $targetDepartment = $request->filled('target_department_id') ? \App\Models\Department::find($request->input('target_department_id')) : null;

            if ($targetOrg && $targetOrg->organisationType) {
                $orgTypeTitle = $targetOrg->organisationType->title;
                $orgTypeId = $targetOrg->organisation_type_id;
            }

            $referenceUrls = [];
            if ($request->has('reference_urls') && is_array($request->input('reference_urls'))) {
                foreach ($request->input('reference_urls') as $ref) {
                    $trimmed = trim((string)$ref);
                    if (!empty($trimmed)) {
                        $referenceUrls[] = $trimmed;
                    }
                }
            }

            $searchGoogle = $request->has('search_google') ? filter_var($request->input('search_google'), FILTER_VALIDATE_BOOLEAN) : true;

            $prompt = $this->scraper->buildExtractionPrompt(
                $url,
                $orgTypeTitle,
                $orgTypeId,
                $referenceUrls,
                $targetOrg,
                '',
                $mode,
                $targetCampus,
                $targetDepartment,
                $searchGoogle
            );

            return response()->json([
                'success' => true,
                'prompt' => $prompt,
                'mode' => $mode,
                'target_organisation_name' => $targetOrg ? $targetOrg->name : null,
                'target_campus_name' => $targetCampus ? $targetCampus->campus_name : null,
                'target_department_name' => $targetDepartment ? $targetDepartment->department_name : null,
                'search_google' => $searchGoogle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to extract and research institution data from URL using Gemini
     */
    public function extract(Request $request)
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $mode = $request->input('mode', 'organisation');

        $rules = [
            'url' => 'required|url',
            'mode' => 'required|in:organisation,campus,department,course',
            'reference_urls' => 'nullable|array',
            'reference_urls.*' => 'nullable|string',
            'custom_prompt' => 'nullable|string',
            'search_google' => 'nullable',
        ];

        if ($mode === 'organisation') {
            $rules['organisation_type_id'] = 'required|exists:organisation_types,id';
        } elseif ($mode === 'campus') {
            $rules['target_organisation_id'] = 'required|exists:organisations,id';
        } elseif ($mode === 'department') {
            $rules['target_organisation_id'] = 'required|exists:organisations,id';
            $rules['target_campus_id'] = 'required|exists:campuses,id';
        } elseif ($mode === 'course') {
            $rules['target_organisation_id'] = 'required|exists:organisations,id';
            $rules['target_campus_id'] = 'required|exists:campuses,id';
            $rules['target_department_id'] = 'required|exists:departments,id';
        }

        $request->validate($rules);

        try {
            $orgType = $request->filled('organisation_type_id') ? \App\Models\OrganisationType::find($request->input('organisation_type_id')) : null;
            $orgTypeId = $orgType ? $orgType->id : 1;
            $orgTypeTitle = $orgType ? $orgType->title : 'University';

            $targetOrgId = $request->input('target_organisation_id');
            $targetOrg = $targetOrgId ? \App\Models\Organisation::find($targetOrgId) : null;
            $targetCampus = $request->filled('target_campus_id') ? \App\Models\Campus::find($request->input('target_campus_id')) : null;
            $targetDepartment = $request->filled('target_department_id') ? \App\Models\Department::find($request->input('target_department_id')) : null;

            if ($targetOrg && $targetOrg->organisationType) {
                $orgTypeTitle = $targetOrg->organisationType->title;
                $orgTypeId = $targetOrg->organisation_type_id;
            }

            $referenceUrls = [];
            if ($request->has('reference_urls') && is_array($request->input('reference_urls'))) {
                foreach ($request->input('reference_urls') as $ref) {
                    $trimmed = trim((string)$ref);
                    if (!empty($trimmed) && filter_var($trimmed, FILTER_VALIDATE_URL)) {
                        $referenceUrls[] = $trimmed;
                    }
                }
            }

            $customPrompt = $request->input('custom_prompt');
            $searchGoogle = $request->has('search_google') ? filter_var($request->input('search_google'), FILTER_VALIDATE_BOOLEAN) : true;

            $data = $this->scraper->extractFromUrl(
                $request->input('url'),
                $orgTypeTitle,
                $orgTypeId,
                $referenceUrls,
                $targetOrg,
                !empty($customPrompt) ? $customPrompt : null,
                $mode,
                $targetCampus,
                $targetDepartment,
                $searchGoogle
            );

            $data['mode'] = $mode;
            $data['target_organisation_id'] = $targetOrg ? $targetOrg->id : null;
            $data['target_organisation_name'] = $targetOrg ? $targetOrg->name : null;
            $data['target_campus_id'] = $targetCampus ? $targetCampus->id : null;
            $data['target_campus_name'] = $targetCampus ? $targetCampus->campus_name : null;
            $data['target_department_id'] = $targetDepartment ? $targetDepartment->id : null;
            $data['target_department_name'] = $targetDepartment ? $targetDepartment->department_name : null;

            // Isolate entity arrays according to mode
            if ($mode === 'organisation') {
                if (!isset($data['organisation'])) {
                    $data['organisation'] = [];
                }
                if ($orgType) {
                    $data['organisation']['organisation_type_id'] = $orgType->id;
                    $data['organisation']['organisation_type'] = $orgType->title;
                }
                $data['campuses'] = [];
                $data['departments'] = [];
                $data['courses'] = [];
            } elseif ($mode === 'campus') {
                $data['organisation'] = [];
                $data['departments'] = [];
                $data['courses'] = [];
                if (!isset($data['campuses']) || !is_array($data['campuses'])) {
                    $data['campuses'] = [];
                } elseif (count($data['campuses']) > 1) {
                    $data['campuses'] = array_slice($data['campuses'], 0, 1);
                }
            } elseif ($mode === 'department') {
                $data['organisation'] = [];
                $data['campuses'] = [];
                $data['courses'] = [];
                if (!isset($data['departments'])) {
                    $data['departments'] = [];
                }
            } elseif ($mode === 'course') {
                $data['organisation'] = [];
                $data['campuses'] = [];
                $data['departments'] = [];
                if (!isset($data['courses'])) {
                    $data['courses'] = [];
                }
            }

            $masters = [
                'courses' => \App\Models\Course::select('id', 'name', 'program_level_id', 'stream_offered_id', 'discipline_id', 'duration')->orderBy('name')->get(),
                'program_levels' => \App\Models\ProgramLevel::select('id', 'title')->orderBy('title')->get(),
                'streams' => \App\Models\StreamOffered::select('id', 'title')->orderBy('title')->get(),
                'disciplines' => \App\Models\Discipline::select('id', 'title')->orderBy('title')->get(),
                'organisation_types' => \App\Models\OrganisationType::select('id', 'title')->orderBy('title')->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'mode' => $mode,
                'selected_type_id' => $orgTypeId,
                'target_organisation_id' => $targetOrg ? $targetOrg->id : null,
                'target_organisation_name' => $targetOrg ? $targetOrg->name : null,
                'target_campus_id' => $targetCampus ? $targetCampus->id : null,
                'target_campus_name' => $targetCampus ? $targetCampus->campus_name : null,
                'target_department_id' => $targetDepartment ? $targetDepartment->id : null,
                'target_department_name' => $targetDepartment ? $targetDepartment->department_name : null,
                'masters' => $masters,
                'message' => 'Data extracted and verified successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('AI Organisation Extract Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to store reviewed & verified data
     */
    public function store(Request $request)
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $request->validate([
            'extracted_json' => 'required',
        ]);

        try {
            $raw = $request->input('extracted_json');
            $data = is_array($raw) ? $raw : json_decode($raw, true);

            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data payload received.'
                ], 422);
            }

            $mode = $data['mode'] ?? 'organisation';
            $organisation = $this->importer->saveImportedData($data);

            if ($mode === 'campus') {
                $message = "Campuses for '{$organisation->name}' created successfully!";
                $redirectUrl = route('admin.organisations.edit', $organisation->id) . '#campuses';
            } elseif ($mode === 'department') {
                $message = "Departments for '{$organisation->name}' created successfully!";
                $redirectUrl = route('admin.organisations.edit', $organisation->id) . '#departments';
            } elseif ($mode === 'course') {
                $message = "Courses for '{$organisation->name}' created successfully!";
                $redirectUrl = route('admin.organisations.edit', $organisation->id) . '#courses';
            } else {
                $message = "Organisation '{$organisation->name}' created successfully!";
                $redirectUrl = route('admin.organisations.edit', $organisation->id);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            Log::error('AI Organisation Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save data: ' . $e->getMessage()
            ], 500);
        }
    }
}
