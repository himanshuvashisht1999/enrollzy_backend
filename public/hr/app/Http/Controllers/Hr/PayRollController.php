<?php

namespace App\Http\Controllers\Hr;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Bank;
use App\Models\Payout;
use App\Models\AdvancePayTransaction;
use App\Models\Attendance;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Models\LeaveSetting;
use App\Models\Holiday;
use App\Http\Controllers\Controller;
use App\Models\EmployeeTxn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use DateTime;

class PayRollController extends Controller
{
    public function showPayroll(Request $request)
    {
        $employees = Admin::with(['attendances', 'designation', 'department'])
            ->when(Auth::guard('admin')->user()->role === 'admin', function ($query) {
                $query->where('organization_id', Auth::guard('admin')->user()->organization_id);
            })
            ->when(!in_array(Auth::guard('admin')->user()->role, ['superadmin', 'admin']), function ($query) {
                $query->where('id', Auth::guard('admin')->id());
            })
            ->where('status', 'active')->get();
            $banks = Bank::where('organization_id', Auth::guard('admin')->user()->organization_id)->where('status','active')->get();
        return view('hr.payroll.index', compact('employees','banks'));
    }

    public function calculateMonthWiseSalary(Request $request)
    {
        $staffId = $request->get('staff_id');
        $month = $request->get('month');
        $year = $request->get('year');
        $staff = Admin::find($staffId);
        if (!$staff) {
            return response()->json(['status' => 0, 'message' => 'Staff not found.']);
        }


        
        $declaredHoliday = Holiday::whereRaw('FIND_IN_SET(?, department_ids)', [$staff->department_id])
        ->whereRaw('FIND_IN_SET(?, designation_ids)', [$staff->designation_id])
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->count();

        $employeeBonusTotal = EmployeeTxn::where('employee_id', $staff->id)
        ->whereMonth('clearance_date', $month)
        ->whereYear('clearance_date', $year)
        ->where('transaction_for', 'bonus')
        ->sum('debit');

        $employeeBonusTotals = EmployeeTxn::where('employee_id', $staff->id)
        ->whereMonth('clearance_date', $month)
        ->whereYear('clearance_date', $year)
        ->where('transaction_for', 'bonus')->first();

        if($staff->pay_based == 'monthly'){
            $workingDays = explode(',', strtolower($staff->working_days));
            $shiftHours = $staff->shift_hours; // Example: 9.5
            $monthlySalary = $staff->salary; // Fetch hourly rate from staff details
            $attendance = Attendance::with('breaks')->where('staff_id', $staffId)->whereMonth('date', $month)->whereYear('date', $year)->get();
            
            $totalWorkedMinutes = $attendance->sum('duration');
            $totalWorkedHoursDecimal = $totalWorkedMinutes / 60; // Keep as precise decimal
            $startOfMonth = Carbon::create($year, $month, 1);
            $monthName = $startOfMonth->format('F');
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $totalMonthDays = $endOfMonth->day; // Total days in the selected month
            $takenLunchMinutes = 0;
            $takenLunchPersonalMinutes = 0;

            foreach ($attendance as $attend) {
                foreach ($attend->breaks as $break) {
                    if ($break->type == 'lunch') {
                        $takenLunchMinutes += $break->duration; // Assuming 'duration' is in minutes
                    } elseif ($break->type == 'personal') {
                        $takenLunchPersonalMinutes += $break->duration; // Assuming 'duration' is in minutes
                    }
                }
            }
            $uniqueAttendanceDates = $attendance->pluck('date')->unique()->count(); // Count unique dates
            $expectedLunchTime = $uniqueAttendanceDates * 30; // 30 minutes per working day
            $lunchDifference = $takenLunchMinutes - $expectedLunchTime; // Positive means excess taken, negative means less taken
            if ($lunchDifference < 0) {
                $extraHoursWorked = abs($lunchDifference) / 60; // Convert minutes to hours
            } else {
                $totalWorkedHoursDecimal -= $lunchDifference / 60; // Convert minutes to hours
                $extraHoursWorked = 0; // Reset extra hours since lunch time exceeded
            }
            $totalExpectedWorkingDays = 0;
            for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
                if (in_array(strtolower($date->format('l')), $workingDays)) {
                    $totalExpectedWorkingDays++;
                }
            }
            $leavesSetting = LeaveSetting::where('department_ids', $staff->department_id)->where('designation_ids', $staff->designation_id)->where('organization_id', Auth::guard('admin')->user()->organization_id)->first();
            $checkDeclaredHoliday = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->count();



            $monthlyLeave = $leavesSetting ? $leavesSetting->monthly_leave : 0;
            $totalExpectedWorkingDays = $totalExpectedWorkingDays - $monthlyLeave - $checkDeclaredHoliday;
            $totalExpectedWorkingHours = $totalExpectedWorkingDays * $shiftHours;
            $extraHoursWorked = 0;
            $totalMonthPayForNormalHours = $monthlySalary;
            $perDaySalary = $totalMonthPayForNormalHours / 30;
            $decalredHolidayAmount = $perDaySalary * $declaredHoliday;
            $totalAmountToBePaid = $perDaySalary * $uniqueAttendanceDates + $decalredHolidayAmount;

            $hourlyRate = 00;
            $extraHoursPayAsDoubleSalary = 00;
            // Penalty Calculation
    
            $penaltyAmount = EmployeeTxn::where('employee_id', $staffId)
                ->whereMonth('clearance_date', $month)
                ->whereYear('clearance_date', $year)
                ->where('transaction_for', 'penalty')
                ->sum('credit'); // Sum of the penalty amount (credit field)
    
    
                $entries = EmployeeTxn::where('employee_id', $staffId)
                    ->where('status', 'active')
                    ->where('transaction_for', 'advance')
                    ->get(['id', 'debit']); // Get the id and debit columns
    
                $totalDebit = 0;
                $validEntries = [];
    
                // Iterate through the entries and accumulate the total debit until it exceeds $numericAmountPayable
                foreach ($entries as $entry) {
                    // Ensure $entry->debit is treated as a numeric value
                    $debitAmount = (float)$entry->debit; // Cast to float to ensure numeric addition
    
                        $totalDebit += $debitAmount;
                        $validEntries[] = $entry->id;
                }
    
            // verify that the selected month payment is already made or not ------------
            $payout = Payout::where('employee_id', $staffId)
                ->where('month', $month)
                ->where('year', $year)
                ->first(); // Fetch the first record matching the conditions
            if ($payout) {
                // If payout record exists
                $isAlreadyPaid = 'yes';
                $payoutData = '<a href="' . route('admin.payOut.show', encrypt($payout->id)) . '" class="btn btn-primary btn-block" target="_blank">View already paid payslip</a>';
            } else {
                // If no payout record exists
                $isAlreadyPaid = 'no';
                $payoutData = null;
            }
            // verify that the selected month payment is already made orr not
            // Subtract the penalty from total salary
    
            $monthName = DateTime::createFromFormat('!m', $month)->format('F');
            $advancePayTran = AdvancePayTransaction::where('month', $monthName)->where('year', $year)->where('transaction_type', 'debit')->where('staff_id',$staff->id)->sum('amount');
            // dd($advancePayTran);
            $totalAmountToBePaidWithPenalty = $totalAmountToBePaid - $penaltyAmount - $advancePayTran;
            // Prepare the data
            $data = [
                'employee_id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'designation' => $staff->designation->name,
                'department' => $staff->department->name,
                'monthName' => $monthName,
                'totalDebit' => $totalDebit,
                'days_in_month' => $totalMonthDays,
                'attendance' => $uniqueAttendanceDates,
                'shift_hours' => $shiftHours,
                'expected_working_day' => $totalExpectedWorkingDays,
                'expected_hours' => $totalExpectedWorkingHours,
                'working_hours' => number_format($totalWorkedHoursDecimal, 2),
                'extra_hours' => number_format($extraHoursWorked, 2),
                'expected_lunch_time' => $expectedLunchTime / 60,
                'taken_lunch_time' => number_format(($takenLunchMinutes / 60), 2),
                'taken_personal_lunch_time' => number_format(($takenLunchPersonalMinutes / 60), 2),
                'salary_hourly' => env('CURRENCY') . ' ' . $totalMonthPayForNormalHours . '/-',
                'normal_pay' => env('CURRENCY') . ' ' . number_format($totalAmountToBePaid, 2) . '/-',
                'extra_pay' => env('CURRENCY') . ' ' . number_format($extraHoursPayAsDoubleSalary, 2) . '/-',
                'penalty_amount' => env('CURRENCY') . ' ' . number_format($penaltyAmount, 2) . '/-', // Showing penalty amount
                
                
                'bonus_amount' => env('CURRENCY') . ' ' . number_format($employeeBonusTotal, 2) . '/-', // Showing Bonus amount
                'total_declared_holidays' => $declaredHoliday, // Showing Bonus amount
                'Holidays_amount' => env('CURRENCY') . ' ' . number_format($decalredHolidayAmount, 2) . '/-', // Showing Bonus amount
                'employeeBonusTotals' => $employeeBonusTotals,

                
                'total_payable_without_penalty' => env('CURRENCY') . ' ' . number_format($totalAmountToBePaid - $advancePayTran, 2) . '/-',
                'total_payable_with_penalty' => env('CURRENCY') . ' ' . number_format($totalAmountToBePaidWithPenalty, 2) . '/-',
                'total_advance_settelled' => env('CURRENCY') . ' ' . number_format($advancePayTran, 2) . '/-',
                'isAlreadyPaid' => $isAlreadyPaid,
                'payoutData' => $payoutData,
                'month' => $month,
                'year' => $year,
                'salary_type' => 'monthly',
            ];

        }else{
            $workingDays = explode(',', strtolower($staff->working_days));
            $shiftHours = $staff->shift_hours; // Example: 9.5
            $hourlyRate = $staff->salary; // Fetch hourly rate from staff details
            $attendance = Attendance::with('breaks')
                ->where('staff_id', $staffId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();
            $totalWorkedMinutes = $attendance->sum('duration');
            $totalWorkedHoursDecimal = $totalWorkedMinutes / 60; // Keep as precise decimal
            $startOfMonth = Carbon::create($year, $month, 1);
            $monthName = $startOfMonth->format('F');
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $totalMonthDays = $endOfMonth->day; // Total days in the selected month
            $takenLunchMinutes = 0;
            $takenLunchPersonalMinutes = 0;
            foreach ($attendance as $attend) {
                foreach ($attend->breaks as $break) {
                    if ($break->type == 'lunch') {
                        $takenLunchMinutes += $break->duration; // Assuming 'duration' is in minutes
                    } elseif ($break->type == 'personal') {
                        $takenLunchPersonalMinutes += $break->duration; // Assuming 'duration' is in minutes
                    }
                }
            }
            $uniqueAttendanceDates = $attendance->pluck('date')->unique()->count(); // Count unique dates
            $expectedLunchTime = $uniqueAttendanceDates * 30; // 30 minutes per working day
            $lunchDifference = $takenLunchMinutes - $expectedLunchTime; // Positive means excess taken, negative means less taken
            if ($lunchDifference < 0) {
                $extraHoursWorked = abs($lunchDifference) / 60; // Convert minutes to hours
            } else {
                $totalWorkedHoursDecimal -= $lunchDifference / 60; // Convert minutes to hours
                $extraHoursWorked = 0; // Reset extra hours since lunch time exceeded
            }
            $totalExpectedWorkingDays = 0;
            for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
                if (in_array(strtolower($date->format('l')), $workingDays)) {
                    $totalExpectedWorkingDays++;
                }
            }
            $totalExpectedWorkingHours = $totalExpectedWorkingDays * $shiftHours;
            $extraHoursWorked = max(0, $totalWorkedHoursDecimal - $totalExpectedWorkingHours + $extraHoursWorked);
            $totalMonthPayForNormalHours = min($totalWorkedHoursDecimal, $totalExpectedWorkingHours) * $hourlyRate;
            $extraHoursPayAsDoubleSalary = $extraHoursWorked * $hourlyRate * 2;
            $pperdaysalary = $hourlyRate * $shiftHours;

            $decalredHolidayAmount = $pperdaysalary * $declaredHoliday;
            $totalAmountToBePaid = $totalMonthPayForNormalHours + $extraHoursPayAsDoubleSalary + $decalredHolidayAmounts;
            // Penalty Calculation
    
            $penaltyAmount = EmployeeTxn::where('employee_id', $staffId)
                ->whereMonth('clearance_date', $month)
                ->whereYear('clearance_date', $year)
                ->where('transaction_for', 'penalty')
                ->sum('credit'); // Sum of the penalty amount (credit field)
    
    
                $entries = EmployeeTxn::where('employee_id', $staffId)
                    ->where('status', 'active')
                    ->where('transaction_for', 'advance')
                    ->get(['id', 'debit']); // Get the id and debit columns
    
                $totalDebit = 0;
                $validEntries = [];
    
                // Iterate through the entries and accumulate the total debit until it exceeds $numericAmountPayable
                foreach ($entries as $entry) {
                    // Ensure $entry->debit is treated as a numeric value
                    $debitAmount = (float)$entry->debit; // Cast to float to ensure numeric addition
    
                        $totalDebit += $debitAmount;
                        $validEntries[] = $entry->id;
                }
    
            // verify that the selected month payment is already made or not ------------
            $payout = Payout::where('employee_id', $staffId)
                ->where('month', $month)
                ->where('year', $year)
                ->first(); // Fetch the first record matching the conditions
            if ($payout) {
                // If payout record exists
                $isAlreadyPaid = 'yes';
                $payoutData = '<a href="' . route('admin.payOut.show', encrypt($payout->id)) . '" class="btn btn-primary btn-block" target="_blank">View already paid payslip</a>';
            } else {
                // If no payout record exists
                $isAlreadyPaid = 'no';
                $payoutData = null;
            }
            // verify that the selected month payment is already made orr not
            // Subtract the penalty from total salary
    
            $monthName = DateTime::createFromFormat('!m', $month)->format('F');
            $advancePayTran = AdvancePayTransaction::where('month', $monthName)->where('year', $year)->where('transaction_type', 'debit')->sum('amount');
            // dd($advancePayTran);
            $totalAmountToBePaidWithPenalty = $totalAmountToBePaid - $penaltyAmount - $advancePayTran;
            // Prepare the data
            $data = [
                'employee_id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'designation' => $staff->designation->name,
                'department' => $staff->department->name,
                'monthName' => $monthName,
                'totalDebit' => $totalDebit,
                'days_in_month' => $totalMonthDays,
                'attendance' => $uniqueAttendanceDates,
                'shift_hours' => $shiftHours,
                'expected_working_day' => $totalExpectedWorkingDays,
                'expected_hours' => $totalExpectedWorkingHours,
                'working_hours' => number_format($totalWorkedHoursDecimal, 2),
                'extra_hours' => number_format($extraHoursWorked, 2),
                'expected_lunch_time' => $expectedLunchTime / 60,
                'taken_lunch_time' => number_format(($takenLunchMinutes / 60), 2),
                'taken_personal_lunch_time' => number_format(($takenLunchPersonalMinutes / 60), 2),
                'salary_hourly' => env('CURRENCY') . ' ' . $hourlyRate . '/-',
                'normal_pay' => env('CURRENCY') . ' ' . number_format($totalMonthPayForNormalHours, 2) . '/-',
                'extra_pay' => env('CURRENCY') . ' ' . number_format($extraHoursPayAsDoubleSalary, 2) . '/-',
                'penalty_amount' => env('CURRENCY') . ' ' . number_format($penaltyAmount, 2) . '/-', // Showing penalty amount
                
                'bonus_amount' => env('CURRENCY') . ' ' . number_format($employeeBonusTotal, 2) . '/-', // Showing Bonus amount
                'total_declared_holidays' => $declaredHoliday, // Showing Bonus amount
                'Holidays_amount' => env('CURRENCY') . ' ' . number_format($decalredHolidayAmount, 2) . '/-', // Showing Bonus amount
                'employeeBonusTotals' => $employeeBonusTotals,

                'total_payable_without_penalty' => env('CURRENCY') . ' ' . number_format($totalAmountToBePaid - $advancePayTran, 2) . '/-',
                'total_payable_with_penalty' => env('CURRENCY') . ' ' . number_format($totalAmountToBePaidWithPenalty, 2) . '/-',
                'total_advance_settelled' => env('CURRENCY') . ' ' . number_format($advancePayTran, 2) . '/-',
                'isAlreadyPaid' => $isAlreadyPaid,
                'payoutData' => $payoutData,
                'month' => $month,
                'year' => $year,
                'salary_type' => 'hourly',
            ];
        }

        session()->put('salaryCalculation', $data);
        return response()->json(['status' => 1, 'data' => $data]);
    }

    public function makeEmployeePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer|min:1',
            'debit_account' => 'required',
            'payment_method' => 'required',
            'clearance_date' => 'required|date',
            'initiation_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $data = session()->get('salaryCalculation');
        if (!$data) {
            return response()->json(['status' => 0, 'message' => 'Some Data is missing, please contact Developer, or Refresh the Page']);
        }
            // Extract the numeric values
            $amount = $this->extractAmount($request->amount);
            $amountPayableTotal = $this->extractAmount($request->amountPayableTotal);

            // Check if any amount is less than or equal to 0
            if ($amount < 0 || $amountPayableTotal < 0) {
                return response()->json(['status' => 0, 'message' => 'Salary amount should not be less 0']);
            }

        try {

            $payslipIdData = DB::table('employee_payout_temp')->where('month',$data['month'])->where('year',$data['year'])->where('staff_id',$request->employee_id)->first();

            if ($payslipIdData) {
                $paySlipID = $payslipIdData->pay_slip_id;
            } else {
                $paySlipID = $this->generatePaySlipID($request->employee_id, $data);
                DB::table('employee_payout_temp')->insert([
                    'pay_slip_id' => $paySlipID,
                    'month' => $data['month'],
                    'year' => $data['year'],
                    'staff_id' => $request->employee_id,

                ]);
            }
            if($request->debit_account === "advance_pay"){
                $monthName = DateTime::createFromFormat('!m', $data['month'])->format('F');
                $insertAdvancePay = [
                    'amount' => $request->paid_amount,
                    'advance_pay_ids' => $request->advancepayIds,
                    'transaction_type' => 'debit',
                    'transaction_for' => 'salary',
                    'log' => $request->paid_amount.' adjusted in month of '.$data['month'],
                    'staff_id' => $request->employee_id,
                    'status' => 'settlement',
                    'month' => $monthName,
                    'year' => $data['year'],
                ];


                $advancePayTranDebit = EmployeeTxn::whereIn('transaction_for', ['advance', 'penalty', 'bonus', 'tax', 'other','settlement'])
                ->where('employee_id', $request->employee_id) // Use $staff_id variable here
                ->latest()
                ->first(); 

                $advancePayTranDebit->balance;

                $balance = $advancePayTranDebit->balance - $request->paid_amount;

                $insertTxn = [
                    'employee_id' => $request->employee_id,
                    'debit' => 0,
                    'credit' => $request->paid_amount,
                    'balance' => $balance,
                    'payslip_id' => $paySlipID,
                    'debit_account' => $request->debit_account,
                    'payment_method' => $request->payment_method,
                    'bank_charges' => $request->bank_charges,
                    'clearance_date' => $request->clearance_date,
                    'initiation_date' => $request->initiation_date,
                    'transaction_for' => 'settlement',
                    'log' => $request->data,
                    'comment' => $request->comment,
                    'txn_id' => $request->txn_id,
                    'staff_id' => Auth::guard('admin')->id(),
                ];
                $result = EmployeeTxn::create($insertTxn);

                AdvancePayTransaction::create($insertAdvancePay);

                if($request->debit_account == "advance_pay" && $amount > 1){
                    return response()->json(['status' => 1, 'message' => 'Payment Success']);
                }
                
            }

            // $paySlipID = $this->generatePaySlipID($request->employee_id, $data);
            $statementId = $this->makeTransaction($paySlipID, $data, $request);
            $insertPay = [
                'payslip_id' => $paySlipID,
                'employee_id' => $request->employee_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'month' => $data['month'],
                'year' => $data['year'],
                'total_salary_amount' => $request->amountPayableTotal,
                'deducted_amount' => $request->paid_amount,
                'amount' => $request->amount,
                'comment' => $request->comment,
                'comment' => $request->comment,
                'statement_id' => $statementId,
                'slip_data' => json_encode($data),
                'staff_id' => Auth::guard('admin')->id(),
            ];
            Payout::create($insertPay);
           
            return response()->json(['status' => 1, 'message' => 'Payment Success']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 0, 'message' => 'An unexpected error occurred, please refresh the page.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    // Function to clean up the amount and extract the numeric value
    function extractAmount($amountStr) {
        // Remove the currency symbol and any extra characters
        $cleanedAmount = preg_replace('/[^\d.-]/', '', $amountStr);
        return (float)$cleanedAmount;
    }

    private function generatePaySlipID($empID, $data)
    {
        if (!is_numeric($empID) || $empID <= 0) {
            throw new InvalidArgumentException('Invalid Employee ID.');
        }
        return $this->generatePaySlip($empID, $data['month'], $data['year']);
        return 'ADV-' . date('Hi-dmy');
    }

    private function generatePaySlip($empID, $month, $year)
    {
        if ($month < 1 || $month > 12 || $year < 2000 || $year > date('Y')) {
            throw new InvalidArgumentException('Invalid month or year.');
        }
        $sequence = Payout::where('employee_id', $empID)
            ->where('month', $month)
            ->where('year', $year)
            ->count() + 1;
        return sprintf("SLIP%02d%d%02d", $month, $empID, $sequence);
    }

    private function makeTransaction($paySlipID, $data, $request)
    {
        if (!is_numeric($request->employee_id) || $request->employee_id <= 0) {
            throw new InvalidArgumentException('Invalid Employee ID.');
        }
        if ($data['month'] < 1 || $data['month'] > 12 || $data['year'] < 2000 || $data['year'] > date('Y')) {
            throw new InvalidArgumentException('Invalid month or year.');
        }
        $transactionData = [
            'employee_id' => $request->employee_id,
            'debit' => $request->amount,
            'credit' => 0.00,
            'payslip_id' => $paySlipID,
            'debit_account' => $request->debit_account,
            'payment_method' => $request->payment_method,
            'bank_charges' => $request->bank_charges,
            'clearance_date' => $request->clearance_date,
            'initiation_date' => $request->initiation_date,
            'transaction_for' => 'salary',
            'log' => 'Paying INR ' . $request->amount . ' as salary',
            'comment' => $request->comment,
            'txn_id' => $request->txn_id,
            'staff_id' => Auth::guard('admin')->id(),
        ];
        return EmployeeTxn::create($transactionData)->id;
    }
}
