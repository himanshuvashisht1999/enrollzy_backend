@extends('admin.layouts.master')

@section('title', 'Manage Exam Status Stages')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Exam Stages (Status)</h4>
            <p class="text-muted mb-0">Manage the list of exam status stages used across the system.</p>
        </div>
        <!-- <a href="{{ route('admin.exam-stages.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New
            </a> -->
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Stage Name</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="ps-4">{{ $loop->iteration }}</td>
                                <td class="fw-medium">{{ $item->title }}</td>
                                <td>
                                    @if($item->status)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $item->sort_order }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.exam-stages.edit', $item->id) }}"
                                        class="btn btn-sm btn-light border me-1">
                                        <i class="fas fa-edit text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.exam-stages.destroy', $item->id) }}" method="POST"
                                        class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <!-- <button type="submit" class="btn btn-sm btn-light border text-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button> -->
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                    <p>No stages found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection