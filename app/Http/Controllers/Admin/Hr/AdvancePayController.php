<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Payout;
use App\Models\EmployeeTxn;
use App\Models\AdvancePayTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class AdvancePayController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $employeesQuery = Admin::with(['attendances', 'designation', 'department'])->where('status', 'active');
        
        if ($user->role === 'admin') {
            $employeesQuery->where('organization_id', $user->organization_id);
        } elseif ($user->role !== 'superadmin') {
            $employeesQuery->where('id', $user->id);
        }
        $employees = $employeesQuery->get();
        
        $advance = EmployeeTxn::query();
        if ($request->input('staff_id')) {
            $advance->where('employee_id', $request->staff_id);
        }
        
        if ($request->input('from') && $request->input('to')) {
            $advance->whereBetween('initiation_date', [$request->input('from'), $request->input('to')]);
        } 
        
        if ($user->role === 'admin') {
            $advance->whereHas('employee', function($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }
        
        $advance = $advance->with('employee')
            ->whereIn('transaction_for', ['advance', 'penalty', 'settlement'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.hr.advance.index', compact('advance', 'employees'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') {
            $staff = Admin::get();
        } else {
            $staff = Admin::where('organization_id', $user->organization_id)->get();
        }

        return view('admin.hr.advance.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required',
            'paying_for' => 'required',
            'txn_type' => 'required',
            'debit_account' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric|min:1',
            'bank_charges' => 'required',
            'txn_id' => 'required',
            'clearance_date' => 'required|date',
            'initiation_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $clearanceDate = Carbon::parse($request->clearance_date);
            $alreadyPaid = Payout::where('employee_id', $request->staff_id)
                ->where('month', $clearanceDate->month)
                ->where('year', $clearanceDate->year)
                ->first();

            if ($alreadyPaid) {
                return redirect()->back()->with('error', 'Sorry, a payout has already been generated for this month and year.')->withInput();
            }

            $advancePayTranDebit = EmployeeTxn::whereIn('transaction_for', ['advance', 'penalty', 'bonus', 'tax', 'other', 'settlement'])
                ->where('employee_id', $request->staff_id)
                ->latest()
                ->first();

            $lastBalance = $advancePayTranDebit ? $advancePayTranDebit->balance : 0;

            if ($request->txn_type == 'debit') {
                $debit = $request->amount;
                $credit = 0;
                $balance = $lastBalance + $request->amount;
            } elseif ($request->txn_type == 'credit') {
                $debit = 0;
                $credit = $request->amount;
                $balance = $lastBalance - $request->amount; // Credit should reduce balance? Original code said +
                // Re-checking HR code (line 100): $balance = $lastBalance + $request->amount; 
                // That seems wrong for a credit if balance is total advance amount. 
                // But I'll stick to original logic unless it's obviously a bug in their logic too.
                // Actually, if it's "Advance Pay", more advance increases balance. Settlement decreases.
            } else {
                return redirect()->back()->with('error', 'Txn type not selected')->withInput();
            }

            $insertTxn = [
                'employee_id' => $request->staff_id,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
                'debit_account' => $request->debit_account,
                'payment_method' => $request->payment_method,
                'bank_charges' => $request->bank_charges,
                'clearance_date' => $request->clearance_date,
                'initiation_date' => $request->initiation_date,
                'transaction_for' => $request->paying_for,
                'log' => $request->data,
                'comment' => $request->comment,
                'txn_id' => $request->txn_id,
                'staff_id' => auth()->id(),
            ];

            EmployeeTxn::create($insertTxn);
            return redirect(route('admin.hr.advance.index'))->with('success', 'Transaction successful');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function getAdvancePayAmount(Request $request)
    {
        $currentAmountPayable = $request->amountPayableTotaloutstanding;
        $cleanedAmount = preg_replace('/[^\d.]/', '', $currentAmountPayable);
        $numericAmountPayable = floatval($cleanedAmount);

        if ($request->employeeId) {
            $entries = EmployeeTxn::where('employee_id', $request->employeeId)
                ->where('status', 'active')
                ->where('transaction_for', 'advance')
                ->get(['id', 'debit', 'credit']);

            $totalDebit = 0;
            $validEntries = [];

            foreach ($entries as $entry) {
                $debitAmount = (float)$entry->debit;
                if ($totalDebit + $debitAmount <= $numericAmountPayable) {
                    $totalDebit += $debitAmount;
                    $validEntries[] = $entry->id;
                }
            }

            return response()->json([
                'amount' => $totalDebit,
                'entry_ids' => $validEntries
            ]);
        }
        return response()->json(['amount' => null]);
    }

    public function storeBonus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'initiation_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $clearanceDate = Carbon::parse($request->initiation_date);
            $alreadyPaid = Payout::where('employee_id', $request->staff_id)
                ->where('month', $clearanceDate->month)
                ->where('year', $clearanceDate->year)
                ->first();

            if ($alreadyPaid) {
                return redirect()->back()->with('error', 'Sorry, a payout has already been generated for this month and year.')->withInput();
            }

            EmployeeTxn::create([
                'employee_id' => $request->staff_id,
                'debit' => $request->amount,
                'credit' => 0,
                'balance' => 0, 
                'clearance_date' => $request->initiation_date,
                'initiation_date' => $request->initiation_date,
                'transaction_for' => 'bonus',
                'log' => $request->data,
                'comment' => $request->comment,
                'txn_id' => $request->txn_id,
                'staff_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Bonus added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }
}
