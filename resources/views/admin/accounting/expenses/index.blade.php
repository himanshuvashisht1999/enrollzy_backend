@extends('admin.layouts.master')

@section('title', 'Expenses & Employee Claims')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-receipt text-danger me-2"></i>Expenses & Claims</h3>
            <p class="text-muted small mb-0">Multi-Tier Approval Workflow (Draft &rarr; Submitted &rarr; Approved/Rejected &rarr; Paid & Posted)</p>
        </div>
        <a href="{{ route('admin.accounting.expenses.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> New Expense
        </a>
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

    <!-- Status Filter Tabs -->
    <ul class="nav nav-pills mb-3" id="expenseTabs">
        <li class="nav-item">
            <a class="nav-link {{ !request('status') ? 'active' : '' }} fw-bold" href="{{ route('admin.accounting.expenses.index') }}">
                All Expenses
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'submitted' ? 'active' : '' }} fw-bold" href="{{ route('admin.accounting.expenses.index', ['status' => 'submitted']) }}">
                <i class="fas fa-clock me-1 text-warning"></i> Pending Approval
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'approved' ? 'active' : '' }} fw-bold" href="{{ route('admin.accounting.expenses.index', ['status' => 'approved']) }}">
                <i class="fas fa-check-circle me-1 text-primary"></i> Approved (Awaiting Payment)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'paid' ? 'active' : '' }} fw-bold" href="{{ route('admin.accounting.expenses.index', ['status' => 'paid']) }}">
                <i class="fas fa-money-check-alt me-1 text-success"></i> Paid & Posted
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'draft' ? 'active' : '' }} fw-bold" href="{{ route('admin.accounting.expenses.index', ['status' => 'draft']) }}">
                Drafts
            </a>
        </li>
    </ul>

    <!-- Expenses Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Expense #</th>
                            <th>Date</th>
                            <th>Expense Title</th>
                            <th>Category</th>
                            <th>Payee / Staff</th>
                            <th>Paid Through</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $exp)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.accounting.expenses.show', $exp->id) }}" class="fw-bold font-monospace text-decoration-none">
                                        {{ $exp->expense_number }}
                                    </a>
                                </td>
                                <td>{{ $exp->expense_date->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $exp->title }}</div>
                                    @if($exp->payment_reference)
                                        <small class="text-muted">Ref: {{ $exp->payment_reference }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $exp->categoryAccount ? $exp->categoryAccount->name : 'General Expense' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $exp->party ? $exp->party->name : ($exp->submitter->name ?? 'Staff') }}
                                </td>
                                <td>
                                    <small class="text-muted">{{ $exp->paidThroughAccount->account_name ?? 'Cash / Bank' }}</small>
                                </td>
                                <td class="text-end fw-bold">₹{{ number_format($exp->total_amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge 
                                        @if($exp->status === 'paid' || $exp->status === 'posted') bg-success
                                        @elseif($exp->status === 'approved') bg-primary
                                        @elseif($exp->status === 'submitted') bg-warning text-dark
                                        @elseif($exp->status === 'rejected') bg-danger
                                        @else bg-secondary
                                        @endif
                                        text-uppercase">
                                        {{ $exp->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.accounting.expenses.show', $exp->id) }}" class="btn btn-outline-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($exp->status === 'submitted')
                                            <form action="{{ route('admin.accounting.expenses.approve', $exp->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($exp->status === 'approved')
                                            <form action="{{ route('admin.accounting.expenses.pay', $exp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Pay and post this expense to ledger?');">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-primary" title="Pay & Post">
                                                    <i class="fas fa-money-bill-wave"></i> Pay
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No expense records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())
                <div class="p-3 border-top">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
