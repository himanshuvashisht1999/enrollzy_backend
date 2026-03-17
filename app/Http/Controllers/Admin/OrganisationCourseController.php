<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationCourse;
use App\Models\OrganisationSchoolCourse;
use App\Models\Course;
use Illuminate\Support\Str;
use App\Models\Exam; // Import Exam
use Illuminate\Http\Request;

class OrganisationCourseController extends Controller
{
    public function index(Request $request)
    {
        $organisationId = $request->query('organisation_id');
        $campusId = $request->query('campus_id');
        $departmentId = $request->query('department_id');

        $organisation = Organisation::with('organisationType')->findOrFail($organisationId);

        $query = $organisation->courses()->with(['course', 'campus', 'department', 'entranceExam']);

        if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $courses = $query->latest()->paginate(10);
        $organisationTypeId = $organisation->organisation_type_id;

        // Populate schoolCourses if it's a school to maintain view compatibility
        $schoolCourses = ($organisationTypeId == 4) ? $courses : collect();

        $department = $departmentId ? \App\Models\Department::find($departmentId) : null;
        $campus = $campusId ? \App\Models\Campus::find($campusId) : null;

        return view('admin.organisation-courses.index', compact('organisation', 'courses', 'schoolCourses', 'campusId', 'departmentId', 'organisationTypeId', 'department', 'campus'));
    }

    public function create(Request $request)
    {
        $organisationId = $request->query('organisation_id');
        $campusId = $request->query('campus_id');
        $departmentId = $request->query('department_id');

        $organisation = Organisation::findOrFail($organisationId);

        $masterCourses = Course::with(['programLevel', 'streamOffered', 'discipline'])->where('status', 1)->orderBy('name')->get();
        $specializations = \App\Models\Specialization::where('status', true)->get();
        $languages = \App\Models\Language::where('status', true)->orderBy('sort_order')->get();
        $campuses = $organisation->campuses()->where('status', true)->get();
        $departments = $organisation->departments()->with('campus')->where('status', 'Active')->get();

        // Master Data for School/Institute
        $examCategories = Exam::whereNotNull('exam_category')->get()
            ->pluck('exam_category')
            ->flatten()
            ->unique()
            ->filter()
            ->values();
        $exams = Exam::select('id', 'name', 'exam_category')->orderBy('name')->get();

        return view('admin.organisation-courses.create', compact(
            'organisation',
            'masterCourses',
            'specializations',
            'languages',
            'campuses',
            'exams',
            'examCategories',
            'departments',
            'campusId',
            'departmentId'
        ));
    }

    public function store(Request $request)
    {
        $organisation = Organisation::findOrFail($request->organisation_id);
        $typeId = $organisation->organisation_type_id;

        // Base Rules
        $rules = [
            'organisation_id' => 'required|exists:organisations,id',
            'status' => 'boolean',
            'campus_id' => ($typeId == 1 || $typeId == 2) ? 'required|exists:campuses,id' : 'nullable|exists:campuses,id',
            'department_id' => 'nullable|exists:departments,id',
            'course_languages' => 'nullable|array',
            'entrance_exam_ids' => 'nullable|array',
            'total_fees' => 'nullable|numeric',
        ];

        // Type Specific Rules
        if (in_array($typeId, [1, 2])) {
            // University / College
            $rules['course_id'] = 'required|exists:courses,id';
            $rules['sort_order'] = 'required|integer';
        } elseif ($typeId == 3) {
            // Institute
            $rules['academic_unit_name'] = 'required|string|max:255';
            $rules['course_id'] = 'required|exists:courses,id';
        } elseif ($typeId == 4) {
            // School
            $rules['academic_unit_name'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $data = $request->all();

        // Boolean handling
        $booleans = [
            'status',
            'provisional_admission',
            'special_educator_available',
            'school_counsellor_available',
            'remedial_classes_available',
            'olympiad_participation',
            'competitive_exam_preparation_support',
            'parent_app_available',
            'attendance_tracking_available',
            'arts_music_programs_available',
            'integrated_schooling_available',
            'separate_batches_for_droppers',
            'merit_based_batching',
            'visiting_faculty_available',
            'personal_mentorship_available',
            'extra_classes_for_weak_students',
            'parent_counselling_available',
            'dpp_provided',
            'test_series_available',
            'online_test_platform_available',
            'installment_available',
            'scholarship_available',
            'refund_policy_available',
            'verified_reviews_only',
            'transport_fee',
            'hostel_fee'
        ];

        foreach ($booleans as $field) {
            $data[$field] = $request->has($field);
        }

        // Slug generation if not provided
        if (empty($data['slug']) && !empty($data['academic_unit_name'])) {
            $data['slug'] = Str::slug($data['academic_unit_name']);
        }

        OrganisationCourse::create($data);

        return redirect()->route('admin.organisation-courses.index', ['organisation_id' => $request->organisation_id])
            ->with('success', 'Created successfully.');
    }

    public function edit(OrganisationCourse $organisationCourse)
    {
        $organisation = $organisationCourse->organisation;

        $masterCourses = Course::with(['programLevel', 'streamOffered', 'discipline'])->where('status', 1)->orderBy('name')->get();
        $specializations = \App\Models\Specialization::where('status', true)->get();
        $languages = \App\Models\Language::where('status', true)->orderBy('sort_order')->get();
        $campuses = $organisation->campuses()->where('status', true)->get();
        $departments = $organisation->departments()->with('campus')->where('status', 'Active')->get();

        $examCategories = Exam::whereNotNull('exam_category')->get()
            ->pluck('exam_category')
            ->flatten()
            ->unique()
            ->filter()
            ->values();
        $exams = Exam::select('id', 'name', 'exam_category')->orderBy('name')->get();

        return view('admin.organisation-courses.edit', compact(
            'organisationCourse',
            'organisation',
            'masterCourses',
            'specializations',
            'languages',
            'campuses',
            'exams',
            'examCategories',
            'departments'
        ));
    }

    public function update(Request $request, OrganisationCourse $organisationCourse)
    {
        $organisation = $organisationCourse->organisation;
        $typeId = $organisation->organisation_type_id;

        // Base Rules
        $rules = [
            'status' => 'boolean',
            'campus_id' => ($typeId == 1 || $typeId == 2) ? 'required|exists:campuses,id' : 'nullable|exists:campuses,id',
            'department_id' => 'nullable|exists:departments,id',
            'course_languages' => 'nullable|array',
            'entrance_exam_ids' => 'nullable|array',
            'total_fees' => 'nullable|numeric',
        ];

        // Type Specific Rules
        if (in_array($typeId, [1, 2])) {
            $rules['course_id'] = 'required|exists:courses,id';
            $rules['sort_order'] = 'required|integer';
        } elseif ($typeId == 3) {
            $rules['academic_unit_name'] = 'required|string|max:255';
            $rules['course_id'] = 'required|exists:courses,id';
        } elseif ($typeId == 4) {
            $rules['academic_unit_name'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $data = $request->all();

        // Boolean handling
        $booleans = [
            'status',
            'provisional_admission',
            'special_educator_available',
            'school_counsellor_available',
            'remedial_classes_available',
            'olympiad_participation',
            'competitive_exam_preparation_support',
            'parent_app_available',
            'attendance_tracking_available',
            'arts_music_programs_available',
            'integrated_schooling_available',
            'separate_batches_for_droppers',
            'merit_based_batching',
            'visiting_faculty_available',
            'personal_mentorship_available',
            'extra_classes_for_weak_students',
            'parent_counselling_available',
            'dpp_provided',
            'test_series_available',
            'online_test_platform_available',
            'installment_available',
            'scholarship_available',
            'refund_policy_available',
            'verified_reviews_only',
            'transport_fee',
            'hostel_fee'
        ];

        foreach ($booleans as $field) {
            $data[$field] = $request->has($field);
        }

        if (empty($data['slug']) && !empty($data['academic_unit_name'])) {
            $data['slug'] = Str::slug($data['academic_unit_name']);
        }

        $organisationCourse->update($data);

        return redirect()->route('admin.organisation-courses.index', ['organisation_id' => $organisation->id])
            ->with('success', 'Updated successfully.');
    }

    public function destroy(OrganisationCourse $organisationCourse)
    {
        $organisationId = $organisationCourse->organisation_id;
        $organisationCourse->delete();

        return redirect()->route('admin.organisation-courses.index', ['organisation_id' => $organisationId])
            ->with('success', 'Deleted successfully.');
    }

    public function duplicate($id)
    {
        $originalCourse = OrganisationCourse::findOrFail($id);

        $newCourse = $originalCourse->replicate();

        // Handle slug uniqueness
        if ($originalCourse->slug) {
            $newCourse->slug = Str::slug($originalCourse->slug . '-copy-' . time());
        }

        $newCourse->save();

        return redirect()->route('admin.organisation-courses.index', ['organisation_id' => $originalCourse->organisation_id])
            ->with('success', 'Course duplicated successfully.');
    }
}
