@extends('admin.layouts.master')

@section('title', 'Create Expense Claim')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.accounting.expenses.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Expenses
        </a>
        <h3 class="fw-bold mb-1">Record Expense Claim</h3>
        <p class="text-muted small mb-0">Record office, travel, software, or operational expense with receipt attachment & approval.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.accounting.expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Expense Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. AWS Cloud Hosting Bill / Staff Dinner / Flight Ticket" value="{{ old('title') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Expense Category (COA) <span class="text-danger">*</span></label>
                        <select name="category_account_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_account_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->account_code }} - {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Payee / Beneficiary / Staff</label>
                        <select name="party_id" class="form-select">
                            <option value="">— Direct Company Expense —</option>
                            @foreach($parties as $p)
                                <option value="{{ $p->id }}" {{ old('party_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Paid Through (Bank / Cash) <span class="text-danger">*</span></label>
                        <select name="paid_through_account_id" class="form-select" required>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}" {{ old('paid_through_account_id') == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->account_name }} (₹{{ number_format($bank->current_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / NEFT / IMPS</option>
                            <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI / QR</option>
                            <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Corporate Credit Card</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Petty Cash</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Subtotal / Basic Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="subtotal" id="exp_subtotal" class="form-control" placeholder="0.00" value="{{ old('subtotal') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">GST Tax Amount (₹) (If Tax Invoice)</label>
                        <input type="number" step="0.01" name="tax_amount" id="exp_tax" class="form-control" placeholder="0.00" value="{{ old('tax_amount', '0.00') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Total Expense (₹)</label>
                        <input type="text" id="exp_total" class="form-control fw-bold bg-light" readonly value="₹0.00">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Reference / Transaction ID</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="e.g. UPI Ref / Txn ID" value="{{ old('payment_reference') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Receipt / Invoice Attachment</label>
                        <input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">PDF, PNG, JPG up to 5MB</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Notes / Business Justification</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Explain the purpose of this expense...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('admin.accounting.expenses.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary">
                        <i class="fas fa-save me-1"></i> Save as Draft
                    </button>
                    <button type="submit" name="action" value="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-paper-plane me-1"></i> Submit for Approval
                    </button>
                    <button type="submit" name="action" value="pay_now" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Pay & Post Immediately
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const subtotalInput = document.getElementById('exp_subtotal');
    const taxInput = document.getElementById('exp_tax');
    const totalDisplay = document.getElementById('exp_total');

    function updateExpTotal() {
        const sub = parseFloat(subtotalInput.value) || 0;
        const tax = parseFloat(taxInput.value) || 0;
        totalDisplay.value = '₹' + (sub + tax).toFixed(2);
    }

    subtotalInput.addEventListener('input', updateExpTotal);
    taxInput.addEventListener('input', updateExpTotal);
    updateExpTotal();
});
</script>
@endpush
@endsection
