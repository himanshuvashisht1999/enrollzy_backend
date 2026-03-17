@extends('admin.layouts.master')

@section('title', 'Add Program Type')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Add Program Type</h4>
    </div>
    <a href="{{ route('admin.program-types.index') }}" class="btn btn-light border">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.program-types.store') }}" method="POST">
            @csrf
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold d-block">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Save Item
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
