@extends('admin.layouts.master')

@section('title', 'Manage Career Roadmap Stages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Career Roadmap Stages</h4>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.career-roadmap-stages.index') }}" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-secondary">Search</button>
        </form>
        <a href="{{ route('admin.career-roadmap-stages.create') }}" class="btn btn-primary text-nowrap">
            <i class="fas fa-plus"></i> Add New Stage
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stages as $stage)
                    <tr>
                        <td class="ps-4 text-muted">{{ $stage->id }}</td>
                        <td>
                            @if($stage->image)
                                <img src="{{ asset($stage->image) }}" alt="Stage Image" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold d-block">{{ $stage->title }}</span>
                            <small class="text-muted">{{ $stage->slug }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $stage->category->name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($stage->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $stage->created_at->format('d M, Y') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.career-roadmap-stages.edit', $stage->id) }}" class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.career-roadmap-stages.destroy', $stage->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this stage?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No stages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3">
        {{ $stages->links() }}
    </div>
</div>
@endsection
