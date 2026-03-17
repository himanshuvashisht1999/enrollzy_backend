@extends('admin.layouts.master')

@section('title', 'Manage Courses - ' . $organisation->name)

@section('content')

    {{-- ===================== PAGE HEADING ===================== --}}
    <h3 class="fw-bold mb-3">
        @if(isset($department))
            Courses for {{ $department->department_name }} ({{ $campus->campus_name }})
        @elseif(in_array($organisationTypeId, [1, 2]))
            Courses for {{ $organisation->name }}
        @elseif($organisationTypeId == 3)
            Institute Programs – {{ $organisation->name }}
        @elseif($organisationTypeId == 4)
            School Academic Profile – {{ $organisation->name }}
        @else
            {{ $organisation->name }}
        @endif
    </h3>

    {{-- ===================== BACK & ADD BUTTON ===================== --}}
    <div class="mb-4 d-flex justify-content-between align-items-center">
        @if(isset($department) && isset($campus))
            <a href="{{ route('admin.departments.index', ['organisation_id' => $organisation->id, 'campus_id' => $campus->id]) }}"
                class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-1"></i> Back to Departments
            </a>
        @else
            <a href="{{ route('admin.organisations.index') }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-1"></i> Back to Organisations
            </a>
        @endif

        {{-- Add button only for University / College / Institute --}}
        @if(in_array($organisationTypeId, [1, 2, 3, 4]))
            <a href="{{ route('admin.organisation-courses.create', ['organisation_id' => $organisation->id, 'campus_id' => $campusId ?? null, 'department_id' => $departmentId ?? null]) }}"
                class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Course
            </a>
        @endif
    </div>

    {{-- ========================================================= --}}
    {{-- UNIVERSITY / COLLEGE / INSTITUTE (TYPE 1,2,3) --}}
    {{-- ========================================================= --}}
    @if(in_array($organisationTypeId, [1, 2, 3]))

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Course Name</th>
                                <th>Campus</th>
                                <th>Mode</th>
                                <th>Fees</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $course)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $course->course->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($course->campus)
                                            <span class="badge bg-info text-dark">
                                                {{ $course->campus->campus_name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">All Campuses</span>
                                        @endif
                                    </td>
                                    <td>{{ $course->mode }}</td>
                                    <td class="text-primary fw-bold">{{ $course->fees }}</td>
                                    <td>{{ $course->duration }}</td>
                                    <td>
                                        <span class="badge {{ $course->status ? 'bg-success' : 'bg-danger' }}">
                                            {{ $course->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $course->sort_order }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.organisation-courses.duplicate', $course->id) }}"
                                            class="btn btn-sm btn-outline-secondary me-1"
                                            onclick="return confirm('Duplicate this course?')">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                        <a href="{{ route('admin.organisation-courses.edit', $course->id) }}"
                                            class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.organisation-courses.destroy', $course->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        No courses found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(is_object($courses) && method_exists($courses, 'hasPages') && $courses->hasPages())
                <div class="card-footer bg-white">
                    {{ $courses->links() }}
                </div>
            @endif
        </div>

        {{-- ========================================================= --}}
        {{-- SCHOOL (TYPE 4) --}}
        {{-- ========================================================= --}}
    @elseif($organisationTypeId == 4)

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">School Name</th>
                                <th>Board</th>
                                <th>Grades</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schoolCourses as $school)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $school->school_name }}</td>
                                    <td>{{ $school->education_board }}</td>
                                    <td>{{ $school->grade_range }}</td>
                                    <td>
                                        <span class="badge {{ $school->status ? 'bg-success' : 'bg-danger' }}">
                                            {{ $school->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.organisation-courses.duplicate', $school->id) }}"
                                            class="btn btn-sm btn-outline-secondary me-1"
                                            onclick="return confirm('Duplicate this record?')">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                        <a href="{{ route('admin.organisation-school.edit', $school->id) }}"
                                            class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.organisation-school.destroy', $school->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No school records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(is_object($schoolCourses) && method_exists($schoolCourses, 'hasPages') && $schoolCourses->hasPages())
                <div class="card-footer bg-white">
                    {{ $schoolCourses->links() }}
                </div>
            @endif
        </div>

    @endif

@endsection