<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('discipline')->orderBy('sort_order')->latest()->paginate(10);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $programLevels = \App\Models\ProgramLevel::where('status', true)->get();
        $streamOffereds = \App\Models\StreamOffered::where('status', true)->get();
        $disciplines = \App\Models\Discipline::where('status', true)->get();

        return view(
            'admin.courses.create',
            compact('programLevels', 'streamOffereds', 'disciplines')
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

        return view(
            'admin.courses.edit',
            compact('course', 'programLevels', 'streamOffereds', 'disciplines')
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
