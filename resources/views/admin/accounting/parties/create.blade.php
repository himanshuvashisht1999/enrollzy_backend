@extends('admin.layouts.master')

@section('title', 'Add New Party')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.accounting.parties.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Parties
        </a>
        <h3 class="fw-bold mb-1">Add New Entity / Party</h3>
        <p class="text-muted small mb-0">Create a customer, vendor, founder, or investor profile with statutory details.</p>
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
            <form action="{{ route('admin.accounting.parties.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Primary Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Oxford University / AWS India / John Doe" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Legal Business Name (for Invoices)</label>
                        <input type="text" name="legal_name" class="form-control" placeholder="e.g. Oxford International Education Ltd" value="{{ old('legal_name') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Primary Party Type <span class="text-danger">*</span></label>
                        <select name="party_type" class="form-select" required>
                            <option value="customer" {{ old('party_type') == 'customer' ? 'selected' : '' }}>Customer (University/Student)</option>
                            <option value="vendor" {{ old('party_type') == 'vendor' ? 'selected' : '' }}>Vendor (Supplier/Contractor)</option>
                            <option value="founder" {{ old('party_type') == 'founder' ? 'selected' : '' }}>Founder</option>
                            <option value="investor" {{ old('party_type') == 'investor' ? 'selected' : '' }}>Investor</option>
                            <option value="employee" {{ old('party_type') == 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="accounts@example.com" value="{{ old('email') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" value="{{ old('phone') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">GSTIN (15 Digits)</label>
                        <input type="text" name="gstin" class="form-control text-uppercase font-monospace" placeholder="07AAAAA0000A1Z5" value="{{ old('gstin') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">PAN Number (10 Digits)</label>
                        <input type="text" name="pan" class="form-control text-uppercase font-monospace" placeholder="AAAAA0000A" value="{{ old('pan') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">State / Province</label>
                        <input type="text" name="state" class="form-control" placeholder="e.g. Delhi, Maharashtra, Karnataka" value="{{ old('state') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">GST State Code</label>
                        <input type="text" name="state_code" class="form-control font-monospace" placeholder="e.g. 07 (Delhi), 27 (Maharashtra)" value="{{ old('state_code') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Billing Address</label>
                        <textarea name="billing_address" class="form-control" rows="3" placeholder="Full postal billing address...">{{ old('billing_address') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Shipping / Operating Address</label>
                        <textarea name="shipping_address" class="form-control" rows="3" placeholder="Shipping or office branch address...">{{ old('shipping_address') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Opening Balance (₹)</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', '0.00') }}">
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label fw-bold d-block">Applicable Multi-Roles</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_customer" value="1" id="is_cust" {{ old('is_customer') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_cust">Is Customer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_vendor" value="1" id="is_vend" {{ old('is_vendor') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_vend">Is Vendor</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_founder" value="1" id="is_fnd" {{ old('is_founder') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_fnd">Is Founder</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_investor" value="1" id="is_inv" {{ old('is_investor') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_inv">Is Investor</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.accounting.parties.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Party</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
