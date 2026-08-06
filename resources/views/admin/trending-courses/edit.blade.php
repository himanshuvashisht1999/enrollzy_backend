@extends('admin.layouts.master')

@section('title', 'Edit Trending Course')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Trending Course: {{ $trendingCourse->name }}</h4>
        <p class="text-muted small mb-0">Update course details, instructor, price, rating, and link.</p>
    </div>
    <a href="{{ route('admin.trending-courses.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.trending-courses.update', $trendingCourse->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Course Title <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $trendingCourse->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Instructor / Provider</label>
                    <input type="text" name="instructor" class="form-control" value="{{ old('instructor', $trendingCourse->instructor) }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Price / Fees</label>
                    <input type="text" name="price" class="form-control" value="{{ old('price', $trendingCourse->price) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Rating</label>
                    <input type="text" name="rating" class="form-control" value="{{ old('rating', $trendingCourse->rating) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Course Detail URL</label>
                    <input type="text" name="url" class="form-control" value="{{ old('url', $trendingCourse->url) }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sort Order <span class="text-danger">*</span></label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $trendingCourse->sort_order) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" {{ old('status', $trendingCourse->status) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $trendingCourse->status) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.trending-courses.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Update Course</button>
            </div>
        </form>
    </div>
</div>
@endsection
