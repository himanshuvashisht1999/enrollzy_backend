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
        $universities = \App\Models\Organisation::where('status', 1)->get();
        $courses = Course::where('status', 1)->get();
        
        $staffs = \App\Models\Admin::where('status', 1)->where('organization_id', $organization_id)->get();
        
        $program_levels = \App\Models\ProgramLevel::where('status', 1)->get();
        $program_types = \App\Models\ProgramType::where('status', 1)->get();
        
        return view('admin.students_crm.calling.index', compact('statuses', 'actions', 'categories', 'templates', 'count', 'user_with_out_status', 'data', 'universities', 'courses', 'staffs', 'program_levels', 'program_types'));
    }

    public function history(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = CallingHistory::with(['customer', 'calling_status', 'calling_action', 'staff'])
                ->where('organization_id', $organization_id);
                
            if (auth()->user()->role == 'staff') {
                $data->where('updated_by', auth()->id());
            } else {
                if ($request->filled('staff_id')) {
                    $data->where('updated_by', $request->staff_id);
                }
            }
            
            $data = $data->latest();

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
        
        $staffs = [];
        if (auth()->user()->role != 'staff') {
            $staffs = \App\Models\Admin::where('status', 1)->where('organization_id', auth()->user()->organization_id)->get();
        }

        return view('admin.students_crm.calling.history', compact('staffs'));
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


