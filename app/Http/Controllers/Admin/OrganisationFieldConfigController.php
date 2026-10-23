<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganisationType;
use App\Models\OrganisationFieldConfig;
use App\Services\OrganisationFieldCatalogService;
use App\Services\OrganisationFields\OrganisationFieldPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrganisationFieldConfigController extends Controller
{
    protected OrganisationFieldCatalogService $catalogService;
    protected OrganisationFieldPolicy $policy;

    public function __construct(OrganisationFieldCatalogService $catalogService, OrganisationFieldPolicy $policy)
    {
        $this->catalogService = $catalogService;
        $this->policy = $policy;
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
        $courseSections = collect($this->catalogService->getCourseSections())->map(function ($sec) use ($activeTypeId) {
            $sec['fields'] = array_values(array_filter($sec['fields'], fn ($f) => \App\Support\OrganisationFields\CourseFields::appliesTo($f['name'], $activeTypeId)));
            return $sec;
        })->filter(fn ($sec) => $sec['fields'])->values()->all();

        // Load all saved configurations indexed by organisation_type_id
        $savedConfigs = OrganisationFieldConfig::all()->keyBy('organisation_type_id');
        $activeSavedConfig = $savedConfigs->get($activeTypeId);

        // Active enabled fields map: ['organisation' => [...], 'campus' => [...], 'department' => [...], 'course' => [...]]
        $activeFieldsConfig = $activeSavedConfig ? ($activeSavedConfig->fields_config ?? []) : null;
        // A config saved before the keys were unified may use campuses / departments / courses: show it under the names this screen uses.
        if ($activeFieldsConfig !== null) {
            foreach (['campuses' => 'campus', 'departments' => 'department', 'courses' => 'course'] as $plural => $singular) {
                if (!isset($activeFieldsConfig[$singular]) && isset($activeFieldsConfig[$plural])) $activeFieldsConfig[$singular] = $activeFieldsConfig[$plural];
            }
        }
        $fieldStatus = [];
        foreach (OrganisationFieldPolicy::LEVELS as $level) $fieldStatus[$level] = $this->policy->states($level, $activeTypeId);
        $formScan = $this->policy->scanner()->manifest();

        return view('admin.organisation_fields.index', compact(
            'organisationTypes',
            'activeTypeId',
            'orgTabs',
            'campusSections',
            'deptSections',
            'courseSections',
            'savedConfigs',
            'activeFieldsConfig',
            'fieldStatus',
            'formScan'
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

        // Drop fields that can never work (no database column, not on the edit form...) so the saved config tells the truth.
        $dropped = 0;
        foreach (OrganisationFieldPolicy::LEVELS as $level) {
            $before = count($sanitizedConfig[$level]);
            $sanitizedConfig[$level] = $this->policy->keepUsable($level, $typeId, $sanitizedConfig[$level]);
            $dropped += $before - count($sanitizedConfig[$level]);
        }

        $config = OrganisationFieldConfig::saveTypeConfig($typeId, $sanitizedConfig);

        return response()->json([
            'success' => true,
            'message' => 'Organisation fields configuration saved successfully!' . ($dropped ? " $dropped field(s) were left out because they cannot be used (see the grey labels)." : ''),
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

    /** Where the field catalogue, the edit forms and the database disagree, per organisation type and level. */
    public function audit(Request $request)
    {
        $types = OrganisationType::orderBy('id')->pluck('title', 'id');
        $report = [];
        foreach ($types as $typeId => $title) {
            foreach (OrganisationFieldPolicy::LEVELS as $level) {
                $states = $this->policy->states($level, (int) $typeId);
                $report[$typeId][$level] = [
                    'summary' => $this->policy->summary($level, (int) $typeId),
                    'problems' => array_filter($states, fn ($s) => $s['state'] !== 'ok'),
                    'form' => $this->policy->scanner()->formFor($level, (int) $typeId),
                ];
            }
        }
        return view('admin.organisation_fields.audit', ['types' => $types, 'report' => $report, 'manifest' => $this->policy->scanner()->manifest(),
            'quality' => ['on' => (bool) config('organisation_fields.block_quality_managed'), 'fields' => config('organisation_fields.quality_managed')],
            'unavailable' => config('organisation_fields.unavailable', []), 'renames' => config('organisation_fields.catalogue_renames', [])]);
    }

    /** Re-read the real edit forms now (they are also re-read automatically whenever a form template changes). */
    public function rescan()
    {
        $m = $this->policy->scanner()->manifest(true);
        return redirect()->route('admin.organisation-fields.audit')->with($m['errors'] ? 'error' : 'success',
            $m['errors'] ? 'Forms re-read, but some could not be rendered: ' . implode(' | ', $m['errors']) : 'Forms re-read.');
    }
}
