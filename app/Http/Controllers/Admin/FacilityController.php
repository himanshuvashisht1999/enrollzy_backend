<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $facilities = Facility::latest()->get();
        return view('admin.facilities.index', compact('facilities'));
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
        ]);

        Facility::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('facilities.index')->with('success', 'Facility created successfully.');
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
        ]);

        $facility->update([
            'name' => $request->name,
            'icon' => $request->icon,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('facilities.index')->with('success', 'Facility updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility)
    {
        $facility->delete();
        return redirect()->route('facilities.index')->with('success', 'Facility deleted successfully.');
    }
}
