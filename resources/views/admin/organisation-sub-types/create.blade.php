@extends('admin.layouts.master')

@section('title', 'Add Organisation Sub-Type')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold">Add Organisation Sub-Type</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.organisation-types.index') }}">Organisation Types</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.organisation-sub-types.index') }}">Sub-Types</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.organisation-sub-types.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Parent Organisation Type <span class="text-danger">*</span></label>
                        <select name="organisation_type_id" class="form-select @error('organisation_type_id') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            @foreach($organisationTypes as $type)
                                <option value="{{ $type->id }}" {{ (old('organisation_type_id') ?? $selectedTypeId) == $type->id ? 'selected' : '' }}>
                                    {{ $type->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('organisation_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Sub-Type Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. High School, Primary School" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">Save Sub-Type</button>
                        <a href="{{ route('admin.organisation-sub-types.index', ['organisation_type_id' => $selectedTypeId]) }}" class="btn btn-light border px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
