<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\CallingHistory;
use App\Models\CallingStatus;
use App\Models\CallingAction;
use App\Models\Customer;
use App\Models\Course;
use App\Models\Organisation;
use App\Models\CallingManualUser;
use App\Models\CallingHistoryLog;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\CustomerCategory;
use App\Models\WhatsappTemplate;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CallingHistoryImport;
use App\Exports\CallingHistorySampleExport;

class CallingController extends Controller
{
    public function reassign(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'staff_id' => 'required'
        ]);

        $user = auth()->user();

        $existingAssignment = \App\Models\LeadAssignment::where('customer_id', $request->customer_id)
            ->orderBy('id', 'desc')
            ->first();

        if ($existingAssignment) {
            $oldStaffId = $existingAssignment->staff_id;
            $hasPastCallingHistory = \App\Models\CallingHistory::where('user_id', $request->customer_id)->exists();
            $isReassigned = $hasPastCallingHistory || ($oldStaffId != $user->id) || ($existingAssignment->is_reassigned == 1);

            $existingAssignment->staff_id = $request->staff_id;
            $existingAssignment->assigned_by = $user->id;
            $existingAssignment->is_reassigned = $isReassigned ? 1 : 0;
            $existingAssignment->created_at = now();
            $existingAssignment->updated_at = now();
            $existingAssignment->save();

            // Clean up any historical duplicate assignment records for this customer
            \App\Models\LeadAssignment::where('customer_id', $request->customer_id)
                ->where('id', '!=', $existingAssignment->id)
                ->delete();

            if ($isReassigned) {
                \App\Models\LeadActivityLog::create([
                    'customer_id' => $request->customer_id,
                    'admin_id' => $user->id,
                    'action_type' => 'reassigned',
                    'description' => 'Lead reassigned to staff ID ' . $request->staff_id . ' from staff ID ' . $oldStaffId,
                    'properties' => ['old_staff_id' => $oldStaffId, 'new_staff_id' => $request->staff_id]
                ]);
            } else {
                \App\Models\LeadActivityLog::create([
                    'customer_id' => $request->customer_id,
                    'admin_id' => $user->id,
                    'action_type' => 'assigned',
                    'description' => 'Lead assigned to staff ID ' . $request->staff_id,
                    'properties' => ['new_staff_id' => $request->staff_id]
                ]);
            }
        } else {
            $hasPastCallingHistory = \App\Models\CallingHistory::where('user_id', $request->customer_id)->exists();
            $isReassigned = $hasPastCallingHistory ? 1 : 0;

            \App\Models\LeadAssignment::create([
                'customer_id' => $request->customer_id,
                'staff_id' => $request->staff_id,
                'assigned_by' => $user->id,
                'is_reassigned' => $isReassigned
            ]);

            \App\Models\LeadActivityLog::create([
                'customer_id' => $request->customer_id,
                'admin_id' => $user->id,
                'action_type' => $isReassigned ? 'reassigned' : 'assigned',
                'description' => 'Lead assigned to staff ID ' . $request->staff_id . ($isReassigned ? ' (Reassigned / Previously Contacted)' : ''),
                'properties' => ['new_staff_id' => $request->staff_id]
            ]);
        }

        return response()->json(['status' => 1, 'message' => 'Lead reassigned successfully!']);
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'queue_customer_ids' => 'required',
        ]);

        $customerIds = json_decode($request->queue_customer_ids, true);
        if (!is_array($customerIds) || empty($customerIds)) {
            return redirect()->back()->with('error', 'No leads found in the queue.');
        }

        if ($request->select_all != 1) {
            $request->validate([
                'start_number' => 'required|integer|min:1',
                'end_number' => 'required|integer|gte:start_number'
            ]);

            $skip = $request->start_number - 1;
            $take = $request->end_number - $request->start_number + 1;
            $customerIds = array_slice($customerIds, $skip, $take);
        }

        if (empty($customerIds)) {
            return redirect()->back()->with('error', 'No leads selected for assignment.');
        }

        $user = auth()->user();
        $now = now();
        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($customerIds as $customerId) {
            $existingAssignment = \App\Models\LeadAssignment::where('customer_id', $customerId)->first();
            $hasPastCallingHistory = \App\Models\CallingHistory::where('user_id', $customerId)->exists();

            if ($existingAssignment) {
                if ($existingAssignment->staff_id == $request->staff_id) {
                    $skippedCount++;
                } else {
                    $oldStaffId = $existingAssignment->staff_id;
                    $isReassigned = $hasPastCallingHistory || ($oldStaffId != $user->id) || ($existingAssignment->is_reassigned == 1);

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
                $isReassigned = $hasPastCallingHistory ? 1 : 0;
                \App\Models\LeadAssignment::create([
                    'customer_id' => $customerId,
                    'staff_id' => $request->staff_id,
                    'assigned_by' => $user->id,
                    'is_reassigned' => $isReassigned,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                \App\Models\LeadActivityLog::create([
                    'customer_id' => $customerId,
                    'admin_id' => $user->id,
                    'action_type' => $isReassigned ? 'reassigned' : 'assigned',
                    'description' => 'Lead assigned to staff ID ' . $request->staff_id . ($isReassigned ? ' (Reassigned / Previously Contacted)' : ''),
                    'properties' => ['new_staff_id' => $request->staff_id]
                ]);
                $assignedCount++;
            }
        }

        $msg = "Successfully assigned $assignedCount leads.";
        if ($skippedCount > 0) {
            $msg .= " ($skippedCount skipped because they were already assigned to this staff.)";
        }

        return redirect()->back()->with('success', $msg);
    }

    public function unlockNumber(Request $request)
    {
        $customerId = $request->customer_id;
        $user = auth()->user();
        
        if ($user->unlocked_lead_id && $user->unlocked_lead_id != $customerId) {
            return response()->json([
                'status' => 0,
                'message' => 'You must update the status of the previously unlocked lead before viewing another number.'
            ]);
        }
        
        $customer = \App\Models\Customer::find($customerId);
        
        $user->unlocked_lead_id = $customerId;
        $user->save();
        
        return response()->json([
            'status' => 1,
            'phone' => $customer->phone
        ]);
    }

    public function dashboard(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $staff_id = auth()->id();
        
        $user = auth()->user();
        if (isset($user->is_admin) && $user->is_admin) {
            $staffs = \App\Models\Admin::where('organization_id', $organization_id)->where('status', 1)->get();
        } else {
            $allowedRoleIds = \App\Models\RoleAssignRule::whereHas('role', function($q) use ($user) {
                $q->where('name', $user->role);
            })->pluck('can_assign_to_role_id');
            $allowedRoleNames = \Spatie\Permission\Models\Role::whereIn('id', $allowedRoleIds)->pluck('name')->toArray();

            $staffs = \App\Models\Admin::where('organization_id', $organization_id)
                ->where('status', 1)
                ->whereIn('role', $allowedRoleNames)
                ->where('manager_id', $user->id)
                ->get();
        }

        $query_staff_id = $staff_id;
        $is_filtering_subordinate = false;
        if ($request->filled('staff_id')) {
            $reqStaffId = $request->staff_id;
            if ($reqStaffId == 'all') {
                $query_staff_ids = array_merge([$staff_id], $staffs->pluck('id')->toArray());
            } elseif ($reqStaffId == $staff_id || $staffs->contains('id', $reqStaffId)) {
                $query_staff_id = $reqStaffId;
                if ($reqStaffId != $staff_id) {
                    $is_filtering_subordinate = true;
                }
            }
        }

        if (!isset($query_staff_ids)) {
            $query_staff_ids = [$query_staff_id];
        }

        $defaultEndDate = now()->format('Y-m-d');
        $sevenDaysAgo = now()->subDays(7);
        $defaultStartDate = $sevenDaysAgo->isSameMonth(now())
            ? $sevenDaysAgo->format('Y-m-d')
            : now()->startOfMonth()->format('Y-m-d');

        $startDate = $request->input('start_date', $defaultStartDate);
        $endDate = $request->input('end_date', $defaultEndDate);
        
        $currentMonth = \Carbon\Carbon::parse($startDate)->format('F');
        $currentYear = \Carbon\Carbon::parse($startDate)->format('Y');

        // 1. Leads assigned in date range
                // Filters
        $customerFilterQuery = \App\Models\Customer::select('id')->where('organization_id', $organization_id);
        $hasCustomerFilter = false;

        if ($request->filled('category')) {
            $customerFilterQuery->where('category_id', $request->category);
            $hasCustomerFilter = true;
        }
        if ($request->filled('country')) {
            $customerFilterQuery->where('country', $request->country);
            $hasCustomerFilter = true;
        }
        if ($request->filled('state')) {
            $customerFilterQuery->where('state', $request->state);
            $hasCustomerFilter = true;
        }
        if ($request->filled('city')) {
            $customerFilterQuery->where('city', $request->city);
            $hasCustomerFilter = true;
        }
        if ($request->filled('filter_name')) {
            $searchTerm = trim($request->filter_name);
            $customerFilterQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('id', $searchTerm);
            });
            $hasCustomerFilter = true;
        }
        if ($request->filled('filter_phone')) {
            $customerFilterQuery->where('phone', 'LIKE', '%' . $request->filter_phone . '%');
            $hasCustomerFilter = true;
        }
        if ($request->filled('session_id')) {
            $customerFilterQuery->whereJsonContains('session_ids', (string)$request->session_id);
            $hasCustomerFilter = true;
        }

        $filteredCustomerIds = [];
        if ($hasCustomerFilter) {
            $filteredCustomerIds = $customerFilterQuery->pluck('id')->toArray();
        }
        $assignedTodayQuery = \App\Models\LeadAssignment::whereIn('staff_id', $query_staff_ids)
            ->whereDate('updated_at', '>=', $startDate)
            ->whereDate('updated_at', '<=', $endDate);
            
        if ($hasCustomerFilter) {
            $assignedTodayQuery->whereIn('customer_id', $filteredCustomerIds);
        }

        $assignedToday = $assignedTodayQuery->pluck('customer_id')->toArray();
        $leadsAssignedTodayCount = count($assignedToday);

        // 2. Leads pending in queue (assigned in date range but no calling history by this staff since assignment)
        $workedOnCustomerIds = \App\Models\CallingHistory::join('lead_assignments', function($join) {
                $join->on('calling_histories.user_id', '=', 'lead_assignments.customer_id')
                     ->on('calling_histories.updated_by', '=', 'lead_assignments.staff_id')
                     ->whereColumn('calling_histories.created_at', '>=', 'lead_assignments.updated_at');
            })
            ->whereIn('calling_histories.updated_by', $query_staff_ids)
            ->whereIn('calling_histories.user_id', $assignedToday)
            ->pluck('calling_histories.user_id')
            ->toArray();
        
        $pendingInQueueCount = $leadsAssignedTodayCount - count(array_unique($workedOnCustomerIds));

        // 3. Follow-ups due in date range & Overdue
        // We need to find customers whose LATEST calling history by this staff has date_required = date range since assignment
        $latestHistoriesSub = \Illuminate\Support\Facades\DB::table('calling_histories')
            ->join('lead_assignments', function($join) {
                $join->on('calling_histories.user_id', '=', 'lead_assignments.customer_id')
                     ->on('calling_histories.updated_by', '=', 'lead_assignments.staff_id')
                     ->whereColumn('calling_histories.created_at', '>=', 'lead_assignments.updated_at');
            })
            ->select(\Illuminate\Support\Facades\DB::raw('MAX(calling_histories.id) as id'))
            ->whereIn('calling_histories.updated_by', $query_staff_ids);
            
        if ($hasCustomerFilter) {
            $latestHistoriesSub->whereIn('calling_histories.user_id', $filteredCustomerIds);
        }

        $latestHistoriesSub = $latestHistoriesSub->groupBy('calling_histories.user_id');

        $latestHistoriesIds = $latestHistoriesSub->pluck('id')->toArray();

        $latestHistoriesQuery = \App\Models\CallingHistory::with(['customer', 'calling_status'])
            ->whereIn('id', $latestHistoriesIds);

        if ($request->filled('call_status_id')) {
            if ($request->call_status_id === 'all') {
                $latestHistoriesQuery->whereNotNull('reason');
            } elseif (in_array($request->call_status_id, ['new', 'unattempted', 'reassigned'])) {
                $latestHistoriesQuery->whereRaw('1 = 0');
            } else {
                $latestHistoriesQuery->where('reason', $request->call_status_id);
            }
        }

        $latestHistories = $latestHistoriesQuery->get();
        
        $followUpsDueToday = $latestHistories->filter(function($h) use ($startDate, $endDate) {
            return $h->date_required >= $startDate && $h->date_required <= $endDate;
        });
        $followUpsDueTodayCount = $followUpsDueToday->count();

        $overdueFollowUps = $latestHistories->filter(function($h) use ($startDate) {
            return !empty($h->date_required) && $h->date_required < $startDate;
        });

        // 4. Admissions in date range
        $admissionStatus = \App\Models\CallingStatus::where('organization_id', $organization_id)
            ->where('name', 'like', '%Admission%')
            ->first();
            
        $admissionsThisMonthCount = 0;
        if ($admissionStatus) {
            $admissionsThisMonthCount = \App\Models\CallingHistory::whereIn('updated_by', $query_staff_ids)
                ->where('reason', $admissionStatus->id)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count();
        }

        // Target for the start date's month
        $admissionsTarget = \App\Models\TargetLead::whereIn('staff_id', $query_staff_ids)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->sum('month_target_admissions');
            
        $targetProgress = 0;
        if ($admissionsTarget > 0) {
            $targetProgress = round(($admissionsThisMonthCount / $admissionsTarget) * 100);
            if ($targetProgress > 100) $targetProgress = 100;
        }

        // --- TEAM METRICS ---
        $subordinates = \App\Models\Admin::where('manager_id', $query_staff_id)->where('status', 1)->get();
        $hasSubordinates = $subordinates->count() > 0;
        
        $teamLeadsDelegated = 0;
        $teamAdmissionsCount = 0;
        $teamMetrics = [];
        
        if ($hasSubordinates) {
            $subordinateIds = $subordinates->pluck('id')->toArray();
            
            // Total leads delegated to them in this date range
            $teamLeadsDelegatedQuery = \App\Models\LeadAssignment::whereIn('staff_id', $subordinateIds)
                ->where('assigned_by', $query_staff_id)
                ->whereDate('updated_at', '>=', $startDate)
                ->whereDate('updated_at', '<=', $endDate);
                
            if ($hasCustomerFilter) {
                $teamLeadsDelegatedQuery->whereIn('customer_id', $filteredCustomerIds);
            }
            $teamLeadsDelegated = $teamLeadsDelegatedQuery->count();
                
            if ($admissionStatus) {
                $teamAdmQuery = \App\Models\CallingHistory::whereIn('updated_by', $subordinateIds)
                    ->where('reason', $admissionStatus->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
                if ($hasCustomerFilter) {
                    $teamAdmQuery->whereIn('user_id', $filteredCustomerIds);
                }
                $teamAdmissionsCount = $teamAdmQuery->count();
            }
            
            // Roll up team admissions into personal target progress! (User request)
            $admissionsThisMonthCount += $teamAdmissionsCount; // include team admissions
            if ($admissionsTarget > 0) {
                $targetProgress = round(($admissionsThisMonthCount / $admissionsTarget) * 100);
                if ($targetProgress > 100) $targetProgress = 100;
            }
            
            // Build subordinate leaderboard
            foreach ($subordinates as $sub) {
                // Get the exact customers assigned to this sub by me in this date range
                $subAssignedQuery = \App\Models\LeadAssignment::where('staff_id', $sub->id)
                    ->where('assigned_by', $query_staff_id)
                    ->whereDate('updated_at', '>=', $startDate)
                    ->whereDate('updated_at', '<=', $endDate);

                if ($hasCustomerFilter) {
                    $subAssignedQuery->whereIn('customer_id', $filteredCustomerIds);
                }
                $subAssignedCustomers = $subAssignedQuery->pluck('customer_id')->toArray();
                
                $subLeadsCount = count($subAssignedCustomers);

                // How many of these has the sub worked on since assignment?
                $subWorkedOnCustomers = \App\Models\CallingHistory::join('lead_assignments', function($join) {
                        $join->on('calling_histories.user_id', '=', 'lead_assignments.customer_id')
                             ->on('calling_histories.updated_by', '=', 'lead_assignments.staff_id')
                             ->whereColumn('calling_histories.created_at', '>=', 'lead_assignments.updated_at');
                    })
                    ->where('calling_histories.updated_by', $sub->id)
                    ->whereIn('calling_histories.user_id', $subAssignedCustomers)
                    ->pluck('calling_histories.user_id')->toArray();
                $subWorkedOnCount = count(array_unique($subWorkedOnCustomers));
                
                // Pending means Assigned - Worked On
                $subPendingCount = $subLeadsCount - $subWorkedOnCount;

                // Follow ups due in this date range for this sub since assignment
                $subLatestSub = \Illuminate\Support\Facades\DB::table('calling_histories')
                    ->join('lead_assignments', function($join) {
                        $join->on('calling_histories.user_id', '=', 'lead_assignments.customer_id')
                             ->on('calling_histories.updated_by', '=', 'lead_assignments.staff_id')
                             ->whereColumn('calling_histories.created_at', '>=', 'lead_assignments.updated_at');
                    })
                    ->select(\Illuminate\Support\Facades\DB::raw('MAX(calling_histories.id) as id'))
                    ->where('calling_histories.updated_by', $sub->id)
                    ->groupBy('calling_histories.user_id');
                
                $subLatestIds = $subLatestSub->pluck('id')->toArray();
                
                $subFollowUpsDue = \App\Models\CallingHistory::whereIn('id', $subLatestIds)
                    ->where('date_required', '>=', $startDate)
                    ->where('date_required', '<=', $endDate)
                    ->count();
                    
                $subAdmissions = 0;
                if ($admissionStatus) {
                    $subAdmissions = \App\Models\CallingHistory::where('updated_by', $sub->id)
                        ->where('reason', $admissionStatus->id)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->count();
                }
                
                $teamMetrics[] = [
                    'name' => $sub->name,
                    'role' => $sub->role,
                    'leads_assigned' => $subLeadsCount,
                    'leads_worked' => $subWorkedOnCount,
                    'leads_pending' => $subPendingCount,
                    'followups_due' => $subFollowUpsDue,
                    'admissions' => $subAdmissions
                ];
            }
        }
        // --- END TEAM METRICS ---

        // Build the Queue List
        $queue = collect();
        
        foreach ($overdueFollowUps as $history) {
            if($history->customer) {
                $queue->push([
                    'type' => 'overdue',
                    'customer' => $history->customer,
                    'history' => $history,
                    'sort_date' => $history->date_required
                ]);
            }
        }
        
        foreach ($followUpsDueToday as $history) {
            if($history->customer) {
                $queue->push([
                    'type' => 'due_today',
                    'customer' => $history->customer,
                    'history' => $history,
                    'sort_date' => $history->date_required
                ]);
            }
        }
        
        $unattemptedIds = array_diff($assignedToday, $workedOnCustomerIds);
        if ($request->filled('call_status_id') && !in_array($request->call_status_id, ['new', 'unattempted', 'reassigned'])) {
            $unattemptedIds = [];
        }

        // Fetch assignments lookup for accurate assignment timestamps
        $allQueueCustomerIds = array_unique(array_merge(
            $overdueFollowUps->pluck('user_id')->toArray(),
            $followUpsDueToday->pluck('user_id')->toArray(),
            $unattemptedIds
        ));

        $assignmentsLookup = \App\Models\LeadAssignment::with('assigner')->whereIn('staff_id', $query_staff_ids)
            ->whereIn('customer_id', $allQueueCustomerIds)
            ->orderBy('id', 'desc')
            ->get()
            ->unique('customer_id')
            ->keyBy('customer_id');

        if (!empty($unattemptedIds)) {
            $unattemptedCustomers = \App\Models\Customer::whereIn('id', $unattemptedIds)->get()->keyBy('id');
            $unattemptedPastHistories = \App\Models\CallingHistory::with(['calling_status', 'staff'])
                ->whereIn('user_id', $unattemptedIds)
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('user_id');

            foreach ($unattemptedIds as $cid) {
                if (isset($unattemptedCustomers[$cid])) {
                    $asgn = $assignmentsLookup->get($cid);
                    $pastHistories = $unattemptedPastHistories->get($cid);
                    $hasPastHistory = ($pastHistories && $pastHistories->isNotEmpty());
                    $latestPastHistory = $hasPastHistory ? $pastHistories->first() : null;

                    $isReassigned = ($asgn ? (bool)$asgn->is_reassigned : false) || $hasPastHistory;

                    if ($request->filled('call_status_id')) {
                        if ($request->call_status_id === 'new' && $isReassigned) {
                            continue;
                        }
                        if ($request->call_status_id === 'reassigned' && !$isReassigned) {
                            continue;
                        }
                    }

                    $queue->push([
                        'type' => $isReassigned ? 'reassigned' : 'new',
                        'customer' => $unattemptedCustomers[$cid],
                        'history' => $latestPastHistory,
                        'has_past_history' => $hasPastHistory,
                        'sort_date' => $asgn ? $asgn->updated_at : $startDate,
                        'assignment_id' => $asgn ? $asgn->id : $cid
                    ]);
                }
            }
        }
        
        // Sort queue: Priority (Overdue -> Due Today -> Reassigned Leads -> New Leads) and newest first within each category
        $queue = $queue->sort(function($a, $b) {
            $typePriority = ['overdue' => 1, 'due_today' => 2, 'reassigned' => 3, 'new' => 4];
            $pA = $typePriority[$a['type']] ?? 5;
            $pB = $typePriority[$b['type']] ?? 5;

            if ($pA !== $pB) {
                return $pA <=> $pB;
            }

            // Within same type, sort newest first
            $dateA = $a['sort_date'] ?? '';
            $dateB = $b['sort_date'] ?? '';

            if ($dateA != $dateB) {
                return $dateB <=> $dateA; // Newest first
            }

            $idA = $a['assignment_id'] ?? $a['customer']->id;
            $idB = $b['assignment_id'] ?? $b['customer']->id;

            return $idB <=> $idA; // Newest first
        })->values();

        $latestAssignmentsIds = \Illuminate\Support\Facades\DB::table('lead_assignments')
            ->select(\Illuminate\Support\Facades\DB::raw('MAX(id) as id'))
            ->groupBy('customer_id')
            ->pluck('id')->toArray();

        $delegatedQuery = \App\Models\LeadAssignment::with(['customer', 'staff'])
            ->whereIn('id', $latestAssignmentsIds)
            ->whereDate('updated_at', '>=', $startDate)
            ->whereDate('updated_at', '<=', $endDate)
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc');

        if ($is_filtering_subordinate) {
            $delegatedQuery->where('assigned_by', $staff_id)
                ->where('staff_id', $query_staff_id);
        } else {
            $delegatedQuery->where('assigned_by', $staff_id)
                ->where('staff_id', '!=', $staff_id);
        }

        if ($hasCustomerFilter) {
            $delegatedQuery->whereIn('customer_id', $filteredCustomerIds);
        }

        $delegatedLeads = $delegatedQuery->orderBy('updated_at', 'desc')->get();

        // Filter out leads that the assigned staff member has already worked on since the latest assignment
        $delegatedLeadsCustomerIds = $delegatedLeads->pluck('customer_id')->toArray();
        $histories = \App\Models\CallingHistory::whereIn('user_id', $delegatedLeadsCustomerIds)
            ->with(['calling_status', 'staff'])
            ->get()
            ->groupBy('user_id');

        $delegatedLeads = $delegatedLeads->filter(function($assignment) use ($histories) {
            $customerHistories = $histories->get($assignment->customer_id);
            if (!$customerHistories) {
                return true;
            }
            foreach ($customerHistories as $h) {
                if ($h->updated_by == $assignment->staff_id && $h->created_at >= $assignment->updated_at) {
                    return false;
                }
            }
            return true;
        })->values();

        foreach ($delegatedLeads as $assignment) {
            $customerHistories = $histories->get($assignment->customer_id);
            $hasAnyHistory = ($customerHistories && $customerHistories->isNotEmpty());
            $isReassigned = (bool)$assignment->is_reassigned || $hasAnyHistory;

            if (!$hasAnyHistory) {
                $assignment->lead_type = $isReassigned ? 'reassigned' : 'new';
                $assignment->latest_history = null;
            } else {
                $latestHistory = $customerHistories->sortByDesc('id')->first();
                $assignment->latest_history = $latestHistory;
                if ($latestHistory && $latestHistory->date_required) {
                    if ($latestHistory->date_required < $startDate) {
                        $assignment->lead_type = 'overdue';
                    } else {
                        $assignment->lead_type = 'due_today';
                    }
                } else {
                    $assignment->lead_type = 'reassigned';
                }
            }
        }

        if ($request->filled('call_status_id')) {
            $statusFilter = $request->call_status_id;
            $delegatedLeads = $delegatedLeads->filter(function($assignment) use ($statusFilter) {
                if ($statusFilter === 'new') {
                    return $assignment->lead_type === 'new';
                } elseif ($statusFilter === 'reassigned') {
                    return $assignment->lead_type === 'reassigned';
                } elseif ($statusFilter === 'unattempted') {
                    return in_array($assignment->lead_type, ['new', 'reassigned']);
                } elseif ($statusFilter === 'all') {
                    $lh = $assignment->latest_history;
                    return !empty($lh && $lh->reason);
                } else {
                    $lh = $assignment->latest_history;
                    return $lh && $lh->reason == $statusFilter;
                }
            })->values();
        }

        // 5. Worked Leads History Queue
        $historyQuery = \App\Models\CallingHistory::with(['customer', 'calling_status', 'staff'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);
            
        if ($hasCustomerFilter) {
            $historyQuery->whereIn('user_id', $filteredCustomerIds);
        }

        if ($request->filled('call_status_id')) {
            if ($request->call_status_id === 'all') {
                $historyQuery->whereNotNull('reason');
            } elseif (in_array($request->call_status_id, ['new', 'unattempted', 'reassigned'])) {
                $historyQuery->whereRaw('1 = 0');
            } else {
                $historyQuery->where('reason', $request->call_status_id);
            }
        }
        
        // Scope history by staff role & latest active assignments
        $latestAssignmentsLookup = \App\Models\LeadAssignment::with(['assigner', 'staff'])
            ->whereIn('id', $latestAssignmentsIds)
            ->get()
            ->keyBy('customer_id');

        if ($is_filtering_subordinate) {
            $subordinateActiveCustomerIds = $latestAssignmentsLookup->filter(function($asgn) use ($staff_id, $query_staff_id, $user) {
                if (isset($user->is_admin) && $user->is_admin) {
                    return $asgn->staff_id == $query_staff_id;
                }
                return $asgn->staff_id == $query_staff_id && $asgn->assigned_by == $staff_id;
            })->pluck('customer_id')->toArray();

            $historyQuery->where('updated_by', $query_staff_id)
                ->whereIn('user_id', $subordinateActiveCustomerIds);
        } else {
            if (isset($user->is_admin) && $user->is_admin) {
                $allOrgActiveCustomerIds = $latestAssignmentsLookup->pluck('customer_id')->toArray();
                $historyQuery->whereIn('user_id', $allOrgActiveCustomerIds);
            } else {
                $myActiveAssignedCustomerIds = $latestAssignmentsLookup->filter(function($asgn) use ($staff_id) {
                    return $asgn->staff_id == $staff_id;
                })->pluck('customer_id')->toArray();

                $delegatedActiveCustomerIds = $latestAssignmentsLookup->filter(function($asgn) use ($staff_id) {
                    return $asgn->assigned_by == $staff_id;
                })->pluck('customer_id')->toArray();

                $historyQuery->where(function($q) use ($staff_id, $myActiveAssignedCustomerIds, $delegatedActiveCustomerIds) {
                    $q->where(function($subQ) use ($staff_id, $myActiveAssignedCustomerIds) {
                        $subQ->where('updated_by', $staff_id)
                             ->whereIn('user_id', $myActiveAssignedCustomerIds);
                    });

                    if (!empty($delegatedActiveCustomerIds)) {
                        $q->orWhereIn('user_id', $delegatedActiveCustomerIds);
                    }
                });
            }
        }
        
        $workedHistory = $historyQuery->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();

        // Strictly verify that for every history record:
        // 1. The customer's CURRENT active assignment is held by the staff member who made the call
        // 2. The call was logged after or on the latest assignment
        // 3. Reassigned leads move out until worked on by the newly assigned staff
        // 4. Multiple calls to the same lead show only the single latest attempt
        $workedHistory = $workedHistory->filter(function($history) use ($latestAssignmentsLookup, $staff_id, $is_filtering_subordinate, $query_staff_id, $user) {
            $latestAssignment = $latestAssignmentsLookup->get($history->user_id);
            if (!$latestAssignment) {
                return false;
            }

            // The caller must be the CURRENT assignee of the lead
            if ($latestAssignment->staff_id != $history->updated_by) {
                return false;
            }

            // The call must have been made on or after the latest assignment timestamp
            if ($history->created_at < $latestAssignment->updated_at) {
                return false;
            }

            if (isset($user->is_admin) && $user->is_admin) {
                if ($is_filtering_subordinate) {
                    return $latestAssignment->staff_id == $query_staff_id;
                }
                return true;
            }

            if ($is_filtering_subordinate) {
                return $latestAssignment->staff_id == $query_staff_id && $latestAssignment->assigned_by == $staff_id;
            }

            // Logged in user: either assigned to me or assigned by me
            return ($latestAssignment->staff_id == $staff_id) || ($latestAssignment->assigned_by == $staff_id);
        })->unique('user_id')->values();

        $historyAssignmentsLookup = $latestAssignmentsLookup;

        $statuses = \App\Models\CallingStatus::where('organization_id', $organization_id)->where('status', 1)->get();
        $actions = \App\Models\CallingAction::where('organization_id', $organization_id)->where('status', 1)->get();
        $categories = \App\Models\CustomerCategory::where('organization_id', $organization_id)->where('parent_id', 0)->with('childrenRecursive')->get();
        $templates = \App\Models\WhatsappTemplate::where('organization_id', $organization_id)->get();
        $universities = \App\Models\Organisation::with('campuses')->where('status', 1)->get();
        $courses = \App\Models\Course::where('status', 1)->get();

        $program_levels = \App\Models\ProgramLevel::where('status', 1)->get();
        $program_types = \App\Models\ProgramType::where('status', 1)->get();
        $sessions = \App\Models\CustomerSession::where('organization_id', $organization_id)->where('status', 1)->get();
        $school_types = \App\Models\CampusTypeNew::where('status', 1)->get();
        $course_program_types = \Illuminate\Support\Facades\DB::table('course_program_type')->get();
        $lead_qualities = \App\Models\LeadQuality::where('organization_id', $organization_id)->where('status', 1)->get();

        $unlocked_lead_id = auth()->user()->unlocked_lead_id;

        $dbCountries = \App\Models\Customer::where('organization_id', $organization_id)
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->pluck('country')
            ->toArray();

        $dbStates = \App\Models\Customer::where('organization_id', $organization_id)
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->pluck('state')
            ->toArray();

        $dbCities = \App\Models\Customer::where('organization_id', $organization_id)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city')
            ->toArray();

        return view('admin.students_crm.calling.dashboard', compact(
            'leadsAssignedTodayCount',
            'pendingInQueueCount',
            'followUpsDueTodayCount',
            'admissionsThisMonthCount',
            'admissionsTarget',
            'targetProgress',
            'queue',
            'startDate',
            'endDate',
            'hasSubordinates',
            'teamLeadsDelegated',
            'teamAdmissionsCount',
            'teamMetrics',
            'unlocked_lead_id',
            'assignmentsLookup',
            'delegatedLeads',
            'workedHistory',
            'historyAssignmentsLookup',
            'query_staff_id',
            'statuses', 'actions', 'categories', 'templates', 'universities', 'courses', 'staffs', 'program_levels', 'program_types', 'sessions', 'school_types', 'course_program_types', 'lead_qualities',
            'dbCountries', 'dbStates', 'dbCities'
        ));
    }

    public function getLocations(\Illuminate\Http\Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $country = $request->country;
        $state = $request->state;

        $states = \App\Models\Customer::where('organization_id', $organization_id)
            ->when($country, function($q) use ($country) {
                $q->where('country', $country);
            })
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->pluck('state')
            ->toArray();

        $cities = \App\Models\Customer::where('organization_id', $organization_id)
            ->when($country, function($q) use ($country) {
                $q->where('country', $country);
            })
            ->when($state, function($q) use ($state) {
                $q->where('state', $state);
            })
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city')
            ->toArray();

        return response()->json([
            'status' => 1,
            'states' => array_values(array_unique(array_filter($states))),
            'cities' => array_values(array_unique(array_filter($cities)))
        ]);
    }

    public function customerHistory($id)
    {
        $customer = \App\Models\Customer::with('leadQuality')->find($id);
        
        $histories = \App\Models\CallingHistory::with([
            'calling_status', 'calling_action', 'staff',
            'university', 'course', 'programLevel', 'schoolType', 'sessionModel',
            'currentUniversity', 'currentCourse', 'currentSessionModel', 'leadQuality'
        ])
            ->where('user_id', $id)
            ->where('user_type', 'customer')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $latestHistory = $histories->first();
        $hasHistory = $histories->count() > 0;
        $isReassignedLead = \App\Models\LeadAssignment::where('customer_id', $id)->where('is_reassigned', 1)->exists() || $hasHistory;

        $currentStaffId = auth()->id();
        $workedByCurrentStaff = $histories->where('updated_by', $currentStaffId)->isNotEmpty();

        if ($workedByCurrentStaff && $latestHistory && $latestHistory->calling_status) {
            $statusName = $latestHistory->calling_status->name;
            $statusBadgeHtml = "<span class='badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1'><i class='fas fa-tag me-1'></i>{$statusName}</span>";
        } elseif ($hasHistory) {
            $prevStaffName = $latestHistory->staff ? $latestHistory->staff->name : 'Previous Caller';
            $prevStatus = $latestHistory->calling_status ? $latestHistory->calling_status->name : 'Contacted';
            $statusName = "Reassigned Lead (Last: {$prevStatus} by {$prevStaffName})";
            $statusBadgeHtml = "<span class='badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1'><i class='fas fa-random me-1'></i>{$statusName}</span>";
        } elseif ($isReassignedLead) {
            $statusName = 'Reassigned Lead';
            $statusBadgeHtml = "<span class='badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1'><i class='fas fa-random me-1'></i>{$statusName}</span>";
        } else {
            $statusName = 'New Lead';
            $statusBadgeHtml = "<span class='badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1'><i class='fas fa-sparkles me-1'></i>{$statusName}</span>";
        }
        $leadQualityName = $customer && $customer->leadQuality ? $customer->leadQuality->name : '';
        
        $nameParts = explode(' ', $customer->name ?? 'User');
        $initials = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
        
        $phone = $customer->phone ?? '';
        $user = auth()->user();
        
        if($user && $user->unlocked_lead_id == $id) {
            $displayPhone = $phone;
        } else {
            $displayPhone = strlen($phone) >= 10 ? substr($phone, 0, 2) . '••••••' . substr($phone, -2) : $phone;
        }
        $displayPhone = "+91 " . $displayPhone;
        
        $qualityBadgeHtml = '';
        if ($leadQualityName) {
            $lqUpper = strtoupper($leadQualityName);
            $bg = 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
            $icon = 'fa-fire';
            if (stripos($leadQualityName, 'warm') !== false) {
                $bg = 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25';
                $icon = 'fa-bolt';
            } elseif (stripos($leadQualityName, 'cold') !== false) {
                $bg = 'bg-info bg-opacity-10 text-info border border-info border-opacity-25';
                $icon = 'fa-snowflake';
            }
            $qualityBadgeHtml = "<span class='badge rounded-pill {$bg} fw-bold px-3 py-1.5' style='font-size: 0.75rem;'><i class='fas {$icon} me-1'></i>{$lqUpper}</span>";
        }
        
        $canEditStudent = false;
        $currentUser = auth()->user();
        if ($currentUser) {
            if ($currentUser->is_admin || $currentUser->can('customer-edit') || $currentUser->can('customer-browse') || $currentUser->hasRole('superadmin')) {
                $canEditStudent = true;
            }
        }
        
        $editBtnHtml = '';
        $editUrl = '';
        if ($canEditStudent && $customer) {
            $editUrl = route('admin.customers.main.index.edit', ['index' => encrypt($customer->id), 'from' => 'calling_dashboard']);
            $editBtnHtml = "
            <a href='{$editUrl}' target='_blank' class='btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm' style='font-size: 0.8rem; transition: all 0.2s ease;' title='Edit Student Profile in Student Module'>
                <i class='fas fa-user-edit text-primary'></i>
                <span>Edit Profile</span>
                <i class='fas fa-external-link-alt ms-1' style='font-size: 0.68rem; opacity: 0.75;'></i>
            </a>";
        }
        
        $headerHtml = "
        <div class='d-flex align-items-center justify-content-between w-100 pe-2 flex-wrap gap-2'>
            <div class='d-flex align-items-center gap-3'>
                <div class='d-flex align-items-center justify-content-center fw-bold rounded-4 shadow-sm text-white flex-shrink-0' style='width: 48px; height: 48px; font-size: 1.15rem; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); letter-spacing: 0.5px;'>
                    {$initials}
                </div>
                <div>
                    <div class='d-flex align-items-center gap-2 mb-1 flex-wrap'>
                        <h5 class='fw-bold mb-0 text-dark' style='font-size: 1.2rem; letter-spacing: -0.2px;'>{$customer->name}</h5>
                        {$qualityBadgeHtml}
                    </div>
                    <div class='d-flex align-items-center gap-2 text-muted flex-wrap' style='font-size: 0.82rem;'>
                        <span class='badge bg-light text-secondary border px-2 py-1 font-monospace'>#EZ-{$customer->id}</span>
                        <span class='fw-semibold text-dark'><i class='fas fa-phone-alt me-1 text-primary'></i>{$displayPhone}</span>
                        <span class='text-muted opacity-50'>|</span>
                        {$statusBadgeHtml}
                    </div>
                </div>
            </div>
            <div class='d-flex align-items-center gap-2'>
                {$editBtnHtml}
            </div>
        </div>
        ";
            
        $html = '<div class="timeline-stepper position-relative pb-1">';
            
        if($histories->count() > 0) {
            foreach($histories as $h) {
                $status = $h->calling_status ? $h->calling_status->name : 'Unknown';
                $staff = $h->staff ? $h->staff->name : 'System';
                $date = $h->created_at->format('d M Y, h:i A');
                
                $staffInitials = 'S';
                if($staff !== 'System') {
                    $nameParts = explode(' ', $staff);
                    $staffInitials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                }

                $leadQualityName = $h->leadQuality ? $h->leadQuality->name : '';
                $leadQualityHtml = '';
                if ($leadQualityName) {
                    $leadQualityHtml = "<span class='badge bg-light text-secondary border px-1.5 py-0.5 rounded-pill ms-1' style='font-size: 0.68rem;'>{$leadQualityName}</span>";
                }

                // Semantic color & icon mapping based on status
                $statusLower = strtolower($status);
                $statusColor = '#3b82f6';
                $statusIcon = 'fa-phone-alt';
                $statusBg = 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-25';

                if (str_contains($statusLower, 'hot') || str_contains($statusLower, 'interest') || str_contains($statusLower, 'admission') || str_contains($statusLower, 'token') || str_contains($statusLower, 'enroll')) {
                    $statusColor = '#10b981';
                    $statusIcon = 'fa-check-circle';
                    $statusBg = 'bg-success bg-opacity-10 text-success border-success border-opacity-25';
                } elseif (str_contains($statusLower, 'call back') || str_contains($statusLower, 'follow') || str_contains($statusLower, 'appoint') || str_contains($statusLower, 'schedul')) {
                    $statusColor = '#6366f1';
                    $statusIcon = 'fa-calendar-check';
                    $statusBg = 'bg-indigo bg-opacity-10 text-indigo border-indigo border-opacity-25';
                } elseif (str_contains($statusLower, 'warm') || str_contains($statusLower, 'ring') || str_contains($statusLower, 'busy') || str_contains($statusLower, 'no ans')) {
                    $statusColor = '#f59e0b';
                    $statusIcon = 'fa-hourglass-half';
                    $statusBg = 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25';
                } elseif (str_contains($statusLower, 'switch') || str_contains($statusLower, 'reach') || str_contains($statusLower, 'out of')) {
                    $statusColor = '#64748b';
                    $statusIcon = 'fa-phone-slash';
                    $statusBg = 'bg-secondary bg-opacity-10 text-secondary border-secondary border-opacity-25';
                } elseif (str_contains($statusLower, 'not int') || str_contains($statusLower, 'wrong') || str_contains($statusLower, 'dnd') || str_contains($statusLower, 'lost')) {
                    $statusColor = '#ef4444';
                    $statusIcon = 'fa-times-circle';
                    $statusBg = 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
                }
                
                $comment = '';
                if ($h->comment) {
                    $comment = "<div class='mt-2 p-2.5 rounded-3 border' style='background-color: #f8fafc; font-size: 0.83rem; color: #334155; border-left: 3px solid {$statusColor} !important;'>{$h->comment}</div>";
                }
                
                $html .= "
                <div class='timeline-item position-relative mb-3'>
                    <span class='timeline-dot position-absolute' style='background-color: {$statusColor}; box-shadow: 0 0 0 3px #ffffff, 0 0 0 5px {$statusColor}33;'>
                        <i class='fas {$statusIcon}'></i>
                    </span>
                    <div class='timeline-card p-3 bg-white rounded-3 border'>
                        <div class='d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1'>
                            <div class='d-flex align-items-center gap-1.5'>
                                <span class='badge {$statusBg} border px-2 py-1 fw-bold rounded-pill' style='font-size: 0.78rem;'>{$status}</span>
                                {$leadQualityHtml}
                            </div>
                            <span class='text-muted small fw-medium' style='font-size: 0.73rem;'>
                                <i class='far fa-clock me-1 opacity-75'></i>{$date}
                            </span>
                        </div>
                        <div class='d-flex align-items-center text-muted mt-1' style='font-size: 0.77rem;'>
                            <span class='d-inline-flex align-items-center justify-content-center fw-bold rounded-circle me-1.5' style='width: 18px; height: 18px; font-size: 0.6rem; background-color: #e2e8f0; color: #334155;'>{$staffInitials}</span>
                            <span>By <strong class='text-dark'>{$staff}</strong></span>
                        </div>
                        {$comment}
                    </div>
                </div>";
            }
        } else {
            $html .= '<div class="text-center text-muted py-5"><i class="fas fa-history fs-2 opacity-25 mb-2 d-block"></i>No interaction history found yet</div>';
        }
        
        $html .= '</div>';
        
        $customerData = null;
        if ($customer) {
            // Find latest available values across histories as fallbacks
            $histWithCourse = $histories->first(function($h) { return !empty($h->course_id) || !empty($h->course_text); });
            $courseInput = $customer->interested_in_course ?: ($histWithCourse ? ($histWithCourse->course_id ?: $histWithCourse->course_text) : null);

            $histWithMode = $histories->first(function($h) { return !empty($h->course_type); });
            $courseType = $customer->mode ?: ($histWithMode ? $histWithMode->course_type : null);

            $histWithLevel = $histories->first(function($h) { return !empty($h->program_level_id) || !empty($h->program_level_text); });
            $programLevelId = $customer->program_level ?: ($histWithLevel ? ($histWithLevel->program_level_id ?: $histWithLevel->program_level_text) : null);

            $histWithSchoolType = $histories->first(function($h) { return !empty($h->school_type_id) || !empty($h->school_type_text); });
            $schoolType = $customer->school_type ?: ($histWithSchoolType ? ($histWithSchoolType->school_type_id ?: $histWithSchoolType->school_type_text) : null);

            $uniInput = null;
            if (is_array($customer->interested_in_ids)) {
                $uniInput = $customer->interested_in_ids[0] ?? null;
            } elseif (is_string($customer->interested_in_ids)) {
                $decoded = json_decode($customer->interested_in_ids, true);
                $uniInput = is_array($decoded) ? ($decoded[0] ?? null) : $customer->interested_in_ids;
            }
            if (empty($uniInput)) {
                $histWithUni = $histories->first(function($h) { return !empty($h->university_id) || !empty($h->university_text); });
                $uniInput = $histWithUni ? ($histWithUni->university_id ?: $histWithUni->university_text) : null;
            }

            $sessId = null;
            if (is_array($customer->session_ids)) {
                $sessId = $customer->session_ids[0] ?? null;
            } elseif (is_string($customer->session_ids)) {
                $decoded = json_decode($customer->session_ids, true);
                $sessId = is_array($decoded) ? ($decoded[0] ?? null) : $customer->session_ids;
            }
            if (empty($sessId)) {
                $histWithSess = $histories->first(function($h) { return !empty($h->session); });
                $sessId = $histWithSess ? $histWithSess->session : null;
            }

            $histWithCurCourse = $histories->first(function($h) { return !empty($h->current_course_id) || !empty($h->current_course_text); });
            $curCourse = ($customer->current_course_id ?: $customer->current_course_text) ?: ($histWithCurCourse ? ($histWithCurCourse->current_course_id ?: $histWithCurCourse->current_course_text) : null);

            $histWithCurUni = $histories->first(function($h) { return !empty($h->current_university_id) || !empty($h->current_university_text); });
            $curUni = ($customer->current_university_id ?: $customer->current_university_text) ?: ($histWithCurUni ? ($histWithCurUni->current_university_id ?: $histWithCurUni->current_university_text) : null);

            $histWithCurSess = $histories->first(function($h) { return !empty($h->current_session); });
            $curSess = $customer->current_session ?: ($histWithCurSess ? $histWithCurSess->current_session : null);

            $histWithCurMode = $histories->first(function($h) { return !empty($h->current_course_type); });
            $curMode = $customer->current_course_type ?: ($histWithCurMode ? $histWithCurMode->current_course_type : null);

            $customerData = [
                'email' => $customer->email,
                'current_course' => $curCourse,
                'current_session' => $curSess,
                'current_university' => $curUni,
                'current_program_mode' => $curMode,
                'program_level_id' => $programLevelId,
                'school_type' => $schoolType,
                'course_input' => $courseInput,
                'course_type' => $courseType,
                'university_input' => $uniInput,
                'session_id' => $sessId,
            ];
        }

        return response()->json([
            'html' => $html,
            'headerHtml' => $headerHtml,
            'customer' => $customerData,
            'editUrl' => $editUrl
        ]);
    }

    public function index(Request $request)
    {
        $organization_id = auth()->user()->organization_id;

        $data = Customer::where('organization_id', $organization_id);

        $assigned_ids = \App\Models\LeadAssignment::where('staff_id', auth()->id())->pluck('customer_id');
        $data->whereIn('id', $assigned_ids);

        $hasFilter = $request->filled('category') || 
                     $request->filled('country') || 
                     $request->filled('state') || 
                     $request->filled('city') || 
                     $request->filled('filter_name') || 
                     $request->filled('filter_phone') || 
                     $request->filled('session_id') ||
                     $request->user_with_out_status == 1 || 
                     $request->sequence_mode == 1;

        if (!$hasFilter) {
            $data->whereRaw('1 = 0');
        } else {
            if ($request->filled('category')) {
                $data->where('category_id', $request->category);
            }
            if ($request->filled('country')) {
                $data->where('country', $request->country);
            }
            if ($request->filled('state')) {
                $data->where('state', $request->state);
            }
            if ($request->filled('city')) {
                $data->where('city', $request->city);
            }
            if ($request->filled('filter_name')) {
                $data->where('name', $request->filter_name);
            }
            if ($request->filled('filter_phone')) {
                $data->where('phone', $request->filter_phone);
            }
            if ($request->filled('session_id')) {
                // session_ids is a JSON array
                $data->whereJsonContains('session_ids', (string)$request->session_id);
            }
        }

        $historyQuery = CallingHistory::where('organization_id', $organization_id);
        
        if ($request->user_with_out_status != 1) {
            $historyQuery->where('is_done', 1);
        }
        
        $calling_ids = $historyQuery->pluck('user_id');
        $data->whereNotIn('id', $calling_ids);

        $data = $data->latest();
        
        $count = $data->count();
        $data = $data->limit(1)->get();
        
        $statuses = CallingStatus::where('organization_id', $organization_id)->where('status', 1)->get();
        $actions = CallingAction::where('organization_id', $organization_id)->where('status', 1)->get();
        $categories = CustomerCategory::where('organization_id', $organization_id)->where('parent_id', 0)->with('childrenRecursive')->get();
        $templates = WhatsappTemplate::where('organization_id', $organization_id)->get();
        
        $user_with_out_status = request('user_with_out_status', 0);
        $universities = \App\Models\Organisation::with('campuses')->where('status', 1)->get();
        $courses = Course::where('status', 1)->get();
        
        $staffs = \App\Models\Admin::where('status', 1)->where('organization_id', $organization_id)->get();
        
        $program_levels = \App\Models\ProgramLevel::where('status', 1)->get();
        $program_types = \App\Models\ProgramType::where('status', 1)->get();
        $sessions = \App\Models\CustomerSession::where('organization_id', $organization_id)->where('status', 1)->get();
        $school_types = \App\Models\CampusTypeNew::where('status', 1)->get();
        $course_program_types = \Illuminate\Support\Facades\DB::table('course_program_type')->get();
        
        return view('admin.students_crm.calling.index', compact('statuses', 'actions', 'categories', 'templates', 'count', 'user_with_out_status', 'data', 'universities', 'courses', 'staffs', 'program_levels', 'program_types', 'sessions', 'school_types', 'course_program_types'));
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['staff', 'Telle Caller']) && !$user->can('calling-history-browse')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = CallingHistory::with(['customer', 'calling_status', 'calling_action', 'staff', 'logs.calling_action', 'logs.user'])
                ->where('organization_id', $organization_id);
                
            if (in_array(auth()->user()->role, ['staff', 'Telle Caller'])) {
                $assignedCustomerIds = \App\Models\LeadAssignment::where('staff_id', auth()->id())->pluck('customer_id')->toArray();
                $data->whereIn('user_id', $assignedCustomerIds);
            } else {
                if ($request->filled('staff_id')) {
                    $assignedCustomerIds = \App\Models\LeadAssignment::where('staff_id', $request->staff_id)->pluck('customer_id')->toArray();
                    $data->whereIn('user_id', $assignedCustomerIds);
                }
            }

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $data->whereBetween('created_at', [$request->from_date . ' 00:00:00', $request->to_date . ' 23:59:59']);
            }

            if ($request->filled('reminder_date')) {
                $data->whereDate('date_required', $request->reminder_date);
            }

            if ($request->filled('call_status_id')) {
                $data->where('reason', $request->call_status_id);
            }

            if ($request->filled('call_action_id')) {
                $data->where('calling_action_id', $request->call_action_id);
            }

            if ($request->filled('filter_name')) {
                $data->where(function($q) use ($request) {
                    $q->where('user_name', 'LIKE', '%' . $request->filter_name . '%')
                      ->orWhereHas('customer', function($q2) use ($request) {
                          $q2->where('name', 'LIKE', '%' . $request->filter_name . '%');
                      });
                });
            }

            if ($request->filled('filter_phone')) {
                $data->where(function($q) use ($request) {
                    $q->where('user_phone', 'LIKE', '%' . $request->filter_phone . '%')
                      ->orWhereHas('customer', function($q2) use ($request) {
                          $q2->where('phone', 'LIKE', '%' . $request->filter_phone . '%');
                      });
                });
            }

            if ($request->filled('category') || $request->filled('country') || $request->filled('state') || $request->filled('city')) {
                $data->whereHas('customer', function($q) use ($request) {
                    if ($request->filled('category')) {
                        $q->where('category_id', $request->category);
                    }
                    if ($request->filled('country')) {
                        $q->where('country', $request->country);
                    }
                    if ($request->filled('state')) {
                        $q->where('state', $request->state);
                    }
                    if ($request->filled('city')) {
                        $q->where('city', $request->city);
                    }
                });
            }

            $data = $data->latest();
            
            $calling_actions = CallingAction::where('status', 1)->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('customer_info', function ($row) {
                    return $row->user_name ?? ($row->customer->name ?? 'N/A');
                })
                ->addColumn('status_info', function ($row) {
                    return $row->calling_status->name ?? 'N/A';
                })
                ->addColumn('action_info', function ($row) use ($calling_actions) {
                    $options = '';
                    foreach ($calling_actions as $action) {
                        $selected = $row->calling_action_id == $action->id ? 'selected' : '';
                        $options .= "<option value='{$action->id}' {$selected}>{$action->name}</option>";
                    }
                    $select = "<select class='form-select form-select-sm d-inline-block w-auto' onchange='updateStatus(this, {$row->id})'>
                                <option value=''>Select Action</option>
                                {$options}
                               </select>";
                    
                    $logsJson = htmlspecialchars(json_encode($row->logs), ENT_QUOTES, 'UTF-8');
                    $rowJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    $infoIcon = "<i class='fas fa-info-circle text-primary ms-2 show-logs' style='cursor:pointer;' data-logs='{$logsJson}' data-bs-toggle='modal' data-bs-target='#logsModal' title='View Logs'></i>";
                    $viewIcon = "<i class='fas fa-eye text-success ms-2 show-details' style='cursor:pointer;' data-row='{$rowJson}' data-bs-toggle='modal' data-bs-target='#detailsModal' title='View Details'></i>";

                    return $select . $infoIcon . $viewIcon;
                })
                ->addColumn('staff_info', function ($row) {
                    return $row->staff->name ?? 'N/A';
                })
                ->addColumn('call_date', function ($row) {
                    return $row->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['customer_info', 'status_info', 'action_info', 'staff_info', 'call_date'])
                ->make(true);
        }
        
        $staffs = [];
        $organization_id = auth()->user()->organization_id;
        if (!in_array(auth()->user()->role, ['staff', 'Telle Caller'])) {
            $staffs = \App\Models\Admin::where('status', 1)->where('organization_id', $organization_id)->get();
        }
        $statuses = CallingStatus::where('status', 1)->get();
        $actions = CallingAction::where('status', 1)->get();
        $categories = CustomerCategory::where('organization_id', $organization_id)->get();

        return view('admin.students_crm.calling.history', compact('staffs', 'statuses', 'actions', 'categories'));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['staff', 'Telle Caller']) && !$user->can('calling-history-edit')) {
            abort(403, 'Unauthorized action.');
        }

        $item = CallingHistory::find($id);
        if ($item) {
            $item->calling_action_id = $request->status;
            $item->save();
            
            CallingHistoryLog::create([
                'history_id' => $id,
                'log_type' => 'Updated',
                'updated_by' => auth()->user()->id,
                'status' => 'Active',
                'calling_action_id' => $request->status
            ]);

            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'status_id' => 'required',
            'action_id' => 'nullable',
            'next_call_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $statusRecord = \App\Models\CallingStatus::find($request->status_id);
        if ($statusRecord && $statusRecord->comment_require === 'yes' && empty($request->remark)) {
            return response()->json(['status' => 0, 'message' => 'The comments field is required for this call status.']);
        }

        try {
            $customer = Customer::find($request->customer_id);
            $university_id = null;
            $university_text = null;
            if ($request->filled('university_input')) {
                if (is_numeric($request->university_input)) {
                    $university_id = $request->university_input;
                } else {
                    $university_text = $request->university_input;
                }
            }

            $course_id = null;
            $course_text = null;
            if ($request->filled('course_input')) {
                if (is_numeric($request->course_input)) {
                    $course_id = $request->course_input;
                } else {
                    $course_text = $request->course_input;
                }
            }

            $program_level_id = null;
            $program_level_text = null;
            if ($request->filled('program_level_id')) {
                if (is_numeric($request->program_level_id)) {
                    $program_level_id = $request->program_level_id;
                } else {
                    $program_level_text = $request->program_level_id;
                }
            }

            $school_type_id = null;
            $school_type_text = null;
            if ($request->filled('school_type')) {
                if (is_numeric($request->school_type)) {
                    $school_type_id = $request->school_type;
                } else {
                    $school_type_text = $request->school_type;
                }
            }

            $current_university_id = null;
            $current_university_text = null;
            if ($request->filled('current_university')) {
                if (is_numeric($request->current_university)) {
                    $current_university_id = $request->current_university;
                } else {
                    $current_university_text = $request->current_university;
                }
            }

            $current_course_id = null;
            $current_course_text = null;
            if ($request->filled('current_course')) {
                if (is_numeric($request->current_course)) {
                    $current_course_id = $request->current_course;
                } else {
                    $current_course_text = $request->current_course;
                }
            }

            if ($request->filled('lead_quality_id')) {
                $customer->lead_quality_id = $request->lead_quality_id;
            }

            // Sync with Customer's Current Academic Details
            if ($request->filled('current_university')) {
                if (is_numeric($request->current_university)) {
                    $customer->current_university_id = $request->current_university;
                    $customer->current_university_text = null;
                } else {
                    $customer->current_university_id = null;
                    $customer->current_university_text = $request->current_university;
                }
            }

            if ($request->filled('current_course')) {
                if (is_numeric($request->current_course)) {
                    $customer->current_course_id = $request->current_course;
                    $customer->current_course_text = null;
                } else {
                    $customer->current_course_id = null;
                    $customer->current_course_text = $request->current_course;
                }
            }

            if ($request->filled('current_program_mode')) {
                $customer->current_course_type = $request->current_program_mode;
            }
            if ($request->filled('current_session')) {
                $customer->current_session = $request->current_session;
            }

            // Sync with Customer's Program of Interest
            if ($request->filled('program_level_id')) {
                $customer->program_level = $request->program_level_id;
            }
            if ($request->filled('school_type')) {
                $customer->school_type = $request->school_type;
            }
            if ($request->filled('course_input')) {
                $customer->interested_in_course = $request->course_input;
            }
            if ($request->filled('course_type')) {
                $customer->mode = $request->course_type;
            }
            
            if ($request->filled('university_input')) {
                $customer->interested_in_ids = [$request->university_input];
            }

            if ($request->filled('session')) {
                $customer->session_ids = [$request->session];
            }

            if ($request->filled('email')) {
                $customer->email = $request->email;
            }

            $customer->save();

            // Fallback history snapshot values from customer if not provided in this specific call request
            if (empty($university_id) && empty($university_text) && !empty($customer->interested_in_ids)) {
                $uVal = is_array($customer->interested_in_ids) ? ($customer->interested_in_ids[0] ?? null) : $customer->interested_in_ids;
                if ($uVal) {
                    if (is_numeric($uVal)) $university_id = $uVal;
                    else $university_text = $uVal;
                }
            }
            if (empty($program_level_id) && empty($program_level_text) && !empty($customer->program_level)) {
                if (is_numeric($customer->program_level)) $program_level_id = $customer->program_level;
                else $program_level_text = $customer->program_level;
            }
            if (empty($school_type_id) && empty($school_type_text) && !empty($customer->school_type)) {
                if (is_numeric($customer->school_type)) $school_type_id = $customer->school_type;
                else $school_type_text = $customer->school_type;
            }
            if (empty($course_id) && empty($course_text) && !empty($customer->interested_in_course)) {
                if (is_numeric($customer->interested_in_course)) $course_id = $customer->interested_in_course;
                else $course_text = $customer->interested_in_course;
            }
            $historyCourseType = $request->filled('course_type') ? $request->course_type : ($customer->mode ?? null);
            $historySession = $request->filled('session') ? $request->session : (is_array($customer->session_ids) ? ($customer->session_ids[0] ?? null) : $customer->session_ids);

            CallingHistory::create([
                'user_type' => 'customer',
                'user_id' => $request->customer_id,
                'category_id' => $request->category,
                'lead_quality_id' => $request->lead_quality_id,
                'user_name' => $customer->name ?? '',
                'user_phone' => $customer->phone ?? '',
                'reason' => $request->status_id, // Legacy Status field
                'calling_action_id' => $request->action_id,
                'comment' => $request->remark,
                'date_required' => $request->next_call_date, // Legacy Next Date field
                'university_id' => $university_id,
                'university_text' => $university_text,
                'program_level_id' => $program_level_id,
                'program_level_text' => $program_level_text,
                'school_type_id' => $school_type_id,
                'school_type_text' => $school_type_text,
                'course_id' => $course_id,
                'course_text' => $course_text,
                'course_type' => $historyCourseType,
                'session' => $historySession,
                'current_university_id' => $current_university_id,
                'current_university_text' => $current_university_text,
                'current_course_id' => $current_course_id,
                'current_course_text' => $current_course_text,
                'current_course_type' => $request->current_program_mode ?? $customer->current_course_type,
                'current_session' => $request->current_session ?? $customer->current_session,
                'meeting_date' => $request->meeting_date,
                'time_slot' => $request->time_slot,
                'meeting_link' => $request->meeting_link,
                'assign_to_staff_id' => $request->assign_to_staff_id,
                'updated_by' => auth()->id(),
                'status' => 1,
                'is_done' => 1,
                'organization_id' => auth()->user()->organization_id,
            ]);

            \App\Models\LeadActivityLog::create([
                'customer_id' => $request->customer_id,
                'admin_id' => auth()->id(),
                'action_type' => 'status_update',
                'description' => 'Updated calling status to ' . ($statusRecord->name ?? 'Unknown'),
                'properties' => ['status_id' => $request->status_id]
            ]);

            $user = auth()->user();
            if ($user->unlocked_lead_id == $request->customer_id) {
                $user->unlocked_lead_id = null;
                $user->save();
            }

            return response()->json(['status' => 1, 'message' => 'Calling record saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function importHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            ignore_user_abort(true);
            \Illuminate\Support\Facades\DB::disableQueryLog();

            Excel::import(new CallingHistoryImport(auth()->user()->organization_id), $request->file('file'));
            return response()->json(['status' => 1, 'message' => 'Calling history imported successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function downloadSample()
    {
        return Excel::download(new CallingHistorySampleExport, 'calling_history_sample.xlsx');
    }

    public function restart(Request $request)
    {
        $query = CallingHistory::where('updated_by', auth()->id())
            ->where('organization_id', auth()->user()->organization_id);
            
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $query->update(['is_done' => 0]);

        return redirect()->back()->with('success', 'Restart calling Successfully');
    }

    public function getCoursesByProgramLevel(Request $request)
    {
        if ($request->ajax()) {
            $courses = Course::where('status', 1);
            if ($request->filled('program_level_id') && is_numeric($request->program_level_id)) {
                $courses->where('program_level_id', $request->program_level_id);
            }
            return response()->json($courses->get(['id', 'name']));
        }
        return response()->json([]);
    }
}


