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
        
        $totalLeads = Customer::where('organization_id', $organization_id)->count();
        
        $totalAssigned = LeadAssignment::whereHas('staff', function($q) use ($organization_id) {
            $q->where('organization_id', $organization_id);
        })->distinct('customer_id')->count('customer_id');
        
        $totalPending = $totalLeads - $totalAssigned;

        $staffStats = [];
        foreach ($staffs as $s) {
            $assignedIds = LeadAssignment::where('staff_id', $s->id)->pluck('customer_id')->toArray();
            $assignedCount = count($assignedIds);
            
            $workedCount = 0;
            if ($assignedCount > 0) {
                $workedCount = \App\Models\CallingHistory::where('updated_by', $s->id)
                    ->whereIn('user_id', $assignedIds)
                    ->distinct('user_id')
                    ->count('user_id');
            }
            
            $pendingCount = $assignedCount - $workedCount;
            
            $staffStats[$s->id] = [
                'staff' => $s,
                'assigned' => $assignedCount,
                'worked' => $workedCount,
                'pending' => $pendingCount
            ];
        }

        $assignmentsSummary = LeadAssignment::select('staff_id', 'created_at as batch_date', DB::raw('count(*) as total_leads'))
            ->whereHas('staff', function($q) use ($organization_id) {
                $q->where('organization_id', $organization_id);
            })
            ->with('staff')
            ->groupBy('staff_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.students_crm.lead_assign.index', compact('categories', 'statuses', 'staffs', 'assignmentsSummary', 'totalLeads', 'totalAssigned', 'totalPending', 'staffStats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'start_number' => 'required|integer|min:1',
            'end_number' => 'required|integer|gte:start_number'
        ]);

        if (!$request->filled('category_id') && !$request->filled('call_status_id')) {
            return redirect()->back()->with('error', 'Please select a Category Pool or a Call Status Filter before assigning leads.');
        }

        $user = auth()->user();
        $organization_id = $user->organization_id;
        $query = Customer::where('organization_id', $organization_id);

        // If not top level, they can only assign leads they currently own
        if (!in_array($user->role, ['superadmin', 'admin', 'Admin'])) {
            $myAssignedCustomerIds = LeadAssignment::where('staff_id', $user->id)->pluck('customer_id');
            $query->whereIn('id', $myAssignedCustomerIds);
        }

        // Filter to select only from available (unassigned) leads
        $query->whereNotExists(function($q) {
            $q->select(DB::raw(1))
              ->from('lead_assignments')
              ->whereColumn('lead_assignments.customer_id', 'users.id');
        });

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('call_status_id')) {
            if ($request->call_status_id === 'all') {
                $customerIdsWithStatus = DB::table('calling_histories')
                    ->where('organization_id', $organization_id)
                    ->whereNotNull('reason')
                    ->where('reason', '<>', '')
                    ->pluck('user_id');
            } else {
                $customerIdsWithStatus = DB::table('calling_histories')
                    ->where('organization_id', $organization_id)
                    ->where('reason', $request->call_status_id)
                    ->pluck('user_id');
            }
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
                    $oldStaffId = $existingAssignment->staff_id;
                    $existingAssignment->staff_id = $request->staff_id;
                    $existingAssignment->assigned_by = $user->id;
                    $existingAssignment->updated_at = $now;
                    $existingAssignment->save();

                    \App\Models\LeadActivityLog::create([
                        'customer_id' => $customerId,
                        'admin_id' => $user->id,
                        'action_type' => 'reassigned',
                        'description' => 'Lead reassigned to staff ID ' . $request->staff_id . ' from staff ID ' . $oldStaffId,
                        'properties' => ['old_staff_id' => $oldStaffId, 'new_staff_id' => $request->staff_id]
                    ]);
                }
            } else {
                $assignments[] = [
                    'customer_id' => $customerId,
                    'staff_id' => $request->staff_id,
                    'assigned_by' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                
                \App\Models\LeadActivityLog::create([
                    'customer_id' => $customerId,
                    'admin_id' => $user->id,
                    'action_type' => 'assigned',
                    'description' => 'Lead assigned to staff ID ' . $request->staff_id,
                    'properties' => ['new_staff_id' => $request->staff_id]
                ]);
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

    public function getFilteredCounts(Request $request)
    {
        $user = auth()->user();
        $organization_id = $user->organization_id;
        
        $query = Customer::where('organization_id', $organization_id);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('call_status_id')) {
            if ($request->call_status_id === 'all') {
                $customerIdsWithStatus = DB::table('calling_histories')
                    ->where('organization_id', $organization_id)
                    ->whereNotNull('reason')
                    ->where('reason', '<>', '')
                    ->pluck('user_id');
            } else {
                $customerIdsWithStatus = DB::table('calling_histories')
                    ->where('organization_id', $organization_id)
                    ->where('reason', $request->call_status_id)
                    ->pluck('user_id');
            }
            $query->whereIn('id', $customerIdsWithStatus);
        }

        $allLeadsIds = $query->pluck('id')->toArray();
        $totalLeads = count($allLeadsIds);

        $assignedLeads = LeadAssignment::whereIn('customer_id', $allLeadsIds)->pluck('customer_id')->toArray();
        $totalAssigned = count(array_unique($assignedLeads));

        $totalPending = $totalLeads - $totalAssigned;

        return response()->json([
            'total' => $totalLeads,
            'assigned' => $totalAssigned,
            'pending' => $totalPending
        ]);
    }

    public function getBatchDetails(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'batch_date' => 'required'
        ]);

        $organization_id = auth()->user()->organization_id;

        $assignments = LeadAssignment::with('customer')
            ->where('staff_id', $request->staff_id)
            ->where('created_at', $request->batch_date)
            ->whereHas('staff', function($q) use ($organization_id) {
                $q->where('organization_id', $organization_id);
            })
            ->get();

        $leads = $assignments->map(function($a) {
            return [
                'id' => $a->customer->id ?? '',
                'name' => $a->customer->name ?? 'Unknown',
                'phone' => $a->customer->phone ?? 'N/A',
                'city' => $a->customer->city ?? 'N/A',
                'category' => $a->customer->category->name ?? 'N/A'
            ];
        });

        return response()->json([
            'status' => 1,
            'leads' => $leads
        ]);
    }

    public function revokeBatch(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'batch_date' => 'required'
        ]);

        $user = auth()->user();
        $organization_id = $user->organization_id;

        $assignments = LeadAssignment::where('staff_id', $request->staff_id)
            ->where('created_at', $request->batch_date)
            ->whereHas('staff', function($q) use ($organization_id) {
                $q->where('organization_id', $organization_id);
            });

        $count = $assignments->count();
        
        if ($count > 0) {
            $assignments->delete();
            return response()->json(['status' => 1, 'message' => "Successfully revoked $count lead assignments."]);
        }

        return response()->json(['status' => 0, 'message' => "No matching assignments found to revoke."]);
    }
}
