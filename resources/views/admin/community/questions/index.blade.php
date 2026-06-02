@extends('admin.layouts.master')

@section('title', 'Moderate Community Questions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Community Questions</h3>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.community-questions.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search questions..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @foreach($cat->children as $child)
                            <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>-- {{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
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
                        <th class="ps-4">Question</th>
                        <th>User</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $question)
                    <tr>
                        <td class="ps-4">
                            <span class="d-block fw-bold">{{ Str::limit($question->question_text, 100) }}</span>
                            @if($question->image)
                                <a href="{{ env('APP_ENV') == 'local' ? 'http://127.0.0.1:8000' : 'https://enrollzy.com' }}/{{ ltrim($question->image, '/') }}" target="_blank" class="text-primary small mt-1 d-inline-block">View Image</a>
                            @endif
                        </td>
                        <td>{{ $question->user->name ?? 'Deleted User' }}</td>
                        <td><span class="badge bg-light text-dark">{{ $question->category->name }}</span></td>
                        <td>
                            @if($question->status == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($question->status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                            
                            @if($question->is_active)
                                <span class="badge bg-info">Active</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <form action="{{ route('admin.community-questions.toggle-verify', $question->id) }}" method="POST" class="d-inline" title="Toggle Enable/Disable">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $question->is_active ? 'btn-secondary' : 'btn-info' }} me-1">
                                    <i class="fas {{ $question->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('admin.community-questions.edit', $community_question = $question->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.community-questions.destroy', $question->id) }}" method="POST" class="d-inline">
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
                        <td colspan="5" class="text-center py-5 text-muted">No questions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($questions->hasPages())
    <div class="card-footer bg-white">
        {{ $questions->links() }}
    </div>
    @endif
</div>
@endsection
