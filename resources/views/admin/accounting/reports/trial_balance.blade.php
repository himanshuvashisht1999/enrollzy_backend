@extends('admin.layouts.master')

@section('title', 'Trial Balance Report')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <h3 class="fw-bold mb-1"><i class="fas fa-balance-scale text-primary me-2"></i>Trial Balance</h3>
            <p class="text-muted small mb-0">Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
        </div>
        <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.reports.trial_balance') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Update Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Balance Status Banner -->
    <div class="alert {{ $report['is_balanced'] ? 'alert-success' : 'alert-danger' }} d-flex justify-content-between align-items-center mb-4">
        <div>
            <i class="fas {{ $report['is_balanced'] ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-2"></i>
            <strong>Status:</strong> {{ $report['is_balanced'] ? 'Trial Balance is perfectly balanced (Total Debits == Total Credits)' : 'Trial Balance is Out of Balance by ₹' . number_format($report['difference'], 2) }}
        </div>
        <div class="fw-bold font-monospace">
            Debits: ₹{{ number_format($report['total_debit'], 2) }} &bull; Credits: ₹{{ number_format($report['total_credit'], 2) }}
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="width: 120px;">Code</th>
                            <th>Account Name</th>
                            <th>Category</th>
                            <th class="text-end" style="width: 180px;">Debit Balance (₹)</th>
                            <th class="text-end" style="width: 180px;">Credit Balance (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['rows'] as $r)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $r['account']->account_code }}</td>
                                <td>
                                    <a href="{{ route('admin.accounting.reports.general_ledger', ['account_id' => $r['account']->id, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="text-decoration-none fw-bold text-dark">
                                        {{ $r['account']->name }}
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border text-uppercase">{{ $r['account']->account_type }}</span></td>
                                <td class="text-end fw-bold text-success">
                                    {{ $r['debit_balance'] > 0 ? '₹' . number_format($r['debit_balance'], 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ $r['credit_balance'] > 0 ? '₹' . number_format($r['credit_balance'], 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No posted transactions found in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold fs-5">
                        <tr>
                            <td colspan="3" class="text-end text-uppercase">Total:</td>
                            <td class="text-end text-success">₹{{ number_format($report['total_debit'], 2) }}</td>
                            <td class="text-end text-danger">₹{{ number_format($report['total_credit'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
