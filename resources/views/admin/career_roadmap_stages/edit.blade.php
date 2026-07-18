@extends('admin.layouts.master')

@section('title', 'Edit Career Roadmap Stage')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Career Roadmap Stage</h4>
    <a href="{{ route('admin.career-roadmap-stages.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.career-roadmap-stages.update', $careerRoadmapStage->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="" disabled>Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $careerRoadmapStage->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $careerRoadmapStage->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label fw-bold">Image</label>
                        @if($careerRoadmapStage->image)
                            <div class="mb-2">
                                <img src="{{ asset($careerRoadmapStage->image) }}" alt="Current Image" class="rounded border" style="max-height: 100px;">
                            </div>
                        @endif
                        <input class="form-control @error('image') is-invalid @enderror" type="file" id="image" name="image" accept="image/*">
                        <small class="text-muted">Leave empty to keep the current image.</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $careerRoadmapStage->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" {{ old('status', $careerRoadmapStage->status) ? 'checked' : '' }} value="1">
                        <label class="form-check-label" for="status">Active Status</label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">Update Stage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
