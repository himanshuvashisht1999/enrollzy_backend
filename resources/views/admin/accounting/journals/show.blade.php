@extends('admin.layouts.master')

@section('title', 'Journal Voucher: ' . $journal->journal_number)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.journals.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Journals
            </a>
            <h3 class="fw-bold mb-1">{{ $journal->journal_number }}</h3>
            <p class="text-muted small mb-0">{{ $journal->description }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($journal->status === 'posted' && $journal->entry_type !== 'reversal')
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reverseModal">
                    <i class="fas fa-undo me-1"></i> Reverse Voucher
                </button>
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
                <span><strong>Entry Type:</strong> 
                    <span class="badge {{ $journal->entry_type === 'manual' ? 'bg-info text-dark' : 'bg-secondary' }} text-uppercase">
                        {{ $journal->entry_type }}
                    </span>
                </span>
                <span><strong>Status:</strong> 
                    <span class="badge {{ $journal->status === 'posted' ? 'bg-success' : 'bg-danger' }} text-uppercase">
                        {{ $journal->status }}
                    </span>
                </span>
                <span><strong>Date:</strong> {{ $journal->journal_date->format('d F Y') }}</span>
                @if($journal->creator)
                    <span><strong>Created By:</strong> {{ $journal->creator->name }}</span>
                @endif
            </div>
            <div>
                <span class="text-muted">Total Amount:</span>
                <span class="fw-bold fs-5 text-dark ms-1">
                    ₹{{ number_format($journal->total_debit, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Journal Lines Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-list-ol text-primary me-2"></i>Double-Entry Journal Lines</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 35%;">Account</th>
                            <th style="width: 20%;">Entity / Party</th>
                            <th style="width: 20%;">Description</th>
                            <th class="text-end" style="width: 10%;">Debit (₹)</th>
                            <th class="text-end" style="width: 10%;">Credit (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journal->lines as $idx => $line)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace me-1">{{ $line->account->account_code }}</span>
                                    <span class="fw-bold">{{ $line->account->name }}</span>
                                    <small class="text-muted d-block text-uppercase">{{ $line->account->account_type }}</small>
                                </td>
                                <td>
                                    {{ $line->party ? $line->party->name : '—' }}
                                </td>
                                <td>{{ $line->description }}</td>
                                <td class="text-end fw-bold text-success">
                                    {{ $line->debit > 0 ? '₹' . number_format($line->debit, 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ $line->credit > 0 ? '₹' . number_format($line->credit, 2) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end text-uppercase">Total:</td>
                            <td class="text-end text-success fs-5">₹{{ number_format($journal->total_debit, 2) }}</td>
                            <td class="text-end text-danger fs-5">₹{{ number_format($journal->total_credit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Reverse Voucher -->
<div class="modal fade" id="reverseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.accounting.journals.reverse', $journal->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Reverse Journal Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger small">
                        <i class="fas fa-exclamation-triangle me-1"></i> Reversing this voucher will post an equal and opposite offsetting double-entry transaction, restoring original account balances.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason for Reversal <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Explain why this entry is being reversed..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm & Post Reversal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
