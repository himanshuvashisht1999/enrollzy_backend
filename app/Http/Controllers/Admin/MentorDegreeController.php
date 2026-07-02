<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentorDegree;

class MentorDegreeController extends Controller
{
    public function index()
    {
        $degrees = MentorDegree::latest()->get();
        return view('admin.mentor.degrees.index', compact('degrees'));
    }

    public function create()
    {
        return view('admin.mentor.degrees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mentor_degrees,name',
            'status' => 'required|boolean'
        ]);

        MentorDegree::create($request->all());
        return redirect()->route('admin.mentor.degrees.index')->with('success', 'Degree added successfully');
    }

    public function edit(MentorDegree $degree)
    {
        return view('admin.mentor.degrees.edit', compact('degree'));
    }

    public function update(Request $request, MentorDegree $degree)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mentor_degrees,name,' . $degree->id,
            'status' => 'required|boolean'
        ]);

        $degree->update($request->all());
        return redirect()->route('admin.mentor.degrees.index')->with('success', 'Degree updated successfully');
    }

    public function destroy(MentorDegree $degree)
    {
        $degree->delete();
        return redirect()->route('admin.mentor.degrees.index')->with('success', 'Degree deleted successfully');
    }
}
