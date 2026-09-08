@extends('admin.layouts.master')

@section('title', 'Expense: ' . $expense->expense_number)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.expenses.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Expenses
            </a>
            <h3 class="fw-bold mb-1">{{ $expense->expense_number }}</h3>
            <p class="text-muted small mb-0">{{ $expense->title }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($expense->status === 'draft')
                <form action="{{ route('admin.accounting.expenses.submit', $expense->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm text-dark">
                        <i class="fas fa-paper-plane me-1"></i> Submit for Approval
                    </button>
                </form>
            @endif

            @if($expense->status === 'submitted')
                <form action="{{ route('admin.accounting.expenses.approve', $expense->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check me-1"></i> Approve Expense
                    </button>
                </form>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times me-1"></i> Reject
                </button>
            @endif

            @if($expense->status === 'approved')
                <form action="{{ route('admin.accounting.expenses.pay', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Pay and post this expense to the General Ledger?');">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-money-bill-wave me-1"></i> Pay & Post to Ledger
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
                <span><strong>Lifecycle Status:</strong> 
                    <span class="badge 
                        @if($expense->status === 'paid' || $expense->status === 'posted') bg-success
                        @elseif($expense->status === 'approved') bg-primary
                        @elseif($expense->status === 'submitted') bg-warning text-dark
                        @elseif($expense->status === 'rejected') bg-danger
                        @else bg-secondary
                        @endif
                        text-uppercase">
                        {{ $expense->status }}
                    </span>
                </span>
                @if($expense->submitter)
                    <span><strong>Submitted By:</strong> {{ $expense->submitter->name }} ({{ $expense->submitted_at ? $expense->submitted_at->format('d M Y') : '' }})</span>
                @endif
                @if($expense->approver)
                    <span><strong>Approved By:</strong> {{ $expense->approver->name }}</span>
                @endif
            </div>
            <div>
                <span class="text-muted">Total Amount:</span>
                <span class="fw-bold fs-5 text-success ms-1">
                    ₹{{ number_format($expense->total_amount, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Expense Details Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="text-uppercase fw-bold text-muted small">Expense Information:</h6>
                    <h4 class="fw-bold text-dark mb-2">{{ $expense->title }}</h4>
                    <div class="small text-muted">
                        <div><strong>Expense Number:</strong> {{ $expense->expense_number }}</div>
                        <div><strong>Expense Date:</strong> {{ $expense->expense_date->format('d F Y') }}</div>
                        <div><strong>Category Account:</strong> <span class="badge bg-light text-dark border">{{ $expense->categoryAccount->account_code ?? '' }} - {{ $expense->categoryAccount->name ?? 'General Expense' }}</span></div>
                        <div><strong>Paid Through:</strong> {{ $expense->paidThroughAccount->account_name ?? 'Cash / Bank' }}</div>
                        <div><strong>Payment Method:</strong> {{ strtoupper(str_replace('_', ' ', $expense->payment_method)) }}</div>
                        @if($expense->payment_reference) <div><strong>Reference / Txn ID:</strong> {{ $expense->payment_reference }}</div> @endif
                    </div>
                </div>

                <div class="col-md-6 text-md-end">
                    <h6 class="text-uppercase fw-bold text-muted small">Payee / Beneficiary:</h6>
                    <h5 class="fw-bold mb-1">{{ $expense->party ? $expense->party->name : 'Direct Company Expense' }}</h5>
                    <div class="small text-muted mt-2">
                        @if($expense->party && $expense->party->email) <div>{{ $expense->party->email }}</div> @endif
                        @if($expense->party && $expense->party->gstin) <div>GSTIN: {{ $expense->party->gstin }}</div> @endif
                    </div>

                    @if($expense->receipt_attachment)
                        <div class="mt-3">
                            <a href="{{ asset('storage/' . $expense->receipt_attachment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-paperclip me-1"></i> View Attached Receipt
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="row justify-content-end border-top pt-4">
                <div class="col-md-5">
                    <table class="table table-sm table-borderless text-end">
                        <tr>
                            <td class="fw-bold">Subtotal / Basic:</td>
                            <td class="fw-bold">₹{{ number_format($expense->subtotal, 2) }}</td>
                        </tr>
                        @if($expense->tax_amount > 0)
                        <tr>
                            <td class="text-muted">GST Input Tax Credit:</td>
                            <td>₹{{ number_format($expense->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <td class="fw-bold fs-5 text-dark">Total Claim Amount:</td>
                            <td class="fw-bold fs-5 text-success">₹{{ number_format($expense->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($expense->notes)
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold small text-uppercase text-muted">Notes / Justification:</h6>
                    <p class="small text-muted mb-0">{{ $expense->notes }}</p>
                </div>
            @endif

            @if($expense->rejection_reason)
                <div class="alert alert-danger mt-3 mb-0">
                    <strong>Rejection Reason:</strong> {{ $expense->rejection_reason }}
                </div>
            @endif
        </div>
    </div>

    <!-- Double-Entry Ledger Journal Lines -->
    @if($expense->transaction && $expense->transaction->journalEntries->count() > 0)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-book text-primary me-2"></i>Double-Entry General Ledger Impact</h6>
            </div>
            <div class="card-body p-0">
                @foreach($expense->transaction->journalEntries as $journal)
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

<!-- Modal: Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.accounting.expenses.reject', $expense->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Reject Expense Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Provide feedback to the submitter..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
