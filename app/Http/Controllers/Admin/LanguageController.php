<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::orderBy('sort_order')->latest()->paginate(10);
        return view('admin.masters.languages.index', compact('languages'));
    }

    public function create()
    {
        return view('admin.masters.languages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sort_order' => 'required|integer',
        ]);

        Language::create([
            'title' => $request->title,
            'sort_order' => $request->sort_order,
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin.languages.index')->with('success', 'Language created successfully.');
    }

    public function edit(Language $language)
    {
        return view('admin.masters.languages.edit', compact('language'));
    }

    public function update(Request $request, Language $language)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sort_order' => 'required|integer',
        ]);

        $language->update([
            'title' => $request->title,
            'sort_order' => $request->sort_order,
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin.languages.index')->with('success', 'Language updated successfully.');
    }

    public function destroy(Language $language)
    {
        $language->delete();
        return redirect()->route('admin.languages.index')->with('success', 'Language deleted successfully.');
    }
}
