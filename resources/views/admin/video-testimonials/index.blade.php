@extends('admin.layouts.master')

@section('title', 'Video Testimonials')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Video Testimonials</h3>
        <p class="text-muted">Manage student video stories and testimonials for the home page.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
        <i class="fas fa-plus me-2"></i>Add New Video
    </button>
</div>

<div class="row g-4">
    @forelse($testimonials as $item)
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm overflow-hidden testimonial-card">
                <div class="position-relative">
                    @if($item->thumbnail)
                        <img src="{{ asset($item->thumbnail) }}" class="card-img-top" alt="{{ $item->name }}" style="height: 180px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="fas fa-video fa-3x text-light"></i>
                        </div>
                    @endif
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <a href="{{ $item->video_url }}" target="_blank" class="btn btn-white rounded-circle shadow">
                            <i class="fas fa-play text-primary"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-1">{{ $item->name }}</h6>
                    <p class="text-muted small mb-2">{{ $item->course }}</p>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <small class="text-muted">Order: {{ $item->sort_order }}</small>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal{{ $item->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.video-testimonials.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this testimonial?')">
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

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('admin.video-testimonials.update', $item) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-light">
                            <h5 class="modal-title fw-bold">Edit Video Testimonial</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Student Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Course/Designation</label>
                                <input type="text" name="course" class="form-control" value="{{ $item->course }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Video URL (YouTube/Vimeo)</label>
                                <input type="url" name="video_url" class="form-control" value="{{ $item->video_url }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Replace Thumbnail (Optional)</label>
                                <input type="file" name="thumbnail" class="form-control">
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ $item->sort_order }}">
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Update Details</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="card border-0 shadow-sm p-5">
                <i class="fas fa-video fa-4x text-light mb-3"></i>
                <h5 class="text-muted">No video testimonials found.</h5>
                <p class="text-muted">Click the "Add New Video" button to get started.</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Add Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.video-testimonials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold">Add Video Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Course/Designation</label>
                        <input type="text" name="course" class="form-control" placeholder="e.g. MBA Graduate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Video URL</label>
                        <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Thumbnail Image</label>
                        <input type="file" name="thumbnail" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Testimonial</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .testimonial-card .play-btn-overlay {
        background: rgba(0,0,0,0.3);
        opacity: 0;
        transition: 0.3s;
    }
    .testimonial-card:hover .play-btn-overlay {
        opacity: 1;
    }
    .btn-white {
        background: #fff;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
