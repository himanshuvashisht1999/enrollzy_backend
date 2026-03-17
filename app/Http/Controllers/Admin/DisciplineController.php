<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use Illuminate\Http\Request;

class DisciplineController extends Controller
{
    public function index()
    {
        $items = Discipline::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.disciplines.index', compact('items'));
    }

    public function create()
    {
        return view('admin.disciplines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        Discipline::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.disciplines.index')->with('success', 'Discipline created successfully.');
    }

    public function edit(Discipline $discipline)
    {
        return view('admin.disciplines.edit', compact('discipline'));
    }

    public function update(Request $request, Discipline $discipline)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $discipline->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.disciplines.index')->with('success', 'Discipline updated successfully.');
    }

    public function destroy(Discipline $discipline)
    {
        $discipline->delete();
        return redirect()->route('admin.disciplines.index')->with('success', 'Discipline deleted successfully.');
    }
}
