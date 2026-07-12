@extends('admin.layouts.master')

@section('title', 'Edit Billing Service')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Billing Service</h4>
    <a href="{{ route('admin.billing.services.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.billing.services.update', $service->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Service Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">HSN/SAC Code</label>
                    <input type="text" name="hsn_sac_code" class="form-control @error('hsn_sac_code') is-invalid @enderror" value="{{ old('hsn_sac_code', $service->hsn_sac_code) }}">
                    @error('hsn_sac_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Regular Price <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $service->price) }}" required>
                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sale Price</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $service->sale_price) }}">
                    @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <!-- Hidden input to ensure 0 is sent when unchecked -->
                        <input type="hidden" name="status" value="0">
                        <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1" {{ old('status', $service->status) ? 'checked' : '' }}>
                        <label class="form-check-label" for="statusSwitch">Active Status</label>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Update Service</button>
            </div>
        </form>
    </div>
</div>
@endsection
