<?php

namespace App\Http\Controllers\Hr;

use Exception;
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Payout;
use App\Models\EmployeeTxn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\AdvancePayTransaction;
use Illuminate\Support\Facades\Validator;

class AdvancePayController extends Controller
{
    public function index(Request $request)
    {

        $employees = Admin::with(['attendances', 'designation', 'department'])
           
            ->when(Auth::guard('admin')->user()->role === 'admin', function ($query) {
                $query->where('organization_id', Auth::guard('admin')->user()->organization_id);
            })
            ->when(!in_array(Auth::guard('admin')->user()->role, ['superadmin', 'admin']), function ($query) {
                $query->where('id', Auth::guard('admin')->id());
            })
            ->where('status', 'active')->get();
        
        $advance = EmployeeTxn::query();
        if ($request->input('staff_id')) {
            $advance->where('employee_id', $request->staff_id);
        }
        if ($request->input('from') && $request->input('to')) {
            $advance = $advance->whereBetween('initiation_date', [$request->input('from'), $request->input('to')]);
        } 
        if($request->input('staff_id') || $request->input('from') || $request->input('to')){
            $advance = $advance->whereIn('transaction_for', ['advance', 'penalty','settlement'])->orderBy('created_at', 'desc')->get();
        }
        return view('hr.advance.index', compact('advance','employees'));
    }

    public function create()
    {


        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $staff = Admin::get();
        }else{
            $staff = Admin::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }

        return view('hr.advance.create', compact('staff'));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required',
            'paying_for' => 'required',
            'txn_type' => 'required',
            'debit_account' => 'required',
            'payment_method' => 'required',
            'amount' => 'required',
            'bank_charges' => 'required',
            'txn_id' => 'required',
            'clearance_date' => 'required',
            'initiation_date' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            $advancePayTranDebit = EmployeeTxn::whereIn('transaction_for', ['advance', 'penalty', 'bonus', 'tax', 'other','settlement'])
            ->where('employee_id', $request->staff_id) // Use $staff_id variable here
            ->latest()
            ->first();         
        // Calculate debit - credit
            $clearanceDate = Carbon::parse($request->clearance_date);
            // Query to check if a payout has already been made for this employee in the specified month and year
            $alreadyPaid = Payout::where('employee_id', $request->staff_id)
                ->where('month', $clearanceDate->month)  // Directly use the parsed month
                ->where('year', $clearanceDate->year)    // Directly use the parsed year
                ->first(); // Fetch the first record matching the conditions
            // If a payout is already found, redirect back with an error
            if ($alreadyPaid) {
                return redirect()->back()->with('error', 'Sorry, a payout has already been generated for this month and year.')->withInput();
            }
            $lastBalance = $advancePayTranDebit ? $advancePayTranDebit->balance : 0;

            if ($request->txn_type == 'debit') {
                $debit = $request->amount;
                $balance = $lastBalance + $request->amount;
                $credit = 0;
            } elseif ($request->txn_type == 'credit') {
                $debit = 0;
                $credit = $request->amount;
                $balance = $lastBalance + $request->amount;
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
                'staff_id' => Auth::guard('admin')->id(),
            ];
            $result = EmployeeTxn::create($insertTxn);
            return redirect(route('admin.advance.index'))->with('success', 'Txn success for staff');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        // code here
    }

    public function update(Request $request, $id)
    {
        // code here
    }

    public function destroy($id)
    {
        // code here
    }

    public function getAdvancePayAmount(Request $request)
    {

        $currentAmountPayable = $request->amountPayableTotaloutstanding;
        $cleanedAmount = preg_replace('/[₹,\/-]/', '', $currentAmountPayable);
        $numericAmountPayable = floatval($cleanedAmount);
        $numericAmountPayable = (int) $cleanedAmount;

        if ($request->employeeId) {
            // Fetch all the entries for the employee_id
            $entries = EmployeeTxn::where('employee_id', $request->employeeId)
                ->where('status', 'active')
                ->where('transaction_for', 'advance')
                ->get(['id', 'debit','credit']); // Get the id and debit columns

            $totalDebit = 0;
            $validEntries = [];

            // Iterate through the entries and accumulate the total debit until it exceeds $numericAmountPayable
            foreach ($entries as $entry) {
                // Ensure $entry->debit is treated as a numeric value
                $debitAmount = (float)$entry->debit; // Cast to float to ensure numeric addition

                if ($totalDebit + $debitAmount <= $numericAmountPayable) {
                    $totalDebit += $debitAmount;
                    // if($totalDebit){
                        $validEntries[] = $entry->id;
                    // }else{
                    //     $validEntries[] = "";
                    // }
                }
            }

            // Return the results
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
            'amount' => 'required',
            'initiation_date' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            $advancePayTranDebit = EmployeeTxn::whereIn('transaction_for', ['advance', 'penalty', 'bonus', 'tax', 'other','settlement'])
            ->where('employee_id', $request->staff_id) // Use $staff_id variable here
            ->latest()
            ->first();         
        // Calculate debit - credit
            $clearanceDate = Carbon::parse($request->clearance_date);
            // Query to check if a payout has already been made for this employee in the specified month and year
            $alreadyPaid = Payout::where('employee_id', $request->staff_id)
                ->where('month', $clearanceDate->month)  // Directly use the parsed month
                ->where('year', $clearanceDate->year)    // Directly use the parsed year
                ->first(); // Fetch the first record matching the conditions
            // If a payout is already found, redirect back with an error
            if ($alreadyPaid) {
                return redirect()->back()->with('error', 'Sorry, a payout has already been generated for this month and year.')->withInput();
            }
            $insertTxn = [
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
                'staff_id' => Auth::guard('admin')->id(),
            ];
            $result = EmployeeTxn::create($insertTxn);
            return redirect()->back()->with('success', 'Txn success for staff');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong, ' . $e->getMessage())->withInput();
        }
    }

}
