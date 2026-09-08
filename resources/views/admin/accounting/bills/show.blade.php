@extends('admin.layouts.master')

@section('title', 'Vendor Bill: ' . $bill->bill_number)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.bills.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Bills
            </a>
            <h3 class="fw-bold mb-1">{{ $bill->bill_number }}</h3>
            <p class="text-muted small mb-0">Vendor Bill from {{ $bill->party->name }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($bill->status === 'draft')
                <form action="{{ route('admin.accounting.bills.post', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Post this vendor bill to the General Ledger?');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle me-1"></i> Post to Ledger
                    </button>
                </form>
            @endif
            @if($bill->status === 'posted' && $bill->balance_due > 0)
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="fas fa-money-bill-wave me-1"></i> Make Payment
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
                <span><strong>Posting:</strong> 
                    <span class="badge {{ $bill->status === 'posted' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase">
                        {{ $bill->status }}
                    </span>
                </span>
                <span><strong>Payment:</strong> 
                    <span class="badge 
                        @if($bill->payment_status === 'paid') bg-success
                        @elseif($bill->payment_status === 'partially_paid') bg-info text-dark
                        @elseif($bill->isOverdue()) bg-danger
                        @else bg-secondary
                        @endif
                        text-uppercase">
                        {{ str_replace('_', ' ', $bill->payment_status) }}
                    </span>
                </span>
                @if($bill->transaction)
                    <span><strong>Ledger Txn:</strong> <span class="font-monospace text-primary fw-bold">{{ $bill->transaction->transaction_number }}</span></span>
                @endif
            </div>
            <div>
                <span class="text-muted">Balance Due:</span>
                <span class="fw-bold fs-5 {{ $bill->balance_due > 0 ? 'text-danger' : 'text-success' }} ms-1">
                    ₹{{ number_format($bill->balance_due, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Bill Document Details -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="text-uppercase fw-bold text-muted small">Vendor Details:</h6>
                    <h5 class="fw-bold text-dark mb-1">{{ $bill->party->name }}</h5>
                    <div class="text-muted small mt-2">
                        @if($bill->party->gstin) <div><strong>GSTIN:</strong> {{ $bill->party->gstin }}</div> @endif
                        @if($bill->party->pan) <div><strong>PAN:</strong> {{ $bill->party->pan }}</div> @endif
                        @if($bill->party->billing_address) <div>{{ $bill->party->billing_address }}</div> @endif
                        @if($bill->party->state) <div>State: {{ $bill->party->state }} ({{ $bill->party->state_code ?? '—' }})</div> @endif
                    </div>
                </div>

                <div class="col-md-6 text-md-end">
                    <h6 class="text-uppercase fw-bold text-muted small">Bill Information:</h6>
                    <div class="small">
                        <div><strong>Internal Bill #:</strong> {{ $bill->bill_number }}</div>
                        <div><strong>Vendor Invoice Ref:</strong> {{ $bill->vendor_bill_ref ?: '—' }}</div>
                        <div><strong>Bill Date:</strong> {{ $bill->bill_date->format('d F Y') }}</div>
                        <div><strong>Due Date:</strong> <span class="{{ $bill->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $bill->due_date->format('d F Y') }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 30%;">Item & Description</th>
                            <th style="width: 10%;">HSN/SAC</th>
                            <th class="text-center" style="width: 8%;">Qty</th>
                            <th class="text-end" style="width: 12%;">Rate</th>
                            <th class="text-end" style="width: 10%;">Taxable</th>
                            <th class="text-center" style="width: 8%;">GST</th>
                            <th style="width: 15%;">Expense Account</th>
                            <th class="text-end" style="width: 12%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bill->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold">{{ $item->item_name }}</div>
                                    @if($item->description)
                                        <small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->hsn_sac ?: '—' }}</td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">₹{{ number_format($item->taxable_amount, 2) }}</td>
                                <td class="text-center">{{ $item->gst_rate }}%</td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $item->expenseAccount ? $item->expenseAccount->account_code . ' ' . $item->expenseAccount->name : 'General Expense' }}
                                    </span>
                                </td>
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
                            <td class="fw-bold">Subtotal (Expense):</td>
                            <td class="fw-bold">₹{{ number_format($bill->subtotal, 2) }}</td>
                        </tr>
                        @if($bill->cgst_amount > 0)
                        <tr>
                            <td class="text-muted">Input CGST:</td>
                            <td>₹{{ number_format($bill->cgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($bill->sgst_amount > 0)
                        <tr>
                            <td class="text-muted">Input SGST:</td>
                            <td>₹{{ number_format($bill->sgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($bill->igst_amount > 0)
                        <tr>
                            <td class="text-muted">Input IGST:</td>
                            <td>₹{{ number_format($bill->igst_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <td class="fw-bold fs-5 text-dark">Total Bill Value:</td>
                            <td class="fw-bold fs-5 text-dark">₹{{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                        @if($bill->tds_amount > 0)
                        <tr class="text-warning">
                            <td class="fw-bold">Less: TDS Withheld ({{ $bill->tdsRate->name ?? 'TDS' }}):</td>
                            <td class="fw-bold">- ₹{{ number_format($bill->tds_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <td class="fw-bold fs-5 text-danger">Net Payable:</td>
                            <td class="fw-bold fs-5 text-danger">₹{{ number_format($bill->net_payable, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Paid to Date:</td>
                            <td class="text-success fw-bold">₹{{ number_format($bill->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-5 text-danger">Balance Due:</td>
                            <td class="fw-bold fs-5 text-danger">₹{{ number_format($bill->balance_due, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($bill->notes)
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold small text-uppercase text-muted">Notes:</h6>
                    <p class="small text-muted mb-0">{{ $bill->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Double-Entry Ledger Journal Lines -->
    @if($bill->transaction && $bill->transaction->journalEntries->count() > 0)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-book text-primary me-2"></i>Double-Entry General Ledger Impact</h6>
            </div>
            <div class="card-body p-0">
                @foreach($bill->transaction->journalEntries as $journal)
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
    @if($bill->paymentAllocations->count() > 0)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-receipt text-success me-2"></i>Payment Allocations</h6>
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
                            @foreach($bill->paymentAllocations as $alloc)
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

<!-- Modal: Make Payment -->
@if($bill->status === 'posted' && $bill->balance_due > 0)
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.accounting.bills.record_payment', $bill->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Record Vendor Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pay From Bank / Cash Account <span class="text-danger">*</span></label>
                        <select name="bank_account_id" class="form-select" required>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->account_name }} ({{ $bank->bank_name }}) - ₹{{ number_format($bank->current_balance, 2) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Paid (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ $bill->balance_due }}" max="{{ $bill->balance_due }}" min="0.01" required>
                        <small class="text-muted">Balance Due: ₹{{ number_format($bill->balance_due, 2) }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Reference / UTR Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. NEFT12345678, Cheque #">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Payment remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-1"></i> Confirm & Post Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
