<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationType;
use App\Models\OrganisationFieldConfig;
use App\Models\Campus;
use App\Models\Department;
use App\Models\Course;
use App\Models\OrganisationCourse;
use App\Services\GeminiOrganisationScraperService;
use App\Services\OrganisationFieldCatalogService;
use App\Services\OrganisationFields\OrganisationFieldPolicy;
use App\Services\OrganisationImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiOrganisationUpdateController extends Controller
{
    protected GeminiOrganisationScraperService $scraper;
    protected OrganisationFieldCatalogService $catalog;
    protected OrganisationImportService $importer;
    protected OrganisationFieldPolicy $policy;

    public function __construct(
        GeminiOrganisationScraperService $scraper,
        OrganisationFieldCatalogService $catalog,
        OrganisationImportService $importer,
        OrganisationFieldPolicy $policy
    ) {
        $this->scraper = $scraper;
        $this->catalog = $catalog;
        $this->importer = $importer;
        $this->policy = $policy;
    }

    /**
     * Display the Bot Update page with searchable organisation dropdown
     */
    public function index(Request $request)
    {
        $organisations = Organisation::select('id', 'name', 'organisation_type_id', 'official_website')
            ->with('organisationType:id,title')
            ->orderBy('name')
            ->get();

        $selectedOrgId = $request->query('org_id');

        return view('admin.organisations.ai_update', compact('organisations', 'selectedOrgId'));
    }

    /**
     * AJAX: Get organisation details, current database fields, and enabled fields for its type
     */
    public function getOrganisationData($id)
    {
        $organisation = Organisation::with([
            'organisationType:id,title',
            'campuses',
            'departments',
            'organisationCourses.course'
        ])->findOrFail($id);

        $typeId = (int) $organisation->organisation_type_id;

        // Fetch configured fields for this organisation type
        // Only fields the AI may really touch: in the catalogue, a real column, on the edit form, and ticked for this type.
        $fieldsConfig = $this->policy->forAi($typeId);

        // Fetch labels for human-readable diff
        $orgLabels = $this->catalog->getFieldLabels('organisation', $typeId);
        $campusLabels = $this->catalog->getFieldLabels('campus');
        $deptLabels = $this->catalog->getFieldLabels('department');
        $courseLabels = $this->catalog->getFieldLabels('course', $typeId);

        return response()->json([
            'success' => true,
            'organisation' => $organisation,
            'type_id' => $typeId,
            'type_title' => $organisation->organisationType?->title ?? 'Organisation',
            'official_website' => $organisation->official_website ?: '',
            'enabled_fields' => $fieldsConfig,
            'labels' => [
                'organisation' => $orgLabels,
                'campuses' => $campusLabels,
                'departments' => $deptLabels,
                'courses' => $courseLabels
            ]
        ]);
    }

    /**
     * AJAX: Scrape web via Gemini strictly for enabled fields and return structured Old vs New Diff
     */
    public function fetchUpdates(Request $request)
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'url' => 'required|url',
        ]);

        try {
            $org = Organisation::with([
                'organisationType:id,title',
                'campuses',
                'departments',
                'organisationCourses.course'
            ])->findOrFail($request->input('organisation_id'));

            $typeId = (int) $org->organisation_type_id;
            $typeTitle = $org->organisationType?->title ?? 'Organisation';

            // Get enabled fields
            // Same rule everywhere (OrganisationFieldPolicy). This also fixes the old mismatch where the screen saved campus / department / course
            // but this code read campuses / departments / courses, so those restrictions were silently ignored.
            $enabledFields = $this->policy->forAi($typeId);
            if (array_sum(array_map('count', $enabledFields)) === 0) {
                return response()->json(['success' => false, 'message' => 'No field is available to the AI updater for this organisation type. Check the Organisation Fields screen (a field must be ticked, be a real column and be on the edit form).'], 422);
            }

            // The AI is shown the CURRENT value of every field it may change - and only those - so it can tell "unchanged" from "new".
            // (Before, this held a hand-picked list that included fields that are not allowed, not on the form, or not even columns.)
            $currentData = [
                'organisation' => \Illuminate\Support\Arr::only($org->toArray(), $enabledFields['organisation']),
                'campuses' => $org->campuses->map(fn ($c) => ['id' => $c->id, 'campus_name' => $c->campus_name]
                    + \Illuminate\Support\Arr::only($c->toArray(), $enabledFields['campuses']))->values()->toArray(),
                'departments' => $org->departments->map(fn ($d) => ['id' => $d->id, 'department_name' => $d->department_name]
                    + \Illuminate\Support\Arr::only($d->toArray(), $enabledFields['departments']))->values()->toArray(),
                'courses' => $org->organisationCourses->map(fn ($oc) => ['id' => $oc->id, 'academic_unit_name' => $oc->academic_unit_name ?: ($oc->course?->name ?? '')]
                    + \Illuminate\Support\Arr::only($oc->toArray(), $enabledFields['courses']))->values()->toArray(),
            ];
            // Run Scraper with scoped fields
            $extracted = $this->scraper->extractScopedUpdates(
                $request->input('url'),
                $typeTitle,
                $typeId,
                $enabledFields,
                $currentData
            );

            // Build human-friendly Old vs New diff
            $diff = $this->buildDiff($org, $extracted, $enabledFields, $typeId);

            return response()->json([
                'success' => true,
                'organisation_id' => $org->id,
                'diff' => $diff,
                'raw_extracted' => $extracted,
                'message' => 'Fetched data successfully. Please review the changes below.'
            ]);

        } catch (\Exception $e) {
            Log::error('Fetch Bot Updates Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compute a structured Diff (old value vs new value) for enabled fields
     */
    protected function buildDiff(Organisation $org, array $extracted, array $enabledFields, int $typeId): array
    {
        $orgLabels = $this->catalog->getFieldLabels('organisation', $typeId);
        $campusLabels = $this->catalog->getFieldLabels('campus');
        $deptLabels = $this->catalog->getFieldLabels('department');
        $courseLabels = $this->catalog->getFieldLabels('course', $typeId);

        $orgAllowed = $enabledFields['organisation'] ?? array_keys($orgLabels);
        $campusAllowed = $enabledFields['campuses'] ?? array_keys($campusLabels);
        $deptAllowed = $enabledFields['departments'] ?? array_keys($deptLabels);
        $courseAllowed = $enabledFields['courses'] ?? array_keys($courseLabels);

        // 1. Organisation Diff
        $orgDiff = [];
        $extractedOrg = $extracted['organisation'] ?? [];

        foreach ($orgAllowed as $fieldKey) {
            $label = $orgLabels[$fieldKey] ?? ucwords(str_replace('_', ' ', $fieldKey));
            $oldVal = $org->{$fieldKey} ?? null;
            $newVal = $extractedOrg[$fieldKey] ?? null;

            // Normalize for comparison
            $oldNorm = is_array($oldVal) ? json_encode($oldVal) : (string)($oldVal ?? '');
            $newNorm = is_array($newVal) ? json_encode($newVal) : (string)($newVal ?? '');

            $isNewData = ($oldNorm === '' && $newNorm !== '');
            $isChanged = ($oldNorm !== '' && $newNorm !== '' && trim($oldNorm) !== trim($newNorm));

            $status = 'unchanged';
            if ($isNewData) $status = 'new';
            elseif ($isChanged) $status = 'changed';

            $orgDiff[] = [
                'field_key' => $fieldKey,
                'label' => $label,
                'old_value' => $oldVal,
                'new_value' => $newVal,
                'status' => $status,
                'approved' => ($status === 'changed' || $status === 'new'),
            ];
        }

        // 2. Campuses Diff
        $campusesDiff = [];
        $existingCampuses = $org->campuses;
        $extractedCampuses = $extracted['campuses'] ?? [];

        foreach ($extractedCampuses as $index => $extCamp) {
            $campName = $extCamp['name'] ?? ($extCamp['campus_name'] ?? "Campus #" . ($index + 1));
            $matchedCamp = $existingCampuses->first(function ($c) use ($campName) {
                return strtolower(trim($c->campus_name)) === strtolower(trim($campName));
            });

            $fieldsDiff = [];
            foreach ($campusAllowed as $fKey) {
                $dbKey = ($fKey === 'name') ? 'campus_name' : $fKey;
                $label = $campusLabels[$fKey] ?? ucwords(str_replace('_', ' ', $fKey));
                $oldVal = $matchedCamp ? ($matchedCamp->{$dbKey} ?? null) : null;
                $newVal = $extCamp[$fKey] ?? ($extCamp[$dbKey] ?? null);

                $oldNorm = is_array($oldVal) ? json_encode($oldVal) : (string)($oldVal ?? '');
                $newNorm = is_array($newVal) ? json_encode($newVal) : (string)($newVal ?? '');

                $status = 'unchanged';
                if ($oldNorm === '' && $newNorm !== '') $status = 'new';
                elseif ($oldNorm !== '' && $newNorm !== '' && trim($oldNorm) !== trim($newNorm)) $status = 'changed';

                $fieldsDiff[] = [
                    'field_key' => $dbKey,
                    'label' => $label,
                    'old_value' => $oldVal,
                    'new_value' => $newVal,
                    'status' => $status,
                    'approved' => ($status === 'changed' || $status === 'new'),
                ];
            }

            $campusesDiff[] = [
                'id' => $matchedCamp ? $matchedCamp->id : null,
                'campus_name' => $campName,
                'is_new_campus' => !$matchedCamp,
                'fields' => $fieldsDiff,
            ];
        }

        // 3. Departments Diff (e.g. HOD: Atul -> HOD: Mukesh)
        $departmentsDiff = [];
        $existingDepts = $org->departments;
        $extractedDepts = $extracted['departments'] ?? [];

        foreach ($extractedDepts as $index => $extDept) {
            $deptName = $extDept['name'] ?? ($extDept['department_name'] ?? "Department #" . ($index + 1));
            $matchedDept = $existingDepts->first(function ($d) use ($deptName) {
                return strtolower(trim($d->department_name)) === strtolower(trim($deptName));
            });

            $fieldsDiff = [];
            foreach ($deptAllowed as $fKey) {
                $dbKey = ($fKey === 'name') ? 'department_name' : $fKey;
                $label = $deptLabels[$fKey] ?? ucwords(str_replace('_', ' ', $fKey));
                $oldVal = $matchedDept ? ($matchedDept->{$dbKey} ?? null) : null;
                $newVal = $extDept[$fKey] ?? ($extDept[$dbKey] ?? null);

                $oldNorm = is_array($oldVal) ? json_encode($oldVal) : (string)($oldVal ?? '');
                $newNorm = is_array($newVal) ? json_encode($newVal) : (string)($newVal ?? '');

                $status = 'unchanged';
                if ($oldNorm === '' && $newNorm !== '') $status = 'new';
                elseif ($oldNorm !== '' && $newNorm !== '' && trim($oldNorm) !== trim($newNorm)) $status = 'changed';

                $fieldsDiff[] = [
                    'field_key' => $dbKey,
                    'label' => $label,
                    'old_value' => $oldVal,
                    'new_value' => $newVal,
                    'status' => $status,
                    'approved' => ($status === 'changed' || $status === 'new'),
                ];
            }

            $departmentsDiff[] = [
                'id' => $matchedDept ? $matchedDept->id : null,
                'department_name' => $deptName,
                'is_new_department' => !$matchedDept,
                'fields' => $fieldsDiff,
            ];
        }

        // 4. Courses Diff
        $coursesDiff = [];
        $existingOrgCourses = $org->organisationCourses()->with('course')->get();
        $extractedCourses = $extracted['courses'] ?? [];

        foreach ($extractedCourses as $index => $extCourse) {
            $courseName = $extCourse['academic_unit_name'] ?? ($extCourse['name'] ?? "Course #" . ($index + 1));
            $matchedCourse = $existingOrgCourses->first(function ($oc) use ($courseName) {
                $curName = $oc->academic_unit_name ?: ($oc->course?->name ?? '');
                return strtolower(trim($curName)) === strtolower(trim($courseName));
            });

            $fieldsDiff = [];
            foreach ($courseAllowed as $fKey) {
                $label = $courseLabels[$fKey] ?? ucwords(str_replace('_', ' ', $fKey));
                $oldVal = $matchedCourse ? ($matchedCourse->{$fKey} ?? null) : null;
                $newVal = $extCourse[$fKey] ?? null;

                $oldNorm = is_array($oldVal) ? json_encode($oldVal) : (string)($oldVal ?? '');
                $newNorm = is_array($newVal) ? json_encode($newVal) : (string)($newVal ?? '');

                $status = 'unchanged';
                if ($oldNorm === '' && $newNorm !== '') $status = 'new';
                elseif ($oldNorm !== '' && $newNorm !== '' && trim($oldNorm) !== trim($newNorm)) $status = 'changed';

                $fieldsDiff[] = [
                    'field_key' => $fKey,
                    'label' => $label,
                    'old_value' => $oldVal,
                    'new_value' => $newVal,
                    'status' => $status,
                    'approved' => ($status === 'changed' || $status === 'new'),
                ];
            }

            $coursesDiff[] = [
                'id' => $matchedCourse ? $matchedCourse->id : null,
                'course_name' => $courseName,
                'is_new_course' => !$matchedCourse,
                'fields' => $fieldsDiff,
            ];
        }

        return [
            'organisation' => $orgDiff,
            'campuses' => $campusesDiff,
            'departments' => $departmentsDiff,
            'courses' => $coursesDiff,
        ];
    }

    /**
     * AJAX: Apply admin approved updates to the database atomically
     */
    public function applyUpdates(Request $request)
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'approved_updates' => 'required|array',
        ]);

        $target = Organisation::findOrFail($request->input('organisation_id'));
        $problems = $this->unavailableFields((int) $target->organisation_type_id, (array) $request->input('approved_updates'));
        if ($problems) {
            Log::warning('AI updater: apply refused for organisation ' . $target->id, ['blocked' => $problems]);
            return response()->json([
                'success' => false,
                'blocked' => $problems,
                'message' => 'Nothing was saved. The AI updater may only change fields that are ticked for this type, are real columns and are on the edit form staff use. Not allowed: ' . implode('; ', $problems),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $org = Organisation::findOrFail($request->input('organisation_id'));
            $updates = $request->input('approved_updates');
            $appliedCount = 0;

            // 1. Apply Organisation fields
            if (!empty($updates['organisation']) && is_array($updates['organisation'])) {
                $orgUpdateData = [];
                foreach ($updates['organisation'] as $fieldKey => $val) {
                    $orgUpdateData[$fieldKey] = $this->castAware($org, $fieldKey, $val);
                    $appliedCount++;
                }
                if (!empty($orgUpdateData)) {
                    // Every key was checked above, so write them all: update() would silently drop any that are not in $fillable while still counting them as applied.
                    $org->forceFill($orgUpdateData)->save();
                }
            }

            // 2. Apply Campuses fields
            if (!empty($updates['campuses']) && is_array($updates['campuses'])) {
                foreach ($updates['campuses'] as $campData) {
                    $campId = $campData['id'] ?? null;
                    $campName = $campData['campus_name'] ?? 'Main Campus';
                    $fields = $campData['fields'] ?? [];

                    if (empty($fields)) continue;

                    if ($campId) {
                        $campus = Campus::where('organisation_id', $org->id)->find($campId);
                    } else {
                        $campus = new Campus(['organisation_id' => $org->id, 'campus_name' => $campName]);
                    }

                    if ($campus) {
                        foreach ($fields as $k => $v) {
                            $campus->{$k} = $this->castAware($campus, $k, $v);
                            $appliedCount++;
                        }
                        $campus->save();
                    }
                }
            }

            // 3. Apply Departments fields (e.g. HOD: Mukesh)
            if (!empty($updates['departments']) && is_array($updates['departments'])) {
                foreach ($updates['departments'] as $deptData) {
                    $deptId = $deptData['id'] ?? null;
                    $deptName = $deptData['department_name'] ?? 'Department';
                    $fields = $deptData['fields'] ?? [];

                    if (empty($fields)) continue;

                    if ($deptId) {
                        $dept = Department::where('organisation_id', $org->id)->find($deptId);
                    } else {
                        $dept = new Department(['organisation_id' => $org->id, 'department_name' => $deptName]);
                    }

                    if ($dept) {
                        foreach ($fields as $k => $v) {
                            $dept->{$k} = $this->castAware($dept, $k, $v);
                            $appliedCount++;
                        }
                        $dept->save();
                    }
                }
            }

            // 4. Apply Courses fields
            if (!empty($updates['courses']) && is_array($updates['courses'])) {
                foreach ($updates['courses'] as $courseData) {
                    $ocId = $courseData['id'] ?? null;
                    $courseName = $courseData['course_name'] ?? 'Course';
                    $fields = $courseData['fields'] ?? [];

                    if (empty($fields)) continue;

                    if ($ocId) {
                        $oc = OrganisationCourse::where('organisation_id', $org->id)->find($ocId);
                    } else {
                        $masterCourse = $this->importer->resolveMasterCourse(null, $courseName);
                        $oc = new OrganisationCourse([
                            'organisation_id' => $org->id,
                            'academic_unit_name' => $courseName,
                            'course_id' => $masterCourse?->id
                        ]);
                    }

                    if ($oc) {
                        foreach ($fields as $k => $v) {
                            $oc->{$k} = $this->castAware($oc, $k, $v);
                            $appliedCount++;
                        }
                        $oc->save();
                    }
                }
            }

            DB::commit();

            $successMsg = "Successfully updated {$appliedCount} field(s) for {$org->name}!";
            session()->flash('success', $successMsg);

            return response()->json([
                'success' => true,
                'applied_count' => $appliedCount,
                'message' => $successMsg
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Apply Bot Updates Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error applying updates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * An array value is stored as JSON text, except for attributes the model already casts to array/json: the cast encodes it itself,
     * and encoding twice would store a JSON string inside a JSON string.
     */
    protected function castAware($model, string $key, $value)
    {
        if (!is_array($value)) return $value;
        return $model->hasCast($key, ['array', 'json', 'object', 'collection']) ? $value : json_encode($value);
    }

    /**
     * Every field in an apply request that the policy does not allow, as readable strings ("Campus: library_available (Not on the edit form...)").
     *
     * @return string[]
     */
    protected function unavailableFields(int $typeId, array $updates): array
    {
        $problems = [];
        $label = ['organisation' => 'Organisation', 'campus' => 'Campus', 'department' => 'Department', 'course' => 'Programme'];
        $check = function (string $level, array $keys) use (&$problems, $typeId, $label) {
            foreach ($this->policy->blocked($level, $typeId, $keys) as $key => $reason) $problems[] = "{$label[$level]}: $key ($reason)";
        };
        if (!empty($updates['organisation']) && is_array($updates['organisation'])) $check('organisation', array_map('strval', array_keys($updates['organisation'])));
        foreach (['campuses' => 'campus', 'departments' => 'department', 'courses' => 'course'] as $plural => $level) {
            foreach ((array) ($updates[$plural] ?? []) as $item) {
                if (is_array($item) && !empty($item['fields']) && is_array($item['fields'])) $check($level, array_map('strval', array_keys($item['fields'])));
            }
        }
        return array_values(array_unique($problems));
    }
}
