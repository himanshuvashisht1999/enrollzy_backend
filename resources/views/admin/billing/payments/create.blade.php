@extends('admin.layouts.master')

@section('title', 'Record Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Record Invoice Payment</h4>
    <a href="{{ route('admin.billing.payments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('admin.billing.payments.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label">Select Invoice <span class="text-danger">*</span></label>
                        <select name="billing_invoice_id" class="form-select @error('billing_invoice_id') is-invalid @enderror" required>
                            <option value="">Choose an unpaid/partial invoice...</option>
                            @foreach($invoices as $inv)
                                @php
                                    $paid = $inv->payments()->sum('amount');
                                    $remaining = $inv->total_amount - $paid;
                                @endphp
                                <option value="{{ $inv->id }}" {{ old('billing_invoice_id', $selectedInvoiceId) == $inv->id ? 'selected' : '' }}>
                                    {{ $inv->invoice_number }} - {{ $inv->organisation->name ?? 'N/A' }} 
                                    (Total: ₹{{ number_format($inv->total_amount, 2) }}, Due: ₹{{ number_format($remaining, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('billing_invoice_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="tds" {{ old('payment_method') == 'tds' ? 'selected' : '' }}>TDS</option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transaction ID / Reference</label>
                            <input type="text" name="transaction_id" id="transaction_id" class="form-control @error('transaction_id') is-invalid @enderror" value="{{ old('transaction_id') }}" placeholder="e.g. UTR or Cheque No">
                            @error('transaction_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-12 mb-4">
                            <label class="form-label">Internal Notes</label>
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#payment_method').on('change', function() {
            const method = $(this).val();
            const transactionInput = document.getElementById('transaction_id');
            if (method === 'tds') {
                if (!transactionInput.value || transactionInput.value.trim() === '') {
                    transactionInput.value = 'TDS deduction';
                }
            } else if (transactionInput.value === 'TDS deduction') {
                transactionInput.value = '';
            }
        });
    });
</script>
@endsection
