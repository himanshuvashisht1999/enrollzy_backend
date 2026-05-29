@extends('admin.layouts.master')

@section('title', 'Edit Header Link')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i> Edit Header Link</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.header-links.update', $headerLink->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $headerLink->title) }}" required>
                        @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">URL</label>
                        <input type="text" name="url" class="form-control" value="{{ old('url', $headerLink->url) }}" placeholder="e.g. /organisations or https://example.com">
                        @error('url') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $headerLink->sort_order) }}">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ $headerLink->status ? 'checked' : '' }}>
                                <label class="form-check-label fs-6 ms-2 mt-1" for="status">Active Status</label>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.header-links.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
