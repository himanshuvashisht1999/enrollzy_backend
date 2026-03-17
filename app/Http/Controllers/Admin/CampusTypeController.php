<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusType;
use Illuminate\Http\Request;

class CampusTypeController extends Controller
{
    public function index()
    {
        $items = CampusType::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.campus-types.index', compact('items'));
    }

    public function create()
    {
        return view('admin.campus-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        CampusType::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.campus-types.index')->with('success', 'Campus Type created successfully.');
    }

    public function edit(CampusType $campusType)
    {
        return view('admin.campus-types.edit', compact('campusType'));
    }

    public function update(Request $request, CampusType $campusType)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $campusType->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.campus-types.index')->with('success', 'Campus Type updated successfully.');
    }

    public function destroy(CampusType $campusType)
    {
        $campusType->delete();
        return redirect()->route('admin.campus-types.index')->with('success', 'Campus Type deleted successfully.');
    }
}
