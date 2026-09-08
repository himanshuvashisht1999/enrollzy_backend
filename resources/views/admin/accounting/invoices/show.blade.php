@extends('admin.layouts.master')

@section('title', 'Sales Invoice: ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Invoices
            </a>
            <h3 class="fw-bold mb-1">{{ $invoice->invoice_number }}</h3>
            <p class="text-muted small mb-0">Sales Invoice for {{ $invoice->party->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.accounting.invoices.print', $invoice->id) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print me-1"></i> Print / PDF
            </a>
            @if($invoice->status === 'draft')
                <form action="{{ route('admin.accounting.invoices.post', $invoice->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Post this invoice to the General Ledger?');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle me-1"></i> Post to Ledger
                    </button>
                </form>
            @endif
            @if($invoice->status === 'posted' && $invoice->balance_due > 0)
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#receivePaymentModal">
                    <i class="fas fa-money-bill-wave me-1"></i> Receive Payment
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
                <span><strong>Posting Status:</strong> 
                    <span class="badge {{ $invoice->status === 'posted' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase">
                        {{ $invoice->status }}
                    </span>
                </span>
                <span><strong>Payment Status:</strong> 
                    <span class="badge 
                        @if($invoice->payment_status === 'paid') bg-success
                        @elseif($invoice->payment_status === 'partially_paid') bg-info text-dark
                        @elseif($invoice->isOverdue()) bg-danger
                        @else bg-secondary
                        @endif
                        text-uppercase">
                        {{ str_replace('_', ' ', $invoice->payment_status) }}
                    </span>
                </span>
                @if($invoice->transaction)
                    <span><strong>Ledger Txn:</strong> <span class="font-monospace text-primary fw-bold">{{ $invoice->transaction->transaction_number }}</span></span>
                @endif
            </div>
            <div>
                <span class="text-muted">Balance Due:</span>
                <span class="fw-bold fs-5 {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }} ms-1">
                    ₹{{ number_format($invoice->balance_due, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Invoice Document Details -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="text-uppercase fw-bold text-muted small">Billed To:</h6>
                    <h5 class="fw-bold text-dark mb-1">{{ $invoice->party->name }}</h5>
                    @if($invoice->party->legal_name && $invoice->party->legal_name !== $invoice->party->name)
                        <div class="text-muted small">{{ $invoice->party->legal_name }}</div>
                    @endif
                    <div class="text-muted small mt-2">
                        @if($invoice->party->gstin) <div><strong>GSTIN:</strong> {{ $invoice->party->gstin }}</div> @endif
                        @if($invoice->party->pan) <div><strong>PAN:</strong> {{ $invoice->party->pan }}</div> @endif
                        @if($invoice->party->billing_address) <div>{{ $invoice->party->billing_address }}</div> @endif
                        @if($invoice->party->state) <div>State: {{ $invoice->party->state }} ({{ $invoice->party->state_code ?? '—' }})</div> @endif
                    </div>
                </div>

                <div class="col-md-6 text-md-end">
                    <h6 class="text-uppercase fw-bold text-muted small">Invoice Details:</h6>
                    <div class="small">
                        <div><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</div>
                        <div><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d F Y') }}</div>
                        <div><strong>Due Date:</strong> <span class="{{ $invoice->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $invoice->due_date->format('d F Y') }}</span></div>
                        <div><strong>Place of Supply:</strong> {{ $invoice->place_of_supply ?: 'Delhi' }}</div>
                    </div>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 35%;">Item & Description</th>
                            <th style="width: 10%;">HSN/SAC</th>
                            <th class="text-center" style="width: 8%;">Qty</th>
                            <th class="text-end" style="width: 12%;">Unit Price</th>
                            <th class="text-end" style="width: 10%;">Taxable</th>
                            <th class="text-center" style="width: 8%;">GST</th>
                            <th class="text-end" style="width: 12%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold">{{ $item->item_name }}</div>
                                    @if($item->description)
                                        <small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                    @if($item->revenueAccount)
                                        <div class="small text-muted"><span class="badge bg-light text-dark border">{{ $item->revenueAccount->name }}</span></div>
                                    @endif
                                </td>
                                <td>{{ $item->hsn_sac ?: '—' }}</td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">₹{{ number_format($item->taxable_amount, 2) }}</td>
                                <td class="text-center">{{ $item->gst_rate }}%</td>
                                <td class="text-end fw-bold">₹{{ number_format($item->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals Calculation -->
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <table class="table table-sm table-borderless text-end">
                        <tr>
                            <td class="fw-bold">Subtotal:</td>
                            <td class="fw-bold">₹{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->cgst_amount > 0)
                        <tr>
                            <td class="text-muted">Central GST (CGST):</td>
                            <td>₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->sgst_amount > 0)
                        <tr>
                            <td class="text-muted">State GST (SGST):</td>
                            <td>₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->igst_amount > 0)
                        <tr>
                            <td class="text-muted">Integrated GST (IGST):</td>
                            <td>₹{{ number_format($invoice->igst_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <td class="fw-bold fs-5 text-dark">Total Amount:</td>
                            <td class="fw-bold fs-5 text-success">₹{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Paid to Date:</td>
                            <td class="text-success fw-bold">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-5 text-danger">Balance Due:</td>
                            <td class="fw-bold fs-5 text-danger">₹{{ number_format($invoice->balance_due, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($invoice->notes || $invoice->terms)
                <div class="row mt-4 pt-3 border-top">
                    @if($invoice->notes)
                        <div class="col-md-6">
                            <h6 class="fw-bold small text-uppercase text-muted">Notes:</h6>
                            <p class="small text-muted">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                    @if($invoice->terms)
                        <div class="col-md-6">
                            <h6 class="fw-bold small text-uppercase text-muted">Terms:</h6>
                            <p class="small text-muted">{{ $invoice->terms }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Double-Entry Ledger Journal Lines -->
    @if($invoice->transaction && $invoice->transaction->journalEntries->count() > 0)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-book text-primary me-2"></i>Double-Entry General Ledger Impact</h6>
            </div>
            <div class="card-body p-0">
                @foreach($invoice->transaction->journalEntries as $journal)
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

    <!-- Payment Allocations History -->
    @if($invoice->paymentAllocations->count() > 0)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-receipt text-success me-2"></i>Payment Receipt Allocations</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Date</th>
                                <th>Transaction #</th>
                                <th>Allocated Amount</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->paymentAllocations as $alloc)
                                <tr>
                                    <td>{{ $alloc->allocation_date->format('d M Y') }}</td>
                                    <td class="font-monospace fw-bold">{{ $alloc->transaction ? $alloc->transaction->transaction_number : '—' }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($alloc->allocated_amount, 2) }}</td>
                                    <td>{{ $alloc->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal: Receive Payment -->
@if($invoice->status === 'posted' && $invoice->balance_due > 0)
<div class="modal fade" id="receivePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.accounting.invoices.record_payment', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Record Customer Payment Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deposit To Bank / Cash Account <span class="text-danger">*</span></label>
                        <select name="bank_account_id" class="form-select" required>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->account_name }} ({{ $bank->bank_name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Received (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" min="0.01" required>
                        <small class="text-muted">Remaining Balance: ₹{{ number_format($invoice->balance_due, 2) }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">TDS Deducted by Client (₹) (Optional)</label>
                        <input type="number" step="0.01" name="tds_deducted" class="form-control" value="0.00" min="0">
                        <small class="text-muted">If client deducted TDS u/s 194J/194C, enter amount to credit AR and debit TDS Receivable.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cash Discount Allowed (₹) (Optional)</label>
                        <input type="number" step="0.01" name="discount_allowed" class="form-control" value="0.00" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Reference / UTR Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. UTR12345678, IMPS Ref">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Payment remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-1"></i> Confirm & Post Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
