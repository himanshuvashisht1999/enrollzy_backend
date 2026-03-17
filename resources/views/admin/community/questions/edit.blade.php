@extends('admin.layouts.master')

@section('title', 'Edit Community Question')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.community-questions.index') }}" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
    <h3 class="fw-bold mt-2">Edit Community Question</h3>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.community-questions.update', $question->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Question Text</label>
                        <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror" rows="6" required>{{ old('question_text', $question->question_text) }}</textarea>
                        @error('question_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $question->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Verification Status</label>
                            <select name="is_verified" class="form-select">
                                <option value="1" {{ $question->is_verified ? 'selected' : '' }}>Verified (Visible)</option>
                                <option value="0" {{ !$question->is_verified ? 'selected' : '' }}>Pending Approval</option>
                            </select>
                        </div>
                    </div>

                    @if($question->image)
                    <div class="mb-4">
                        <label class="form-label fw-bold">Current Image</label>
                        <div class="d-block pt-2">
                            <img src="{{ asset($question->image) }}" class="img-fluid rounded border" style="max-height: 300px;">
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4">Update Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">User Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-1 text-muted small uppercase">Asked By</p>
                <p class="fw-bold fs-5 mb-3">{{ $question->user->name ?? 'Unknown User' }}</p>
                
                <p class="mb-1 text-muted small uppercase">Asked On</p>
                <p class="mb-0">{{ $question->created_at->format('M d, Y - h:i A') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
