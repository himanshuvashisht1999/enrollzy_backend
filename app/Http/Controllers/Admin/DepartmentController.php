<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Organisation;
use App\Models\Campus;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $organisationId = $request->query('organisation_id');
        $campusId = $request->query('campus_id');

        $query = Department::with(['organisation', 'campus'])->latest();

        if ($organisationId) {
            $query->where('organisation_id', $organisationId);
        }

        if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        $departments = $query->paginate(10);

        $organisation = $organisationId ? Organisation::find($organisationId) : null;
        $campus = $campusId ? Campus::find($campusId) : null;

        return view('admin.departments.index', compact('departments', 'organisation', 'campus'));
    }

    public function create(Request $request)
    {
        $organisationId = $request->query('organisation_id');
        $campusId = $request->query('campus_id');

        $organisations = Organisation::select('id', 'name')->where('status', 1)->get();
        // Campuses will be loaded via AJAX based on Organisation selection, but if we have campusId we can preload relevant content or handle in view

        $selectedOrganisation = $organisationId ? Organisation::find($organisationId) : null;
        $selectedCampus = $campusId ? Campus::find($campusId) : null;
        $campuses = $organisationId ? Campus::where('organisation_id', $organisationId)->get() : collect();

        return view('admin.departments.create', compact('organisations', 'selectedOrganisation', 'selectedCampus', 'campuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'campus_id' => 'required|exists:campuses,id',
            'department_name' => 'required|string|max:255',
            'department_code' => 'nullable|string|max:50',
            'department_type' => 'required|in:Academic,Clinical,Research,Interdisciplinary',
            'slug' => 'nullable|string|unique:departments,slug',
            // Boolean fields (nullable or boolean)
            'is_interdisciplinary' => 'sometimes',
            'curriculum_design_responsibility' => 'sometimes',
            'exam_setting_responsibility' => 'sometimes',
            'research_programs_managed' => 'sometimes',
            'phd_supervision_available' => 'sometimes',
            'industry_collaboration_supported' => 'sometimes',
            'specialized_labs_available' => 'sometimes',
            'department_library_section' => 'sometimes',

            // Numeric validations
            'confidence_score' => 'nullable|numeric|min:0|max:100',
            'public_trust_score' => 'nullable|integer|min:0|max:100',
            'established_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'faculty_count' => 'nullable|integer|min:0',
            'department_labs_count' => 'nullable|string|max:255',
            'research_centers_under_department' => 'nullable|string|max:255',
            'classrooms_count' => 'nullable|string|max:255',
            'research_publications_count' => 'nullable|integer|min:0',
            'college_reviews' => 'nullable|array',
            'college_reviews.*.infrastructure' => 'nullable|numeric|min:0|max:5',
            'college_reviews.*.campus_life' => 'nullable|numeric|min:0|max:5',
            'college_reviews.*.academics' => 'nullable|numeric|min:0|max:5',
            'college_reviews.*.placements' => 'nullable|numeric|min:0|max:5',
            'college_reviews.*.value_for_money' => 'nullable|numeric|min:0|max:5',
            'placement_statistics' => 'nullable|array',
            'placement_statistics.*.year' => 'nullable|integer',
            'placement_statistics.*.dept_students_placed' => 'nullable|integer|min:0',
            'placement_statistics.*.dept_graduating_students' => 'nullable|integer|min:0',
            'placement_statistics.*.dept_placement_percentage' => 'nullable|numeric|min:0|max:100',
            'placement_statistics.*.dept_median_salary' => 'nullable|string|max:255',
            'placement_statistics.*.dept_higher_studies' => 'nullable|integer|min:0',
            'placement_statistics.*.overall_graduating_students' => 'nullable|integer|min:0',
            'placement_statistics.*.overall_students_placed' => 'nullable|integer|min:0',
            'placement_statistics.*.overall_placement_percentage' => 'nullable|numeric|min:0|max:100',
            'placement_statistics.*.overall_median_salary' => 'nullable|string|max:255',
            'placement_statistics.*.overall_higher_studies' => 'nullable|integer|min:0',
            'funded_projects_count' => 'nullable|integer|min:0',
            'patents_filed_count' => 'nullable|integer|min:0',
            'industry_projects_count' => 'nullable|integer|min:0',
        ]);

        // Handle array inputs
        $arrayInputs = [
            'specializations_supported',
            'education_levels_supported',
            'collaborating_departments',
            'online_meeting_tools_used',
            'focus_keywords'
        ];

        foreach ($arrayInputs as $input) {
            if ($request->has($input) && !is_array($request->input($input))) {
                // If it's a comma-separated string, explode it
                $request->merge([$input => array_map('trim', explode(',', $request->input($input)))]);
            }
        }

        // Handle boolean fields
        $booleanFields = [
            'is_interdisciplinary',
            'curriculum_design_responsibility',
            'exam_setting_responsibility',
            'research_programs_managed',
            'phd_supervision_available',
            'industry_collaboration_supported',
            'specialized_labs_available',
            'department_library_section'
        ];

        foreach ($booleanFields as $field) {
            $request->merge([$field => $request->has($field) ? 1 : 0]);
        }

        $department = Department::create($request->all());

        return redirect()->route('admin.departments.index', [
            'organisation_id' => $department->organisation_id,
            'campus_id' => $department->campus_id
        ])->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $organisations = Organisation::select('id', 'name')->where('status', 1)->get();
        $campuses = Campus::where('organisation_id', $department->organisation_id)->get();
        return view('admin.departments.edit', compact('department', 'organisations', 'campuses'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'campus_id' => 'required|exists:campuses,id',
            'department_name' => 'required|string|max:255',
            'department_code' => 'nullable|string|max:50',
            'department_type' => 'required|in:Academic,Clinical,Research,Interdisciplinary',
            'slug' => 'nullable|string|unique:departments,slug,' . $department->id,
            // Boolean fields (nullable or boolean)
            'is_interdisciplinary' => 'sometimes',
            'curriculum_design_responsibility' => 'sometimes',
            'exam_setting_responsibility' => 'sometimes',
            'research_programs_managed' => 'sometimes',
            'phd_supervision_available' => 'sometimes',
            'industry_collaboration_supported' => 'sometimes',
            'specialized_labs_available' => 'sometimes',
            'department_library_section' => 'sometimes',

            // Numeric validations
            'confidence_score' => 'nullable|numeric|min:0|max:100',
            'public_trust_score' => 'nullable|integer|min:0|max:100',
            'established_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'faculty_count' => 'nullable|integer|min:0',
            'department_labs_count' => 'nullable|string|max:255',
            'research_centers_under_department' => 'nullable|string|max:255',
            'classrooms_count' => 'nullable|string|max:255',
            'research_publications_count' => 'nullable|integer|min:0',
            'college_reviews' => 'nullable|array',
            'college_reviews.*.infrastructure' => 'nullable|numeric|min:0|max:5',
            'college_reviews.*.campus_life' => 'nullable|numeric|min:0|max:5',
            'college_reviews.*.academics' => 'nullable|numeric|min:0|max:5',
            'college_reviews.*.placements' => 'nullable|numeric|min:0|max:5',
            'college_reviews.*.value_for_money' => 'nullable|numeric|min:0|max:5',
            'placement_statistics' => 'nullable|array',
            'placement_statistics.*.year' => 'nullable|integer',
            'placement_statistics.*.dept_students_placed' => 'nullable|integer|min:0',
            'placement_statistics.*.dept_graduating_students' => 'nullable|integer|min:0',
            'placement_statistics.*.dept_placement_percentage' => 'nullable|numeric|min:0|max:100',
            'placement_statistics.*.dept_median_salary' => 'nullable|string|max:255',
            'placement_statistics.*.dept_higher_studies' => 'nullable|integer|min:0',
            'placement_statistics.*.overall_graduating_students' => 'nullable|integer|min:0',
            'placement_statistics.*.overall_students_placed' => 'nullable|integer|min:0',
            'placement_statistics.*.overall_placement_percentage' => 'nullable|numeric|min:0|max:100',
            'placement_statistics.*.overall_median_salary' => 'nullable|string|max:255',
            'placement_statistics.*.overall_higher_studies' => 'nullable|integer|min:0',
            'funded_projects_count' => 'nullable|integer|min:0',
            'patents_filed_count' => 'nullable|integer|min:0',
            'industry_projects_count' => 'nullable|integer|min:0',
        ]);

        // Handle array inputs
        $arrayInputs = [
            'specializations_supported',
            'education_levels_supported',
            'collaborating_departments',
            'online_meeting_tools_used',
            'focus_keywords'
        ];

        foreach ($arrayInputs as $input) {
            if ($request->has($input) && !is_array($request->input($input))) {
                $request->merge([$input => array_map('trim', explode(',', $request->input($input)))]);
            }
        }

        // Handle boolean fields
        $booleanFields = [
            'is_interdisciplinary',
            'curriculum_design_responsibility',
            'exam_setting_responsibility',
            'research_programs_managed',
            'phd_supervision_available',
            'industry_collaboration_supported',
            'specialized_labs_available',
            'department_library_section'
        ];

        foreach ($booleanFields as $field) {
            $request->merge([$field => $request->has($field) ? 1 : 0]);
        }

        $data = $request->all();

        // Handle slug: if empty, let model handle it (or use existing one)
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            unset($data['slug']);
        }

        $department->update($data);

        return redirect()->route('admin.departments.index', [
            'organisation_id' => $department->organisation_id,
            'campus_id' => $department->campus_id
        ])->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $orgId = $department->organisation_id;
        $campusId = $department->campus_id;
        $department->delete();

        return redirect()->route('admin.departments.index', [
            'organisation_id' => $orgId,
            'campus_id' => $campusId
        ])->with('success', 'Department deleted successfully.');
    }

    public function storeDraft(Request $request)
    {
        $request->validate([
            'organisation_id' => 'required',
            'campus_id' => 'required',
            'department_name' => 'required',
        ]);

        $data = $request->only(['organisation_id', 'campus_id', 'department_name', 'department_type', 'slug']);
        $data['status'] = $data['status'] ?? 'Active'; // Default to Active

        // Handle slug: if empty, let model auto-generate
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            unset($data['slug']);
        }

        $department = Department::create($data);

        return response()->json([
            'success' => true,
            'department_id' => $department->id,
            'message' => 'Draft created successfully'
        ]);
    }

    public function autosaveTab(Request $request, Department $department)
    {
        $data = $request->all();

        // Handle slug: if empty, don't update it to avoid null constraint violation
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            unset($data['slug']);
        }

        // Handle boolean/switch fields
        $booleanFields = [
            'is_interdisciplinary',
            'curriculum_design_responsibility',
            'exam_setting_responsibility',
            'research_programs_managed',
            'phd_supervision_available',
            'industry_collaboration_supported',
            'specialized_labs_available',
            'department_library_section'
        ];

        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field) ? 1 : 0;
            }
        }

        // Handle numeric/integer fields to ensure they don't break if empty
        $nonNullableNumerics = [
            'faculty_count',
            'research_publications_count',
            'funded_projects_count',
            'patents_filed_count',
            'industry_projects_count',
            'department_labs_count',
            'research_centers_under_department',
            'classrooms_count'
        ];

        foreach ($nonNullableNumerics as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field) ?: 0;
            }
        }

        $nullableNumerics = [
            'established_year',
            'confidence_score'
        ];

        foreach ($nullableNumerics as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field) ?: null;
            }
        }

        \Log::info("Autosave Data: ", $data);
        $department->update($data);

        return response()->json(['success' => true]);
    }
}
