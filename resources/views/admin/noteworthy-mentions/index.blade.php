@extends('admin.layouts.master')

@section('title', 'Manage Noteworthy Mentions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Noteworthy Mentions</h4>
    <a href="{{ route('admin.noteworthy-mentions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Mention
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Subtitle</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mentions as $mention)
                    <tr>
                        <td class="ps-4">
                            @if($mention->image)
                                <img src="{{ asset($mention->image) }}" alt="{{ $mention->title }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <span class="text-muted">No Image</span>
                            @endif
                        </td>
                        <td><span class="fw-bold">{{ $mention->title }}</span></td>
                        <td>
                            @if($mention->category)
                                <span class="badge bg-secondary">{{ $mention->category->name }}</span>
                            @else
                                <span class="text-muted">Uncategorized</span>
                            @endif
                        </td>
                        <td>{{ $mention->subtitle }}</td>
                        <td>
                            @if($mention->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.noteworthy-mentions.edit', $mention->id) }}" class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.noteworthy-mentions.destroy', $mention->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this mention?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No mentions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3">
        {{ $mentions->links() }}
    </div>
</div>
@endsection
