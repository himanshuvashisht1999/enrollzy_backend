@extends('admin.layouts.master')

@section('title', 'All Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Students Management</h1>
    <a href="{{ route('students.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add Student
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Students List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Organisation</th>
                        <th>Class/Course</th>
                        <th>Performance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($student->profile_photo_url)
                                    <img src="{{ asset($student->profile_photo_url) }}" class="rounded-circle me-2" width="40" height="40" alt="">
                                @else
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        {{ substr($student->full_name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold">{{ $student->full_name }}</div>
                                    <small class="text-muted">{{ $student->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $student->organisation?->name ?? 'N/A' }}</td>
                        <td>
                            {{ $student->current_class }}<br>
                            <small class="text-muted">{{ $student->course_enrolled }}</small>
                        </td>
                        <td>
                            @if($student->average_test_score)
                                <span class="badge bg-info">{{ $student->average_test_score }}% Avg</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $student->status == 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $student->status }}
                            </span>
                             @if($student->profile_verified)
                                <i class="fas fa-check-circle text-primary ms-1" title="Verified"></i>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-info" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-4">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
