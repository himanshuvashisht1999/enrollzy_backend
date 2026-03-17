@extends('admin.layouts.master')

@section('title', 'Manage Specialized Courses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Specialized Courses (Homepage)</h4>
    <a href="{{ route('admin.home-services.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Course
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Footer Text</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td class="ps-4 text-muted">{{ $service->id }}</td>
                        <td><span class="fw-bold">{{ $service->title }}</span></td>
                        <td>{{ Str::limit($service->description, 50) }}</td>
                        <td>{{ $service->footer_text }}</td>
                        <td>{{ $service->sort_order }}</td>
                        <td>
                            <span class="badge bg-{{ $service->status ? 'success' : 'danger' }}">
                                {{ $service->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.home-services.edit', $service->id) }}" class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.home-services.destroy', $service->id) }}" method="POST" class="d-inline">
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
                        <td colspan="7" class="text-center py-5 text-muted">No courses found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
