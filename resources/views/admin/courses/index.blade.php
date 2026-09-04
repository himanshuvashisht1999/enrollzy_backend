@extends('admin.layouts.master')

@section('title', 'Master Course List')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mt-2">
            <h3 class="fw-bold">Master Course List</h3>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create New Course
            </a>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="text-muted mb-0">Manage the global list of courses that universities can offer.</p>
            <form action="{{ route('admin.courses.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search courses..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none ms-1">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Course Name</th>
                            <th>Slug</th>
                            <th>Discipline</th>
                            <th>Status</th>
                            <th>Show on Website</th>
                            <th>Sort Order</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">{{ $course->name }}</span>
                                </td>
                                <td><code class="text-muted">{{ $course->slug }}</code></td>
                                <td>{{ $course->discipline->title ?? '-' }}</td>
                                <td>
                                    @if($course->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($course->is_show_on_website)
                                        <span class="badge bg-info text-dark">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $course->sort_order }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.courses.edit', $course->id) }}"
                                        class="btn btn-sm btn-outline-primary me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.courses.duplicate', $course->id) }}"
                                        class="btn btn-sm btn-outline-secondary me-1" title="Duplicate Course">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure? This will delete all university associations for this course.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No courses found in the master list.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($courses->hasPages())
            <div class="card-footer bg-white">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
@endsection