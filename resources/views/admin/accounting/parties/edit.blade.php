@extends('admin.layouts.master')

@section('title', 'Edit Party: ' . $party->name)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.accounting.parties.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Parties
        </a>
        <h3 class="fw-bold mb-1">Edit Party: {{ $party->name }}</h3>
        <p class="text-muted small mb-0">Update contact, address, and statutory information.</p>
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
            <form action="{{ route('admin.accounting.parties.update', $party->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Primary Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $party->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Legal Business Name</label>
                        <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name', $party->legal_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Primary Party Type <span class="text-danger">*</span></label>
                        <select name="party_type" class="form-select" required>
                            <option value="customer" {{ old('party_type', $party->party_type) == 'customer' ? 'selected' : '' }}>Customer (University/Student)</option>
                            <option value="vendor" {{ old('party_type', $party->party_type) == 'vendor' ? 'selected' : '' }}>Vendor (Supplier/Contractor)</option>
                            <option value="founder" {{ old('party_type', $party->party_type) == 'founder' ? 'selected' : '' }}>Founder</option>
                            <option value="investor" {{ old('party_type', $party->party_type) == 'investor' ? 'selected' : '' }}>Investor</option>
                            <option value="employee" {{ old('party_type', $party->party_type) == 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $party->email) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $party->phone) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">GSTIN</label>
                        <input type="text" name="gstin" class="form-control text-uppercase font-monospace" value="{{ old('gstin', $party->gstin) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">PAN Number</label>
                        <input type="text" name="pan" class="form-control text-uppercase font-monospace" value="{{ old('pan', $party->pan) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">State / Province</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $party->state) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">GST State Code</label>
                        <input type="text" name="state_code" class="form-control font-monospace" value="{{ old('state_code', $party->state_code) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Billing Address</label>
                        <textarea name="billing_address" class="form-control" rows="3">{{ old('billing_address', $party->billing_address) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Shipping / Operating Address</label>
                        <textarea name="shipping_address" class="form-control" rows="3">{{ old('shipping_address', $party->shipping_address) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status', $party->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $party->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label fw-bold d-block">Applicable Multi-Roles</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_customer" value="1" id="is_cust" {{ old('is_customer', $party->is_customer) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_cust">Is Customer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_vendor" value="1" id="is_vend" {{ old('is_vendor', $party->is_vendor) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_vend">Is Vendor</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_founder" value="1" id="is_fnd" {{ old('is_founder', $party->is_founder) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_fnd">Is Founder</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_investor" value="1" id="is_inv" {{ old('is_investor', $party->is_investor) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_inv">Is Investor</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.accounting.parties.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Party</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
