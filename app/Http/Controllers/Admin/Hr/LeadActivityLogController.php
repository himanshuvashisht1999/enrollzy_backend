<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadActivityLog;
use App\Models\Admin;

class LeadActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $organization_id = auth()->user()->organization_id;

        if ($request->ajax()) {
            $data = LeadActivityLog::with(['customer', 'admin'])
                ->whereHas('customer', function($q) use ($organization_id) {
                    $q->where('organization_id', $organization_id);
                });

            if ($request->filled('staff_id')) {
                $data->where('admin_id', $request->staff_id);
            }

            if ($request->filled('action_type')) {
                $data->where('action_type', $request->action_type);
            }

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $data->whereBetween('created_at', [$request->from_date . ' 00:00:00', $request->to_date . ' 23:59:59']);
            }

            $data->orderBy('id', 'desc');

            return datatables()->of($data)
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('d M Y, h:i A');
                })
                ->addColumn('lead_name', function ($row) {
                    $html = $row->customer ? $row->customer->name : 'N/A';
                    if($row->customer) {
                        $html .= '<br><small class="text-muted">LEAD #EZ-' . $row->customer_id . '</small>';
                    }
                    return $html;
                })
                ->addColumn('staff_name', function ($row) {
                    return $row->admin ? $row->admin->name : 'System';
                })
                ->addColumn('action_type_html', function ($row) {
                    $action = ucfirst(str_replace('_', ' ', $row->action_type));
                    if ($row->action_type == 'assigned' || $row->action_type == 'reassigned') {
                        return '<span class="badge bg-primary">' . $action . '</span>';
                    }
                    return '<span class="badge bg-info">' . $action . '</span>';
                })
                ->rawColumns(['date', 'lead_name', 'staff_name', 'action_type_html', 'description'])
                ->make(true);
        }

        $staffs = Admin::where('organization_id', $organization_id)->where('status', 1)->get();
        return view('admin.students_crm.calling.activity_logs', compact('staffs'));
    }
}
