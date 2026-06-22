@extends('admin.layouts.master')

@section('title', 'Manage Career Roadmap Sub Modules')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Career Roadmap Sub Modules</h4>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.career-roadmap-sub-modules.index') }}" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-secondary">Search</button>
        </form>
        <a href="{{ route('admin.career-roadmap-sub-modules.create') }}" class="btn btn-primary text-nowrap">
            <i class="fas fa-plus"></i> Add New Sub Module
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
                        <th>Title & Slug</th>
                        <th>Stage</th>
                        <th>Parent Module</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subModules as $module)
                    <tr>
                        <td class="ps-4 text-muted">{{ $module->id }}</td>
                        <td>
                            @if($module->image)
                                <img src="{{ asset($module->image) }}" alt="Image" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold d-block">{{ $module->title }}</span>
                            <small class="text-muted">{{ $module->slug }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $module->stage->title ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($module->parent)
                                <span class="badge bg-info text-dark">{{ $module->parent->title }}</span>
                            @else
                                <span class="text-muted">None (Top Level)</span>
                            @endif
                        </td>
                        <td>
                            @if($module->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.career-roadmap-sub-modules.edit', $module->id) }}" class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.career-roadmap-sub-modules.destroy', $module->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this sub module?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No sub-modules found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3">
        {{ $subModules->links() }}
    </div>
</div>
@endsection
