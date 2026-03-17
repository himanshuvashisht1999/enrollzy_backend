<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramLevel;
use Illuminate\Http\Request;

class ProgramLevelController extends Controller
{
    public function index()
    {
        $items = ProgramLevel::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.program-levels.index', compact('items'));
    }

    public function create()
    {
        return view('admin.program-levels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        ProgramLevel::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.program-levels.index')->with('success', 'Program Level created successfully.');
    }

    public function edit(ProgramLevel $programLevel)
    {
        return view('admin.program-levels.edit', compact('programLevel'));
    }

    public function update(Request $request, ProgramLevel $programLevel)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $programLevel->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.program-levels.index')->with('success', 'Program Level updated successfully.');
    }

    public function destroy(ProgramLevel $programLevel)
    {
        $programLevel->delete();
        return redirect()->route('admin.program-levels.index')->with('success', 'Program Level deleted successfully.');
    }
}
