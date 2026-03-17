<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Organisation $organisation)
    {
        $campuses = $organisation->campuses()->latest()->get();
        return view('admin.organisations.campuses.index', compact('organisation', 'campuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Organisation $organisation)
    {
        $brandTypes = Organisation::BRAND_TYPES;
        $examCategories = \App\Models\Exam::whereNotNull('exam_category')->get()
            ->pluck('exam_category')
            ->flatten()
            ->unique()
            ->filter()
            ->values();
        $exams = \App\Models\Exam::select('id', 'name', 'exam_category')->orderBy('name')->get();
        return view('admin.organisations.campuses.create', compact('organisation', 'brandTypes', 'exams', 'examCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Organisation $organisation)
    {
        $validated = $request->validate([
            'campus_name' => 'required|string|max:255',
            // 'campus_type' => 'required|in:Main,Regional,Satellite',
            'established_year' => 'nullable|integer|between:1900,2100',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'brand_type' => 'nullable|string',
            'franchise_partner_name' => 'nullable|string',
            'franchise_start_year' => 'nullable|integer|between:1900,2100',
            'brand_compliance_verified' => 'nullable|boolean',
            'exams_prepared_for' => 'nullable|array',
            'target_classes' => 'nullable|array',
            'about_institute' => 'nullable|string',
            // Add other validations as needed, most are nullable
        ]);

        $campus = new Campus($request->all());
        $campus->organisation_id = $organisation->id;

        // Handle Booleans
        $campus->brand_compliance_verified = $request->has('brand_compliance_verified');

        if ($request->campus_contact_numbers) {
            $campus->campus_contact_numbers = array_map(
                'trim',
                explode(',', $request->campus_contact_numbers[0] ?? '')
            );
        }

        if ($request->has('bus_routes')) {
            $campus->bus_routes = array_filter($request->bus_routes);
        }

        // Handle JSON fields if not auto-cast by Eloquent (Eloquent casts handle arrays usually, but good to ensure)
        // Since we cast them in model, passing array is fine.

        $campus->save();

        return redirect()->route('admin.organisations.campuses.index', $organisation->id)
            ->with('success', 'Campus created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organisation $organisation, Campus $campus)
    {
        $brandTypes = Organisation::BRAND_TYPES;
        $examCategories = \App\Models\Exam::whereNotNull('exam_category')->get()
            ->pluck('exam_category')
            ->flatten()
            ->unique()
            ->filter()
            ->values();
        $exams = \App\Models\Exam::select('id', 'name', 'exam_category')->orderBy('name')->get();
        return view('admin.organisations.campuses.edit', compact('organisation', 'campus', 'brandTypes', 'exams', 'examCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organisation $organisation, Campus $campus)
    {
        $validated = $request->validate([
            'campus_name' => 'required|string|max:255',
            'campus_type' => 'required',
            'brand_type' => 'nullable|string',
            'established_year' => 'nullable|integer|between:1900,2100',
            'franchise_start_year' => 'nullable|integer|between:1900,2100',
            'exams_prepared_for' => 'nullable|array',
            'target_classes' => 'nullable|array',
            'about_institute' => 'nullable|string',
        ]);

        $input = $request->all();

        // Handle Checkboxes that might not be sent if unchecked
        // The model casts boolean, but if input is missing, update() might ignore it depending on how it's called.
        // Explicitly setting booleans for unchecked fields is safer.
        $booleans = [
            'smart_classrooms',
            'library_available',
            'digital_library_access',
            'hostel_available',
            'medical_facility_available',
            'transport_available',
            'parking_available',
            'cctv_coverage',
            'fire_safety_certified',
            'disaster_management_plan',
            'verification_status',
            'status',
            'brand_compliance_verified',
            'science_labs_available',
            'computer_labs_available',
            'playground_available',
            'gps_enabled_buses',
            'visitor_management_system'
        ];

        foreach ($booleans as $field) {
            $input[$field] = $request->has($field) ? 1 : 0;
        }

        if ($request->has('bus_routes')) {
            $input['bus_routes'] = array_filter($request->bus_routes);
        }

        $campus->update($input);

        return redirect()->route('admin.organisations.campuses.index', $organisation->id)
            ->with('success', 'Campus updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organisation $organisation, Campus $campus)
    {
        $campus->delete();
        return redirect()->route('admin.organisations.campuses.index', $organisation->id)
            ->with('success', 'Campus deleted successfully');
    }
}
