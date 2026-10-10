<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationCourse;
use App\Models\OrganisationSchoolCourse;
use App\Models\Course;
use App\Models\OrganisationCourseDocument;
use Illuminate\Support\Str;
use App\Models\Exam; // Import Exam
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganisationCourseController extends Controller
{
    public function index(Request $request)
    {
        $organisationId = $request->query('organisation_id');
        $campusId = $request->query('campus_id');
        $departmentId = $request->query('department_id');

        $organisation = Organisation::with('organisationType')->findOrFail($organisationId);

        $query = $organisation->courses()->with(['course', 'campus', 'department', 'entranceExam'])->orderBy('sort_order', 'asc');

        if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $courses = $query->paginate(10);
        $organisationTypeId = $organisation->organisation_type_id;

        // Populate schoolCourses if it's a school to maintain view compatibility
        $schoolCourses = ($organisationTypeId == 4) ? $courses : collect();

        $department = $departmentId ? \App\Models\Department::find($departmentId) : null;
        $campus = $campusId ? \App\Models\Campus::find($campusId) : null;
        $allSpecializations = \App\Models\Specialization::pluck('title', 'id');
        $allCampuses = $organisation->campuses()->where('status', true)->orderBy('campus_name')->get();
        $allDepartments = $organisation->departments()->where('status', 'Active')->orderBy('department_name')->get();

        return view('admin.organisation-courses.index', compact('organisation', 'courses', 'schoolCourses', 'campusId', 'departmentId', 'organisationTypeId', 'department', 'campus', 'allSpecializations', 'allCampuses', 'allDepartments'));
    }

    public function create(Request $request)
    {
        $organisationId = $request->query('organisation_id');
        $campusId = $request->query('campus_id');
        $departmentId = $request->query('department_id');

        $organisation = Organisation::findOrFail($organisationId);

        $masterCourses = Course::with(['programLevel', 'streamOffered', 'discipline'])->where('status', 1)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
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
            'campus_id' => 'nullable|exists:campuses,id',
            'department_id' => 'nullable|exists:departments,id',
            'course_languages' => 'nullable|array',
            'entrance_exam_ids' => 'nullable|array',
            'entrance_exam_category' => 'nullable|string|max:255',
            'specialization_ids' => 'nullable|array',
            'total_fees' => 'nullable|numeric',
            'documents' => 'nullable|array',
            'documents.*.title' => 'nullable|string|max:255',
            'documents.*.file' => 'nullable|file|mimes:pdf|max:20480',
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
        } elseif ($typeId == 9) {
            // E-Learning Platform: Direct Course
            $rules['course_id'] = 'required|exists:courses,id';
            $rules['sort_order'] = 'required|integer';
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

        unset($data['documents'], $data['has_documents_form']);

        $course = OrganisationCourse::create($data);
        $this->syncDocuments($request, $course);

        return redirect()->route('admin.organisation-courses.index', [
            'organisation_id' => $request->organisation_id,
            'campus_id' => $request->campus_id,
            'department_id' => $request->department_id
        ])->with('success', 'Created successfully.');
    }

    public function edit(Request $request, OrganisationCourse $organisationCourse)
    {
        $organisation = $organisationCourse->organisation;
        $organisationCourse->load('documents');

        $masterCourses = Course::with(['programLevel', 'streamOffered', 'discipline'])->where('status', 1)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
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

        $campusId = $request->query('campus_id');
        $departmentId = $request->query('department_id');

        return view('admin.organisation-courses.edit', compact(
            'organisationCourse',
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

    public function update(Request $request, OrganisationCourse $organisationCourse)
    {
        $organisation = $organisationCourse->organisation;
        $typeId = $organisation->organisation_type_id;

        // Base Rules
        $rules = [
            'status' => 'boolean',
            'campus_id' => 'nullable|exists:campuses,id',
            'department_id' => 'nullable|exists:departments,id',
            'course_languages' => 'nullable|array',
            'entrance_exam_ids' => 'nullable|array',
            'entrance_exam_category' => 'nullable|string|max:255',
            'specialization_ids' => 'nullable|array',
            'total_fees' => 'nullable|numeric',
            'documents' => 'nullable|array',
            'documents.*.title' => 'nullable|string|max:255',
            'documents.*.file' => 'nullable|file|mimes:pdf|max:20480',
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
        } elseif ($typeId == 9) {
            $rules['course_id'] = 'required|exists:courses,id';
            $rules['sort_order'] = 'required|integer';
        }

        $request->validate($rules);

        $data = $request->all();

        // Multi-select fields: when user removes ALL selections, browser sends nothing.
        // Explicitly set to null so the DB value is cleared.
        foreach (['specialization_ids', 'entrance_exam_ids', 'course_languages', 'streams_offered'] as $field) {
            if (!$request->has($field)) {
                $data[$field] = null;
            }
        }

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

        unset($data['documents'], $data['has_documents_form']);

        $organisationCourse->update($data);
        $this->syncDocuments($request, $organisationCourse);

        return redirect()->route('admin.organisation-courses.index', [
            'organisation_id' => $organisation->id,
            'campus_id' => $request->campus_id,
            'department_id' => $request->department_id
        ])->with('success', 'Updated successfully.');
    }

    public function destroy(OrganisationCourse $organisationCourse)
    {
        $organisationId = $organisationCourse->organisation_id;
        $campusId = $organisationCourse->campus_id;
        $departmentId = $organisationCourse->department_id;
        $organisationCourse->delete();

        return redirect()->route('admin.organisation-courses.index', [
            'organisation_id' => $organisationId,
            'campus_id' => $campusId,
            'department_id' => $departmentId
        ])->with('success', 'Deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        if ($request->boolean('delete_all_for_department') && $request->filled('department_id')) {
            $departmentId = $request->input('department_id');
            $count = OrganisationCourse::where('department_id', $departmentId)->count();

            if ($count === 0) {
                return redirect()->back()->with('warning', 'No courses found to delete for this department.');
            }

            DB::transaction(function () use ($departmentId) {
                OrganisationCourse::where('department_id', $departmentId)->delete();
            });

            return redirect()->back()->with('success', "All {$count} course(s) in this department were deleted successfully.");
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required',
        ]);

        $ids = $request->input('ids', []);
        $count = count($ids);

        if ($count === 0) {
            return redirect()->back()->with('warning', 'No courses selected to delete.');
        }

        DB::transaction(function () use ($ids) {
            OrganisationCourse::whereIn('id', $ids)->delete();
            if (\Illuminate\Support\Facades\Schema::hasTable('organisation_school_courses')) {
                \Illuminate\Support\Facades\DB::table('organisation_school_courses')->whereIn('id', $ids)->delete();
            }
        });

        return redirect()->back()->with('success', "{$count} course(s) deleted successfully.");
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

        return redirect()->route('admin.organisation-courses.index', [
            'organisation_id' => $originalCourse->organisation_id,
            'campus_id' => $originalCourse->campus_id,
            'department_id' => $originalCourse->department_id
        ])->with('success', 'Course duplicated successfully.');
    }

    protected function syncDocuments(Request $request, OrganisationCourse $organisationCourse): void
    {
        $documents = $request->input('documents', []);
        $keptIds = [];
        $docDir = public_path('media/organisation_courses/documents');

        if (!file_exists($docDir)) {
            @mkdir($docDir, 0777, true);
        }

        if (is_array($documents)) {
            $order = 0;
            foreach ($documents as $index => $docData) {
                $docId = $docData['id'] ?? null;
                $title = isset($docData['title']) ? trim((string)$docData['title']) : '';
                $uploadedFile = $request->file("documents.{$index}.file");

                if (!empty($docId)) {
                    $existingDoc = $organisationCourse->documents()->where('id', $docId)->first();
                    if ($existingDoc) {
                        $existingDoc->title = !empty($title) ? $title : ($existingDoc->title ?: 'Document');
                        $existingDoc->sort_order = $order++;

                        if ($uploadedFile && $uploadedFile->isValid()) {
                            if ($existingDoc->file_path && file_exists(public_path($existingDoc->file_path))) {
                                @unlink(public_path($existingDoc->file_path));
                            }

                            $origName = $uploadedFile->getClientOriginalName();
                            $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
                            $uploadedFile->move($docDir, $fileName);
                            $savedPath = 'media/organisation_courses/documents/' . $fileName;

                            $existingDoc->file_path = $savedPath;
                            $existingDoc->file_name = $origName;
                            $existingDoc->file_size = @filesize(public_path($savedPath)) ?: null;
                        }

                        $existingDoc->save();
                        $keptIds[] = $existingDoc->id;
                    }
                } else {
                    if ($uploadedFile && $uploadedFile->isValid()) {
                        $origName = $uploadedFile->getClientOriginalName();
                        $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
                        $uploadedFile->move($docDir, $fileName);
                        $savedPath = 'media/organisation_courses/documents/' . $fileName;

                        $newDoc = $organisationCourse->documents()->create([
                            'title' => !empty($title) ? $title : pathinfo($origName, PATHINFO_FILENAME),
                            'file_path' => $savedPath,
                            'file_name' => $origName,
                            'file_size' => @filesize(public_path($savedPath)) ?: null,
                            'sort_order' => $order++,
                        ]);
                        $keptIds[] = $newDoc->id;
                    }
                }
            }
        }

        if ($request->has('has_documents_form')) {
            $organisationCourse->documents()->whereNotIn('id', $keptIds)->get()->each(function ($doc) {
                if ($doc->file_path && file_exists(public_path($doc->file_path))) {
                    @unlink(public_path($doc->file_path));
                }
                $doc->delete();
            });
        }
    }

    public function destroyDocument(OrganisationCourse $organisationCourse, OrganisationCourseDocument $document)
    {
        if ($document->organisation_course_id != $organisationCourse->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document does not belong to this course.'
            ], 403);
        }

        if ($document->file_path && file_exists(public_path($document->file_path))) {
            @unlink(public_path($document->file_path));
        }

        $document->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Document deleted successfully.'
        ]);
    }
}
