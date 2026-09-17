<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganisationType;
use App\Models\OrganisationFieldConfig;
use App\Services\OrganisationFieldCatalogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrganisationFieldConfigController extends Controller
{
    protected OrganisationFieldCatalogService $catalogService;

    public function __construct(OrganisationFieldCatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * Display the Organisation Fields Configuration matrix.
     */
    public function index(Request $request)
    {
        $organisationTypes = OrganisationType::where('status', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($organisationTypes->isEmpty()) {
            $organisationTypes = OrganisationType::orderBy('id')->get();
        }

        $activeTypeId = (int) ($request->query('type_id') ?: ($organisationTypes->first()->id ?? 1));

        // Get tabs/sections catalog
        $orgTabs = $this->catalogService->getOrganisationTabs($activeTypeId);
        $campusSections = $this->catalogService->getCampusSections();
        $deptSections = $this->catalogService->getDepartmentSections();
        $courseSections = $this->catalogService->getCourseSections();

        // Load all saved configurations indexed by organisation_type_id
        $savedConfigs = OrganisationFieldConfig::all()->keyBy('organisation_type_id');
        $activeSavedConfig = $savedConfigs->get($activeTypeId);

        // Active enabled fields map: ['organisation' => [...], 'campus' => [...], 'department' => [...], 'course' => [...]]
        $activeFieldsConfig = $activeSavedConfig ? ($activeSavedConfig->fields_config ?? []) : null;

        return view('admin.organisation_fields.index', compact(
            'organisationTypes',
            'activeTypeId',
            'orgTabs',
            'campusSections',
            'deptSections',
            'courseSections',
            'savedConfigs',
            'activeFieldsConfig'
        ));
    }

    /**
     * Save the checked fields configuration for an organisation type.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'fields_config' => 'required|array',
        ]);

        $typeId = (int) $request->input('organisation_type_id');
        $fieldsConfig = $request->input('fields_config');

        // Sanitize to expected keys: organisation, campus, department, course
        $sanitizedConfig = [
            'organisation' => array_values(array_unique(array_filter($fieldsConfig['organisation'] ?? []))),
            'campus' => array_values(array_unique(array_filter($fieldsConfig['campus'] ?? []))),
            'department' => array_values(array_unique(array_filter($fieldsConfig['department'] ?? []))),
            'course' => array_values(array_unique(array_filter($fieldsConfig['course'] ?? []))),
        ];

        $config = OrganisationFieldConfig::saveTypeConfig($typeId, $sanitizedConfig);

        return response()->json([
            'success' => true,
            'message' => 'Organisation fields configuration saved successfully!',
            'data' => $config
        ]);
    }

    /**
     * Reset configuration for an organisation type (defaults back to all checked).
     */
    public function reset(Request $request, $typeId): JsonResponse
    {
        $deleted = OrganisationFieldConfig::where('organisation_type_id', $typeId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Configuration reset to default (all fields enabled).'
        ]);
    }
}