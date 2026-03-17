@extends('admin.layouts.master')

@section('title', 'Edit Language')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Edit Language</h4>
        <p class="text-muted mb-0">Update course language details.</p>
    </div>
    <a href="{{ route('admin.languages.index') }}" class="btn btn-light border">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.languages.update', $language->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Language Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $language->title }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $language->sort_order }}" required>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" {{ $language->status ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active Status</label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Update Language
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
