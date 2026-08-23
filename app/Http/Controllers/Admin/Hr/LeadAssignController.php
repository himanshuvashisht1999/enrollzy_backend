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
        $user = auth()->user();
        $organization_id = $user->organization_id;
        $categories = CustomerCategory::where('organization_id', $organization_id)->where('parent_id', 0)->with('childrenRecursive')->get();
        $statuses = CallingStatus::where('organization_id', $organization_id)->where('status', 1)->get();
        
        // Filter Staff based on Role Assign Rules and Manager hierarchy
        if (isset($user->is_admin) && $user->is_admin) {
            $staffs = Admin::where('organization_id', $organization_id)->where('status', 1)->get();
        } else {
            $allowedRoleIds = \App\Models\RoleAssignRule::whereHas('role', function($q) use ($user) {
                $q->where('name', $user->role);
            })->pluck('can_assign_to_role_id');
            $allowedRoleNames = \Spatie\Permission\Models\Role::whereIn('id', $allowedRoleIds)->pluck('name')->toArray();

            $staffs = Admin::where('organization_id', $organization_id)
                ->where('status', 1)
                ->whereIn('role', $allowedRoleNames)
                ->where('manager_id', $user->id)
                ->get();
        }
        
        $assignmentsSummary = LeadAssignment::select('staff_id', 'created_at as batch_date', DB::raw('count(*) as total_leads'))
            ->whereHas('staff', function($q) use ($organization_id) {
                $q->where('organization_id', $organization_id);
            })
            ->with('staff')
            ->groupBy('staff_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.students_crm.lead_assign.index', compact('categories', 'statuses', 'staffs', 'assignmentsSummary'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'start_number' => 'required|integer|min:1',
            'end_number' => 'required|integer|gte:start_number'
        ]);

        $user = auth()->user();
        $organization_id = $user->organization_id;
        $query = Customer::where('organization_id', $organization_id);

        // If not top level, they can only assign leads they currently own
        if (!in_array($user->role, ['superadmin', 'admin', 'Admin'])) {
            $myAssignedCustomerIds = LeadAssignment::where('staff_id', $user->id)->pluck('customer_id');
            $query->whereIn('id', $myAssignedCustomerIds);
        }

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
            return redirect()->back()->with('error', 'No leads found for the given criteria (or you do not own them).');
        }

        $assignments = [];
        $skipped = 0;
        $now = now();
        
        foreach ($customers as $customerId) {
            $existingAssignment = LeadAssignment::where('customer_id', $customerId)->first();
            
            if ($existingAssignment) {
                if ($existingAssignment->staff_id == $request->staff_id) {
                    $skipped++;
                } else {
                    // Update current ownership (Delegation or Reassignment)
                    $existingAssignment->staff_id = $request->staff_id;
                    $existingAssignment->assigned_by = $user->id;
                    $existingAssignment->updated_at = $now;
                    $existingAssignment->save();
                }
            } else {
                $assignments[] = [
                    'customer_id' => $customerId,
                    'staff_id' => $request->staff_id,
                    'assigned_by' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($assignments)) {
            LeadAssignment::insert($assignments);
        }

        $assignedCount = $customers->count() - $skipped;
        $msg = "Successfully assigned $assignedCount leads to the selected staff.";
        if ($skipped > 0) {
            $msg .= " ($skipped skipped because they were already assigned to this staff.)";
        }

        return redirect()->back()->with('success', $msg);
    }

    public function show(Request $request, $staff_id)
    {
        $organization_id = auth()->user()->organization_id;
        $staff = Admin::where('organization_id', $organization_id)->findOrFail($staff_id);
        
        $query = LeadAssignment::with('customer')->where('staff_id', $staff_id);

        if ($request->has('batch')) {
            $query->where('created_at', $request->query('batch'));
        }

        $assignments = $query->latest()->paginate(20);

        return view('admin.students_crm.lead_assign.show', compact('staff', 'assignments'));
    }
}
