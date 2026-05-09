@extends('admin.layouts.master')

@section('title', 'Payroll Calculation')

@section('content')
<div class="container-fluid">
    {{-- Calculation Trigger Card --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Monthly Salary Calculator</h6>
        </div>
        <div class="card-body">
            <form id="calculateSalaryForm" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Staff Member</label>
                    <select name="staff_id" id="employeeId" class="form-select rounded-3">
                        <option value="">Select Staff</option>
                        @foreach ($employees as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->name }} - {{ $item->designation->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Year</label>
                    <select name="year" id="yearId" class="form-select rounded-3">
                        @foreach (range(date('Y') - 5, date('Y') + 1) as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Month</label>
                    <select name="month" id="monthId" class="form-select rounded-3">
                        <option value="">Select Month</option>
                        @foreach (range(1, 12) as $m)
                            <option {{ $m == date('n') ? 'selected' : '' }} value="{{ $m }}">
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <label class="form-label d-none d-md-block opacity-0">Action</label>
                    <button type="button" class="btn btn-primary rounded-pill sbmtBtn">Calculate</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Result Section --}}
    <div id="resultCard" class="d-none">
        <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">Calculation Breakdown for <span id="monthNameHeader"></span></h6>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#bonusModal">
                    <i class="fas fa-gift me-1"></i> Add Bonus
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Parameter</th>
                                <th>Value / Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-primary-soft"><td colspan="2" class="ps-4 fw-bold">Employee Profile</td></tr>
                            <tr><td class="ps-4 text-muted small">Name</td><td id="res_name" class="fw-bold"></td></tr>
                            <tr><td class="ps-4 text-muted small">Designation</td><td id="res_designation"></td></tr>
                            <tr><td class="ps-4 text-muted small">Department</td><td id="res_department"></td></tr>
                            
                            <tr class="table-primary-soft"><td colspan="2" class="ps-4 fw-bold">Attendance Statistics</td></tr>
                            <tr><td class="ps-4 text-muted small">Working Days / Expected</td><td><span id="res_attendance" class="fw-bold text-success"></span> / <span id="res_expected_working_day"></span> days</td></tr>
                            <tr><td class="ps-4 text-muted small">Actual Hours / Expected</td><td><span id="res_working_hours" class="fw-bold"></span> / <span id="res_expected_hours"></span> hours</td></tr>
                            <tr><td class="ps-4 text-muted small">Extra Hours (OT)</td><td id="res_extra_hours" class="text-success fw-bold"></td></tr>
                            <tr><td class="ps-4 text-muted small">Attendance Log</td><td id="res_att_url"></td></tr>
                            
                            <tr class="table-primary-soft"><td colspan="2" class="ps-4 fw-bold">Financial Analysis</td></tr>
                            <tr><td class="ps-4 text-muted small">Salary Basis</td><td id="res_salary_basis" class="fw-bold text-primary"></td></tr>
                            <tr><td class="ps-4 text-muted small">Base Pay (for attendance)</td><td id="res_normal_pay"></td></tr>
                            <tr><td class="ps-4 text-muted small">Extra Pay (OT)</td><td id="res_extra_pay" class="text-success"></td></tr>
                            <tr><td class="ps-4 text-muted small">Bonus Adjustment</td><td id="res_bonus_paid" class="text-success"></td></tr>
                            <tr><td class="ps-4 text-muted small">Holiday Compensation</td><td id="res_holiday_pay" class="text-info"></td></tr>
                            <tr><td class="ps-4 text-muted small">Penalty Deductions</td><td id="res_penalty_amount" class="text-danger"></td></tr>
                            <tr><td class="ps-4 text-muted small">Advance Settlement</td><td id="res_advance_settled" class="text-danger"></td></tr>
                            
                            <tr class="bg-primary text-white">
                                <td class="ps-4 py-3 fw-bold fs-5">Final Net Payable</td>
                                <td id="res_total_payable" class="py-3 fw-bold fs-5"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 text-center" id="payFooter">
                <button type="button" class="btn btn-primary rounded-pill px-5" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    Process Payment & Generate Slip
                </button>
            </div>
            <div id="payoutData" class="card-footer bg-light-soft py-3 text-center d-none">
                {{-- Existing slip link will be injected here --}}
            </div>
        </div>
    </div>
</div>

{{-- Bonus Modal --}}
<div class="modal fade" id="bonusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.hr.advance.bonus.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Add One-time Bonus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="staff_id" id="bonus_staff_id">
                    <input type="hidden" name="initiation_date" id="bonus_init_date">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bonus Amount ({{ env('CURRENCY', '₹') }})</label>
                        <input type="number" name="amount" class="form-control rounded-3" required id="bonus_amount_val">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason / Notes</label>
                        <textarea name="comment" class="form-control rounded-3" rows="3" required id="bonus_comment_val"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="bonus_submit_btn">Save Bonus</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="paymentModalForm">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Execute Payroll Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light-soft">
                    <input type="hidden" name="employee_id" id="pay_employee_id">
                    <input type="hidden" name="advancepayIds" id="pay_advance_ids">
                    <input type="hidden" name="amountPayableTotal" id="pay_total_amount_raw">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Debit Target Account</label>
                            <select name="debit_account" id="debit_account" class="form-select rounded-3">
                                <option value="cash">Company Cash Balance</option>
                                @foreach($banks as $b) <option value="{{ $b->name }}">{{ $b->name }}</option> @endforeach
                                <option value="advance_pay" id="advance_pay_option">Settle against Advance Pay</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Payment Channel</label>
                            <select name="payment_method" class="form-select rounded-3">
                                <option value="cash">Physical Cash</option>
                                <option value="bank_transfer">Bank Transfer / IMPS</option>
                                <option value="upi">UPI (GPay/PhonePe)</option>
                                <option value="cheque">Company Cheque</option>
                                <option value="settlement" class="settlement_opt">Internal Settlement</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Net Salary Payable</label>
                            <input type="text" id="pay_display_amount" class="form-control rounded-3 bg-white" readonly name="amount">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Settlement Amount</label>
                            <input type="number" step="0.01" id="pay_adj_amount" class="form-control rounded-3" readonly name="paid_amount" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Bank Charges</label>
                            <input type="number" step="0.01" name="bank_charges" class="form-control rounded-3" value="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Reference / Txn ID</label>
                            <input type="text" name="txn_id" class="form-control rounded-3" placeholder="Reference code">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Initiation Date</label>
                            <input type="date" name="initiation_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Clearance Date</label>
                            <input type="date" name="clearance_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Payout Notes</label>
                            <textarea name="comment" class="form-control rounded-3" rows="2" placeholder="Visible on payslip..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="submitPaymentBtn" class="btn btn-success rounded-pill px-5 fw-bold">Confirm & Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Calculation Trigger
        $('.sbmtBtn').click(function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.hr.payroll.calculate') }}",
                data: $('#calculateSalaryForm').serialize(),
                success: function(resp) {
                    btn.prop('disabled', false).text('Calculate');
                    if (resp.status == 1) {
                        $('#resultCard').removeClass('d-none');
                        const d = resp.data;
                        
                        // Populate results
                        $('#monthNameHeader').text(d.monthName + ' ' + d.year);
                        $('#res_name').text(d.name);
                        $('#res_designation').text(d.designation);
                        $('#res_department').text(d.department);
                        $('#res_attendance').text(d.attendance);
                        $('#res_expected_working_day').text(d.expected_working_day);
                        $('#res_working_hours').text(d.working_hours);
                        $('#res_expected_hours').text(d.expected_hours);
                        $('#res_extra_hours').text(d.extra_hours);
                        $('#res_salary_basis').text(d.salary_basis);
                        $('#res_normal_pay').text(d.normal_pay);
                        $('#res_extra_pay').text(d.extra_pay || '—');
                        $('#res_bonus_paid').text(d.bonus_amount);
                        $('#res_holiday_pay').text(d.Holidays_amount);
                        $('#res_penalty_amount').text(d.penalty_amount);
                        $('#res_advance_settled').text(d.total_advance_settelled);
                        $('#res_total_payable').text(d.total_payable_with_penalty);
                        
                        var attUrl = "{{ route('admin.hr.attendance.index') }}?staff_id=" + d.employee_id + "&year=" + d.year + "&month=" + d.month;
                        $('#res_att_url').html('<a href="' + attUrl + '" target="_blank" class="btn btn-link btn-sm p-0">View Grid</a>');

                        // Data for Modals
                        $('#bonus_staff_id, #pay_employee_id').val(d.employee_id);
                        $('#bonus_init_date').val(d.year + '-' + d.month.toString().padStart(2, '0') + '-01');
                        $('#pay_total_amount_raw').val(d.total_payable_with_penalty);
                        $('#pay_display_amount').val(d.total_payable_with_penalty);
                        $('#advance_pay_option').text('Settle against Advance (Limit: ' + d.total_advance_settelled + ')');

                        if(d.isAlreadyPaid == 'yes') {
                            $('#payFooter').addClass('d-none');
                            $('#payoutData').removeClass('d-none').html(d.payoutData);
                        } else {
                            $('#payFooter').removeClass('d-none');
                            $('#payoutData').addClass('d-none');
                        }
                    } else {
                        Swal.fire('Error', resp.message, 'error');
                    }
                }
            });
        });

        // Advance Pay Logic
        $('#debit_account').change(function() {
            var acc = $(this).val();
            var total = $('#pay_total_amount_raw').val();
            var empId = $('#pay_employee_id').val();
            
            if (acc === 'advance_pay') {
                $.ajax({
                    url: "{{ route('admin.hr.advance.get-amount') }}",
                    type: 'POST',
                    data: {
                        employeeId: empId,
                        amountPayableTotaloutstanding: total,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(resp) {
                        if (resp.amount) {
                            $('#pay_adj_amount').val(resp.amount);
                            $('#pay_advance_ids').val(resp.entry_ids);
                            
                            var raw = parseFloat(total.replace(/[^\d.]/g, ''));
                            var net = raw - parseFloat(resp.amount);
                            $('#pay_display_amount').val(net.toFixed(2));
                            $('.settlement_opt').prop('selected', true);
                        }
                    }
                });
            } else {
                $('#pay_adj_amount').val(0);
                $('#pay_display_amount').val(total);
            }
        });

        // Submit Payment
        $('#submitPaymentBtn').click(function() {
            var btn = $(this);
            btn.prop('disabled', true).html('Processing...');
            
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.hr.payroll.make-payment') }}",
                data: $('#paymentModalForm').serialize(),
                success: function(resp) {
                    if (resp.status == 1) {
                        Swal.fire('Success', resp.message, 'success').then(() => window.location.reload());
                    } else {
                        btn.prop('disabled', false).text('Confirm & Pay');
                        Swal.fire('Failed', resp.message, 'error');
                    }
                }
            });
        });
    });
</script>
@endpush
