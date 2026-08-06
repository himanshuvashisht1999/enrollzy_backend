@extends('admin.layouts.master')

@section('title', 'Add New Stream Tab')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Add New Stream Tab</h4>
        <p class="text-muted small mb-0">Create a new stream tab for the Leading Universities homepage section.</p>
    </div>
    <a href="{{ route('admin.homepage-stream-tabs.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.homepage-stream-tabs.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stream Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Engineering & IT" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Slug / Key (Unique Identifier)</label>
                    <input type="text" name="key" class="form-control @error('key') is-invalid @enderror" value="{{ old('key') }}" placeholder="e.g. engineering (auto-generated if empty)">
                    <small class="text-muted">Will be used in tab link (#tab-key)</small>
                    @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Search Keywords (Comma Separated)</label>
                <textarea name="keywords" class="form-control" rows="2" placeholder="e.g. med, health, pharma, nursing, dental, ayurved">{{ old('keywords') }}</textarea>
                <small class="text-muted">Universities in DB matching these keywords will be automatically filtered under this tab.</small>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Important Exams (Comma Separated)</label>
                    <textarea name="default_exams" class="form-control" rows="3" placeholder="e.g. NEET UG, NEET PG, AIIMS, JIPMER">{{ old('default_exams') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Top States (Comma Separated)</label>
                    <textarea name="default_states" class="form-control" rows="3" placeholder="e.g. Maharashtra, Karnataka, Tamil Nadu, Delhi">{{ old('default_states') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Related Courses (Comma Separated)</label>
                    <textarea name="default_courses" class="form-control" rows="3" placeholder="e.g. MBBS, BDS, B.Sc Nursing, BAMS">{{ old('default_courses') }}</textarea>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sort Order <span class="text-danger">*</span></label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 10) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.homepage-stream-tabs.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save Stream Tab</button>
            </div>
        </form>
    </div>
</div>
@endsection
