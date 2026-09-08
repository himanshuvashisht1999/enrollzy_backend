@extends('admin.layouts.master')

@section('title', 'Add Bank Account')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.accounting.banking.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Banking
        </a>
        <h3 class="fw-bold mb-1">Add Bank Account / Cash Register</h3>
        <p class="text-muted small mb-0">Link a company bank account or cash ledger to your accounting system.</p>
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
            <form action="{{ route('admin.accounting.banking.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Account Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control" placeholder="e.g. HDFC Bank Primary Current A/c" value="{{ old('account_name') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bank Name / Institution <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control" placeholder="e.g. HDFC Bank Ltd / Petty Cash" value="{{ old('bank_name') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Account Number / Identifier <span class="text-danger">*</span></label>
                        <input type="text" name="account_number_masked" class="form-control font-monospace" placeholder="e.g. XXXX-XXXX-4589" value="{{ old('account_number_masked') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">IFSC Code</label>
                        <input type="text" name="ifsc" class="form-control font-monospace text-uppercase" placeholder="e.g. HDFC0001234" value="{{ old('ifsc') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Branch Name</label>
                        <input type="text" name="branch" class="form-control" placeholder="e.g. Connaught Place, New Delhi" value="{{ old('branch') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Opening Balance (₹)</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" placeholder="0.00" value="{{ old('opening_balance', '0.00') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Linked General Ledger Account (COA)</label>
                        <select name="ledger_account_id" class="form-select">
                            <option value="">— Auto Create New Sub-Account under 1120 —</option>
                            @foreach($ledgerAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('ledger_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_code }} - {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('admin.accounting.banking.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Bank Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
