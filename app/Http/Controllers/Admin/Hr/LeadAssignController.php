<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerCategory;
use App\Models\CallingStatus;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\LeadAssignment;
use Illuminate\Support\Facades\DB;

class LeadAssignController extends Controller
{
    public function index()
    {
        $organization_id = auth()->user()->organization_id;
        $categories = CustomerCategory::where('organization_id', $organization_id)->where('parent_id', 0)->with('childrenRecursive')->get();
        $statuses = CallingStatus::where('organization_id', $organization_id)->where('status', 1)->get();
        $staffs = Admin::where('organization_id', $organization_id)->where('status', 1)->get();
        
        return view('admin.students_crm.lead_assign.index', compact('categories', 'statuses', 'staffs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'start_number' => 'required|integer|min:1',
            'end_number' => 'required|integer|gte:start_number'
        ]);

        $organization_id = auth()->user()->organization_id;
        $query = Customer::where('organization_id', $organization_id);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('call_status_id')) {
            $customerIdsWithStatus = DB::table('calling_histories')
                ->where('organization_id', $organization_id)
                ->where('reason', $request->call_status_id)
                ->pluck('user_id');
            $query->whereIn('id', $customerIdsWithStatus);
        }

        $skip = $request->start_number - 1;
        $take = $request->end_number - $request->start_number + 1;

        $customers = $query->orderBy('id', 'asc')->skip($skip)->take($take)->pluck('id');

        if ($customers->isEmpty()) {
            return redirect()->back()->with('error', 'No leads found for the given criteria.');
        }

        $assignments = [];
        $now = now();
        foreach ($customers as $customerId) {
            // Delete existing assignments for this customer? Or allow multiple? "admin can assign same data to another also" -> allow multiple.
            // Or maybe check if already assigned to this staff.
            $exists = LeadAssignment::where('customer_id', $customerId)->where('staff_id', $request->staff_id)->exists();
            if (!$exists) {
                $assignments[] = [
                    'customer_id' => $customerId,
                    'staff_id' => $request->staff_id,
                    'assigned_by' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($assignments)) {
            LeadAssignment::insert($assignments);
        }

        return redirect()->back()->with('success', count($assignments) . ' leads assigned successfully.');
    }
}
