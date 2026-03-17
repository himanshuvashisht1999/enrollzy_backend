<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganisationType;
use Illuminate\Http\Request;

class OrganisationTypeController extends Controller
{
    public function index()
    {
        $items = OrganisationType::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.organisation-types.index', compact('items'));
    }

    public function create()
    {
        return view('admin.organisation-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        OrganisationType::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.organisation-types.index')->with('success', 'Organisation Type created successfully.');
    }

    public function edit(OrganisationType $organisationType)
    {
        return view('admin.organisation-types.edit', compact('organisationType'));
    }

    public function update(Request $request, OrganisationType $organisationType)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $organisationType->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.organisation-types.index')->with('success', 'Organisation Type updated successfully.');
    }

    public function destroy(OrganisationType $organisationType)
    {
        $organisationType->delete();
        return redirect()->route('admin.organisation-types.index')->with('success', 'Organisation Type deleted successfully.');
    }
}
