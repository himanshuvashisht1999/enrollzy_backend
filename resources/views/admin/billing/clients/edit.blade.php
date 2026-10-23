@extends('admin.layouts.master')

@section('title', 'Edit Billing Client - ' . $client->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-dark fw-bold">Edit Billing Client</h4>
        <p class="text-muted small mb-0">Update client details, tax information, or address.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.billing.clients.show', $client->id) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i> View Profile
        </a>
        <a href="{{ route('admin.billing.clients.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Clients
        </a>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</div>
    <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form action="{{ route('admin.billing.clients.update', $client->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Company / Client Profile -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-building me-2"></i> Company & Client Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Client / Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $client->name) }}" placeholder="e.g. Acme Tech Solutions Pvt Ltd" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Company Type</label>
                            <select name="company_type" class="form-select @error('company_type') is-invalid @enderror">
                                <option value="">Select Company Type</option>
                                @foreach($companyTypes as $type)
                                    <option value="{{ $type }}" {{ old('company_type', $client->company_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('company_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax & Legal Identifiers -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-file-invoice me-2"></i> Tax & Legal Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">GSTIN Number</label>
                            <input type="text" name="gstin" class="form-control text-uppercase @error('gstin') is-invalid @enderror" value="{{ old('gstin', $client->gstin) }}" placeholder="e.g. 07AAAAA0000A1Z5" maxlength="25">
                            @error('gstin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">15-digit GST ID</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">TAN Number</label>
                            <input type="text" name="tan_number" class="form-control text-uppercase @error('tan_number') is-invalid @enderror" value="{{ old('tan_number', $client->tan_number) }}" placeholder="e.g. DELA12345E" maxlength="25">
                            @error('tan_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Tax Deduction A/C No</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control text-uppercase @error('pan_number') is-invalid @enderror" value="{{ old('pan_number', $client->pan_number) }}" placeholder="e.g. ABCDE1234F" maxlength="25">
                            @error('pan_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Permanent Account No</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">CIN Number</label>
                            <input type="text" name="cin_number" class="form-control text-uppercase @error('cin_number') is-invalid @enderror" value="{{ old('cin_number', $client->cin_number) }}" placeholder="e.g. U72900DL2020PTC123456" maxlength="50">
                            @error('cin_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Corporate Identity No</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-address-book me-2"></i> Contact Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $client->contact_person) }}" placeholder="e.g. John Doe">
                            @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $client->email) }}" placeholder="e.g. billing@acme.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $client->phone) }}" placeholder="e.g. +91 9876543210">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address & Location -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-map-marker-alt me-2"></i> Address & Location
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Billing Address</label>
                            <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Full street address, suite / unit / floor...">{{ old('address', $client->address) }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $client->city) }}" placeholder="e.g. New Delhi">
                            @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">State</label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $client->state) }}" placeholder="e.g. Delhi">
                            @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Pincode / Postal Code</label>
                            <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode', $client->pincode) }}" placeholder="e.g. 110001">
                            @error('pincode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Country</label>
                            <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $client->country ?? 'India') }}" placeholder="e.g. India">
                            @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Settings -->
        <div class="col-lg-4">
            <!-- Status & Notes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-dark">
                        <i class="fas fa-sliders-h me-2"></i> Settings & Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Account Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1" {{ old('status', $client->status) ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="statusSwitch">Active Client</label>
                        </div>
                        <div class="form-text small">Inactive clients will be hidden from invoice generation by default.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Internal Notes / Remarks</label>
                        <textarea name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Optional notes for internal billing reference...">{{ old('notes', $client->notes) }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fas fa-save me-1"></i> Update Billing Client
                    </button>
                </div>
            </div>

            <!-- Meta info -->
            <div class="card border-0 shadow-sm">
                <div class="card-body small text-muted">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span>Created:</span>
                        <span class="text-dark">{{ $client->created_at ? $client->created_at->format('d M Y, h:i A') : '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 pt-2">
                        <span>Last Updated:</span>
                        <span class="text-dark">{{ $client->updated_at ? $client->updated_at->format('d M Y, h:i A') : '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
