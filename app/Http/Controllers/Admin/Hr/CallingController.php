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

class CallingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            // For general list, we show students/customers who need calling
            $data = Customer::where('organization_id', $organization_id)->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('contact', function ($row) {
                    return '<b>'.$row->name.'</b><br><small class="text-muted">'.$row->phone.'</small>';
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category->name ?? '<span class="text-muted small">No Category</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<button type="button" class="btn btn-sm btn-soft-primary open-calling-modal" data-id="'.$row->id.'"><i class="fas fa-phone-alt"></i> Call</button>';
                    return $btn;
                })
                ->rawColumns(['contact', 'category_name', 'action'])
                ->make(true);
        }

        $organization_id = auth()->user()->organization_id;
        $statuses = CallingStatus::where('organization_id', $organization_id)->where('status', 'active')->get();
        $actions = CallingAction::where('organization_id', $organization_id)->where('status', 'active')->get();

        return view('admin.students_crm.calling.index', compact('statuses', 'actions'));
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
                'organization_id' => auth()->user()->organization_id,
            ]);

            return response()->json(['status' => 1, 'message' => 'Calling record saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }
}


