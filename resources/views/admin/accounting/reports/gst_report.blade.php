@extends('admin.layouts.master')

@section('title', 'GST Summary & Tax Register')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <h3 class="fw-bold mb-1"><i class="fas fa-receipt text-dark me-2"></i>GST Summary & Tax Register</h3>
            <p class="text-muted small mb-0">Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
        </div>
        <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Register
        </button>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.reports.gst_report') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Update Tax Register</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3 Summary Pillar Cards -->
    <div class="row g-3 mb-4">
        <!-- Output GST -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-danger">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">Output GST (Sales Liability)</span>
                    <h3 class="fw-bold text-danger mt-2">₹{{ number_format($report['output_gst']['total'], 2) }}</h3>
                    <div class="small text-muted mt-2">
                        <div>CGST: ₹{{ number_format($report['output_gst']['cgst'], 2) }} | SGST: ₹{{ number_format($report['output_gst']['sgst'], 2) }}</div>
                        <div>IGST: ₹{{ number_format($report['output_gst']['igst'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input GST ITC -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-success">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">Input GST Credit (ITC Claim)</span>
                    <h3 class="fw-bold text-success mt-2">₹{{ number_format($report['input_gst']['total'], 2) }}</h3>
                    <div class="small text-muted mt-2">
                        <div>CGST: ₹{{ number_format($report['input_gst']['cgst'], 2) }} | SGST: ₹{{ number_format($report['input_gst']['sgst'], 2) }}</div>
                        <div>IGST: ₹{{ number_format($report['input_gst']['igst'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Payable -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-primary">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">Net GST Payable / (Credit Carryforward)</span>
                    <h3 class="fw-bold {{ $report['net_gst_liability']['total'] >= 0 ? 'text-primary' : 'text-success' }} mt-2">
                        ₹{{ number_format($report['net_gst_liability']['total'], 2) }}
                    </h3>
                    <p class="small text-muted mb-0">Net Cash Payable after ITC adjustment</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Output GST Details Table (GSTR-1) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Output GST Sales Invoices (GSTR-1)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>GSTIN</th>
                            <th class="text-end">Taxable Value</th>
                            <th class="text-end">CGST</th>
                            <th class="text-end">SGST</th>
                            <th class="text-end">IGST</th>
                            <th class="text-end">Total Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['invoices'] as $inv)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $inv->invoice_number }}</td>
                                <td>{{ $inv->party->name ?? 'Customer' }}</td>
                                <td class="font-monospace small">{{ $inv->party->gstin ?? 'Unregistered' }}</td>
                                <td class="text-end">₹{{ number_format($inv->subtotal, 2) }}</td>
                                <td class="text-end">₹{{ number_format($inv->cgst_amount, 2) }}</td>
                                <td class="text-end">₹{{ number_format($inv->sgst_amount, 2) }}</td>
                                <td class="text-end">₹{{ number_format($inv->igst_amount, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($inv->total_tax, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">No posted sales invoices in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
