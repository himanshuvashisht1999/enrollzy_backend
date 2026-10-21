<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseDocument;
use App\Services\GeminiCourseBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = $this->filtered($request)->with(['discipline', 'programLevel', 'streamOffered', 'courseType']);

        // ---- order ----
        match ($request->sort) {
            'name' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'newest' => $query->latest(),
            'oldest' => $query->oldest(),
            default => $query->orderBy('sort_order')->latest(),
        };

        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100], true) ? (int) $request->per_page : 10;
        $courses = $query->paginate($perPage)->withQueryString();

        $filters = [
            'programLevels' => \App\Models\ProgramLevel::orderBy('sort_order')->orderBy('title')->get(['id', 'title']),
            'streams' => \App\Models\StreamOffered::orderBy('title')->get(['id', 'title']),
            'disciplines' => \App\Models\Discipline::orderBy('title')->get(['id', 'title']),
            'courseTypes' => \App\Models\CourseType::orderBy('sort_order')->orderBy('title')->get(['id', 'title']),
            'programTypes' => \App\Models\ProgramType::orderBy('sort_order')->orderBy('title')->get(['id', 'title']),
        ];
        $activeFilters = collect($request->only(self::FILTERS))->filter(fn ($v) => $v !== null && $v !== '')->count();

        return view('admin.courses.index', compact('courses', 'filters', 'activeFilters'));
    }

    /** Filter parameters of the list (also carried by "select all matching" in the bulk delete). */
    private const FILTERS = ['search', 'program_level_id', 'stream_offered_id', 'discipline_id', 'course_type_id', 'program_type_id', 'status', 'website'];

    /** Tables whose rows are deleted together with a course (database ON DELETE CASCADE): what a bulk delete takes with it. */
    private const LINKED = [
        'organisation_courses' => 'college programmes (courses offered by organisations)',
        'admission_routes' => 'admission routes',
        'scholarship_courses' => 'scholarship links',
        'faculty_course_mapping' => 'faculty links',
        'course_program_type' => 'program type links',
        'course_documents' => 'documents',
    ];

    /** The course list query with the list's filters applied (no order, no paging). */
    private function filtered(Request $request)
    {
        $query = Course::query();

        // ---- filters ----
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('full_form', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        foreach (['program_level_id', 'stream_offered_id', 'discipline_id', 'course_type_id'] as $field) {
            if ($request->filled($field)) {
                $request->input($field) === 'none'
                    ? $query->where(fn ($q) => $q->whereNull($field)->orWhere($field, ''))
                    : $query->where($field, $request->input($field));
            }
        }
        if ($request->filled('program_type_id')) {
            $query->whereHas('programTypes', fn ($q) => $q->where('program_types.id', $request->program_type_id));
        }
        if (in_array($request->status, ['1', '0'], true)) {
            $query->where('status', (int) $request->status);
        }
        if (in_array($request->website, ['1', '0'], true)) {
            $query->where('is_show_on_website', (int) $request->website);
        }
        return $query;
    }

    /**
     * Bulk delete, step 1: show exactly which courses will be deleted and what is deleted with them (college programmes,
     * admission routes... are removed by the database together with the course). Nothing is deleted here.
     * Takes either the ticked ids, or all_matching=1 with the list's filters.
     */
    public function bulkReview(Request $request)
    {
        $ids = $request->boolean('all_matching')
            ? $this->filtered($request)->pluck('id')->all()
            : collect((array) $request->input('ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        $back = route('admin.courses.index', $request->only(array_merge(self::FILTERS, ['sort', 'per_page', 'page'])));
        if (!$ids) {
            return redirect($back)->with('error', 'Select at least one course to delete.');
        }

        $courses = Course::whereIn('id', $ids)->orderBy('name')->get(['id', 'name', 'full_form']);
        $linked = [];
        foreach (self::LINKED as $table => $label) {
            $n = 0;
            foreach (array_chunk($courses->pluck('id')->all(), 1000) as $chunk) $n += \Illuminate\Support\Facades\DB::table($table)->whereIn('course_id', $chunk)->count();
            if ($n) $linked[$label] = $n;
        }
        return view('admin.courses.bulk-delete', ['courses' => $courses, 'linked' => $linked, 'back' => $back]);
    }

    /** Bulk delete, step 2: delete the reviewed courses (each like a single delete, so their documents are removed too). */
    public function bulkDestroy(Request $request)
    {
        $ids = collect(explode(',', (string) $request->input('ids')))->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        $back = $request->input('back') && str_starts_with($request->input('back'), route('admin.courses.index')) ? $request->input('back') : route('admin.courses.index');
        if (!$ids) {
            return redirect($back)->with('error', 'Nothing was selected, nothing was deleted.');
        }
        @set_time_limit(300);

        $deleted = [];
        \Illuminate\Support\Facades\DB::transaction(function () use ($ids, &$deleted) {
            Course::whereIn('id', $ids)->orderBy('id')->chunk(200, function ($chunk) use (&$deleted) {
                foreach ($chunk as $course) {
                    $deleted[$course->id] = $course->name;
                    $course->delete();
                }
            });
            // The admin area accepts both admin logins and website users marked as admin: record whoever it was.
            $who = auth('admin')->user() ?? auth()->user();
            \App\Models\ExamModule\AuditLog::create(['auditable_type' => Course::class, 'auditable_id' => 0, 'user_id' => auth('admin')->id(), 'action' => 'bulk_deleted',
                'old_values' => ['courses' => $deleted], 'new_values' => ['count' => count($deleted),
                    'by' => $who ? trim(($who->name ?? '') . ' <' . ($who->email ?? '') . '>') . (auth('admin')->check() ? ' (admin login)' : ' (website login)') : null,
                    'ip' => request()->ip()]]);
        });

        return redirect($back)->with('success', number_format(count($deleted)) . ' course' . (count($deleted) === 1 ? '' : 's') . ' deleted.');
    }

    public function create()
    {
        $programLevels = \App\Models\ProgramLevel::where('status', true)->get();
        $streamOffereds = \App\Models\StreamOffered::where('status', true)->get();
        $disciplines = \App\Models\Discipline::where('status', true)->get();
        $programTypes = \App\Models\ProgramType::where('status', true)->get();
        $courseTypes = \App\Models\CourseType::where('status', true)->orWhere('status', 1)->get();
        $exams = \App\Models\DynamicExam::where('status', 'Active')->orWhere('status', 1)->orWhere('status', '1')->get();
        $specializations = \App\Models\Specialization::where('status', true)->get();
        $allCourses = Course::where('status', 1)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        return view(
            'admin.courses.create',
            compact('programLevels', 'streamOffereds', 'disciplines', 'programTypes', 'courseTypes', 'exams', 'specializations', 'allCourses')
        );

    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['slug'] = empty($request->slug) ? Str::slug($request->name) : Str::slug($request->slug);

        // Sanitize FAQs array
        if (isset($data['faqs']) && is_array($data['faqs'])) {
            $cleanFaqs = [];
            foreach ($data['faqs'] as $faq) {
                $q = isset($faq['question']) ? trim($faq['question']) : '';
                $a = isset($faq['answer']) ? trim($faq['answer']) : '';
                if ($q !== '' || $a !== '') {
                    $cleanFaqs[] = [
                        'question' => $q,
                        'answer' => $a,
                    ];
                }
            }
            $data['faqs'] = !empty($cleanFaqs) ? $cleanFaqs : null;
        } else {
            $data['faqs'] = null;
        }

        // Sanitize Related Courses array
        if (isset($data['related_courses']) && is_array($data['related_courses'])) {
            $data['related_courses'] = array_values(array_filter($data['related_courses'], function($id) {
                return !empty($id);
            }));
            if (empty($data['related_courses'])) {
                $data['related_courses'] = null;
            }
        } else {
            $data['related_courses'] = null;
        }

        // Validate everything including the potentially auto-generated slug
        \Illuminate\Support\Facades\Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug',
            'status' => 'required|boolean',
            'is_show_on_website' => 'nullable|boolean',
            'sort_order' => 'required|integer',
            'program_level_id' => 'nullable|exists:program_levels,id',
            'stream_offered_id' => 'nullable|exists:stream_offereds,id',
            'discipline_id' => 'nullable|exists:disciplines,id',
            'duration' => 'nullable|string|max:100',
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'nullable|string',
            'faqs.*.answer' => 'nullable|string',
            'related_courses' => 'nullable|array',
            'related_courses.*' => 'nullable|exists:courses,id',
            'documents' => 'nullable|array',
            'documents.*.title' => 'nullable|string|max:255',
            'documents.*.file' => 'nullable|file|mimes:pdf|max:20480',
        ], [
            'slug.unique' => 'A course with this name or slug already exists.'
        ])->validate();

        unset($data['documents'], $data['has_documents_form']);

        $course = new Course($data);
        $course->save();
        $this->syncDocuments($request, $course);

        if ($request->has('program_types')) {
            $course->programTypes()->sync($request->program_types);
        }

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully.');
    }

    // public function edit(Course $course)
    // {
    //     return view('admin.courses.edit', compact('course'));
    // }
    public function edit(Course $course)
    {
        $course->load('documents');
        $programLevels = \App\Models\ProgramLevel::where('status', true)->get();
        $streamOffereds = \App\Models\StreamOffered::where('status', true)->get();
        $disciplines = \App\Models\Discipline::where('status', true)->get();
        $programTypes = \App\Models\ProgramType::where('status', true)->get();
        $courseTypes = \App\Models\CourseType::where('status', true)->orWhere('status', 1)->get();
        $exams = \App\Models\DynamicExam::where('status', 'Active')->orWhere('status', 1)->orWhere('status', '1')->get();
        $specializations = \App\Models\Specialization::where('status', true)->get();
        $allCourses = Course::where('id', '!=', $course->id)->where('status', 1)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        return view(
            'admin.courses.edit',
            compact('course', 'programLevels', 'streamOffereds', 'disciplines', 'programTypes', 'courseTypes', 'exams', 'specializations', 'allCourses')
        );
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->all();
        $data['slug'] = empty($request->slug) ? Str::slug($request->name) : Str::slug($request->slug);

        // Sanitize FAQs array
        if (isset($data['faqs']) && is_array($data['faqs'])) {
            $cleanFaqs = [];
            foreach ($data['faqs'] as $faq) {
                $q = isset($faq['question']) ? trim($faq['question']) : '';
                $a = isset($faq['answer']) ? trim($faq['answer']) : '';
                if ($q !== '' || $a !== '') {
                    $cleanFaqs[] = [
                        'question' => $q,
                        'answer' => $a,
                    ];
                }
            }
            $data['faqs'] = !empty($cleanFaqs) ? $cleanFaqs : null;
        } else {
            $data['faqs'] = null;
        }

        // Sanitize Related Courses array
        if (isset($data['related_courses']) && is_array($data['related_courses'])) {
            $data['related_courses'] = array_values(array_filter($data['related_courses'], function($id) {
                return !empty($id);
            }));
            if (empty($data['related_courses'])) {
                $data['related_courses'] = null;
            }
        } else {
            $data['related_courses'] = null;
        }

        \Illuminate\Support\Facades\Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug,' . $course->id,
            'status' => 'required|boolean',
            'is_show_on_website' => 'nullable|boolean',
            'sort_order' => 'required|integer',
            'program_level_id' => 'nullable|exists:program_levels,id',
            'stream_offered_id' => 'nullable|exists:stream_offereds,id',
            'discipline_id' => 'nullable|exists:disciplines,id',
            'duration' => 'nullable|string|max:100',
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'nullable|string',
            'faqs.*.answer' => 'nullable|string',
            'related_courses' => 'nullable|array',
            'related_courses.*' => 'nullable|exists:courses,id',
            'documents' => 'nullable|array',
            'documents.*.title' => 'nullable|string|max:255',
            'documents.*.file' => 'nullable|file|mimes:pdf|max:20480',
        ], [
            'slug.unique' => 'A course with this name or slug already exists.'
        ])->validate();

        unset($data['documents'], $data['has_documents_form']);

        $course->update($data);
        $this->syncDocuments($request, $course);

        if ($request->has('program_types')) {
            $course->programTypes()->sync($request->program_types);
        } else {
            $course->programTypes()->sync([]);
        }

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }

    public function duplicate(Course $course)
    {
        $newCourse = $course->replicate();

        // unique name & slug
        $newCourse->name = $course->name . ' (Copy)';
        $newCourse->slug = $course->slug . '-copy-' . time();

        $newCourse->status = 0; // inactive by default (safe)
        $newCourse->is_show_on_website = $course->is_show_on_website ?? 1;
        $newCourse->save();


        return redirect()->route('admin.courses.index')
            ->with('success', 'Course duplicated successfully. Please review and update.');
    }

    /**
     * AJAX endpoint to fetch and research course details via Gemini Bot
     */
    public function fetchBotData(Request $request, GeminiCourseBotService $botService)
    {
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');

        $request->validate([
            'course_name' => 'required|string|max:255',
        ]);

        try {
            $data = $botService->fetchCourseData($request->input('course_name'));
            return response()->json([
                'status' => 'success',
                'message' => 'Course details fetched successfully.',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching course data by bot: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    protected function syncDocuments(Request $request, Course $course): void
    {
        $documents = $request->input('documents', []);
        $keptIds = [];
        $docDir = public_path('media/courses/documents');

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
                    $existingDoc = $course->documents()->where('id', $docId)->first();
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
                            $savedPath = 'media/courses/documents/' . $fileName;

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
                        $savedPath = 'media/courses/documents/' . $fileName;

                        $newDoc = $course->documents()->create([
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
            $course->documents()->whereNotIn('id', $keptIds)->get()->each(function ($doc) {
                if ($doc->file_path && file_exists(public_path($doc->file_path))) {
                    @unlink(public_path($doc->file_path));
                }
                $doc->delete();
            });
        }
    }

    public function destroyDocument(Course $course, CourseDocument $document)
    {
        if ($document->course_id != $course->id) {
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
