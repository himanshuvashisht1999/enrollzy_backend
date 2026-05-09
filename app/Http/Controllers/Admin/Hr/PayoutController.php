<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\EmployeeTxn;
use App\Models\AdvancePayTransaction;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Holiday;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Exception;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $data = Payout::query();
            
            if ($user->role === 'admin') {
                $data->where('organization_id', $user->organization_id);
            } elseif ($user->role !== 'superadmin') {
                $data->where('employee_id', $user->id);
            }
            
            $data = $data->orderBy('created_at', 'desc')->get();

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('month', function ($row) {
                    return '<p class="text-sm">' . date('F', mktime(0, 0, 0, $row->month, 1)) . '</p>';
                })
                ->addColumn('created_at', function ($row) {
                    return '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.hr.payout.show', encrypt($row->id)) . '" class="btn btn-sm btn-light rounded-circle me-1"><i class="fa fa-eye text-primary"></i></a>';
                    
                    $btn .= '<form method="POST" action="' . route('admin.hr.payout.destroy', encrypt($row->id)) . '" class="m-0 p-0 d-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm btn-light rounded-circle confirm-button"><i class="fa fa-trash text-danger"></i></button>
                    </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['month', 'created_at', 'action'])
                ->make(true);
        }
        return view('admin.hr.payout.index');
    }

    public function show($id)
    {
        $pId = decrypt($id);
        $payout = Payout::findOrFail($pId);
        $txn = EmployeeTxn::find($payout->statement_id);
        
        $advancepaymenthistory = AdvancePayTransaction::where('staff_id', $payout->employee_id)
            ->where('year', $payout->year)
            ->where('month', $payout->month)
            ->get();
            
        $staff = Admin::find($payout->employee_id);

        $declaredHoliday = Holiday::whereRaw('FIND_IN_SET(?, department_ids)', [$staff->department_id])
            ->whereRaw('FIND_IN_SET(?, designation_ids)', [$staff->designation_id])
            ->whereMonth('date', $payout->month)
            ->whereYear('date', $payout->year)
            ->count();

        $employeeBonusTotal = EmployeeTxn::where('employee_id', $payout->employee_id)
            ->whereMonth('clearance_date', $payout->month)
            ->whereYear('clearance_date', $payout->year)
            ->where('transaction_for', 'bonus')
            ->sum('debit');

        $perDaySalary = ($staff->shift_hours ?? 8) * ($staff->salary ?? 0);
        $declaredHolidayTotal = $perDaySalary * $declaredHoliday;

        $monthName = DateTime::createFromFormat('!m', $payout->month)->format('F');
        $advancePayTranDebit = AdvancePayTransaction::where('month', $monthName)
            ->where('year', $payout->year)
            ->where('transaction_type', 'debit')
            ->where('staff_id', $payout->employee_id)
            ->sum('amount');
            
        $advancePayTranCredit = AdvancePayTransaction::where('month', $monthName)
            ->where('year', $payout->year)
            ->where('transaction_type', 'credit')
            ->where('staff_id', $payout->employee_id)
            ->sum('amount');
            
        $advancePayTran = $advancePayTranDebit - $advancePayTranCredit;
        
        return view('admin.hr.payout.show', compact('txn', 'payout', 'advancepaymenthistory', 'advancePayTran', 'declaredHolidayTotal', 'employeeBonusTotal'));
    }

    public function destroy($id)
    {
        try {
            $pId = decrypt($id);
            $payout = Payout::findOrFail($pId);
            $txn = EmployeeTxn::find($payout->statement_id);
            
            $payout->delete();
            if ($txn) $txn->delete();
            
            return redirect()->back()->with('success', 'Payout and transaction deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
