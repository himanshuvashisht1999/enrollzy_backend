@extends('admin.layouts.master')

@section('title', 'Edit Page')

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('admin.pages.update', $page->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Edit Page: {{ $page->title }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Changes</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-12">
                    <!-- Main Content Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label for="title" class="form-label fw-bold">Page Title</label>
                                <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $page->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="content" class="form-label fw-bold">Page Content</label>
                                <textarea class="form-control editor" name="content" id="editor">{{ old('content', $page->content) }}</textarea>
                                @error('content')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <!-- SEO & Settings Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-cog me-2"></i> Settings & SEO</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold d-block">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $page->status) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">Active (Visible to public)</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4 bg-light p-3 rounded border">
                                    <label class="form-label fw-bold small text-muted">Page URL (Slug)</label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text bg-white text-muted">/page/</span>
                                        <input type="text" class="form-control bg-white" value="{{ $page->slug }}" disabled>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="update_slug" id="update_slug" value="1">
                                        <label class="form-check-label small" for="update_slug">Auto-generate new slug from title</label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="meta_title" class="form-label text-muted small fw-bold">Meta Title</label>
                                    <input type="text" class="form-control" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" placeholder="Optimized title...">
                                </div>
                                
                                <div class="col-md-8 mb-3">
                                    <label for="meta_keywords" class="form-label text-muted small fw-bold">Meta Keywords</label>
                                    <textarea class="form-control" name="meta_keywords" rows="2" placeholder="Keyword 1, Keyword 2...">{{ old('meta_keywords', $page->meta_keywords) }}</textarea>
                                </div>
                                
                                <div class="col-md-12 mb-0">
                                    <label for="meta_description" class="form-label text-muted small fw-bold">Meta Description</label>
                                    <textarea class="form-control" name="meta_description" rows="3" placeholder="Brief description for search engines...">{{ old('meta_description', $page->meta_description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    if (typeof initializeTinyMCE === 'function') {
        initializeTinyMCE('.editor');
    }
</script>
@endpush
@endsection
