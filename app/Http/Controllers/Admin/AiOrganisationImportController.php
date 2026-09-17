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
     * AJAX endpoint to preview/generate the AI prompt before extraction
     */
    public function previewPrompt(Request $request)
    {
        $request->validate([
            'url' => 'nullable|string',
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'target_organisation_id' => 'nullable|exists:organisations,id',
            'reference_urls' => 'nullable|array',
            'reference_urls.*' => 'nullable|string',
        ]);

        try {
            $url = $request->input('url') ?: 'https://www.example.edu';
            $orgType = \App\Models\OrganisationType::findOrFail($request->input('organisation_type_id'));
            $targetOrgId = $request->input('target_organisation_id');
            $targetOrg = $targetOrgId ? \App\Models\Organisation::find($targetOrgId) : null;

            $referenceUrls = [];
            if ($request->has('reference_urls') && is_array($request->input('reference_urls'))) {
                foreach ($request->input('reference_urls') as $ref) {
                    $trimmed = trim((string)$ref);
                    if (!empty($trimmed)) {
                        $referenceUrls[] = $trimmed;
                    }
                }
            }

            $prompt = $this->scraper->buildExtractionPrompt(
                $url,
                $orgType->title,
                $orgType->id,
                $referenceUrls,
                $targetOrg
            );

            return response()->json([
                'success' => true,
                'prompt' => $prompt,
                'mode' => $targetOrg ? 'campuses_and_courses' : 'organisation_only',
                'target_organisation_name' => $targetOrg ? $targetOrg->name : null
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
        $request->validate([
            'url' => 'required|url',
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'target_organisation_id' => 'nullable|exists:organisations,id',
            'reference_urls' => 'nullable|array',
            'reference_urls.*' => 'nullable|string',
            'custom_prompt' => 'nullable|string',
        ]);

        try {
            $orgType = \App\Models\OrganisationType::findOrFail($request->input('organisation_type_id'));
            $targetOrgId = $request->input('target_organisation_id');
            $targetOrg = $targetOrgId ? \App\Models\Organisation::find($targetOrgId) : null;

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

            $data = $this->scraper->extractFromUrl(
                $request->input('url'),
                $orgType->title,
                $orgType->id,
                $referenceUrls,
                $targetOrg,
                !empty($customPrompt) ? $customPrompt : null
            );

            if (!isset($data['organisation'])) {
                $data['organisation'] = [];
            }

            if ($targetOrg) {
                $data['target_organisation_id'] = $targetOrg->id;
                $data['target_organisation_name'] = $targetOrg->name;
                $data['mode'] = 'campuses_and_courses';
                $data['organisation']['id'] = $targetOrg->id;
                $data['organisation']['name'] = $targetOrg->name;
                $data['organisation']['short_name'] = $targetOrg->short_name;
                $data['organisation']['official_website'] = $targetOrg->official_website ?: $request->input('url');
            } else {
                $data['target_organisation_id'] = null;
                $data['mode'] = 'organisation_only';
                // Strictly no campuses, departments, or courses when creating an organisation only
                $data['campuses'] = [];
                $data['departments'] = [];
                $data['courses'] = [];
            }

            $data['organisation']['organisation_type_id'] = $orgType->id;
            $data['organisation']['organisation_type'] = $orgType->title;

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
                'selected_type_id' => $orgType->id,
                'target_organisation_id' => $targetOrg ? $targetOrg->id : null,
                'target_organisation_name' => $targetOrg ? $targetOrg->name : null,
                'mode' => $targetOrg ? 'campuses_and_courses' : 'organisation_only',
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
        $request->validate([
            'extracted_json' => 'required',
        ]);

        try {
            $raw = $request->input('extracted_json');
            $data = is_array($raw) ? $raw : json_decode($raw, true);

            if (!is_array($data) || (empty($data['organisation']) && empty($data['target_organisation_id']))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data payload received.'
                ], 422);
            }

            $isCampusesMode = !empty($data['target_organisation_id']);
            $organisation = $this->importer->saveImportedData($data);

            $message = $isCampusesMode
                ? "Campuses, departments, and courses for '{$organisation->name}' created successfully!"
                : "Organisation '{$organisation->name}' created successfully!";

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('admin.organisations.edit', $organisation->id)
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
