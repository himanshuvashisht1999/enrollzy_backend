@extends('admin.layouts.master')

@section('title', 'Banking & Cash Registers')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-university text-primary me-2"></i>Banking & Cash Registers</h3>
            <p class="text-muted small mb-0">Manage Current Accounts, Cash Registers, Inter-Bank Transfers & General Ledger Statements</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="fas fa-exchange-alt me-1"></i> Inter-Account Transfer
            </button>
            <a href="{{ route('admin.accounting.banking.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add Bank Account
            </a>
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

    <!-- Total Liquidity Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-primary text-white">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <span class="text-white-50 text-uppercase fw-bold small">Total Liquid Cash & Bank Position</span>
                <h2 class="fw-bold mb-0 mt-1">₹{{ number_format($totalBalance, 2) }}</h2>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-primary fs-6 px-3 py-2 rounded-pill">{{ $bankAccounts->count() }} Active Accounts</span>
            </div>
        </div>
    </div>

    <!-- Accounts Grid -->
    <div class="row g-3 mb-4">
        @foreach($bankAccounts as $bank)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                                <i class="fas fa-university fa-lg"></i>
                            </span>
                            <span class="badge {{ $bank->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $bank->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $bank->account_name }}</h5>
                        <p class="text-muted small mb-2">{{ $bank->bank_name }} &bull; {{ $bank->account_number_masked }}</p>
                        @if($bank->ifsc)
                            <div class="small text-muted font-monospace mb-3">IFSC: {{ $bank->ifsc }}</div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Current Balance</small>
                                <span class="fw-bold fs-5 {{ $bank->current_balance < 0 ? 'text-danger' : 'text-dark' }}">
                                    ₹{{ number_format($bank->current_balance, 2) }}
                                </span>
                            </div>
                            <a href="{{ route('admin.accounting.banking.show', $bank->id) }}" class="btn btn-sm btn-outline-primary">
                                View Statement &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal: Fund Transfer -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.accounting.banking.transfer') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Inter-Account Fund Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Transfer From (Source) <span class="text-danger">*</span></label>
                        <select name="from_account_id" class="form-select" required>
                            <option value="">Select Source Account</option>
                            @foreach($bankAccounts as $b)
                                <option value="{{ $b->id }}">{{ $b->account_name }} (₹{{ number_format($b->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Transfer To (Destination) <span class="text-danger">*</span></label>
                        <select name="to_account_id" class="form-select" required>
                            <option value="">Select Destination Account</option>
                            @foreach($bankAccounts as $b)
                                <option value="{{ $b->id }}">{{ $b->account_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Transfer Date <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" min="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reference / IMPS / Cheque Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. TXN12345678">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Transfer description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Post Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
