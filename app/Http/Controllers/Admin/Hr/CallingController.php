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
    public function dashboard(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $staff_id = auth()->id();
        
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        $currentMonth = \Carbon\Carbon::parse($startDate)->format('F');
        $currentYear = \Carbon\Carbon::parse($startDate)->format('Y');

        // 1. Leads assigned in date range
        $assignedToday = \App\Models\LeadAssignment::where('staff_id', $staff_id)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->pluck('customer_id')->toArray();
        $leadsAssignedTodayCount = count($assignedToday);

        // 2. Leads pending in queue (assigned in date range but no calling history by this staff)
        $workedOnCustomerIds = \App\Models\CallingHistory::where('updated_by', $staff_id)
            ->whereIn('user_id', $assignedToday)
            ->pluck('user_id')->toArray();
        
        $pendingInQueueCount = $leadsAssignedTodayCount - count(array_unique($workedOnCustomerIds));

        // 3. Follow-ups due in date range & Overdue
        // We need to find customers whose LATEST calling history by this staff has date_required = date range
        $latestHistoriesSub = \Illuminate\Support\Facades\DB::table('calling_histories')
            ->select(\Illuminate\Support\Facades\DB::raw('MAX(id) as id'))
            ->where('updated_by', $staff_id)
            ->groupBy('user_id');

        $latestHistoriesIds = $latestHistoriesSub->pluck('id')->toArray();

        $latestHistories = \App\Models\CallingHistory::with(['customer', 'calling_status'])
            ->whereIn('id', $latestHistoriesIds)
            ->get();
        
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
            $admissionsThisMonthCount = \App\Models\CallingHistory::where('updated_by', $staff_id)
                ->where('reason', $admissionStatus->id)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count();
        }

        // Target for the start date's month
        $targetRecord = \App\Models\TargetLead::where('staff_id', $staff_id)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();
            
        $admissionsTarget = $targetRecord ? $targetRecord->month_target_admissions : 0;
        
        $targetProgress = 0;
        if ($admissionsTarget > 0) {
            $targetProgress = round(($admissionsThisMonthCount / $admissionsTarget) * 100);
            if ($targetProgress > 100) $targetProgress = 100;
        }

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
        if (!empty($unattemptedIds)) {
            $unattemptedCustomers = \App\Models\Customer::whereIn('id', $unattemptedIds)->get();
            foreach ($unattemptedCustomers as $customer) {
                $queue->push([
                    'type' => 'new',
                    'customer' => $customer,
                    'history' => null,
                    'sort_date' => $startDate
                ]);
            }
        }
        
        $queue = $queue->sortBy(function($item) {
            if ($item['type'] === 'overdue') return '1_' . $item['sort_date'];
            if ($item['type'] === 'due_today') return '2_' . $item['sort_date'];
            return '3_' . $item['sort_date'];
        })->values();

        $statuses = \App\Models\CallingStatus::where('organization_id', $organization_id)->where('status', 1)->get();
        $actions = \App\Models\CallingAction::where('organization_id', $organization_id)->where('status', 1)->get();
        $categories = \App\Models\CustomerCategory::where('organization_id', $organization_id)->where('parent_id', 0)->with('childrenRecursive')->get();
        $templates = \App\Models\WhatsappTemplate::where('organization_id', $organization_id)->get();
        $universities = \App\Models\Organisation::with('campuses')->where('status', 1)->get();
        $courses = \App\Models\Course::where('status', 1)->get();
        $staffs = \App\Models\Admin::where('status', 1)->where('organization_id', $organization_id)->get();
        $program_levels = \App\Models\ProgramLevel::where('status', 1)->get();
        $program_types = \App\Models\ProgramType::where('status', 1)->get();
        $sessions = \App\Models\CustomerSession::where('organization_id', $organization_id)->where('status', 1)->get();
        $school_types = \App\Models\CampusTypeNew::where('status', 1)->get();
        $course_program_types = \Illuminate\Support\Facades\DB::table('course_program_type')->get();

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
            'statuses', 'actions', 'categories', 'templates', 'universities', 'courses', 'staffs', 'program_levels', 'program_types', 'sessions', 'school_types', 'course_program_types'
        ));
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
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = CallingHistory::with(['customer', 'calling_status', 'calling_action', 'staff', 'logs.calling_action', 'logs.user'])
                ->where('organization_id', $organization_id);
                
            if (auth()->user()->role == 'staff') {
                $data->where('updated_by', auth()->id());
            } else {
                if ($request->filled('staff_id')) {
                    $data->where('updated_by', $request->staff_id);
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
        if (auth()->user()->role != 'staff') {
            $staffs = \App\Models\Admin::where('status', 1)->where('organization_id', $organization_id)->get();
        }
        $statuses = CallingStatus::where('status', 1)->get();
        $actions = CallingAction::where('status', 1)->get();
        $categories = CustomerCategory::where('organization_id', $organization_id)->get();

        return view('admin.students_crm.calling.history', compact('staffs', 'statuses', 'actions', 'categories'));
    }

    public function updateStatus(Request $request, $id)
    {
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
            
            CallingHistory::create([
                'user_type' => 'customer',
                'user_id' => $request->customer_id,
                'category_id' => $request->category,
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
                'course_type' => $request->course_type,
                'session' => $request->session,
                'meeting_date' => $request->meeting_date,
                'time_slot' => $request->time_slot,
                'meeting_link' => $request->meeting_link,
                'assign_to_staff_id' => $request->assign_to_staff_id,
                'updated_by' => auth()->id(),
                'status' => 1,
                'is_done' => 1,
                'organization_id' => auth()->user()->organization_id,
            ]);

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


