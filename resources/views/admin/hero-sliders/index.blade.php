@extends('admin.layouts.master')

@section('title', 'Hero Sliders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Hero Images Slider</h3>
        <p class="text-muted">Upload and manage rotating images for the homepage hero section.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
        <i class="fas fa-plus me-2"></i>Add New Image
    </button>
</div>

<div class="row g-4">
    @forelse($sliders as $slider)
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm overflow-hidden">
                <div class="position-relative">
                    <img src="{{ asset($slider->image_path) }}" class="card-img-top" alt="Hero Image" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute top-0 end-0 p-2">
                        <span class="badge {{ $slider->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $slider->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Order: {{ $slider->sort_order }}</small>
                        <div class="btn-group">
                            <form action="{{ route('admin.hero-sliders.destroy', $slider) }}" method="POST" onsubmit="return confirm('Delete this image?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-images fa-3x text-light mb-3"></i>
                    <p class="text-muted">No images added to the slider yet.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Add Image Modal -->
<div class="modal fade" id="addImageModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.hero-sliders.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold">Add Hero Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Image</label>
                        <input type="file" name="image_path" class="form-control" required>
                        <div class="form-text">Recommended size: 1200x800px. Max 5MB.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Upload Image</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
