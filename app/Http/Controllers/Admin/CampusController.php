<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
        $campus = new Campus($request->all());
        $campus->organisation_id = $organisation->id;
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
        $input = $request->all();

        $booleans = [
            'smart_classrooms', 'library_available', 'digital_library_access', 'hostel_available',
            'medical_facility_available', 'transport_available', 'parking_available', 'cctv_coverage',
            'fire_safety_certified', 'disaster_management_plan', 'verification_status', 'status',
            'brand_compliance_verified', 'science_labs_available', 'computer_labs_available',
            'playground_available', 'gps_enabled_buses', 'visitor_management_system'
        ];

        foreach ($booleans as $field) {
            $input[$field] = $request->has($field) ? 1 : 0;
        }

        // Handle slug: if empty, let model handle it
        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
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

    /**
     * Store initial draft for multi-step form
     */
    public function storeDraft(Request $request, Organisation $organisation)
    {
        $request->validate([
            'campus_name' => 'required|string|max:255',
            'campus_type' => 'required',
        ]);

        $input = $request->all();
        $input['organisation_id'] = $organisation->id;

        // Handle slug: if empty, let model handle it
        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        $campus = Campus::create($input);

        return response()->json([
            'status' => 'success',
            'campus_id' => $campus->id,
            'message' => 'Draft created successfully'
        ]);
    }

    /**
     * Batch save multiple fields for a tab
     */
    public function autosaveTab(Request $request, $orgId, $id)
    {
        $campus = Campus::findOrFail($id);
        $data = $request->all();
        
        // Handle slug: if empty, don't update it to avoid null constraint violation
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            unset($data['slug']);
        }

        unset($data['_token']);
        
        $booleans = [
            'smart_classrooms', 'library_available', 'digital_library_access', 'hostel_available',
            'medical_facility_available', 'transport_available', 'parking_available', 'cctv_coverage',
            'fire_safety_certified', 'disaster_management_plan', 'verification_status', 'status',
            'brand_compliance_verified', 'science_labs_available', 'computer_labs_available',
            'playground_available', 'gps_enabled_buses', 'visitor_management_system'
        ];

        $updateData = [];
        foreach ($data as $key => $value) {
            if (Schema::hasColumn('campuses', $key)) {
                if (in_array($key, $booleans)) {
                    $updateData[$key] = ($value === 'true' || $value === 1 || $value === '1' || $value === true) ? 1 : 0;
                } else {
                    $updateData[$key] = $value;
                }
            }
        }
        
        $campus->update($updateData);
        return response()->json(['status' => 'success', 'message' => 'Tab saved']);
    }
}
