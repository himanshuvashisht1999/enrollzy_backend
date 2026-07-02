<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentorIndustry;

class MentorIndustryController extends Controller
{
    public function index()
    {
        $industries = MentorIndustry::latest()->get();
        return view('admin.mentor.industries.index', compact('industries'));
    }

    public function create()
    {
        return view('admin.mentor.industries.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mentor_industries,name',
            'status' => 'required|boolean'
        ]);

        MentorIndustry::create($request->all());
        return redirect()->route('admin.mentor.industries.index')->with('success', 'Industry added successfully');
    }

    public function edit(MentorIndustry $industry)
    {
        return view('admin.mentor.industries.edit', compact('industry'));
    }

    public function update(Request $request, MentorIndustry $industry)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mentor_industries,name,' . $industry->id,
            'status' => 'required|boolean'
        ]);

        $industry->update($request->all());
        return redirect()->route('admin.mentor.industries.index')->with('success', 'Industry updated successfully');
    }

    public function destroy(MentorIndustry $industry)
    {
        $industry->delete();
        return redirect()->route('admin.mentor.industries.index')->with('success', 'Industry deleted successfully');
    }
}
