@extends('admin.layouts.master')

@section('title', 'Sales Invoices & Receivables')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-file-invoice text-primary me-2"></i>Sales Invoices & Receivables</h3>
            <p class="text-muted small mb-0">Customer Invoicing with Automated GST Splitting (CGST+SGST / IGST) & Balanced Ledger Posting</p>
        </div>
        <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Create Invoice
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
            <form method="GET" action="{{ route('admin.accounting.invoices.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Invoice # or customer name" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Customer</label>
                    <select name="party_id" class="form-select">
                        <option value="">All Customers</option>
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
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Balance Due</th>
                            <th class="text-center">Posting</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="fw-bold font-monospace text-decoration-none">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $inv->party->name ?? 'Unknown Customer' }}</div>
                                    @if($inv->party && $inv->party->gstin)
                                        <small class="text-muted font-monospace">{{ $inv->party->gstin }}</small>
                                    @endif
                                </td>
                                <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                                <td>
                                    <span class="{{ $inv->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                        {{ $inv->due_date->format('d M Y') }}
                                        @if($inv->isOverdue())
                                            <i class="fas fa-clock text-danger ms-1" title="Overdue"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="text-end fw-bold">₹{{ number_format($inv->total_amount, 2) }}</td>
                                <td class="text-end fw-bold {{ $inv->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                    ₹{{ number_format($inv->balance_due, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $inv->status === 'posted' ? 'bg-success' : ($inv->status === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge 
                                        @if($inv->payment_status === 'paid') bg-success
                                        @elseif($inv->payment_status === 'partially_paid') bg-info text-dark
                                        @elseif($inv->isOverdue()) bg-danger
                                        @else bg-secondary
                                        @endif
                                    ">
                                        {{ ucfirst(str_replace('_', ' ', $inv->payment_status)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-outline-secondary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.accounting.invoices.print', $inv->id) }}" target="_blank" class="btn btn-outline-dark" title="Print / PDF">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if($inv->status === 'draft')
                                            <form action="{{ route('admin.accounting.invoices.post', $inv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Post this invoice to General Ledger? This will update AR balances.');">
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
                                <td colspan="9" class="text-center text-muted py-4">No sales invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
                <div class="p-3 border-top">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
