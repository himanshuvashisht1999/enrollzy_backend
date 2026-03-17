@extends('admin.layouts.master')

@section('title', 'Edit Specialized Course')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.home-services.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.home-services.update', $homeService->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $homeService->title }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $homeService->sort_order }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1" {{ $homeService->status ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$homeService->status ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ $homeService->description }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Footer Text (Highlight Line)</label>
                    <input type="text" name="footer_text" class="form-control" value="{{ $homeService->footer_text }}">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Update Course</button>
            </div>
        </form>
    </div>
</div>
@endsection
