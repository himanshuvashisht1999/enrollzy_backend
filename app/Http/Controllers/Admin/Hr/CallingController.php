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
    public function index(Request $request)
    {
        $organization_id = auth()->user()->organization_id;

        $data = Customer::where('organization_id', $organization_id);

        $hasFilter = $request->filled('category') || 
                     $request->filled('country') || 
                     $request->filled('state') || 
                     $request->filled('city') || 
                     $request->filled('filter_name') || 
                     $request->filled('filter_phone') || 
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
        }

        $calling_ids = CallingHistory::where('organization_id', $organization_id)->pluck('user_id');
        $data->whereNotIn('id', $calling_ids);

        $data = $data->latest();
        
        $count = $data->count();
        $data = $data->limit(1)->get();
        
        $statuses = CallingStatus::where('organization_id', $organization_id)->where('status', 1)->get();
        $actions = CallingAction::where('organization_id', $organization_id)->where('status', 1)->get();
        $categories = CustomerCategory::where('organization_id', $organization_id)->where('parent_id', 0)->with('childrenRecursive')->get();
        $templates = WhatsappTemplate::where('organization_id', $organization_id)->get();
        
        $user_with_out_status = request('user_with_out_status', 0);
        $universities = Organisation::whereIn('organisation_type_id', [1, 2])->where('status', 1)->get();
        $courses = Course::where('status', 1)->get();
        
        $staffs = \App\Models\Admin::where('status', 1)->where('organization_id', $organization_id)->get();
        
        return view('admin.students_crm.calling.index', compact('statuses', 'actions', 'categories', 'templates', 'count', 'user_with_out_status', 'data', 'universities', 'courses', 'staffs'));
    }

    public function history(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = CallingHistory::with(['customer', 'calling_status', 'calling_action', 'staff'])
                ->where('organization_id', $organization_id)
                ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('customer_info', function ($row) {
                    return $row->user_name ?? ($row->customer->name ?? 'N/A');
                })
                ->addColumn('status_info', function ($row) {
                    return $row->calling_status->name ?? 'N/A';
                })
                ->addColumn('action_info', function ($row) {
                    return $row->calling_action->name ?? 'N/A';
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

        return view('admin.students_crm.calling.history');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'status_id' => 'required',
            'action_id' => 'required',
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
            
            CallingHistory::create([
                'user_type' => 'customer',
                'user_id' => $request->customer_id,
                'user_name' => $customer->name ?? '',
                'user_phone' => $customer->phone ?? '',
                'reason' => $request->status_id, // Legacy Status field
                'calling_action_id' => $request->action_id,
                'comment' => $request->remark,
                'date_required' => $request->next_call_date, // Legacy Next Date field
                'university_id' => $university_id,
                'university_text' => $university_text,
                'course_id' => $course_id,
                'course_text' => $course_text,
                'course_type' => $request->course_type,
                'meeting_date' => $request->meeting_date,
                'time_slot' => $request->time_slot,
                'meeting_link' => $request->meeting_link,
                'assign_to_staff_id' => $request->assign_to_staff_id,
                'updated_by' => auth()->id(),
                'status' => 1,
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
}


