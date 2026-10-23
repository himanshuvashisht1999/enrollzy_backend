<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CourseType;

class CourseTypeController extends Controller
{
    public function index()
    {
        $types = CourseType::orderBy('sort_order', 'asc')->get();
        return view('admin.course_type.index', compact('types'));
    }

    public function create()
    {
        return view('admin.course_type.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);

        CourseType::create($request->all());

        return redirect()->route('admin.course-types.index')->with('success', 'Course Type created successfully.');
    }

    public function edit(CourseType $courseType)
    {
        return view('admin.course_type.edit', compact('courseType'));
    }

    public function update(Request $request, CourseType $courseType)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $courseType->update($request->all());

        return redirect()->route('admin.course-types.index')->with('success', 'Course Type updated successfully.');
    }

    public function destroy(CourseType $courseType)
    {
        $courseType->delete();

        return redirect()->route('admin.course-types.index')->with('success', 'Course Type deleted successfully.');
    }
}
