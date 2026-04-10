@extends('layouts.app')
@section('push_css')
@endsection
@section('content')

    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Calculate Month Salary</h6>
            </div>
            <div class="card-body">
                <form class="row" id="calculateSalaryForm">
                    @csrf
                    <div class="col-md-3 form-group">
                        <label for="employeeId">Select Staff</label>
                        <select name="staff_id" id="employeeId" class="form-control">
                        <option value="">Select Staff</option>
                            @foreach ($employees as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->name }} - {{ $item->designation->name ?? 'Not Assigned' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="yearId">Select Year</label>
                        <select name="year" id="yearId" class="form-control">
                            @foreach (range(date('Y') - 15, date('Y') + 1) as $year)
                                <!-- Adjust the range as needed -->
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="monthId">Select Month</label>
                        <select name="month" id="monthId" class="form-control">
                        <option value="">Select Month</option>
                            @foreach (range(1, 12) as $month)
                                <option {{ $month == date('n') ? 'selected' : '' }} value="{{ $month }}">
                                    {{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group text-center m-0" style="align-self: center">
                        <a href="javascript:;" class="btn btn-primary btn-sm sbmtBtn"> View </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="card shadow mb-4 d-none" id="resultCard">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Result</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalLong">
                Add Bonus
            </button>
            <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Add Bonus</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="post" action="{{ route('admin.advance.storeBonus') }}">
                            @csrf
                            <div class="modal-body">
                                
                                <div class="form-group col-sm-12">
                                    <label for="amount">Paying Amount</label>
                                    <input type="number" required class="form-control" id="amount_bonus" name="amount"
                                        value="{{ old('amount') }}" />
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="comment">Notes / Comments:</label>
                                    <textarea class="form-control" id="comment_bonus" name="comment"
                                        maxlength="400" required>{{ old('comment') }}</textarea>
                                </div>
                                    <input type="hidden" required class="form-control" id="modelstaffid"
                                        name="staff_id" value="" />
                                    <input type="hidden" required class="form-control" id="initiation_date_bonus"
                                        name="initiation_date" value="" />

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="form_submit_bonus">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </div>
            <div class="card-body">
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
                                <td colspan="2"><strong>Staff Name:</strong></td>
                                <td id="name"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Email:</strong></td>
                                <td id="email"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Designation:</strong></td>
                                <td id="designation"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Department:</strong></td>
                                <td id="department"></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h5>Salary Calculation for month: <b id="monthName"></b></h5>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Total Days in Month:</strong></td>
                                <td id="days_in_month"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Expected working days:</strong></td>
                                <td id="expected_working_day"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Total Attendance:</strong></td>
                                <td id="attendance"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>View Attandance</strong></td>
                                <td id="attandanceurl"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Daily Shift Hours:</strong></td>
                                <td id="shift_hours"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Expected Monthly Shift hours:</strong></td>
                                <td id="expected_hours"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Total Worked Hours:</strong></td>
                                <td id="working_hours"></td>
                            </tr>

                            <tr>
                                <td colspan="2"><strong>Extra Hours Worked:</strong></td>
                                <td id="extra_hours"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Expected Lunch Hours:</strong></td>
                                <td id="expected_lunch_time"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Taken Lunch Hours:</strong></td>
                                <td id="taken_lunch_time"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Taken Personal Lunch Hours:</strong></td>
                                <td id="taken_personal_lunch_time"></td>
                            </tr>
                            <tr>
                                <td colspan="2" id="hourlySalary"></td>
                                <td id="salary_hourly"></td>
                            </tr>
                            <tr>
                                <td colspan="2" id="scheduledDays"></td>
                                <td id="normal_pay"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Payable Amount for Extra Hours:</strong></td>
                                <td id="extra_pay"></td>
                            </tr>

                            <tr>
                                <td colspan="2"><strong>Total Bonus Paid:</strong></td>
                                <td id="bonus_paid"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Declared Dolidays:</strong></td>
                                <td id="declared_holidays"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Total Advance settled:</strong></td>
                                <td id="advance_settled"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Penalty Amount:</strong></td>
                                <td id="penalty_amount"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Payable Amount without panelty:</strong></td>
                                <td id="without_penalty"></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h2><strong>Total final Payable Salary:</strong></h2>
                                </td>
                                <td class="text-left text-danger">
                                    <h2><strong id="total_payable"></strong></h2>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-none" id="payFooter">
                <a href="javascript:;" class="btn btn-primary btn-block" data-toggle="modal"
                    data-target="#generatePayrollModal">
                    Generate Payroll </a>
            </div>
            <div class="card-footer" id="payoutData">

            </div>
            <div class="modal fade" id="generatePayrollModal" tabindex="-1" role="dialog"
                aria-labelledby="generatePayrollModal" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Generat Payment Slip</h4>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="paymentModalForm" name="paymentModalForm">
                                @csrf
                                <input type="hidden" class="form-control" name="employee_id" id="employee_id">
                                <input type="hidden" class="form-control" name="advancepayIds" id="advancepayIds">

                                <input type="hidden" class="form-control" id="amountPayableTotal" name="amountPayableTotal">
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label for="debit_account">Debit Account </label>
                                        <select id="debit_account" class="form-control" name="debit_account">
                                            <option selected disabled>Select Debit Account</option>
                                            <option value='cash'>Cash</option>
                                            @foreach($banks as $bank)
                                            <option value='{{$bank->name}}'>{{$bank->name}}</option>
                                            @endforeach
                                            <option value='advance_pay'>Advance Pay - </option> 
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4" style="display: none;" id="showHidePaidAmount">
                                        <label for="amount">Deducted Amount</label>
                                        <input type="text" class="form-control" id="amount" name="paid_amount"  />
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="payment_method">Payment Method </label>
                                        <select id="payment_method" class="form-control" name="payment_method">
                                            <option selected disabled>Select Payment Method</option>
                                            <option value='cash'>Cash</option>
                                            <option value='gpay'>G Pay </option>
                                            <option value='paytm'>Paytm </option>
                                            <option value='phone_pay'>Phone Pay </option>
                                            <option value='net_banking'>Net Banking </option>
                                            <option value='cheque'>Cheque</option>
                                            <option value='advance_payment' class="advanceppay">Advance Payment</option> 
                                        </select>
                                    </div>
                                        <input type="hidden" class="form-control" id="amountPayablenaman" name="amount"
                                            readonly />
                                    <div class="form-group col-sm-4">
                                        <label for="amount">Salary Amount</label>
                                        <input type="text" class="form-control" id="amountPayable" name="amount"
                                            readonly />
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label for="bank_charges">Bank Charges</label>
                                        <input type="text" required class="form-control" id="bank_charges"
                                            name="bank_charges" value="0" />
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label for="txn_id">Cheque / Txn No:</label>
                                        <input type="text" required class="form-control" id="txn_id"
                                            name="txn_id" />
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="clearance_date">Clearance Date:</label>
                                        <input type="date" required class="form-control" id="clearance_date"
                                            name="clearance_date" value="{{ date('Y-m-d') }}" />
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="initiation_date">Initiation Date:</label>
                                        <input type="date" required class="form-control" id="initiation_date"
                                            name="initiation_date" value="{{ date('Y-m-d') }}" />
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="comment">Notes / Comments:</label>
                                    <textarea class="form-control" id="comment" name="comment" maxlength="400"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary" id="submitPaymentBtn" href="javascript:;"> Make Payment </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script>

    $(document).ready(function() {
        $('#debit_account').change(function() {
            var selectedAccount = $(this).val(); // Get the selected value
            var currentAmountPayable = ($('#amountPayable').val());
            var currentAmountPayable = ($('#amountPayable').val());
            var amountPayableTotal = ($('#amountPayableTotal').val());
            var employeeId = ($('#employee_id').val());

            var paymentMethodSelect = document.getElementById('payment_method');
            var advancePaymentOption = paymentMethodSelect.querySelector('.advanceppay');

            var cleanedAmount = currentAmountPayable.replace(/[₹,/-]/g, '').trim();

            // Convert to a float or integer for calculations
            var numericAmountPayable = parseFloat(cleanedAmount);

            // Check if "Advance Pay" is selected
            if (selectedAccount === 'advance_pay') {
                
                $('#txn_id').val('Advance Pay');
                // Send an AJAX request to fetch the amount
                $.ajax({
                    url: "{{ route('admin.get.advance.pay.amount') }}", // The route you defined earlier
                    type: 'POST',
                    data: {
                        employeeId: employeeId,
                        amountPayableTotaloutstanding: amountPayableTotal,
                        _token: "{{ csrf_token() }}"  // CSRF token for security
                    },
                    success: function(response) {
                        // Update the amount input field with the returned amount
                        if (response.amount) {

                            advancePaymentOption.selected = true;
                            $('#showHidePaidAmount').show();
                            $('#amount').val(response.amount);
                            $('#advancepayIds').val(response.entry_ids);
                            var newAmountPayable = numericAmountPayable - response.amount;

                            // Format the number to a currency format with commas
                            var formattedAmount = new Intl.NumberFormat('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(newAmountPayable);

                            // Combine with ₹ and /-
                            var formattedString = '₹ ' + formattedAmount + ' /-';

                            $('#amountPayable').val(formattedString);

                        } else {

                            advancePaymentOption.selected = false;
                            $('#showHidePaidAmount').hide();
                            $('#amount').val(''); // In case no amount is found
                        }
                    },
                    error: function() {
                        alert('An error occurred while fetching the amount.');
                    }
                });
            } else if (selectedAccount === 'cash') {
                $('#txn_id').val('Cash');
                advancePaymentOption.selected = true;
                // Handle the case when "cash" is selected
                advancePaymentOption.selected = false;
                $('#showHidePaidAmount').hide();
                $('#amount').val(''); // Clear the amount input if cash is selected
                $('#amountPayable').val(amountPayableTotal); // Show the total payable amount without adjustment for advance pay

            } else {

                $('#showHidePaidAmount').hide();
                $('#amount').val(''); // Clear the amount input if any other option is selected
                $('#amountPayable').val(amountPayableTotal); // Clear the amount input if any other option is selected
                
            }
        });
    });


    $(document).ready(function() {
    // Initialize the amountPayable (if needed)


    // When paid_amount is changed, subtract that from amountPayable
    $('#amount').on('input', function() {
        var amountPayable = ($('#amountPayablenaman').val());
            var cleanedAmountss = amountPayable.replace(/[₹,/-]/g, '').trim();
            var numericAmountPayabless = parseFloat(cleanedAmountss);

        var paidAmount = parseFloat($(this).val()) || 0; // Get the paid amount

        // alert(numericAmountPayabless);
        var remainingAmount = numericAmountPayabless - paidAmount; // Subtract paid amount from payable amount

        // Update the amountPayable field with the remaining amount
        $('#amountPayable').val(remainingAmount.toFixed(2));
    });
});

        $('.sbmtBtn').click(function() {
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.payroll.calculate_salary') }}",
                data: $('#calculateSalaryForm').serializeArray(),
                success: function(response) {
                    if (response.status == 1) {
                        $('#resultCard').removeClass('d-none');
                        const data = response.data;
                        const iap = response.data.isAlreadyPaid;    
                        var attendanceUrl = "{{ url('admin/attendance/index') }}?staff_id=" + data.employee_id + "&year=" + data.year + "&month=" + data.month;



                        $('#modelstaffid').val(data.employee_id);
                        if (data.employeeBonusTotals && data.employeeBonusTotals.debit) {
                            $('#amount_bonus').val(data.employeeBonusTotals.debit).prop('readonly', true);
                            $('#comment_bonus').val(data.employeeBonusTotals.comment).prop('readonly', true);
                            $('#form_submit_bonus').prop('disabled', true);
                        } else {
                            $('#amount_bonus').prop('readonly', false);
                            $('#comment_bonus').prop('readonly', false);
                            $('#form_submit_bonus').prop('disabled', false);
                        }
                        let yearss = data.year;
                        let monthss = data.month.toString().padStart(2, '0'); // ensures 2-digit format like "03"
                        let dayss = '05'; // the 5th day of the month

                        let fullDate = `${yearss}-${monthss}-${dayss}`;

                        $('#initiation_date_bonus').val(fullDate);
                        $('#modelyear').val(data.year);
                        $('#modelmonth').val(data.month);


                        $('#bonus_paid').text(data.bonus_amount);
                        $('#declared_holidays').text(data.Holidays_amount);
                        $('#total_declared_holidays').text(data.total_declared_holidays);

                        if (data.salary_type == 'monthly') {
                            $('#hourlySalary').html('<strong>Monthly Salary in INR:</strong>');
                            $('#scheduledDays').html('<strong>Payable Amount for Scheduled Days:</strong>');
                        } else {
                            $('#hourlySalary').html('<strong>Hourly Salary in INR:</strong>');
                            $('#scheduledDays').html('<strong>Payable Amount for Scheduled Hours:</strong>');
                        }


                        $('#name').text(data.name);
                        $('#employee_id').val(data.employee_id);
                        $('#email').text(data.email);
                        $('#designation').text(data.designation);
                        $('#department').text(data.department);
                        $('option[value="advance_pay"]').text('Advance Pay - ' + data.totalDebit);
                        $('#monthName').text(data.monthName);
                        $('#days_in_month').text(data.days_in_month);
                        $('#expected_working_day').text(data.expected_working_day);
                        $('#shift_hours').text(data.shift_hours);
                        $('#attendance').text(data.attendance);
                        $('#attandanceurl').html('<a href="' + attendanceUrl + '" target="_blank">View Attendance</a>');
                        $('#expected_hours').text(data.expected_hours);
                        $('#working_hours').text(data.working_hours);
                        $('#extra_hours').text(data.extra_hours);
                        $('#expected_lunch_time').text(data.expected_lunch_time);
                        $('#taken_lunch_time').text(data.taken_lunch_time);
                        $('#taken_personal_lunch_time').text(data.taken_personal_lunch_time);
                        $('#salary_hourly').text(data.salary_hourly);
                        $('#normal_pay').text(data.normal_pay);
                        $('#advance_settled').text(data.total_advance_settelled);
                        $('#extra_pay').text(data.extra_pay);
                        $('#penalty_amount').text(data.penalty_amount);
                        $('#without_penalty').text(data.total_payable_without_penalty);
                        $('#total_payable').text(data.total_payable_with_penalty);
                        $('#amountPayable').val(data.total_payable_with_penalty);
                        $('#amountPayablenaman').val(data.total_payable_with_penalty);
                        $('#amountPayableTotal').val(data.total_payable_with_penalty);
                        if (iap == 'yes') {
                            $('#payFooter').addClass('d-none');
                            $('#payoutData').removeClass('d-none');
                            $('#payoutData').html(data.payoutData);
                        } else {
                            $('#payFooter').removeClass('d-none');
                            $('#payoutData').addClass('d-none');
                        }
                    } else {
                        toastr["error"](response.message, "Error");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status === 404) {
                        toastr["error"](jqXHR.responseJSON.message, "Error");
                    } else {
                        toastr["error"]('An unexpected error occurred. Please try again later',
                            "Error");
                    }
                }
            });
        });
        $('#submitPaymentBtn').click(function() {
            var formData = $('#paymentModalForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.payroll.makeEmployeePayment') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success!");
                        window.location.reload();
                    } else {
                        toastr["error"](response.message, "Error");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status === 404) {
                        toastr["error"](jqXHR.responseJSON.message, "Error");
                    } else {
                        toastr["error"]('An unexpected error occurred. Please try again later',
                            "Error");
                    }
                }
            });

        })
    </script>
@endsection
