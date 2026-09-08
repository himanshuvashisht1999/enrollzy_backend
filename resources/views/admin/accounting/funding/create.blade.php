@extends('admin.layouts.master')

@section('title', 'Record Funding / Capital Infusion')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.accounting.funding.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Funding
        </a>
        <h3 class="fw-bold mb-1">Record Capital Infusion / Investment</h3>
        <p class="text-muted small mb-0">Record Founder capital contributions or third-party venture funding into company bank accounts.</p>
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
            <form action="{{ route('admin.accounting.funding.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Investor / Founder Party <span class="text-danger">*</span></label>
                        <select name="party_id" class="form-select" required>
                            <option value="">Select Contributor</option>
                            @foreach($parties as $p)
                                <option value="{{ $p->id }}" {{ old('party_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ strtoupper($p->party_type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Funding Category / Instrument <span class="text-danger">*</span></label>
                        <select name="funding_type" class="form-select" required>
                            <option value="founder_capital" {{ old('funding_type') == 'founder_capital' ? 'selected' : '' }}>Founder Capital (Core Equity)</option>
                            <option value="founder_current" {{ old('funding_type') == 'founder_current' ? 'selected' : '' }}>Founder Current Account / Temporary Advance</option>
                            <option value="equity_investment" {{ old('funding_type') == 'equity_investment' ? 'selected' : '' }}>3rd-Party Equity Share Capital</option>
                            <option value="preference_shares" {{ old('funding_type') == 'preference_shares' ? 'selected' : '' }}>Compulsorily Convertible Preference Shares (CCPS)</option>
                            <option value="convertible_note" {{ old('funding_type') == 'convertible_note' ? 'selected' : '' }}>Convertible Note</option>
                            <option value="unsecured_loan" {{ old('funding_type') == 'unsecured_loan' ? 'selected' : '' }}>Unsecured Founder/Director Loan</option>
                            <option value="secured_loan" {{ old('funding_type') == 'secured_loan' ? 'selected' : '' }}>Bank / Institutional Loan</option>
                            <option value="grant" {{ old('funding_type') == 'grant' ? 'selected' : '' }}>Government / Startup Grant</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" value="{{ old('amount') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Received Date <span class="text-danger">*</span></label>
                        <input type="date" name="received_date" class="form-control" value="{{ old('received_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Deposit To Bank Account <span class="text-danger">*</span></label>
                        <select name="deposit_bank_account_id" class="form-select" required>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}" {{ old('deposit_bank_account_id') == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->account_name }} ({{ $bank->bank_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Credit to Equity / Liability Account (COA) <span class="text-danger">*</span></label>
                        <select name="equity_ledger_account_id" class="form-select" required>
                            @foreach($equityAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('equity_ledger_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_code }} - {{ $acc->name }} ({{ strtoupper($acc->account_type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bank Reference / UTR Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. UTR / Wire Ref Number" value="{{ old('reference_number') }}">
                    </div>

                    <!-- Investor Cap Table Optional Details -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Equity % Diluted (Optional)</label>
                        <input type="number" step="0.01" name="equity_percentage" class="form-control" placeholder="e.g. 5.00" value="{{ old('equity_percentage') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Post-Money Valuation (₹)</label>
                        <input type="number" step="0.01" name="valuation" class="form-control" placeholder="e.g. 50000000" value="{{ old('valuation') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Shares Issued (Optional)</label>
                        <input type="number" step="0.01" name="shares_issued" class="form-control" placeholder="e.g. 1000" value="{{ old('shares_issued') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Share Price / Face Value (₹)</label>
                        <input type="number" step="0.01" name="share_price" class="form-control" placeholder="e.g. 100.00" value="{{ old('share_price') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Notes / Shareholder Agreement Summary</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Term sheet, SHA clause, or remarks...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('admin.accounting.funding.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary">
                        <i class="fas fa-save me-1"></i> Save as Draft
                    </button>
                    <button type="submit" name="action" value="save_and_post" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Save & Post to Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
