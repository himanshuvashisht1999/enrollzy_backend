<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentorLanguage;

class MentorLanguageController extends Controller
{
    public function index()
    {
        $languages = MentorLanguage::latest()->get();
        return view('admin.mentor.languages.index', compact('languages'));
    }

    public function create()
    {
        return view('admin.mentor.languages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mentor_languages,name',
            'status' => 'required|boolean'
        ]);

        MentorLanguage::create($request->all());
        return redirect()->route('admin.mentor.languages.index')->with('success', 'Language added successfully');
    }

    public function edit(MentorLanguage $language)
    {
        return view('admin.mentor.languages.edit', compact('language'));
    }

    public function update(Request $request, MentorLanguage $language)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mentor_languages,name,' . $language->id,
            'status' => 'required|boolean'
        ]);

        $language->update($request->all());
        return redirect()->route('admin.mentor.languages.index')->with('success', 'Language updated successfully');
    }

    public function destroy(MentorLanguage $language)
    {
        $language->delete();
        return redirect()->route('admin.mentor.languages.index')->with('success', 'Language deleted successfully');
    }
}
