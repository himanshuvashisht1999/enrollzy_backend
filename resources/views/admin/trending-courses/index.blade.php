@extends('admin.layouts.master')

@section('title', 'Manage Trending Courses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Trending Courses (Homepage Section)</h4>
        <p class="text-muted small mb-0">Manage featured courses, instructors, prices, ratings, and URLs displayed on the homepage.</p>
    </div>
    <a href="{{ route('admin.trending-courses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Course
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Course Title</th>
                        <th>Instructor / Provider</th>
                        <th>Price / Fees</th>
                        <th>Rating</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="ps-4 text-muted">{{ $course->id }}</td>
                        <td><span class="fw-bold text-dark">{{ $course->name }}</span></td>
                        <td><small class="text-muted">{{ $course->instructor ?? 'N/A' }}</small></td>
                        <td><span class="badge bg-success-subtle text-success border border-success">{{ $course->price ?? 'Free' }}</span></td>
                        <td><small class="text-warning fw-bold">★ {{ $course->rating ?? '4.9' }}</small></td>
                        <td>{{ $course->sort_order }}</td>
                        <td>
                            <span class="badge bg-{{ $course->status ? 'success' : 'danger' }}">
                                {{ $course->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.trending-courses.edit', $course->id) }}" class="btn btn-sm btn-outline-info me-1">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.trending-courses.destroy', $course->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this course?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No trending courses found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
