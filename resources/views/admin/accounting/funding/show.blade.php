@extends('admin.layouts.master')

@section('title', 'Funding Record: ' . $funding->funding_number)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.funding.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Funding
            </a>
            <h3 class="fw-bold mb-1">{{ $funding->funding_number }}</h3>
            <p class="text-muted small mb-0">{{ strtoupper(str_replace('_', ' ', $funding->funding_type)) }} from {{ $funding->party->name }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($funding->status === 'draft')
                <form action="{{ route('admin.accounting.funding.post', $funding->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Post this funding infusion to the General Ledger?');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle me-1"></i> Post to Ledger
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Status Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
        <div class="card-body py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span><strong>Status:</strong> 
                    <span class="badge {{ $funding->status === 'posted' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase">
                        {{ $funding->status }}
                    </span>
                </span>
                @if($funding->transaction)
                    <span><strong>Ledger Txn:</strong> <span class="font-monospace text-primary fw-bold">{{ $funding->transaction->transaction_number }}</span></span>
                @endif
            </div>
            <div>
                <span class="text-muted">Funding Amount:</span>
                <span class="fw-bold fs-4 text-success ms-1">
                    ₹{{ number_format($funding->amount, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Funding Details -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="text-uppercase fw-bold text-muted small">Contributor Details:</h6>
                    <h4 class="fw-bold text-dark mb-1">{{ $funding->party->name }}</h4>
                    <span class="badge bg-light text-dark border">{{ strtoupper($funding->party->party_type) }}</span>
                    <div class="small text-muted mt-2">
                        @if($funding->party->email) <div>{{ $funding->party->email }}</div> @endif
                        @if($funding->party->phone) <div>{{ $funding->party->phone }}</div> @endif
                        @if($funding->party->pan) <div>PAN: {{ $funding->party->pan }}</div> @endif
                    </div>
                </div>

                <div class="col-md-6 text-md-end">
                    <h6 class="text-uppercase fw-bold text-muted small">Transaction Details:</h6>
                    <div class="small">
                        <div><strong>Funding Type:</strong> {{ strtoupper(str_replace('_', ' ', $funding->funding_type)) }}</div>
                        <div><strong>Received Date:</strong> {{ $funding->received_date->format('d F Y') }}</div>
                        <div><strong>Deposited In:</strong> {{ $funding->depositBankAccount->account_name ?? 'Bank' }}</div>
                        <div><strong>Equity Account:</strong> {{ $funding->equityLedgerAccount->name ?? 'Equity' }}</div>
                        @if($funding->reference_number) <div><strong>UTR / Wire Ref:</strong> {{ $funding->reference_number }}</div> @endif
                    </div>
                </div>
            </div>

            @if($funding->equity_percentage || $funding->valuation || $funding->shares_issued)
                <div class="row g-3 p-3 bg-light rounded-3 mb-3">
                    @if($funding->equity_percentage)
                        <div class="col-md-3">
                            <span class="text-muted small">Equity Diluted:</span>
                            <div class="fw-bold fs-5">{{ $funding->equity_percentage }}%</div>
                        </div>
                    @endif
                    @if($funding->valuation)
                        <div class="col-md-3">
                            <span class="text-muted small">Post-Money Valuation:</span>
                            <div class="fw-bold fs-5">₹{{ number_format($funding->valuation, 2) }}</div>
                        </div>
                    @endif
                    @if($funding->shares_issued)
                        <div class="col-md-3">
                            <span class="text-muted small">Shares Issued:</span>
                            <div class="fw-bold fs-5">{{ number_format($funding->shares_issued) }}</div>
                        </div>
                    @endif
                    @if($funding->share_price)
                        <div class="col-md-3">
                            <span class="text-muted small">Share Price:</span>
                            <div class="fw-bold fs-5">₹{{ number_format($funding->share_price, 2) }}</div>
                        </div>
                    @endif
                </div>
            @endif

            @if($funding->notes)
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold small text-uppercase text-muted">Notes:</h6>
                    <p class="small text-muted mb-0">{{ $funding->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Double-Entry Ledger Journal Lines -->
    @if($funding->transaction && $funding->transaction->journalEntries->count() > 0)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-book text-primary me-2"></i>Double-Entry General Ledger Impact</h6>
            </div>
            <div class="card-body p-0">
                @foreach($funding->transaction->journalEntries as $journal)
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Journal Voucher:</strong> <span class="font-monospace fw-bold">{{ $journal->journal_number }}</span>
                            <span class="text-muted ms-2">&bull; Date: {{ $journal->journal_date->format('d M Y') }}</span>
                        </div>
                        <span class="badge bg-success text-uppercase">Balanced</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Account</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit (₹)</th>
                                    <th class="text-end">Credit (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($journal->lines as $line)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace me-1">{{ $line->account->account_code }}</span>
                                            {{ $line->account->name }}
                                        </td>
                                        <td>{{ $line->description }}</td>
                                        <td class="text-end fw-bold text-success">{{ $line->debit > 0 ? '₹' . number_format($line->debit, 2) : '—' }}</td>
                                        <td class="text-end fw-bold text-danger">{{ $line->credit > 0 ? '₹' . number_format($line->credit, 2) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
