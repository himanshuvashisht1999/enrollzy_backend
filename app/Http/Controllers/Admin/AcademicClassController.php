<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicClass;

class AcademicClassController extends Controller
{
    public function index()
    {
        $classes = AcademicClass::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.academic_classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.academic_classes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        AcademicClass::create([
            'name' => $request->input('name'),
            'title' => $request->input('name'),
            'status' => $request->has('status') ? (bool)$request->input('status') : true,
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.academic-classes.index')->with('success', 'Class created successfully.');
    }

    public function edit(AcademicClass $academicClass)
    {
        return view('admin.academic_classes.edit', compact('academicClass'));
    }

    public function update(Request $request, AcademicClass $academicClass)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $academicClass->update([
            'name' => $request->input('name'),
            'title' => $request->input('name'),
            'status' => $request->has('status') ? (bool)$request->input('status') : false,
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.academic-classes.index')->with('success', 'Class updated successfully.');
    }

    public function destroy(AcademicClass $academicClass)
    {
        $academicClass->delete();

        return redirect()->route('admin.academic-classes.index')->with('success', 'Class deleted successfully.');
    }
}
