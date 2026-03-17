@extends('admin.layouts.master')

@section('title', 'Edit Blog Post')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <form action="{{ route('blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Edit Post</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('blogs.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Post</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Main Content Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label for="title" class="form-label fw-bold">Post Title</label>
                                <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $blog->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="content" class="form-label fw-bold">Post Content</label>
                                <textarea class="form-control" name="content" id="editor">{{ old('content', $blog->content) }}</textarea>
                                @error('content')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="excerpt" class="form-label fw-bold">Short Excerpt</label>
                                <textarea class="form-control" name="excerpt" rows="3">{{ old('excerpt', $blog->excerpt) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-search me-2"></i> SEO Settings</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="meta_title" class="form-label">Meta Title</label>
                                <input type="text" class="form-control" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}" placeholder="Optimize for search engines...">
                            </div>
                            <div class="mb-3">
                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                <textarea class="form-control" name="meta_keywords" rows="2" placeholder="keyword1, keyword2, ...">{{ old('meta_keywords', $blog->meta_keywords) }}</textarea>
                            </div>
                            <div class="mb-0">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <textarea class="form-control" name="meta_description" rows="3" placeholder="Compelling description for search results...">{{ old('meta_description', $blog->meta_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Publishing Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">Publishing Info</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="category_id" class="form-label fw-bold">Category</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" name="category_id">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $blog->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="author" class="form-label fw-bold">Author Name</label>
                                <input type="text" class="form-control" name="author" value="{{ old('author', $blog->author) }}">
                            </div>

                            <div class="mb-0">
                                <label for="published_at" class="form-label fw-bold">Publish Date</label>
                                <input type="date" class="form-control" name="published_at" value="{{ old('published_at', $blog->published_at ? date('Y-m-d', strtotime($blog->published_at)) : '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Media Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">Featured Image</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-0">
                                <input type="file" class="form-control @error('image') is-invalid @enderror" name="image" id="imageInput">
                                <div id="imagePreview" class="mt-3 text-center">
                                    @if($blog->image)
                                        <img src="{{ asset($blog->image) }}" class="img-fluid rounded border p-1" style="max-height: 200px;">
                                    @else
                                        <div class="text-muted small">No image uploaded</div>
                                    @endif
                                </div>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });

    // Image Preview
    document.getElementById('imageInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            const preview = document.querySelector('#imagePreview');
            preview.innerHTML = '<img src="" class="img-fluid rounded border p-1" style="max-height: 200px;">';
            preview.querySelector('img').src = URL.createObjectURL(file);
        }
    };
</script>
<style>
    .ck-editor__editable { min-height: 300px; }
</style>
@endpush

@endsection
