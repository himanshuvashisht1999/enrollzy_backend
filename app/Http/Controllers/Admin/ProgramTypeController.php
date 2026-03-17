<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramType;
use Illuminate\Http\Request;

class ProgramTypeController extends Controller
{
    public function index()
    {
        $items = ProgramType::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.program-types.index', compact('items'));
    }

    public function create()
    {
        return view('admin.program-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        ProgramType::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.program-types.index')->with('success', 'Program Type created successfully.');
    }

    public function edit(ProgramType $programType)
    {
        return view('admin.program-types.edit', compact('programType'));
    }

    public function update(Request $request, ProgramType $programType)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $programType->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.program-types.index')->with('success', 'Program Type updated successfully.');
    }

    public function destroy(ProgramType $programType)
    {
        $programType->delete();
        return redirect()->route('admin.program-types.index')->with('success', 'Program Type deleted successfully.');
    }
}
