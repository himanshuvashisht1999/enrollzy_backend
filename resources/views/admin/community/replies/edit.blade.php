@extends('admin.layouts.master')

@section('title', 'Edit Community Reply')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.community-replies.index') }}" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
    <h3 class="fw-bold mt-2">Edit Community Reply</h3>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.community-replies.update', $reply->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reply Content</label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="6" required>{{ old('content', $reply->content) }}</textarea>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Moderation Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $reply->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $reply->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $reply->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Enable/Disable</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ $reply->is_active ? 'selected' : '' }}>Active (Visible)</option>
                                <option value="0" {{ !$reply->is_active ? 'selected' : '' }}>Disabled (Hidden)</option>
                            </select>
                        </div>
                    </div>

                    @if($reply->image)
                    <div class="mb-4">
                        <label class="form-label fw-bold">Current Image</label>
                        <div class="d-block pt-2">
                            <img src="{{ env('APP_ENV') == 'local' ? 'http://127.0.0.1:8000' : 'https://enrollzy.com' }}/{{ ltrim($reply->image, '/') }}" class="img-fluid rounded border" style="max-height: 300px;">
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4">Update Reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">User Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-1 text-muted small text-uppercase">Replied By</p>
                <p class="fw-bold fs-5 mb-3">{{ $reply->user->name ?? 'Unknown User' }}</p>
                
                <p class="mb-1 text-muted small text-uppercase">Replied On</p>
                <p class="mb-0">{{ $reply->created_at->format('M d, Y - h:i A') }}</p>
            </div>
        </div>
        
        @if($reply->question)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">In Response To</h6>
            </div>
            <div class="card-body">
                <p class="fw-bold mb-2">{{ Str::limit($reply->question->question_text, 150) }}</p>
                <a href="{{ route('admin.community-questions.edit', $reply->question_id) }}" class="btn btn-sm btn-outline-dark">View Question</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
