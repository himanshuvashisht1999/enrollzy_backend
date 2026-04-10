<?php

namespace App\Http\Controllers\Hr;


use App\Models\Payout;
use PDF;
use App\Models\EmployeeTxn;
use App\Models\AdvancePayTransaction;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Holiday;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use DateTime;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Payout::orderBy('created_at', 'desc')->get();
            }else{
                $data = Payout::where('organization_id', Auth::guard('admin')->user()->organization_id)->orderBy('created_at', 'desc')->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('month', function ($row) {
                    $month = '<p class="text-sm">' . date('F', mktime(0, 0, 0, $row->month, 1)) . '</p>';
                    return $month;
                })
                ->addColumn('created_at', function ($row) {
                    $created_at = '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                    return $created_at;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';

                    $btn .= '<a href="' . route('admin.payOut.show', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-eye text-primary"></i></a>';
                    $btn .= ' | ';

                    $btn .= '<form method="POST" action="' . route('admin.payOut.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns([
                    'month',
                    'created_at',
                    'action'
                ])
                ->make(true);
        }
        return view('hr.payout.index');
    }

    public function show($id)
    {
        $pId = decrypt($id);
        $payout = Payout::find($pId);
        if (!$payout) {
            return redirect()->back()->with('error', 'Payout not found.');
        }
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

        $perDaySalary = $staff->shift_hours * $staff->salary;
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
        return view('hr.payout.show', compact('txn', 'payout','advancepaymenthistory','advancePayTran','declaredHolidayTotal','employeeBonusTotal'));
    }

    public function edit($id)
    {
        try {
            $pId = decrypt($id);
            $payout = Payout::findOrFail($pId);
            $txn = EmployeeTxn::findOrFail($payout->statement_id);
            $pdf = PDF::loadView('pdf.employee_payment', [
                'payout' => $payout,
                'txn' => $txn,
            ]);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->stream("employee-payment-slip.pdf");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'payout & transaction cannot delete ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        try {
            $pId = decrypt($id);
            $payout = Payout::findOrFail($pId);
            $txn = EmployeeTxn::findOrFail($payout->statement_id);
            $payout->delete();
            $txn->delete();
            return redirect()->back()->with('success', 'payout & transaction deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'payout & transaction cannot delete ' . $e->getMessage());
        }
    }
}
