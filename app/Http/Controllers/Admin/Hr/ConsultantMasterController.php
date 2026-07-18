<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\ConsultantType;
use App\Models\ConsultantStatus;
use App\Models\ConsultantAccessLevel;
use App\Models\ConsultantLeadVisibility;
use Illuminate\Http\Request;

class ConsultantMasterController extends Controller
{
    public function index()
    {
        $org_id = auth()->user()->organization_id;
        $types = ConsultantType::where('organization_id', $org_id)->get();
        $statuses = ConsultantStatus::where('organization_id', $org_id)->get();
        $access_levels = ConsultantAccessLevel::where('organization_id', $org_id)->get();
        $lead_visibilities = ConsultantLeadVisibility::where('organization_id', $org_id)->get();

        return view('admin.consultant_settings.index', compact('types', 'statuses', 'access_levels', 'lead_visibilities'));
    }

    // Store Methods
    public function storeType(Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        ConsultantType::create(['name' => $request->name, 'organization_id' => auth()->user()->organization_id]);
        return redirect()->back()->with('success', 'Added successfully');
    }

    public function storeStatus(Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        ConsultantStatus::create(['name' => $request->name, 'organization_id' => auth()->user()->organization_id]);
        return redirect()->back()->with('success', 'Added successfully');
    }

    public function storeAccessLevel(Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        ConsultantAccessLevel::create(['name' => $request->name, 'organization_id' => auth()->user()->organization_id]);
        return redirect()->back()->with('success', 'Added successfully');
    }

    public function storeLeadVisibility(Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        ConsultantLeadVisibility::create(['name' => $request->name, 'organization_id' => auth()->user()->organization_id]);
        return redirect()->back()->with('success', 'Added successfully');
    }

    // Update Methods
    public function updateType(Request $request, $id) {
        $request->validate(['name' => 'required|string|max:255']);
        ConsultantType::findOrFail($id)->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Updated successfully');
    }

    public function updateStatus(Request $request, $id) {
        $request->validate(['name' => 'required|string|max:255']);
        ConsultantStatus::findOrFail($id)->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Updated successfully');
    }

    public function updateAccessLevel(Request $request, $id) {
        $request->validate(['name' => 'required|string|max:255']);
        ConsultantAccessLevel::findOrFail($id)->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Updated successfully');
    }

    public function updateLeadVisibility(Request $request, $id) {
        $request->validate(['name' => 'required|string|max:255']);
        ConsultantLeadVisibility::findOrFail($id)->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Updated successfully');
    }

    // Destroy Methods (Soft Delete)
    public function destroyType($id) {
        ConsultantType::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted successfully');
    }

    public function destroyStatus($id) {
        ConsultantStatus::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted successfully');
    }

    public function destroyAccessLevel($id) {
        ConsultantAccessLevel::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted successfully');
    }

    public function destroyLeadVisibility($id) {
        ConsultantLeadVisibility::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted successfully');
    }
}
