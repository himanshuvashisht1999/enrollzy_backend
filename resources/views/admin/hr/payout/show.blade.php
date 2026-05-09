@extends('admin.layouts.master')

@section('title', 'View Payout Slip')

@section('content')
@php
    $emp = App\Models\Admin::find($payout->employee_id);
    $slip = json_decode($payout->slip_data, true);
    $normalPay = (float) preg_replace('/[^0-9.]/', '', $slip['normal_pay'] ?? 0);
    $extraPay = (float) preg_replace('/[^0-9.]/', '', $slip['extra_pay'] ?? 0);
    $totalsalary = $normalPay + $extraPay;
@endphp

<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Payout Slip: {{ date('F Y', mktime(0, 0, 0, $payout->month, 1, $payout->year)) }}</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.hr.payout.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border">Back to List</a>
                <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-print me-1"></i> Print Slip</button>
            </div>
        </div>

        <div class="card-body">
            {{-- Header Info Section --}}
            <div class="row g-3 mb-4 p-3 bg-light rounded-4 ms-0 me-0">
                <div class="col-md-3">
                    <div class="small text-muted">Payslip ID</div>
                    <div class="fw-bold">{{ $payout->payslip_id }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Employee Name</div>
                    <div class="fw-bold">{{ $payout->name }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Designation</div>
                    <div class="fw-bold">{{ $emp->designation->name ?? 'N/A' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Department</div>
                    <div class="fw-bold">{{ $emp->department->name ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Financial Summary --}}
                <div class="col-lg-6">
                    <h6 class="fw-bold text-muted mb-3"><i class="fas fa-calculator me-2"></i> Salary Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered rounded-3 overflow-hidden">
                            <tbody>
                                <tr><td>Total Attendance</td><td class="text-end fw-bold">{{ $slip['attendance'] ?? 0 }} days</td></tr>
                                <tr><td>Expected Days</td><td class="text-end">{{ $slip['expected_working_day'] ?? 0 }} days</td></tr>
                                <tr><td>Worked Hours</td><td class="text-end">{{ $slip['working_hours'] ?? 0 }} hrs</td></tr>
                                <tr><td>Base Payment</td><td class="text-end">{{ env('CURRENCY', '₹') }} {{ number_format($normalPay, 2) }}</td></tr>
                                <tr><td>Extra (OT) Pay</td><td class="text-end">{{ env('CURRENCY', '₹') }} {{ number_format($extraPay, 2) }}</td></tr>
                                <tr class="table-success">
                                    <td class="fw-bold">Gross Salary</td>
                                    <td class="text-end fw-bold">{{ env('CURRENCY', '₹') }} {{ number_format($totalsalary, 2) }}</td>
                                </tr>
                                <tr><td>Bonus Added</td><td class="text-end text-success">+ {{ env('CURRENCY', '₹') }} {{ number_format($employeeBonusTotal, 2) }}</td></tr>
                                <tr><td>Holiday Compensation</td><td class="text-end text-info">+ {{ env('CURRENCY', '₹') }} {{ number_format($declaredHolidayTotal, 2) }}</td></tr>
                                <tr><td>Advance Deductions</td><td class="text-end text-danger">- {{ env('CURRENCY', '₹') }} {{ number_format($advancePayTran, 2) }}</td></tr>
                                <tr class="bg-primary text-white">
                                    <td class="fw-bold py-3 fs-5">Net Payable</td>
                                    <td class="text-end fw-bold py-3 fs-5">{{ env('CURRENCY', '₹') }} {{ number_format($payout->amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Transaction Details --}}
                <div class="col-lg-6">
                    <h6 class="fw-bold text-muted mb-3"><i class="fas fa-file-invoice-dollar me-2"></i> Payout Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered rounded-3 overflow-hidden">
                            <tbody>
                                <tr><td>Paid From</td><td class="text-end fw-bold">{{ $txn->debit_account ?? 'N/A' }}</td></tr>
                                <tr><td>Payment Method</td><td class="text-end">{{ ucfirst($txn->payment_method ?? 'N/A') }}</td></tr>
                                <tr><td>Ref / Txn ID</td><td class="text-end font-monospace">{{ $txn->txn_id ?? 'N/A' }}</td></tr>
                                <tr><td>Bank Charges</td><td class="text-end">{{ env('CURRENCY', '₹') }} {{ number_format($txn->bank_charges ?? 0, 2) }}</td></tr>
                                <tr><td>Clearance Date</td><td class="text-end">{{ $txn->clearance_date ?? 'N/A' }}</td></tr>
                                <tr><td>Processed By</td><td class="text-end">
                                    @php $processor = App\Models\Admin::find($payout->staff_id); @endphp
                                    {{ $processor->name ?? 'System' }}
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                    @if($payout->comment)
                    <div class="alert alert-light border rounded-4 mt-3">
                        <small class="fw-bold text-muted d-block mb-1">Memo/Notes:</small>
                        {{ $payout->comment }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="card-footer bg-light py-3 border-0 text-center text-muted small">
            This is a computer-generated document and does not require a physical signature.
            <br>Generated on {{ date('d M, Y \a\t h:i A', strtotime($payout->created_at)) }}
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .container-fluid, .container-fluid * { visibility: visible; }
    .container-fluid { position: absolute; left: 0; top: 0; width: 100%; }
    .btn-light, .btn-primary { display: none !important; }
}
</style>
@endsection
