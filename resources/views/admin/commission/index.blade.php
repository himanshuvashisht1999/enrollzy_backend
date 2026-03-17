@extends('admin.layouts.master')

@section('title', 'Commission Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Global Settings -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-globe me-2"></i> Global Platform Settings</h5>
                    <small class="opacity-75">Fallback commission for all experts</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.commission.global.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Commission Type</label>
                            <select name="commission_type" class="form-select">
                                <option value="percentage" {{ $globalPolicy->commission_type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="flat_fee" {{ $globalPolicy->commission_type == 'flat_fee' ? 'selected' : '' }}>Flat Fee (₹)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Value</label>
                            <input type="number" step="0.01" name="commission_value" class="form-control" value="{{ $globalPolicy->commission_value }}">
                        </div>
                        <hr>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="gst_applicable" value="1" {{ $globalPolicy->gst_applicable ? 'checked' : '' }}>
                            <label class="form-check-label">Applicable GST (18% on Fee)</label>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="tds_applicable" value="1" {{ $globalPolicy->tds_applicable ? 'checked' : '' }}>
                            <label class="form-check-label">Deduct TDS (10% on Net)</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Save Global Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Category Settings -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2"></i> Category-wise Logic</h5>
                    <small class="text-muted">Overrides global settings based on Expert Role</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Expert Role (Category)</th>
                                    <th>Commission</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($existingCategories as $category)
                                    @php
                                        // $category is now an Object of ExpertCategory model
                                        // KeyBy ID might be needed
                                        $policy = $categoryPolicies[$category->id] ?? null;
                                    @endphp
                                    <tr>
                                        <form action="{{ route('admin.commission.category.update') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="expert_category_id" value="{{ $category->id }}">
                                            <td>
                                                <div class="fw-bold">{{ $category->name }}</div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="max-width: 200px;">
                                                    <select name="commission_type" class="form-select bg-light">
                                                        <option value="percentage" {{ ($policy->commission_type ?? '') == 'percentage' ? 'selected' : '' }}>%</option>
                                                        <option value="flat_fee" {{ ($policy->commission_type ?? '') == 'flat_fee' ? 'selected' : '' }}>₹</option>
                                                    </select>
                                                    <input type="number" step="0.01" name="commission_value" class="form-control" value="{{ $policy->commission_value ?? '' }}" placeholder="Default">
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
