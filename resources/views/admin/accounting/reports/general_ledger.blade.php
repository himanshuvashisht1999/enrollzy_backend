@extends('admin.layouts.master')

@section('title', 'General Ledger Statement')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <h3 class="fw-bold mb-1"><i class="fas fa-book-open text-info me-2"></i>General Ledger Statement</h3>
            <p class="text-muted small mb-0">{{ $report['account']->account_code }} - {{ $report['account']->name }} ({{ strtoupper($report['account']->account_type) }})</p>
        </div>
        <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Ledger
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.reports.general_ledger') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Select Account (COA)</label>
                    <select name="account_id" class="form-select">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                                {{ $acc->account_code }} - {{ $acc->name }} ({{ strtoupper($acc->account_type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> View Ledger</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Opening Balance Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
        <div class="card-body py-3 d-flex justify-content-between align-items-center">
            <div>
                <strong>Opening Balance (as of {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}):</strong>
                <span class="font-monospace ms-2 fw-bold fs-5 {{ $report['opening_balance'] < 0 ? 'text-danger' : 'text-dark' }}">
                    ₹{{ number_format($report['opening_balance'], 2) }}
                </span>
            </div>
            <div>
                <strong>Closing Balance (as of {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}):</strong>
                <span class="font-monospace ms-2 fw-bold fs-5 text-primary">
                    ₹{{ number_format($report['closing_balance'], 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Ledger Lines Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="width: 120px;">Date</th>
                            <th style="width: 140px;">Journal #</th>
                            <th>Description</th>
                            <th>Entity / Party</th>
                            <th class="text-end" style="width: 140px;">Debit (₹)</th>
                            <th class="text-end" style="width: 140px;">Credit (₹)</th>
                            <th class="text-end" style="width: 160px;">Running Balance (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['lines'] as $l)
                            <tr>
                                <td>{{ $l['journal_entry']->journal_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.accounting.journals.show', $l['journal_entry']->id) }}" class="fw-bold font-monospace text-decoration-none">
                                        {{ $l['journal_entry']->journal_number }}
                                    </a>
                                </td>
                                <td>{{ $l['line']->description }}</td>
                                <td>{{ $l['line']->party ? $l['line']->party->name : '—' }}</td>
                                <td class="text-end fw-bold text-success">
                                    {{ $l['debit'] > 0 ? '₹' . number_format($l['debit'], 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ $l['credit'] > 0 ? '₹' . number_format($l['credit'], 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold font-monospace {{ $l['running_balance'] < 0 ? 'text-danger' : 'text-dark' }}">
                                    ₹{{ number_format($l['running_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No journal entries found for this account in the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end text-uppercase">Period Total:</td>
                            <td class="text-end text-success fs-6">₹{{ number_format($report['total_debit'], 2) }}</td>
                            <td class="text-end text-danger fs-6">₹{{ number_format($report['total_credit'], 2) }}</td>
                            <td class="text-end text-primary fs-6">Closing: ₹{{ number_format($report['closing_balance'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
