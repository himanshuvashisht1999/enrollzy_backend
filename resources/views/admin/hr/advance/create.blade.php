@extends('admin.layouts.master')

@section('title', 'New Advance/Penalty Transaction')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-gradient-primary text-white py-4 px-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                    <div>
                        <h4 class="mb-1 fw-bold text-white"><i class="fas fa-hand-holding-usd me-2"></i>New Transaction</h4>
                        <p class="mb-0 text-white-50 small">Record a new advance, penalty, or bonus for a staff member.</p>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.hr.advance.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <!-- Section: Employee & Type -->
                            <div class="col-12 border-bottom pb-2 mb-2">
                                <h6 class="text-uppercase text-secondary fw-bold small">Transaction Context</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-600 text-dark small">Staff Member <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fas fa-user-tie text-muted"></i></span>
                                    <select name="staff_id" class="form-select border-start-0 rounded-end-3 shadow-none" required>
                                        <option value="">Select Employee</option>
                                        @foreach($staff as $s)
                                            <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>
                                                {{ $s->name }} ({{ $s->designation->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-600 text-dark small">Transaction For <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fas fa-tag text-muted"></i></span>
                                    <select name="paying_for" class="form-select border-start-0 rounded-end-3 shadow-none" required id="paying_for">
                                        <option value="advance" {{ old('paying_for') == 'advance' ? 'selected' : '' }}>Advanced Pay</option>
                                        <option value="penalty" {{ old('paying_for') == 'penalty' ? 'selected' : '' }}>Penalty / Fine</option>
                                        <option value="bonus" {{ old('paying_for') == 'bonus' ? 'selected' : '' }}>Bonus</option>
                                        <option value="settlement" {{ old('paying_for') == 'settlement' ? 'selected' : '' }}>Settlement</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Section: Financial Details -->
                            <div class="col-12 border-bottom pb-2 mb-2 mt-4">
                                <h6 class="text-uppercase text-secondary fw-bold small">Financial Details</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-600 text-dark small">Direction <span class="text-danger">*</span></label>
                                <select name="txn_type" class="form-select rounded-3 shadow-none" required>
                                    <option value="debit" {{ old('txn_type') == 'debit' ? 'selected' : '' }}>Outgoing (Debit)</option>
                                    <option value="credit" {{ old('txn_type') == 'credit' ? 'selected' : '' }}>Incoming/Refund (Credit)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-600 text-dark small">Amount ({{ env('CURRENCY', '₹') }}) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3 text-secondary fw-bold">{{ env('CURRENCY', '₹') }}</span>
                                    <input type="number" step="0.01" name="amount" class="form-control border-start-0 rounded-end-3 shadow-none" required placeholder="0.00" value="{{ old('amount') }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-600 text-dark small">Bank Charges</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3 text-secondary">{{ env('CURRENCY', '₹') }}</span>
                                    <input type="number" step="0.01" name="bank_charges" class="form-control border-start-0 rounded-end-3 shadow-none" value="{{ old('bank_charges', '0.00') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-600 text-dark small">Payment Source <span class="text-danger">*</span></label>
                                <select name="debit_account" class="form-select rounded-3 shadow-none" required>
                                    <option value="company_cash" {{ old('debit_account') == 'company_cash' ? 'selected' : '' }}>Company Cash</option>
                                    <option value="bank_account" {{ old('debit_account', 'bank_account') == 'bank_account' ? 'selected' : '' }}>Bank Account</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-600 text-dark small">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select rounded-3 shadow-none" required>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="upi" {{ old('payment_method', 'upi') == 'upi' ? 'selected' : '' }}>UPI / Online</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-600 text-dark small">Initiation Date <span class="text-danger">*</span></label>
                                <input type="date" name="initiation_date" class="form-control rounded-3 shadow-none" value="{{ old('initiation_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-600 text-dark small">Clearance Date <span class="text-danger">*</span></label>
                                <input type="date" name="clearance_date" class="form-control rounded-3 shadow-none" value="{{ old('clearance_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-600 text-dark small">Transaction ID / Reference <span class="text-danger">*</span></label>
                                <input type="text" name="txn_id" class="form-control rounded-3 shadow-none" placeholder="Ref No, Cheque No, or N/A" required value="{{ old('txn_id') }}">
                                <div class="form-text text-muted small">Required for internal auditing and record keeping.</div>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label fw-600 text-dark small">Internal Log / Notes</label>
                                <textarea name="data" class="form-control rounded-3 shadow-none" rows="3" placeholder="Describe the transaction purpose or any internal details...">{{ old('data') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-5 pt-3 border-top">
                            <a href="{{ route('admin.hr.advance.index') }}" class="btn btn-outline-secondary px-4 rounded-pill fw-bold transition-all"><i class="fas fa-times me-2"></i>Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold shadow-sm transition-all py-2 hover-lift">
                                <i class="fas fa-check-circle me-2"></i>Save Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-600 { font-weight: 600; }
    .transition-all { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-2px); }
    .form-select, .form-control { border: 1px solid #e0e0e0; }
    .form-select:focus, .form-control:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1); }
</style>
@endsection

