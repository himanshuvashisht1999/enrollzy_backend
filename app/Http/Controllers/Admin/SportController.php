<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Illuminate\Http\Request;

class SportController extends Controller
{
    public function index()
    {
        $items = Sport::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.sports.index', compact('items'));
    }

    public function create()
    {
        return view('admin.sports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        Sport::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.sports.index')->with('success', 'Sport created successfully.');
    }

    public function edit(Sport $sport)
    {
        return view('admin.sports.edit', compact('sport'));
    }

    public function update(Request $request, Sport $sport)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $sport->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.sports.index')->with('success', 'Sport updated successfully.');
    }

    public function destroy(Sport $sport)
    {
        $sport->delete();
        return redirect()->route('admin.sports.index')->with('success', 'Sport deleted successfully.');
    }
}
