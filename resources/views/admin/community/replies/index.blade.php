@extends('admin.layouts.master')

@section('title', 'Moderate Community Replies')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Community Answers / Replies</h3>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.community-replies.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search replies..." value="{{ request('search') }}">
            </div>
            <div class="col-md-5">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Reply Content</th>
                        <th>Question</th>
                        <th>User</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($replies as $reply)
                    <tr>
                        <td class="ps-4">
                            <span class="d-block">{{ Str::limit($reply->content, 80) }}</span>
                            @if($reply->image)
                                <a href="{{ env('APP_ENV') == 'local' ? 'http://127.0.0.1:8000' : 'https://enrollzy.com' }}/{{ ltrim($reply->image, '/') }}" target="_blank" class="text-primary small mt-1 d-inline-block">View Image</a>
                            @endif
                        </td>
                        <td>
                            @if($reply->question)
                                <a href="{{ route('admin.community-questions.edit', $reply->question_id) }}" class="text-decoration-none">
                                    {{ Str::limit($reply->question->question_text, 50) }}
                                </a>
                            @else
                                <span class="text-muted">Deleted Question</span>
                            @endif
                        </td>
                        <td>{{ $reply->user->name ?? 'Deleted User' }}</td>
                        <td>
                            @if($reply->status == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($reply->status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                            
                            @if($reply->is_active)
                                <span class="badge bg-info">Active</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <form action="{{ route('admin.community-replies.toggle-active', $reply->id) }}" method="POST" class="d-inline" title="Toggle Enable/Disable">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $reply->is_active ? 'btn-secondary' : 'btn-info' }} me-1">
                                    <i class="fas {{ $reply->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('admin.community-replies.edit', $reply->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.community-replies.destroy', $reply->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No replies found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($replies->hasPages())
    <div class="card-footer bg-white">
        {{ $replies->links() }}
    </div>
    @endif
</div>
@endsection
