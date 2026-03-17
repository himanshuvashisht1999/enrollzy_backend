@extends('admin.layouts.master')

@section('title', 'Manage Alumni')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold">Alumni Network</h3>
        <a href="{{ route('admin.alumni.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Alumni
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumni as $alumnus)
                        <tr>
                            <td class="ps-4">{{ $loop->iteration + $alumni->firstItem() - 1 }}</td>
                            <td>
                                @if($alumnus->image)
                                    <img src="{{ asset($alumnus->image) }}" alt="{{ $alumnus->name }}" class="rounded-circle object-fit-cover" width="40" height="40">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $alumnus->name }}</div>
                                <div class="text-muted small">{{ $alumnus->email }}</div>
                            </td>
                            <td>{{ $alumnus->designation ?? '-' }}</td>
                            <td>{{ $alumnus->company ?? '-' }}</td>
                            <td>
                                @if($alumnus->status)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $alumnus->sort_order }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.alumni.edit', $alumnus->id) }}" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.alumni.destroy', $alumnus->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this alumni?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-user-graduate fa-2x mb-3 d-block"></i>
                                No alumni found. Click "Add New Alumni" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 py-3">
        {{ $alumni->links() }}
    </div>
</div>
@endsection
