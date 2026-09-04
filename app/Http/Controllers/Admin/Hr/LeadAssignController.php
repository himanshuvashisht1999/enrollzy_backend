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
    private function isTopLevelUser($user)
    {
        return in_array(strtolower($user->role ?? ''), ['superadmin', 'admin']);
    }

    private function getAssignableStaffs($user, $organization_id)
    {
        $userRoleName = $user->role ?? '';
        $userRole = \Spatie\Permission\Models\Role::where('name', $userRoleName)->first();

        $allowedRoleNames = [];
        if ($userRole) {
            $allowedRoleIds = \App\Models\RoleAssignRule::where('role_id', $userRole->id)->pluck('can_assign_to_role_id');
            $allowedRoleNames = \Spatie\Permission\Models\Role::whereIn('id', $allowedRoleIds)->pluck('name')->toArray();
        }

        if ($this->isTopLevelUser($user)) {
            // Superadmin / Admin can assign to all staffs with allowed roles
            $staffQuery = Admin::where('organization_id', $organization_id)
                ->where('status', 1)
                ->where('id', '!=', $user->id);

            if (!empty($allowedRoleNames)) {
                $staffQuery->whereIn('role', $allowedRoleNames);
            }

            return $staffQuery->get();
        } else {
            // Manager: only subordinates reporting to this manager matching allowed roles
            if (empty($allowedRoleNames)) {
                return collect();
            }

            return Admin::where('organization_id', $organization_id)
                ->where('status', 1)
                ->whereIn('role', $allowedRoleNames)
                ->where('manager_id', $user->id)
                ->get();
        }
    }

    public function index()
    {
        $user = auth()->user();
        $organization_id = $user->organization_id;
        $categories = CustomerCategory::where('organization_id', $organization_id)->where('parent_id', 0)->with('childrenRecursive')->get();
        $statuses = CallingStatus::where('organization_id', $organization_id)->where('status', 1)->get();
        $staffs = $this->getAssignableStaffs($user, $organization_id);

        $isTopLevel = $this->isTopLevelUser($user);

        if ($isTopLevel) {
            // Top Level: Global Pool Numbers
            $totalLeads = Customer::where('organization_id', $organization_id)->count();
            $totalAssigned = LeadAssignment::whereHas('staff', function($q) use ($organization_id) {
                $q->where('organization_id', $organization_id);
            })->distinct('customer_id')->count('customer_id');
            $totalPending = max(0, $totalLeads - $totalAssigned);

            $assignmentsSummary = LeadAssignment::select('staff_id', 'created_at as batch_date', DB::raw('count(*) as total_leads'))
                ->whereHas('staff', function($q) use ($organization_id) {
                    $q->where('organization_id', $organization_id);
                })
                ->with('staff')
                ->groupBy('staff_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'log_page');
        } else {
            // Manager Level: Scoped to Manager's own quota / pool
            $totalPending = LeadAssignment::where('staff_id', $user->id)->count();
            $totalAssigned = LeadAssignment::where('assigned_by', $user->id)
                ->where('staff_id', '!=', $user->id)
                ->count();
            $totalLeads = $totalPending + $totalAssigned;

            $assignmentsSummary = LeadAssignment::select('staff_id', 'created_at as batch_date', DB::raw('count(*) as total_leads'))
                ->where('assigned_by', $user->id)
                ->where('staff_id', '!=', $user->id)
                ->with('staff')
                ->groupBy('staff_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'log_page');
        }

        $staffStats = [];
        foreach ($staffs as $s) {
            $assignedCount = LeadAssignment::where('staff_id', $s->id)->count();
            
            $workedCount = 0;
            if ($assignedCount > 0) {
                $workedCount = DB::table('calling_histories')
                    ->join('lead_assignments', 'lead_assignments.customer_id', '=', 'calling_histories.user_id')
                    ->where('lead_assignments.staff_id', $s->id)
                    ->where('calling_histories.updated_by', $s->id)
                    ->distinct('calling_histories.user_id')
                    ->count('calling_histories.user_id');
            }
            
            $pendingCount = max(0, $assignedCount - $workedCount);
            
            $staffStats[$s->id] = [
                'staff' => $s,
                'assigned' => $assignedCount,
                'worked' => $workedCount,
                'pending' => $pendingCount
            ];
        }

        return view('admin.students_crm.lead_assign.index', compact(
            'categories', 'statuses', 'staffs', 'assignmentsSummary', 
            'totalLeads', 'totalAssigned', 'totalPending', 'staffStats', 'isTopLevel'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:admin,id',
            'start_number' => 'required|integer|min:1',
            'end_number' => 'required|integer|gte:start_number'
        ]);

        $user = auth()->user();
        $organization_id = $user->organization_id;
        $isTopLevel = $this->isTopLevelUser($user);

        // Verify target staff is allowed for this user based on role assign rules
        $allowedStaffs = $this->getAssignableStaffs($user, $organization_id);
        if (!$allowedStaffs->contains('id', $request->staff_id)) {
            return redirect()->back()->with('error', 'You do not have permission to assign leads to the selected staff member based on Role Assignment Rules.');
        }

        return DB::transaction(function() use ($request, $user, $organization_id, $isTopLevel) {
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

            if ($isTopLevel) {
                // Top Level: pull from unassigned leads if no call status specified
                if (!$request->filled('call_status_id')) {
                    $query->whereNotExists(function($q) {
                        $q->select(DB::raw(1))
                          ->from('lead_assignments')
                          ->whereColumn('lead_assignments.customer_id', 'users.id');
                    });
                }
            } else {
                // Manager: pull strictly from leads currently assigned to this manager
                $myAvailableCustomerIds = LeadAssignment::where('staff_id', $user->id)->pluck('customer_id')->toArray();
                $query->whereIn('id', $myAvailableCustomerIds);
            }

            $skip = $request->start_number - 1;
            $take = $request->end_number - $request->start_number + 1;

            $customers = $query->orderBy('id', 'asc')->skip($skip)->take($take)->lockForUpdate()->pluck('id');

            if ($customers->isEmpty()) {
                return redirect()->back()->with('error', 'No leads found for the given criteria in your available pool.');
            }

            $assignedCount = 0;
            $skipped = 0;
            $now = now();

            foreach ($customers as $customerId) {
                $existingAssignment = LeadAssignment::where('customer_id', $customerId)->first();

                if ($existingAssignment) {
                    if ($existingAssignment->staff_id == $request->staff_id) {
                        $skipped++;
                    } else {
                        // Reassign / Delegate lead
                        $oldStaffId = $existingAssignment->staff_id;
                        $isReassigned = ($oldStaffId != $user->id);

                        $existingAssignment->staff_id = $request->staff_id;
                        $existingAssignment->assigned_by = $user->id;
                        $existingAssignment->is_reassigned = $isReassigned ? 1 : 0;
                        $existingAssignment->created_at = $now;
                        $existingAssignment->updated_at = $now;
                        $existingAssignment->save();

                        if ($isReassigned) {
                            \App\Models\LeadActivityLog::create([
                                'customer_id' => $customerId,
                                'admin_id' => $user->id,
                                'action_type' => 'reassigned',
                                'description' => 'Lead reassigned to staff ID ' . $request->staff_id . ' from staff ID ' . $oldStaffId,
                                'properties' => ['old_staff_id' => $oldStaffId, 'new_staff_id' => $request->staff_id]
                            ]);
                        } else {
                            \App\Models\LeadActivityLog::create([
                                'customer_id' => $customerId,
                                'admin_id' => $user->id,
                                'action_type' => 'assigned',
                                'description' => 'Lead assigned to staff ID ' . $request->staff_id,
                                'properties' => ['new_staff_id' => $request->staff_id]
                            ]);
                        }
                        $assignedCount++;
                    }
                } else {
                    // Create new assignment - guaranteed unique per customer
                    LeadAssignment::create([
                        'customer_id' => $customerId,
                        'staff_id' => $request->staff_id,
                        'assigned_by' => $user->id,
                        'is_reassigned' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    \App\Models\LeadActivityLog::create([
                        'customer_id' => $customerId,
                        'admin_id' => $user->id,
                        'action_type' => 'assigned',
                        'description' => 'Lead assigned to staff ID ' . $request->staff_id,
                        'properties' => ['new_staff_id' => $request->staff_id]
                    ]);
                    $assignedCount++;
                }
            }

            $msg = "Successfully assigned {$assignedCount} leads to the selected staff member.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} skipped because they were already assigned to this staff.)";
            }

            return redirect()->back()->with('success', $msg);
        });
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
        $isTopLevel = $this->isTopLevelUser($user);
        
        $query = Customer::where('organization_id', $organization_id);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('call_status_id')) {
            if ($request->call_status_id === 'all') {
                $query->whereExists(function($q) use ($organization_id) {
                    $q->select(DB::raw(1))
                      ->from('calling_histories')
                      ->whereColumn('calling_histories.user_id', 'users.id')
                      ->where('calling_histories.organization_id', $organization_id)
                      ->whereNotNull('reason')
                      ->where('reason', '<>', '');
                });
            } else {
                $query->whereExists(function($q) use ($organization_id, $request) {
                    $q->select(DB::raw(1))
                      ->from('calling_histories')
                      ->whereColumn('calling_histories.user_id', 'users.id')
                      ->where('calling_histories.organization_id', $organization_id)
                      ->where('reason', $request->call_status_id);
                });
            }
        }

        if ($isTopLevel) {
            $totalLeads = (clone $query)->count();

            $totalAssigned = (clone $query)->whereExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('lead_assignments')
                  ->whereColumn('lead_assignments.customer_id', 'users.id');
            })->count();

            if ($request->filled('call_status_id')) {
                $totalPending = $totalLeads;
            } else {
                $totalPending = max(0, $totalLeads - $totalAssigned);
            }
        } else {
            // Manager: count within manager's assigned leads
            $totalPending = (clone $query)->whereExists(function($q) use ($user) {
                $q->select(DB::raw(1))
                  ->from('lead_assignments')
                  ->whereColumn('lead_assignments.customer_id', 'users.id')
                  ->where('lead_assignments.staff_id', $user->id);
            })->count();

            $totalAssigned = (clone $query)->whereExists(function($q) use ($user) {
                $q->select(DB::raw(1))
                  ->from('lead_assignments')
                  ->whereColumn('lead_assignments.customer_id', 'users.id')
                  ->where('lead_assignments.assigned_by', $user->id)
                  ->where('lead_assignments.staff_id', '!=', $user->id);
            })->count();

            $totalLeads = $totalPending + $totalAssigned;
        }

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
            'batch_date' => 'required',
            'page' => 'nullable|integer|min:1'
        ]);

        $organization_id = auth()->user()->organization_id;
        $perPage = 20;

        $paginatedAssignments = LeadAssignment::with(['customer.category'])
            ->where('staff_id', $request->staff_id)
            ->where('created_at', $request->batch_date)
            ->whereHas('staff', function($q) use ($organization_id) {
                $q->where('organization_id', $organization_id);
            })
            ->paginate($perPage);

        $leads = collect($paginatedAssignments->items())->map(function($a) {
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
            'leads' => $leads,
            'pagination' => [
                'current_page' => $paginatedAssignments->currentPage(),
                'last_page' => $paginatedAssignments->lastPage(),
                'total' => $paginatedAssignments->total(),
                'per_page' => $paginatedAssignments->perPage(),
                'has_more' => $paginatedAssignments->hasMorePages()
            ]
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
