<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\CallingHistory;
use App\Models\CallingStatus;
use App\Models\CallingAction;
use App\Models\Customer;
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

        if ($request->ajax()) {
            // For general list, we show students/customers who need calling
            $data = Customer::where('organization_id', $organization_id);

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
            // Logic for user_with_out_status if needed
            // if ($request->filled('user_with_out_status') && $request->user_with_out_status == 1) {
            //    $data->whereDoesntHave('callingHistory'); // Adjust based on relation
            // }

            $data = $data->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('contact', function ($row) {
                    return '<b>'.$row->name.'</b><br><small class="text-muted">'.$row->phone.'</small>';
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category->name ?? '<span class="text-muted small">No Category</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<button type="button" class="btn btn-sm btn-soft-primary open-calling-modal" data-id="'.$row->id.'" data-name="'.$row->name.'" data-phone="'.$row->phone.'" data-category="'.($row->category_id ?? '').'"><i class="fas fa-phone-alt"></i> Update Status</button>';
                    return $btn;
                })
                ->rawColumns(['contact', 'category_name', 'action'])
                ->make(true);
        }

        $statuses = CallingStatus::where('organization_id', $organization_id)->where('status', 1)->get();
        $actions = CallingAction::where('organization_id', $organization_id)->where('status', 1)->get();
        $categories = CustomerCategory::where('organization_id', $organization_id)->where('parent_id', 0)->with('childrenRecursive')->get();
        $templates = WhatsappTemplate::where('organization_id', $organization_id)->get();
        
        $count = Customer::where('organization_id', $organization_id)->count();
        $user_with_out_status = request('user_with_out_status', 0);

        return view('admin.students_crm.calling.index', compact('statuses', 'actions', 'categories', 'templates', 'count', 'user_with_out_status'));
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
            
            CallingHistory::create([
                'user_type' => 'customer',
                'user_id' => $request->customer_id,
                'user_name' => $customer->name ?? '',
                'user_phone' => $customer->phone ?? '',
                'reason' => $request->status_id, // Legacy Status field
                'calling_action_id' => $request->action_id,
                'comment' => $request->remark,
                'date_required' => $request->next_call_date, // Legacy Next Date field
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


