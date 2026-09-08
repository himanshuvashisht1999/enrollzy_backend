@extends('admin.layouts.master')

@section('title', 'Party Details: ' . $party->name)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.parties.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Parties
            </a>
            <h3 class="fw-bold mb-1">{{ $party->name }}</h3>
            <p class="text-muted small mb-0">{{ $party->legal_name ?: 'Contact Statement & Transaction History' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($party->is_customer)
                <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> New Invoice
                </a>
            @endif
            @if($party->is_vendor)
                <a href="{{ route('admin.accounting.bills.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> New Bill
                </a>
            @endif
            <a href="{{ route('admin.accounting.parties.edit', $party->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">Current Ledger Balance</span>
                    <h3 class="fw-bold mt-2 {{ $party->current_balance < 0 ? 'text-danger' : 'text-dark' }}">
                        ₹{{ number_format($party->current_balance, 2) }}
                    </h3>
                    <div class="small text-muted">Opening: ₹{{ number_format($party->opening_balance, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">Statutory & Tax Details</span>
                    <div class="mt-2 small">
                        <div><strong>GSTIN:</strong> {{ $party->gstin ?: 'Not Registered' }}</div>
                        <div><strong>PAN:</strong> {{ $party->pan ?: 'Not Provided' }}</div>
                        <div><strong>State Code:</strong> {{ $party->state_code ? $party->state_code . ' (' . $party->state . ')' : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">Contact Info</span>
                    <div class="mt-2 small">
                        <div><strong>Email:</strong> {{ $party->email ?: '—' }}</div>
                        <div><strong>Phone:</strong> {{ $party->phone ?: '—' }}</div>
                        <div><strong>Address:</strong> {{ Str::limit($party->billing_address, 40) ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs: Statement, Invoices, Bills -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#statement-tab" type="button">General Ledger Statement</button>
        </li>
        @if($party->is_customer)
        <li class="nav-item">
            <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#invoices-tab" type="button">Invoices ({{ $party->salesInvoices->count() }})</button>
        </li>
        @endif
        @if($party->is_vendor)
        <li class="nav-item">
            <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#bills-tab" type="button">Vendor Bills ({{ $party->vendorBills->count() }})</button>
        </li>
        @endif
    </ul>

    <div class="tab-content">
        <!-- Statement Tab -->
        <div class="tab-pane fade show active" id="statement-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Posted Journal Lines</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Date</th>
                                    <th>Journal #</th>
                                    <th>Account</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledgerLines as $line)
                                    <tr>
                                        <td>{{ $line->journalEntry->journal_date->format('d M Y') }}</td>
                                        <td class="font-monospace fw-bold">{{ $line->journalEntry->journal_number }}</td>
                                        <td>{{ $line->account->account_code }} - {{ $line->account->name }}</td>
                                        <td>{{ $line->description }}</td>
                                        <td class="text-end fw-bold text-success">{{ $line->debit > 0 ? '₹' . number_format($line->debit, 2) : '—' }}</td>
                                        <td class="text-end fw-bold text-danger">{{ $line->credit > 0 ? '₹' . number_format($line->credit, 2) : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No posted journal transactions for this party yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Tab -->
        @if($party->is_customer)
        <div class="tab-pane fade" id="invoices-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Balance Due</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($party->salesInvoices as $inv)
                                    <tr>
                                        <td class="fw-bold">{{ $inv->invoice_number }}</td>
                                        <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                                        <td>{{ $inv->due_date->format('d M Y') }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format($inv->total_amount, 2) }}</td>
                                        <td class="text-end fw-bold text-danger">₹{{ number_format($inv->balance_due, 2) }}</td>
                                        <td class="text-center"><span class="badge bg-secondary">{{ ucfirst($inv->payment_status) }}</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No invoices created for this customer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Bills Tab -->
        @if($party->is_vendor)
        <div class="tab-pane fade" id="bills-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Bill #</th>
                                    <th>Vendor Ref</th>
                                    <th>Date</th>
                                    <th class="text-end">Net Payable</th>
                                    <th class="text-end">Balance Due</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($party->vendorBills as $bill)
                                    <tr>
                                        <td class="fw-bold">{{ $bill->bill_number }}</td>
                                        <td>{{ $bill->vendor_bill_ref ?: '—' }}</td>
                                        <td>{{ $bill->bill_date->format('d M Y') }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format($bill->net_payable, 2) }}</td>
                                        <td class="text-end fw-bold text-danger">₹{{ number_format($bill->balance_due, 2) }}</td>
                                        <td class="text-center"><span class="badge bg-secondary">{{ ucfirst($bill->payment_status) }}</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.accounting.bills.show', $bill->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No bills recorded for this vendor.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
