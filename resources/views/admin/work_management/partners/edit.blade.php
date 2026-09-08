@extends('admin.layouts.master')

@section('title', 'Edit Partner: ' . $partner->name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="m-0 fw-bold text-dark">
                            <i class="fas fa-edit text-primary me-2"></i> Edit Partner: {{ $partner->name }}
                        </h5>
                        <small class="text-muted">Update organization details, contact information and status</small>
                    </div>
                    <a href="{{ route('admin.work_management.partners.show', encrypt($partner->id)) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i> Back to Partner Hub
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.work_management.partners.update', encrypt($partner->id)) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Organization / Agency Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $partner->name) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Organization Type <span class="text-danger">*</span></label>
                                <select name="organization_type" class="form-select rounded-3" required>
                                    <option value="agency" {{ old('organization_type', $partner->organization_type) == 'agency' ? 'selected' : '' }}>Agency</option>
                                    <option value="contractor" {{ old('organization_type', $partner->organization_type) == 'contractor' ? 'selected' : '' }}>Contractor</option>
                                    <option value="consultant" {{ old('organization_type', $partner->organization_type) == 'consultant' ? 'selected' : '' }}>Consultant</option>
                                    <option value="vendor" {{ old('organization_type', $partner->organization_type) == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                    <option value="client" {{ old('organization_type', $partner->organization_type) == 'client' ? 'selected' : '' }}>Client</option>
                                    <option value="partner" {{ old('organization_type', $partner->organization_type) == 'partner' ? 'selected' : '' }}>Partner</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $partner->email) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $partner->phone) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Website</label>
                                <input type="url" name="website" class="form-control rounded-3" value="{{ old('website', $partner->website) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select rounded-3" required>
                                    <option value="active" {{ old('status', $partner->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $partner->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Office Address / Location</label>
                                <input type="text" name="address" class="form-control rounded-3" value="{{ old('address', $partner->address) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description & Service Notes</label>
                                <textarea name="description" class="form-control rounded-3" rows="3">{{ old('description', $partner->description) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.work_management.partners.show', encrypt($partner->id)) }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Partner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
