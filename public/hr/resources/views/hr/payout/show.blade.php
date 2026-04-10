@extends('layouts.app')
@section('push_css')
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
                                @php
                                    $emp = App\Models\Admin::find($payout->employee_id);
                                    $slip = json_decode($payout->slip_data, true);
                                    $normalPay = (float) preg_replace('/[^0-9.]/', '', $slip['normal_pay']);
                                    
                                    $extraPay = (float) preg_replace('/[^0-9.]/', '', $slip['extra_pay']);

                                    $totalsalary = $normalPay + $extraPay;
                                @endphp
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">View Payout for
                    {{ date('F', mktime(0, 0, 0, $payout->month, 1)) }}</h6>
                <a href="{{ route('admin.payOut.edit', encrypt($payout->id)) }}" target="_blank"
                    class="btn btn-primary btn-sm">Print
                    PDF</a>
                <a href="{{ route('admin.payOut.index') }}" class="btn btn-secondary btn-sm w-50"> Go Back</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 form-group">
                        <label for="payslip_id">Payslip Id</label>
                        <input type="text" class="form-control" value="{{ $payout->payslip_id }}" readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="name">Name <a href="{{ route('admin.staff.edit', encrypt($payout->employee_id)) }}">
                                <i class="fa fa-link"></i> </a></label>
                        <input type="text" class="form-control" value="{{ $payout->name }}" readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" value="{{ $payout->email }}" readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" value="{{ $payout->phone }}" readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="month">For Month</label>
                        <input type="text" class="form-control"
                            value="{{ date('F', mktime(0, 0, 0, $payout->month, 1)) }}" readonly>
                    </div>
                    <div class="col-lg-2 form-group">
                        <label for="year">For Year</label>
                        <input type="text" class="form-control" value="{{ $payout->year }}" readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="amount">Total Salary Amount INR</label>
                        <input type="text" class="form-control" value="{{ $totalsalary }}"
                            readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="amount">Total Declared Holidays INR</label>
                        <input type="text" class="form-control" value="{{ $declaredHolidayTotal }}"
                            readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="amount">Total Bonus INR</label>
                        <input type="text" class="form-control" value="{{ $employeeBonusTotal }}"
                            readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="amount">Total Salary Deductions INR</label>
                        <input type="text" class="form-control" value="{{ env('CURRENCY') . $advancePayTran }}"
                            readonly>
                    </div>
                    <div class="col-lg-2 form-group">
                        <label for="amount">Paid salary </label>
                        <input type="text" class="form-control" value="{{ $payout->amount }}"
                            readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="staff_id">Created By</label>
                        @php
                            $staff = App\Models\Admin::find($payout->staff_id);
                        @endphp
                        <input type="text" class="form-control" value="{{ $staff->name . ' - ' . $staff->role }}"
                            readonly>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label for="created_at">Created At</label>
                        <input type="text" class="form-control"
                            value="{{ date('h:i A - d-M-Y', strtotime($payout->created_at)) }}" readonly>
                    </div>
                    <div class="col-lg-12 form-group">
                        <label for="comment">Comment</label>
                        <textarea class="form-control" readonly>{{ $payout->comment }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        Pay Slip Details
                        <div>
                            <table class="table table-bordered table-responsive">
                                <thead>
                                    <tr>
                                        <th colspan="2">Details</th>
                                        <th>Values</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="2"><strong>Designation:</strong></td>
                                        <td id="designation">{{ $emp->department->name }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Department:</strong></td>
                                        <td id="department">{{ $emp->designation->name }}</td>
                                    </tr>
                                    {{-- -------------------- --}}
                                    @if ($slip)
                                        <tr>
                                            <td colspan="2"><strong>Total Days in Month:</strong></td>
                                            <td id="days_in_month">{{ $slip['days_in_month'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Expected Working Days:</strong></td>
                                            <td id="expected_working_day">{{ $slip['expected_working_day'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Total Attendance:</strong></td>
                                            <td id="attendance">{{ $slip['attendance'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Daily Shift Hours:</strong></td>
                                            <td id="shift_hours">{{ $slip['shift_hours'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Expected Working Hours:</strong></td>
                                            <td id="expected_hours">{{ $slip['expected_hours'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Total Worked Hours:</strong></td>
                                            <td id="working_hours">{{ $slip['working_hours'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Extra Hours Worked:</strong></td>
                                            <td id="extra_hours">{{ $slip['extra_hours'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Expected Lunch Hours:</strong></td>
                                            <td id="expected_lunch_time">{{ $slip['expected_lunch_time'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Taken Lunch Hours:</strong></td>
                                            <td id="taken_lunch_time">{{ $slip['taken_lunch_time'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Taken Personal Lunch Hours:</strong></td>
                                            <td id="taken_personal_lunch_time">{{ $slip['taken_personal_lunch_time'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Hourly Salary in INR:</strong></td>
                                            <td id="salary_hourly">{{ $slip['salary_hourly'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Payable Amount for Scheduled Hours:</strong></td>
                                            <td id="normal_pay">{{ $slip['normal_pay'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Total Advance Settled:</strong></td>
                                            <td id="normal_pay">₹ {{ $advancePayTran }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Total Bonus Added:</strong></td>
                                            <td id="normal_pay">₹ {{ $employeeBonusTotal }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>Payable Amount for Extra Hours:</strong></td>
                                            <td id="extra_pay">{{ $slip['extra_pay'] }}</td>
                                        </tr>
                                        @php
                                            
                                            $subtotalAmounts = $totalsalary - $advancePayTran;
                                            $totalsalaryAmount = $subtotalAmounts + $extraPay + $employeeBonusTotal;

                                        @endphp
                                        <tr>
                                            <td colspan="2"><strong>Total Payable Amount:</strong></td>
                                            <td id="extra_pay">₹ {{ $totalsalaryAmount }}</td>
                                        </tr>
                                        
                                        
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        Transaction Details
                        <div>
                            <table class="table table-bordered table-responsive">
                                <thead>
                                    <tr>
                                        <th colspan="2">Details</th>
                                        <th>Values</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="2"><strong>Debit:</strong></td>
                                        <td id="debit">{{ $txn->debit }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Credit:</strong></td>
                                        <td id="credit">{{ $txn->credit }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Payslip ID:</strong></td>
                                        <td id="payslip_id">{{ $txn->payslip_id }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Debit Account:</strong></td>
                                        <td id="debit_account">{{ $txn->debit_account }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Payment Method:</strong></td>
                                        <td id="payment_method">{{ $txn->payment_method }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Bank Charges:</strong></td>
                                        <td id="bank_charges">{{ $txn->bank_charges }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Clearance Date:</strong></td>
                                        <td id="clearance_date">{{ $txn->clearance_date }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Initiation Date:</strong></td>
                                        <td id="initiation_date">{{ $txn->initiation_date }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Transaction For:</strong></td>
                                        <td id="transaction_for">{{ $txn->transaction_for }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Log:</strong></td>
                                        <td id="log">{{ $txn->log }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Comment:</strong></td>
                                        <td id="comment">{{ $txn->comment }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Transaction ID:</strong></td>
                                        <td id="txn_id">{{ $txn->txn_id }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @php
                        $emp = App\Models\Admin::find($payout->employee_id);
                        $slip = json_decode($payout->slip_data, true);
                    @endphp
                   

                </div>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
@endsection
