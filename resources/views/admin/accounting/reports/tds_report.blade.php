@extends('admin.layouts.master')

@section('title', 'TDS Tax Summary Register')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <h3 class="fw-bold mb-1"><i class="fas fa-percentage text-info me-2"></i>TDS Summary & Tax Register</h3>
            <p class="text-muted small mb-0">Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
        </div>
        <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Register
        </button>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.reports.tds_report') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Update TDS Register</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-danger">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">TDS Payable (Withheld on Vendor Payments)</span>
                    <h3 class="fw-bold text-danger mt-2">₹{{ number_format($report['tds_payable_total'], 2) }}</h3>
                    <p class="small text-muted mb-0">Form 26Q / Challan 281 remittance obligation to Government.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-success">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">TDS Receivable (Deducted by Clients)</span>
                    <h3 class="fw-bold text-success mt-2">₹{{ number_format($report['tds_receivable_total'], 2) }}</h3>
                    <p class="small text-muted mb-0">Eligible for tax credit / refund against Annual Income Tax Return (ITR).</p>
                </div>
            </div>
        </div>
    </div>

    <!-- TDS Withheld on Bills Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">TDS Withheld on Vendor Bills (Form 26Q)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Bill #</th>
                            <th>Vendor</th>
                            <th>PAN</th>
                            <th>Section</th>
                            <th class="text-end">Taxable Subtotal</th>
                            <th class="text-center">Rate</th>
                            <th class="text-end">TDS Withheld (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['bills_with_tds'] as $bill)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $bill->bill_number }}</td>
                                <td>{{ $bill->party->name ?? 'Vendor' }}</td>
                                <td class="font-monospace small">{{ $bill->party->pan ?? '—' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $bill->tdsRate->section ?? 'TDS' }}</span></td>
                                <td class="text-end">₹{{ number_format($bill->subtotal, 2) }}</td>
                                <td class="text-center">{{ $bill->tds_rate }}%</td>
                                <td class="text-end fw-bold text-danger">₹{{ number_format($bill->tds_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No TDS withholding transactions in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
