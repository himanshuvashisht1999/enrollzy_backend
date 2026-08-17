<?php

namespace App\Http\Controllers\Admin\Hr;

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
use Illuminate\Support\Facades\DB;
use DateTime;

class PayRollController extends Controller
{
    public function showPayroll(Request $request)
    {
        $user = auth()->user();
        $employeesQuery = Admin::with(['attendances', 'designation', 'department'])->where('status', 'active');
        
        if ($user->role === 'admin') {
            $employeesQuery->where('organization_id', $user->organization_id);
        } elseif ($user->role !== 'superadmin') {
            $employeesQuery->where('id', $user->id);
        }
        $employees = $employeesQuery->get();

        $banksQuery = Bank::where('status', 'active');
        if ($user->role === 'admin') {
            $banksQuery->where('organization_id', $user->organization_id);
        }
        $banks = $banksQuery->get();

        return view('admin.hr.payroll.index', compact('employees', 'banks'));
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
            $shiftHours = $staff->shift_hours ?? 9;
            $monthlySalary = $staff->salary;
            
            $attendance = Attendance::with('breaks')->where('staff_id', $staffId)
                ->whereMonth('date', $month)->whereYear('date', $year)->get();
            
            $totalWorkedMinutes = $attendance->sum('duration');
            $totalWorkedHoursDecimal = $totalWorkedMinutes / 60;
            
            $startOfMonth = Carbon::create($year, $month, 1);
            $monthNameFull = $startOfMonth->format('F');
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $totalMonthDays = $endOfMonth->day;
            
            $takenLunchPersonalMinutes = 0;

            foreach ($attendance as $attend) {
                foreach ($attend->breaks as $break) {
                    if ($break->type == 'personal') {
                        $takenLunchPersonalMinutes += $break->duration;
                    }
                }
            }
            $uniqueAttendanceDates = $attendance->pluck('date')->unique()->count();
            
            $extraHoursWorked = 0;

            $totalExpectedWorkingDays = 0;
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                if (in_array(strtolower($date->format('l')), $workingDays)) {
                    $totalExpectedWorkingDays++;
                }
            }
            
            $leavesSetting = LeaveSetting::where('department_ids', 'LIKE', '%'.$staff->department_id.'%')
                ->where('designation_ids', 'LIKE', '%'.$staff->designation_id.'%')
                ->where('organization_id', $staff->organization_id)
                ->first();

            $checkDeclaredHolidays = Holiday::whereYear('date', $year)->whereMonth('date', $month)->count();
            $monthlyLeave = $leavesSetting ? $leavesSetting->monthly_leave : 0;
            
            $adjustedExpectedWorkingDays = $totalExpectedWorkingDays - $monthlyLeave - $checkDeclaredHolidays;
            $totalExpectedWorkingHours = ($adjustedExpectedWorkingDays > 0 ? $adjustedExpectedWorkingDays : 0) * $shiftHours;
            
            $totalMonthPayForNormalHours = $monthlySalary;
            $perDaySalary = $totalMonthPayForNormalHours / 30;
            $decalredHolidayAmount = $perDaySalary * $declaredHoliday;
            $totalAmountToBePaid = ($perDaySalary * $uniqueAttendanceDates) + $decalredHolidayAmount;

            $penaltyAmount = EmployeeTxn::where('employee_id', $staffId)
                ->whereMonth('clearance_date', $month)->whereYear('clearance_date', $year)
                ->where('transaction_for', 'penalty')->sum('credit');

            $advancePayTran = AdvancePayTransaction::where('month', $monthNameFull)->where('year', $year)
                ->where('transaction_type', 'debit')->where('staff_id', $staff->id)->sum('amount');

            $payout = Payout::where('employee_id', $staffId)->where('month', $month)->where('year', $year)->first();
            $isAlreadyPaid = $payout ? 'yes' : 'no';
            $payoutData = $payout ? '<a href="' . route('admin.hr.payout.show', encrypt($payout->id)) . '" class="btn btn-primary d-block" target="_blank">View already paid payslip</a>' : null;

            $totalAmountToBePaidWithPenalty = $totalAmountToBePaid - $penaltyAmount - $advancePayTran;

            $data = [
                'employee_id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'designation' => $staff->designation->name ?? 'N/A',
                'department' => $staff->department->name ?? 'N/A',
                'monthName' => $monthNameFull,
                'days_in_month' => $totalMonthDays,
                'attendance' => $uniqueAttendanceDates,
                'shift_hours' => $shiftHours,
                'expected_working_day' => $totalExpectedWorkingDays,
                'expected_hours' => $totalExpectedWorkingHours,
                'working_hours' => number_format($totalWorkedHoursDecimal, 2),
                'extra_hours' => number_format($extraHoursWorked, 2),
                'expected_lunch_time' => 0,
                'taken_lunch_time' => 0,
                'taken_personal_lunch_time' => number_format(($takenLunchPersonalMinutes / 60), 2),
                'salary_basis' => 'Monthly: ' . env('CURRENCY', '₹') . ' ' . $totalMonthPayForNormalHours,
                'normal_pay' => env('CURRENCY', '₹') . ' ' . number_format($totalAmountToBePaid, 2),
                'penalty_amount' => env('CURRENCY', '₹') . ' ' . number_format($penaltyAmount, 2),
                'bonus_amount' => env('CURRENCY', '₹') . ' ' . number_format($employeeBonusTotal, 2),
                'total_declared_holidays' => $declaredHoliday,
                'Holidays_amount' => env('CURRENCY', '₹') . ' ' . number_format($decalredHolidayAmount, 2),
                'total_payable_without_penalty' => env('CURRENCY', '₹') . ' ' . number_format($totalAmountToBePaid - $advancePayTran, 2),
                'total_payable_with_penalty' => env('CURRENCY', '₹') . ' ' . number_format($totalAmountToBePaidWithPenalty, 2),
                'total_advance_settelled' => env('CURRENCY', '₹') . ' ' . number_format($advancePayTran, 2),
                'isAlreadyPaid' => $isAlreadyPaid,
                'payoutData' => $payoutData,
                'month' => $month, 'year' => $year,
                'salary_type' => 'monthly',
            ];

        } else {
            // Hourly logic...
            $workingDays = explode(',', strtolower($staff->working_days));
            $shiftHours = $staff->shift_hours ?? 8;
            $hourlyRate = $staff->salary;
            
            $attendance = Attendance::with('breaks')->where('staff_id', $staffId)
                ->whereMonth('date', $month)->whereYear('date', $year)->get();
            
            $totalWorkedMinutes = $attendance->sum('duration');
            $totalWorkedHoursDecimal = $totalWorkedMinutes / 60;
            
            $startOfMonth = Carbon::create($year, $month, 1);
            $monthNameFull = $startOfMonth->format('F');
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            
            $uniqueAttendanceDates = $attendance->pluck('date')->unique()->count();
            
            $totalExpectedWorkingDays = 0;
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                if (in_array(strtolower($date->format('l')), $workingDays)) $totalExpectedWorkingDays++;
            }
            
            $totalExpectedWorkingHours = $totalExpectedWorkingDays * $shiftHours;
            $extraHoursWorked = max(0, $totalWorkedHoursDecimal - $totalExpectedWorkingHours);
            $totalMonthPayForNormalHours = min($totalWorkedHoursDecimal, $totalExpectedWorkingHours) * $hourlyRate;
            $extraHoursPay = $extraHoursWorked * $hourlyRate * 1.5; // Example 1.5x for OT
            
            $perDaySalary = $shiftHours * $hourlyRate;
            $decalredHolidayAmount = $perDaySalary * $declaredHoliday;
            $totalAmountToBePaid = $totalMonthPayForNormalHours + $extraHoursPay + $decalredHolidayAmount;

            $penaltyAmount = EmployeeTxn::where('employee_id', $staffId)
                ->whereMonth('clearance_date', $month)->whereYear('clearance_date', $year)
                ->where('transaction_for', 'penalty')->sum('credit');

            $advancePayTran = AdvancePayTransaction::where('month', $monthNameFull)->where('year', $year)
                ->where('transaction_type', 'debit')->where('staff_id', $staff->id)->sum('amount');

            $totalAmountToBePaidWithPenalty = $totalAmountToBePaid - $penaltyAmount - $advancePayTran;

            $data = [
                'employee_id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'designation' => $staff->designation->name ?? 'N/A',
                'department' => $staff->department->name ?? 'N/A',
                'monthName' => $monthNameFull,
                'days_in_month' => $endOfMonth->day,
                'attendance' => $uniqueAttendanceDates,
                'shift_hours' => $shiftHours,
                'expected_working_day' => $totalExpectedWorkingDays,
                'expected_hours' => $totalExpectedWorkingHours,
                'working_hours' => number_format($totalWorkedHoursDecimal, 2),
                'extra_hours' => number_format($extraHoursWorked, 2),
                'salary_basis' => 'Hourly Rate: ' . env('CURRENCY', '₹') . ' ' . $hourlyRate,
                'normal_pay' => env('CURRENCY', '₹') . ' ' . number_format($totalMonthPayForNormalHours, 2),
                'extra_pay' => env('CURRENCY', '₹') . ' ' . number_format($extraHoursPay, 2),
                'penalty_amount' => env('CURRENCY', '₹') . ' ' . number_format($penaltyAmount, 2),
                'bonus_amount' => env('CURRENCY', '₹') . ' ' . number_format($employeeBonusTotal, 2),
                'Holidays_amount' => env('CURRENCY', '₹') . ' ' . number_format($decalredHolidayAmount, 2),
                'total_payable_with_penalty' => env('CURRENCY', '₹') . ' ' . number_format($totalAmountToBePaidWithPenalty, 2),
                'total_advance_settelled' => env('CURRENCY', '₹') . ' ' . number_format($advancePayTran, 2),
                'month' => $month, 'year' => $year,
                'salary_type' => 'hourly',
            ];
        }

        session()->put('salaryCalculation', $data);
        return response()->json(['status' => 1, 'data' => $data]);
    }

    public function makeEmployeePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
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
            return response()->json(['status' => 0, 'message' => 'Salary session data missing. Please reload.']);
        }

        $amount = (float)preg_replace('/[^\d.]/', '', $request->amount);
        if ($amount < 0) {
            return response()->json(['status' => 0, 'message' => 'Salary amount cannot be negative.']);
        }

        try {
            $paySlipID = $this->generatePaySlipID($request->employee_id, $data);
            
            $transactionData = [
                'employee_id' => $request->employee_id,
                'debit' => $amount,
                'credit' => 0.00,
                'payslip_id' => $paySlipID,
                'debit_account' => $request->debit_account,
                'payment_method' => $request->payment_method,
                'bank_charges' => $request->bank_charges,
                'clearance_date' => $request->clearance_date,
                'initiation_date' => $request->initiation_date,
                'transaction_for' => 'salary',
                'log' => 'Paid ' . env('CURRENCY', '₹') . ' ' . $amount . ' as salary for ' . $data['monthName'],
                'comment' => $request->comment,
                'txn_id' => $request->txn_id,
                'staff_id' => auth()->id(),
            ];
            $statement = EmployeeTxn::create($transactionData);

            Payout::create([
                'payslip_id' => $paySlipID,
                'employee_id' => $request->employee_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'month' => $data['month'],
                'year' => $data['year'],
                'total_salary_amount' => $request->amountPayableTotal,
                'deducted_amount' => $request->paid_amount,
                'amount' => $amount,
                'comment' => $request->comment,
                'statement_id' => $statement->id,
                'slip_data' => json_encode($data),
                'staff_id' => auth()->id(),
                'organization_id' => auth()->user()->organization_id ?? null
            ]);
           
            return response()->json(['status' => 1, 'message' => 'Payment processed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    private function generatePaySlipID($empID, $data)
    {
        $sequence = Payout::where('employee_id', $empID)->where('month', $data['month'])->where('year', $data['year'])->count() + 1;
        return sprintf("SLIP%02d%d%02d", $data['month'], $empID, $sequence);
    }
}
