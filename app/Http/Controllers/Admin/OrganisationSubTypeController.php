<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganisationType;
use App\Models\OrganisationSubType;
use Illuminate\Http\Request;

class OrganisationSubTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = OrganisationSubType::with('organisationType');

        if ($request->filled('organisation_type_id')) {
            $query->where('organisation_type_id', $request->organisation_type_id);
        }

        $items = $query->orderBy('organisation_type_id')->orderBy('sort_order')->orderBy('title')->get();
        $organisationTypes = OrganisationType::orderBy('title')->get();

        return view('admin.organisation-sub-types.index', compact('items', 'organisationTypes'));
    }

    public function create(Request $request)
    {
        $organisationTypes = OrganisationType::orderBy('title')->get();
        $selectedTypeId = $request->organisation_type_id;
        return view('admin.organisation-sub-types.create', compact('organisationTypes', 'selectedTypeId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        OrganisationSubType::create([
            'organisation_type_id' => $request->organisation_type_id,
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.organisation-sub-types.index', ['organisation_type_id' => $request->organisation_type_id])
            ->with('success', 'Organisation Sub-Type created successfully.');
    }

    public function edit(OrganisationSubType $organisationSubType)
    {
        $organisationTypes = OrganisationType::orderBy('title')->get();
        return view('admin.organisation-sub-types.edit', compact('organisationSubType', 'organisationTypes'));
    }

    public function update(Request $request, OrganisationSubType $organisationSubType)
    {
        $request->validate([
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $organisationSubType->update([
            'organisation_type_id' => $request->organisation_type_id,
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.organisation-sub-types.index', ['organisation_type_id' => $request->organisation_type_id])
            ->with('success', 'Organisation Sub-Type updated successfully.');
    }

    public function destroy(OrganisationSubType $organisationSubType)
    {
        $typeId = $organisationSubType->organisation_type_id;
        $organisationSubType->delete();
        return redirect()->route('admin.organisation-sub-types.index', ['organisation_type_id' => $typeId])
            ->with('success', 'Organisation Sub-Type deleted successfully.');
    }
}
