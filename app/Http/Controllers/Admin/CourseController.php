<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
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

        return view(
            'admin.courses.create',
            compact('programLevels', 'streamOffereds', 'disciplines', 'programTypes', 'courseTypes', 'exams', 'specializations')
        );

    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['slug'] = empty($request->slug) ? Str::slug($request->name) : Str::slug($request->slug);

        // Validate everything including the potentially auto-generated slug
        \Illuminate\Support\Facades\Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
            'program_level_id' => 'nullable|exists:program_levels,id',
            'stream_offered_id' => 'nullable|exists:stream_offereds,id',
            'discipline_id' => 'nullable|exists:disciplines,id',
            'duration' => 'nullable|string|max:100',
        ], [
            'slug.unique' => 'A course with this name or slug already exists.'
        ])->validate();

        $course = new Course($data);
        $course->save();

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
        $programLevels = \App\Models\ProgramLevel::where('status', true)->get();
        $streamOffereds = \App\Models\StreamOffered::where('status', true)->get();
        $disciplines = \App\Models\Discipline::where('status', true)->get();
        $programTypes = \App\Models\ProgramType::where('status', true)->get();
        $courseTypes = \App\Models\CourseType::where('status', true)->orWhere('status', 1)->get();
        $exams = \App\Models\DynamicExam::where('status', 'Active')->orWhere('status', 1)->orWhere('status', '1')->get();
        $specializations = \App\Models\Specialization::where('status', true)->get();

        return view(
            'admin.courses.edit',
            compact('course', 'programLevels', 'streamOffereds', 'disciplines', 'programTypes', 'courseTypes', 'exams', 'specializations')
        );
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->all();
        $data['slug'] = empty($request->slug) ? Str::slug($request->name) : Str::slug($request->slug);

        \Illuminate\Support\Facades\Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug,' . $course->id,
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
            'program_level_id' => 'nullable|exists:program_levels,id',
            'stream_offered_id' => 'nullable|exists:stream_offereds,id',
            'discipline_id' => 'nullable|exists:disciplines,id',
            'duration' => 'nullable|string|max:100',
        ], [
            'slug.unique' => 'A course with this name or slug already exists.'
        ])->validate();

        $course->update($data);

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
        $newCourse->save();


        return redirect()->route('admin.courses.index')
            ->with('success', 'Course duplicated successfully. Please review and update.');
    }

}
