@extends('admin.layouts.master')

@section('title', 'Balance Sheet Statement')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <h3 class="fw-bold mb-1"><i class="fas fa-university text-warning me-2"></i>Balance Sheet Statement</h3>
            <p class="text-muted small mb-0">As of: {{ \Carbon\Carbon::parse($asOfDate)->format('d F Y') }}</p>
        </div>
        <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Balance Sheet
        </button>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.reports.balance_sheet') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Statement As Of Date</label>
                    <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate }}">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Update Balance Sheet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Balance Sheet Equation Check Banner -->
    <div class="alert {{ $report['is_balanced'] ? 'alert-success' : 'alert-danger' }} d-flex justify-content-between align-items-center mb-4">
        <div>
            <i class="fas {{ $report['is_balanced'] ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-2"></i>
            <strong>Accounting Equation Check:</strong> Assets (₹{{ number_format($report['total_assets'], 2) }}) = Liabilities & Equity (₹{{ number_format($report['total_liabilities_and_equity'], 2) }})
        </div>
        <span class="badge {{ $report['is_balanced'] ? 'bg-success' : 'bg-danger' }} fs-6">
            {{ $report['is_balanced'] ? 'BALANCED' : 'OUT OF BALANCE' }}
        </span>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: ASSETS -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-primary">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-primary">ASSETS (1000s)</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-sm table-borderless mb-4">
                        <tbody>
                            @forelse($report['asset_rows'] as $a)
                                <tr>
                                    <td style="width: 20%;" class="font-monospace text-muted">{{ $a['account']->account_code }}</td>
                                    <td>{{ $a['account']->name }}</td>
                                    <td class="text-end fw-bold" style="width: 30%;">₹{{ number_format($a['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No asset balances found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light p-3 border-top d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary text-uppercase">Total Assets:</h5>
                    <h4 class="fw-bold mb-0 text-primary">₹{{ number_format($report['total_assets'], 2) }}</h4>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: LIABILITIES & EQUITY -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-danger">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-danger">LIABILITIES & EQUITY (2000s & 3000s)</h5>
                </div>
                <div class="card-body p-4">
                    <!-- 1. Liabilities -->
                    <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2 mb-3">Liabilities</h6>
                    <table class="table table-sm table-borderless mb-4">
                        <tbody>
                            @forelse($report['liability_rows'] as $l)
                                <tr>
                                    <td style="width: 20%;" class="font-monospace text-muted">{{ $l['account']->account_code }}</td>
                                    <td>{{ $l['account']->name }}</td>
                                    <td class="text-end fw-bold" style="width: 30%;">₹{{ number_format($l['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No liabilities recorded.</td>
                                </tr>
                            @endforelse
                            <tr class="fw-bold border-top">
                                <td colspan="2">Total Liabilities:</td>
                                <td class="text-end text-danger">₹{{ number_format($report['total_liabilities'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- 2. Equity & Capital -->
                    <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2 mb-3">Equity & Retained Earnings</h6>
                    <table class="table table-sm table-borderless mb-2">
                        <tbody>
                            @foreach($report['equity_rows'] as $eq)
                                <tr>
                                    <td style="width: 20%;" class="font-monospace text-muted">{{ $eq['account']->account_code }}</td>
                                    <td>{{ $eq['account']->name }}</td>
                                    <td class="text-end fw-bold" style="width: 30%;">₹{{ number_format($eq['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="font-monospace text-muted">3310</td>
                                <td>Cumulative Net Profit / (Loss) to Date</td>
                                <td class="text-end fw-bold {{ $report['cumulative_net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    ₹{{ number_format($report['cumulative_net_profit'], 2) }}
                                </td>
                            </tr>
                            <tr class="fw-bold border-top">
                                <td colspan="2">Total Equity:</td>
                                <td class="text-end text-warning">₹{{ number_format($report['total_equity'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light p-3 border-top d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-danger text-uppercase">Total Liabilities & Equity:</h5>
                    <h4 class="fw-bold mb-0 text-danger">₹{{ number_format($report['total_liabilities_and_equity'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
