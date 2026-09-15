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
        $programLevels = \App\Models\ProgramLevel::where('status', true)->orderBy('title')->get();
        $streams = \App\Models\StreamOffered::where('status', true)->orderBy('title')->get();
        $disciplines = \App\Models\Discipline::where('status', true)->orderBy('title')->get();
        $courses = \App\Models\Course::select('id', 'name', 'program_level_id', 'stream_offered_id', 'discipline_id', 'duration')->orderBy('name')->get();
        $organisation = new \App\Models\Organisation();

        return view('admin.organisations.ai_create', compact(
            'organisation',
            'organisationTypes',
            'programLevels',
            'streams',
            'disciplines',
            'courses'
        ));
    }

    /**
     * AJAX endpoint to extract and research institution data from URL using Gemini
     */
    public function extract(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'organisation_type_id' => 'required|exists:organisation_types,id',
        ]);

        try {
            $orgType = \App\Models\OrganisationType::findOrFail($request->input('organisation_type_id'));

            $data = $this->scraper->extractFromUrl(
                $request->input('url'),
                $orgType->title,
                $orgType->id
            );

            if (!isset($data['organisation'])) {
                $data['organisation'] = [];
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

            if (!is_array($data) || empty($data['organisation'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data payload received.'
                ], 422);
            }

            $organisation = $this->importer->saveImportedData($data);

            return response()->json([
                'success' => true,
                'message' => "Organisation '{$organisation->name}', campuses, departments, and courses created successfully!",
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
