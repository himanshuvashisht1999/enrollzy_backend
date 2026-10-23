<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\OrganisationType;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $facilities = Facility::with('organisationTypes')->latest()->get();
        $organisationTypes = OrganisationType::where('status', true)->orderBy('sort_order')->orderBy('title')->get();
        return view('admin.facilities.index', compact('facilities', 'organisationTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:facilities,name',
            'icon' => 'nullable|string|max:255',
            'status' => 'boolean',
            'organisation_type_ids' => 'nullable|array',
            'organisation_type_ids.*' => 'exists:organisation_types,id',
        ]);

        $facility = Facility::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        if ($request->has('organisation_type_ids')) {
            $facility->organisationTypes()->sync($request->organisation_type_ids);
        }

        return redirect()->route('admin.facilities.index')->with('success', 'Facility created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:facilities,name,' . $facility->id,
            'icon' => 'nullable|string|max:255',
            'status' => 'boolean',
            'organisation_type_ids' => 'nullable|array',
            'organisation_type_ids.*' => 'exists:organisation_types,id',
        ]);

        $facility->update([
            'name' => $request->name,
            'icon' => $request->icon,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        $facility->organisationTypes()->sync($request->input('organisation_type_ids', []));

        return redirect()->route('admin.facilities.index')->with('success', 'Facility updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility)
    {
        $facility->delete();
        return redirect()->route('admin.facilities.index')->with('success', 'Facility deleted successfully.');
    }
}
