@extends('admin.layouts.master')

@section('title', 'Bank Statement: ' . $bankAccount->account_name)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.banking.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Banking
            </a>
            <h3 class="fw-bold mb-1">{{ $bankAccount->account_name }}</h3>
            <p class="text-muted small mb-0">{{ $bankAccount->bank_name }} &bull; {{ $bankAccount->account_number_masked }}</p>
        </div>
        <div class="text-end">
            <span class="text-muted small d-block">Current Balance</span>
            <span class="fw-bold fs-4 {{ $bankAccount->current_balance < 0 ? 'text-danger' : 'text-success' }}">
                ₹{{ number_format($bankAccount->current_balance, 2) }}
            </span>
        </div>
    </div>

    <!-- Account Details Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <span class="text-muted small">Opening Balance</span>
                    <h5 class="fw-bold mt-1">₹{{ number_format($bankAccount->opening_balance, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <span class="text-muted small">IFSC Code</span>
                    <h5 class="fw-bold font-monospace mt-1">{{ $bankAccount->ifsc ?: '—' }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <span class="text-muted small">Branch</span>
                    <h5 class="fw-bold mt-1">{{ $bankAccount->branch ?: '—' }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <span class="text-muted small">COA Ledger Account</span>
                    <h5 class="fw-bold mt-1 small">{{ $bankAccount->ledgerAccount->name ?? '1120' }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Lines Statement -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-list-alt text-primary me-2"></i>Account Statement (Posted Ledger Transactions)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Date</th>
                            <th>Journal #</th>
                            <th>Description</th>
                            <th>Party / Payee</th>
                            <th class="text-end">Deposits (Dr)</th>
                            <th class="text-end">Withdrawals (Cr)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgerLines as $line)
                            <tr>
                                <td>{{ $line->journalEntry->journal_date->format('d M Y') }}</td>
                                <td class="font-monospace fw-bold">{{ $line->journalEntry->journal_number }}</td>
                                <td>{{ $line->description }}</td>
                                <td>{{ $line->party->name ?? '—' }}</td>
                                <td class="text-end fw-bold text-success">
                                    {{ $line->debit > 0 ? '₹' . number_format($line->debit, 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ $line->credit > 0 ? '₹' . number_format($line->credit, 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No transactions recorded for this bank account.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ledgerLines->hasPages())
                <div class="p-3 border-top">
                    {{ $ledgerLines->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
