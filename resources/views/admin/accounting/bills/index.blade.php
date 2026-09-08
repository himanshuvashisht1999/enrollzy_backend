@extends('admin.layouts.master')

@section('title', 'Vendor Bills & Payables')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-file-invoice-dollar text-danger me-2"></i>Vendor Bills & Payables</h3>
            <p class="text-muted small mb-0">Record Supplier Bills, Track Expense Distribution, Input GST Credit & TDS Withholding</p>
        </div>
        <a href="{{ route('admin.accounting.bills.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> New Vendor Bill
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

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.bills.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Bill # or vendor name" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Vendor</label>
                    <select name="party_id" class="form-select">
                        <option value="">All Vendors</option>
                        @foreach($parties as $p)
                            <option value="{{ $p->id }}" {{ request('party_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Posting Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted to Ledger</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All Payments</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partially_paid" {{ request('payment_status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.accounting.bills.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bills Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Bill #</th>
                            <th>Vendor</th>
                            <th>Bill Date</th>
                            <th>Due Date</th>
                            <th class="text-end">Total Bill</th>
                            <th class="text-end">TDS Withheld</th>
                            <th class="text-end">Net Payable</th>
                            <th class="text-end">Balance Due</th>
                            <th class="text-center">Posting</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.accounting.bills.show', $bill->id) }}" class="fw-bold font-monospace text-decoration-none">
                                        {{ $bill->bill_number }}
                                    </a>
                                    @if($bill->vendor_bill_ref)
                                        <div class="small text-muted">Ref: {{ $bill->vendor_bill_ref }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $bill->party->name ?? 'Unknown Vendor' }}</div>
                                    @if($bill->party && $bill->party->gstin)
                                        <small class="text-muted font-monospace">{{ $bill->party->gstin }}</small>
                                    @endif
                                </td>
                                <td>{{ $bill->bill_date->format('d M Y') }}</td>
                                <td>
                                    <span class="{{ $bill->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                        {{ $bill->due_date->format('d M Y') }}
                                        @if($bill->isOverdue())
                                            <i class="fas fa-clock text-danger ms-1" title="Overdue"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="text-end fw-bold">₹{{ number_format($bill->total_amount, 2) }}</td>
                                <td class="text-end text-muted">
                                    @if($bill->tds_amount > 0)
                                        <span class="badge bg-warning-subtle text-dark">₹{{ number_format($bill->tds_amount, 2) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end fw-bold">₹{{ number_format($bill->net_payable, 2) }}</td>
                                <td class="text-end fw-bold {{ $bill->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                    ₹{{ number_format($bill->balance_due, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $bill->status === 'posted' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($bill->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge 
                                        @if($bill->payment_status === 'paid') bg-success
                                        @elseif($bill->payment_status === 'partially_paid') bg-info text-dark
                                        @elseif($bill->isOverdue()) bg-danger
                                        @else bg-secondary
                                        @endif
                                    ">
                                        {{ ucfirst(str_replace('_', ' ', $bill->payment_status)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.accounting.bills.show', $bill->id) }}" class="btn btn-outline-secondary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($bill->status === 'draft')
                                            <form action="{{ route('admin.accounting.bills.post', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Post this vendor bill to General Ledger?');">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Post to Ledger">
                                                    <i class="fas fa-check"></i> Post
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">No vendor bills recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bills->hasPages())
                <div class="p-3 border-top">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
