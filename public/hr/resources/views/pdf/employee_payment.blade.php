<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 9in 12in;
            margin: 0.1in;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;

        }

        h4 {
            margin: 2px;
            padding: 2px;
        }

        .header,
        .footer {
            width: 100%;
            text-align: center;
            /* position: fixed; */
        }

        .header {
            top: 0px;
        }

        .footer {
            bottom: 0px;
            padding-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .details {
            margin-bottom: 20px;
            padding-bottom: 20px;
            page-break-inside: avoid;
        }

        .details .user,
        .details .seller {
            display: inline-block;
            vertical-align: top;
        }

        .details .user {
            text-align: left;
        }

        .details .seller {
            text-align: right;
        }

        .total,
        .subtotal,
        .coupon-discount {
            text-align: right;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .page-break {
            page-break-before: always;
        }

        ul {
            margin: 10px 10;
        }

        ul li {
            margin-bottom: 8px;
        }

        .summary-table {
            width: 40%;
            float: right;
        }

        .summary-table td {
            border: none;
            text-align: right;
            padding: 5px 10px;
        }

        .page-break {
            page-break-inside: always;
        }

    </style>
</head>

<body>

    @php
    $emp = App\Models\Admin::find($payout->employee_id);
    $slip = json_decode($payout->slip_data, true);
    $normalPay = (float) preg_replace('/[^0-9.]/', '', $slip['normal_pay']);
    $extraPay = (float) preg_replace('/[^0-9.]/', '', $slip['extra_pay']);
    $totalsalary = $normalPay + $extraPay;
    @endphp
    <div class="header">
        <h1 style="margin-bottom: 0px">AMIT BOOK DEPOT </h1>
        <p>+91 9216499664</p>
    </div>
    <div class="details" style="display: flex;">
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
                <input type="text" class="form-control" value="{{ date('F', mktime(0, 0, 0, $payout->month, 1)) }}"
                    readonly>
            </div>
            <div class="col-lg-2 form-group">
                <label for="year">For Year</label>
                <input type="text" class="form-control" value="{{ $payout->year }}" readonly>
            </div>
            <div class="col-lg-3 form-group">
                <label for="amount">Total Salary Amount INR</label>
                <input type="text" class="form-control" value="{{ $totalsalary }}" readonly>
            </div>
            <div class="col-lg-3 form-group">
                <label for="amount">Total Salary Deductions INR</label>
                <input type="text" class="form-control" value="{{ '₹' . $advancePayTran }}" readonly>
            </div>
            <div class="col-lg-2 form-group">
                <label for="amount">Paid salary </label>
                <input type="text" class="form-control" value="{{ $payout->amount }}" readonly>
            </div>
            <div class="col-lg-2 form-group">
                <label for="staff_id">Staff Name (Creator)</label>
                @php
                $staff = App\Models\Admin::find($payout->staff_id);
                @endphp
                <input type="text" class="form-control" value="{{ $staff->name . ' - ' . $staff->role }}" readonly>
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
                                <td colspan="2"><strong>Payable Amount for Extra Hours:</strong></td>
                                <td id="extra_pay">{{ $slip['extra_pay'] }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h2><strong>Total Payable Salary:</strong></h2>
                                </td>
                                <td class="text-left text-danger">
                                    <h2><strong id="total_payable">{{ $totalsalary }}</strong></h2>
                                </td>
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
        </div>
    </div>
    <div class="section">
        <h4>This is a computer-generated invoice and does not require any stamp or signature.</h4>
        <div class="header page-break">
            <h1 style="margin-bottom: 0px">AMIT BOOK DEPOT </h1>
            <p>+91 9216499664</p>
        </div>
    </div>
</body>

</html>
