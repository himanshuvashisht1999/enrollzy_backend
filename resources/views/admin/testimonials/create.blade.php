@extends('admin.layouts.master')

@section('title', 'Add New Testimonial')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Testimonial Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('testimonials.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">User Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role / Position</label>
                        <input type="text" class="form-control @error('role') is-invalid @enderror" id="role" name="role" value="{{ old('role') }}" placeholder="e.g. Student, Alumni, Parent">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Testimonial Content</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="4" required placeholder="What did they say?">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rating" class="form-label">Rating (1-5)</label>
                            <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating">
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="image" class="form-label">User Photo</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Save Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="mt-3 text-center">
            <a href="{{ route('testimonials.index') }}" class="text-muted">← Back to List</a>
        </div>
    </div>
</div>
@endsection
