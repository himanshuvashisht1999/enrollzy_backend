@extends('admin.layouts.master')

@section('title', 'Edit Testimonial')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('testimonials.index') }}" class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-1"></i> Back to Testimonials
        </a>
        <h3 class="fw-bold mt-2">Edit Testimonial</h3>
    </div>

    <div class="row">
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('testimonials.update', $testimonial->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Expert (For whom this review is)</label>
                                <select name="expert_id" class="form-select @error('expert_id') is-invalid @enderror">
                                    <option value="">-- Select Expert --</option>
                                    @foreach($experts as $expert)
                                        <option value="{{ $expert->id }}" {{ old('expert_id', $testimonial->expert_id) == $expert->id ? 'selected' : '' }}>
                                            {{ $expert->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('expert_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mentee / Student Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $testimonial->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mentee Role / Position</label>
                                <input type="text" name="role" class="form-control @error('role') is-invalid @enderror" value="{{ old('role', $testimonial->role) }}" placeholder="e.g. Student, Alumni, Parent">
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rating (1-5 Stars)</label>
                                <select name="rating" class="form-select @error('rating') is-invalid @enderror">
                                    <option value="5" {{ old('rating', $testimonial->rating) == 5 ? 'selected' : '' }}>5 Stars ⭐⭐⭐⭐⭐</option>
                                    <option value="4" {{ old('rating', $testimonial->rating) == 4 ? 'selected' : '' }}>4 Stars ⭐⭐⭐⭐</option>
                                    <option value="3" {{ old('rating', $testimonial->rating) == 3 ? 'selected' : '' }}>3 Stars ⭐⭐⭐</option>
                                    <option value="2" {{ old('rating', $testimonial->rating) == 2 ? 'selected' : '' }}>2 Stars ⭐⭐</option>
                                    <option value="1" {{ old('rating', $testimonial->rating) == 1 ? 'selected' : '' }}>1 Star ⭐</option>
                                </select>
                                @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Testimonial Content</label>
                                <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="4" style="width: 100%; max-width: 100%; box-sizing: border-box; resize: vertical;" required>{{ old('content', $testimonial->content) }}</textarea>
                                @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Mentee Photo</label>
                                @if($testimonial->image)
                                    @php
                                        $photoUrl = str_starts_with($testimonial->image, 'http') ? $testimonial->image : asset($testimonial->image);
                                    @endphp
                                    <div class="mb-2">
                                        <img src="{{ $photoUrl }}" alt="{{ $testimonial->name }}" class="rounded-circle border p-1" style="width: 50px; height: 50px; object-fit: cover;" onError="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Mentee&background=4e73df&color=fff';">
                                    </div>
                                @endif
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <div class="form-text text-muted">Upload mentee profile image. Max size: 2MB.</div>
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                    <i class="fas fa-save me-1"></i> Update Testimonial
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
