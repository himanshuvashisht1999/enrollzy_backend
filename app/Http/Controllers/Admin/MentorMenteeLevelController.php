<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentorMenteeLevel;

class MentorMenteeLevelController extends Controller
{
    public function index()
    {
        $menteeLevels = MentorMenteeLevel::latest()->get();
        return view('admin.mentor.mentee_levels.index', compact('menteeLevels'));
    }

    public function create()
    {
        return view('admin.mentor.mentee_levels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mentor_mentee_levels,name',
            'status' => 'required|boolean',
            'commission_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        MentorMenteeLevel::create($request->all());
        return redirect()->route('admin.mentor.mentee_levels.index')->with('success', 'Mentee Level added successfully');
    }

    public function edit(MentorMenteeLevel $menteeLevel)
    {
        return view('admin.mentor.mentee_levels.edit', compact('menteeLevel'));
    }

    public function update(Request $request, MentorMenteeLevel $menteeLevel)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mentor_mentee_levels,name,' . $menteeLevel->id,
            'status' => 'required|boolean',
            'commission_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $menteeLevel->update($request->all());
        return redirect()->route('admin.mentor.mentee_levels.index')->with('success', 'Mentee Level updated successfully');
    }

    public function destroy(MentorMenteeLevel $menteeLevel)
    {
        $menteeLevel->delete();
        return redirect()->route('admin.mentor.mentee_levels.index')->with('success', 'Mentee Level deleted successfully');
    }
}


