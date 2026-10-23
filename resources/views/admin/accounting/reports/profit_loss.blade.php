@extends('admin.layouts.master')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <h3 class="fw-bold mb-1"><i class="fas fa-chart-line text-success me-2"></i>Profit & Loss Statement (Income Statement)</h3>
            <p class="text-muted small mb-0">Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
        </div>
        <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print P&L
        </button>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.reports.profit_loss') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Update Statement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- P&L Statement Document -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <!-- 1. Revenue Section -->
            <h5 class="fw-bold text-success border-bottom pb-2 mb-3">1. REVENUE / INCOME</h5>
            <table class="table table-sm table-borderless mb-4">
                <tbody>
                    @forelse($report['revenue_rows'] as $r)
                        <tr>
                            <td style="width: 15%;" class="font-monospace text-muted">{{ $r['account']->account_code }}</td>
                            <td>{{ $r['account']->name }}</td>
                            <td class="text-end fw-bold" style="width: 25%;">₹{{ number_format($r['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-muted">No operating revenue recorded in this period.</td>
                        </tr>
                    @endforelse
                    <tr class="border-top border-dark fw-bold fs-6 bg-light">
                        <td colspan="2" class="text-uppercase">Total Revenue (A):</td>
                        <td class="text-end text-success">₹{{ number_format($report['total_revenue'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- 2. Expenses Section -->
            <h5 class="fw-bold text-danger border-bottom pb-2 mb-3">2. OPERATING & ADMINISTRATIVE EXPENSES</h5>
            <table class="table table-sm table-borderless mb-4">
                <tbody>
                    @forelse($report['expense_rows'] as $e)
                        <tr>
                            <td style="width: 15%;" class="font-monospace text-muted">{{ $e['account']->account_code }}</td>
                            <td>{{ $e['account']->name }}</td>
                            <td class="text-end fw-bold" style="width: 25%;">₹{{ number_format($e['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-muted">No operating expenses recorded in this period.</td>
                        </tr>
                    @endforelse
                    <tr class="border-top border-dark fw-bold fs-6 bg-light">
                        <td colspan="2" class="text-uppercase">Total Expenses (B):</td>
                        <td class="text-end text-danger">₹{{ number_format($report['total_expenses'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- 3. Operating Profit -->
            <div class="p-3 bg-light rounded-3 mb-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">OPERATING PROFIT / EBITDA (A - B):</h5>
                <h4 class="fw-bold mb-0 {{ $report['operating_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ₹{{ number_format($report['operating_profit'], 2) }}
                </h4>
            </div>

            <!-- 4. Taxes Section -->
            @if(count($report['tax_rows']) > 0)
                <h5 class="fw-bold text-secondary border-bottom pb-2 mb-3">3. DIRECT TAXES</h5>
                <table class="table table-sm table-borderless mb-4">
                    <tbody>
                        @foreach($report['tax_rows'] as $t)
                            <tr>
                                <td style="width: 15%;" class="font-monospace text-muted">{{ $t['account']->account_code }}</td>
                                <td>{{ $t['account']->name }}</td>
                                <td class="text-end fw-bold" style="width: 25%;">₹{{ number_format($t['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-top border-dark fw-bold fs-6 bg-light">
                            <td colspan="2" class="text-uppercase">Total Direct Taxes (C):</td>
                            <td class="text-end text-dark">₹{{ number_format($report['total_taxes'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            <!-- 5. Net Profit / Loss -->
            <div class="p-4 {{ $report['net_profit'] >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }} rounded-3 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0">NET PROFIT / (LOSS) FOR THE PERIOD:</h4>
                    <small class="text-white-50">Transfers directly into Balance Sheet Retained Earnings</small>
                </div>
                <h2 class="fw-bold mb-0">
                    ₹{{ number_format($report['net_profit'], 2) }}
                </h2>
            </div>
        </div>
    </div>
</div>
@endsection
