@extends('admin.layouts.master')

@section('title', 'Edit Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Payment <span class="text-muted fs-6">#{{ $payment->id }}</span></h4>
    <a href="{{ route('admin.billing.payments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                    <span class="fw-semibold">Invoice: {{ $payment->invoice->invoice_number }}</span>
                    <span class="text-muted">—</span>
                    <span class="text-muted">{{ $payment->invoice->organisation->name ?? 'N/A' }}</span>
                </div>
            </div>
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

                <form action="{{ route('admin.billing.payments.update', $payment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Amount --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="amount"
                                    class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount', $payment->amount) }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @php
                                $paidExcludingThis = $payment->invoice->payments()->where('id', '!=', $payment->id)->sum('amount');
                                $remaining = $payment->invoice->total_amount - $paidExcludingThis;
                            @endphp
                            <small class="text-muted">Max allowed: ₹{{ number_format($remaining, 2) }}</small>
                        </div>

                        {{-- Date --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date"
                                class="form-control @error('payment_date') is-invalid @enderror"
                                value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Payment Method --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            @php
                                $modeMap = [
                                    'Bank Transfer' => 'bank_transfer',
                                    'UPI'           => 'upi',
                                    'Cash'          => 'cash',
                                    'Cheque'        => 'cheque',
                                    'TDS'           => 'tds',
                                ];
                                $currentMethod = $modeMap[$payment->payment_mode] ?? 'bank_transfer';
                            @endphp
                            <select name="payment_method" id="payment_method"
                                class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="bank_transfer" {{ old('payment_method', $currentMethod) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="upi"           {{ old('payment_method', $currentMethod) == 'upi'           ? 'selected' : '' }}>UPI</option>
                                <option value="cash"          {{ old('payment_method', $currentMethod) == 'cash'          ? 'selected' : '' }}>Cash</option>
                                <option value="cheque"        {{ old('payment_method', $currentMethod) == 'cheque'        ? 'selected' : '' }}>Cheque</option>
                                <option value="tds"           {{ old('payment_method', $currentMethod) == 'tds'           ? 'selected' : '' }}>TDS</option>
                                <option value="other"         {{ old('payment_method', $currentMethod) == 'other'         ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Transaction ID --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Transaction ID / Reference</label>
                            <input type="text" name="transaction_id" id="transaction_id"
                                class="form-control @error('transaction_id') is-invalid @enderror"
                                value="{{ old('transaction_id', $payment->transaction_id) }}"
                                placeholder="e.g. UTR or Cheque No">
                            @error('transaction_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="col-12 mb-4">
                            <label class="form-label fw-semibold">Internal Notes</label>
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $payment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.billing.payments.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Payment
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Info Sidebar --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 fw-semibold">
                <i class="fas fa-info-circle text-muted me-1"></i> Invoice Summary
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Invoice No.</td>
                        <td class="fw-bold text-end">{{ $payment->invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Client</td>
                        <td class="fw-bold text-end">{{ $payment->invoice->organisation->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Amount</td>
                        <td class="fw-bold text-end">₹{{ number_format($payment->invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Paid (other)</td>
                        <td class="text-success fw-bold text-end">₹{{ number_format($paidExcludingThis, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-muted">Max Editable Amount</td>
                        <td class="text-danger fw-bold text-end">₹{{ number_format($remaining, 2) }}</td>
                    </tr>
                </table>
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
            const txnInput = document.getElementById('transaction_id');
            if (method === 'tds') {
                if (!txnInput.value || txnInput.value.trim() === '') {
                    txnInput.value = 'TDS deduction';
                }
            } else if (txnInput.value === 'TDS deduction') {
                txnInput.value = '';
            }
        });
    });
</script>
@endsection
