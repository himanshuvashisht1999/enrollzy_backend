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
        $query = Course::with('discipline')->orderBy('sort_order')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $courses = $query->paginate(10)->withQueryString();
        return view('admin.courses.index', compact('courses'));
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
