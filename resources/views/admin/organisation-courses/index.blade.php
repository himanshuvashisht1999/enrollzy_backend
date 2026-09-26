@extends('admin.layouts.master')

@section('title', 'Manage Courses - ' . $organisation->name)

@section('content')

    {{-- ===================== PAGE HEADING ===================== --}}
    <h3 class="fw-bold mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span>
            @if(isset($department))
                Courses for {{ $department->department_name }} ({{ $campus->campus_name ?? '' }})
            @elseif(in_array($organisationTypeId, [1, 2]))
                Courses for {{ $organisation->name }}
            @elseif($organisationTypeId == 3)
                Institute Programs – {{ $organisation->name }}
            @elseif($organisationTypeId == 4)
                School Academic Profile – {{ $organisation->name }}
            @elseif($organisationTypeId == 9)
                E-Learning Programs – {{ $organisation->name }}
            @else
                {{ $organisation->name }}
            @endif
        </span>
        <span class="badge bg-primary fs-6 px-3 py-2">
            <i class="fas fa-graduation-cap me-1"></i> Total Courses: {{ is_object($courses) && method_exists($courses, 'total') ? $courses->total() : (is_countable($courses) ? count($courses) : 0) }}
        </span>
    </h3>

    {{-- ===================== BACK & ACTION BUTTONS ===================== --}}
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
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
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" id="bulkDeleteCoursesBtn" class="btn btn-danger btn-sm" disabled>
                <i class="fas fa-trash-alt me-1"></i> Delete Selected (<span id="selectedCoursesCount">0</span>)
            </button>
            @if(isset($department))
                <button type="button" id="deleteAllDeptCoursesBtn" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash me-1"></i> Delete All Courses in Department
                </button>
            @endif

            {{-- Add button only for University / College / Institute / School / E-Learning --}}
            @if(in_array($organisationTypeId, [1, 2, 3, 4, 9]))
                <a href="{{ route('admin.organisation-courses.create', ['organisation_id' => $organisation->id, 'campus_id' => $campusId ?? null, 'department_id' => $departmentId ?? null]) }}"
                    class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Course
                </a>
            @endif
        </div>
    </div>

    {{-- Hidden form for bulk delete --}}
    <form id="bulkDeleteCoursesForm" action="{{ route('admin.organisation-courses.bulk-destroy') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="delete_all_for_department" id="deleteAllForDepartmentInput" value="0">
        <input type="hidden" name="department_id" value="{{ $departmentId ?? '' }}">
        <div id="bulkDeleteCoursesIdsContainer"></div>
    </form>

    {{-- ===================== FILTERS (CAMPUS & DEPARTMENT) ===================== --}}
    @if($organisationTypeId != 9 && ((isset($allDepartments) && $allDepartments->count() > 0) || (isset($allCampuses) && $allCampuses->count() > 0)))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2 px-3">
                <form method="GET" action="{{ route('admin.organisation-courses.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="organisation_id" value="{{ $organisation->id }}">
                    @if(isset($allCampuses) && $allCampuses->count() > 0)
                        <div class="col-md-3 col-sm-6">
                            <select name="campus_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- All Campuses --</option>
                                @foreach($allCampuses as $c)
                                    <option value="{{ $c->id }}" {{ $campusId == $c->id ? 'selected' : '' }}>{{ $c->campus_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($allDepartments) && $allDepartments->count() > 0)
                        <div class="col-md-4 col-sm-6">
                            <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- All Departments --</option>
                                @foreach($allDepartments as $d)
                                    <option value="{{ $d->id }}" {{ $departmentId == $d->id ? 'selected' : '' }}>{{ $d->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if($campusId || $departmentId)
                        <div class="col-auto">
                            <a href="{{ route('admin.organisation-courses.index', ['organisation_id' => $organisation->id]) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Clear Filters
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- UNIVERSITY / COLLEGE / INSTITUTE / E-LEARNING (TYPE 1,2,3,9) --}}
    {{-- ========================================================= --}}
    @if(in_array($organisationTypeId, [1, 2, 3, 9]))

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40px;" class="ps-3 text-center">
                                    <input type="checkbox" id="selectAllCourses" class="form-check-input" title="Select / Unselect All">
                                </th>
                                <th>Course Name</th>
                                <th>Specialization Area</th>
                                <th>Campus</th>
                                @if($organisationTypeId != 9)
                                    <th>Department</th>
                                @endif
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
                                    <td class="ps-3 text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $course->id }}" class="form-check-input course-checkbox">
                                    </td>
                                    <td class="fw-bold">{{ $course->course->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($course->specialization_ids)
                                            @foreach($course->specialization_ids as $specId)
                                                <div class="mb-1">
                                                    <span class="badge bg-light text-dark border">{{ $allSpecializations[$specId] ?? 'Unknown' }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($organisationTypeId == 9)
                                            <span class="badge bg-light text-muted border"><i class="fas fa-globe me-1"></i>Direct Online</span>
                                        @elseif($course->campus)
                                            <span class="badge bg-info text-dark">
                                                {{ $course->campus->campus_name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">All Campuses</span>
                                        @endif
                                    </td>
                                    @if($organisationTypeId != 9)
                                        <td>
                                            @if($course->department)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                    {{ $course->department->department_name }}
                                                </span>
                                            @else
                                                <span class="text-muted small">No Department</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>{{ $course->mode }}</td>
                                    <td class="text-primary fw-bold">{{ $course->total_fees ?? $course->fees }}</td>
                                    <td>{{ $course->duration ?? ($course->course->duration ?? 'N/A') }}</td>
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
                                    <td colspan="{{ $organisationTypeId != 9 ? '11' : '10' }}" class="text-center py-5 text-muted">
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
                    {{ $courses->appends(request()->query())->links() }}
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
                                <th style="width: 40px;" class="ps-3 text-center">
                                    <input type="checkbox" id="selectAllCoursesSchool" class="form-check-input" title="Select / Unselect All">
                                </th>
                                <th>School Name</th>
                                <th>Board</th>
                                <th>Grades</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schoolCourses as $school)
                                <tr>
                                    <td class="ps-3 text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $school->id }}" class="form-check-input course-checkbox">
                                    </td>
                                    <td class="fw-bold">{{ $school->school_name }}</td>
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
                                    <td colspan="6" class="text-center py-4 text-muted">
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
                    {{ $schoolCourses->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    @endif

    @push('js')
        <script>
            $(document).ready(function() {
                const $selectAll = $('#selectAllCourses, #selectAllCoursesSchool');
                const $bulkBtn = $('#bulkDeleteCoursesBtn');
                const $countSpan = $('#selectedCoursesCount');

                function updateBulkButton() {
                    const $checkboxes = $('.course-checkbox');
                    const checkedCount = $('.course-checkbox:checked').length;
                    $countSpan.text(checkedCount);
                    $bulkBtn.prop('disabled', checkedCount === 0);

                    if (checkedCount === 0) {
                        $selectAll.prop('checked', false).prop('indeterminate', false);
                    } else if (checkedCount === $checkboxes.length && $checkboxes.length > 0) {
                        $selectAll.prop('checked', true).prop('indeterminate', false);
                    } else {
                        $selectAll.prop('checked', false).prop('indeterminate', true);
                    }
                }

                $selectAll.on('change', function() {
                    $('.course-checkbox').prop('checked', $(this).is(':checked'));
                    updateBulkButton();
                });

                $(document).on('change', '.course-checkbox', function() {
                    updateBulkButton();
                });

                $bulkBtn.on('click', function() {
                    const checkedCount = $('.course-checkbox:checked').length;
                    if (checkedCount === 0) {
                        alert('Please select at least one course to delete.');
                        return;
                    }

                    if (confirm(`Are you sure you want to delete the selected ${checkedCount} course(s)? This action cannot be undone.`)) {
                        const $container = $('#bulkDeleteCoursesIdsContainer');
                        $container.empty();
                        $('#deleteAllForDepartmentInput').val('0');

                        $('.course-checkbox:checked').each(function() {
                            $container.append(`<input type="hidden" name="ids[]" value="${$(this).val()}">`);
                        });

                        $('#bulkDeleteCoursesForm').submit();
                    }
                });

                $('#deleteAllDeptCoursesBtn').on('click', function() {
                    if (confirm('Are you sure you want to delete ALL courses in this department? This action cannot be undone.')) {
                        $('#deleteAllForDepartmentInput').val('1');
                        $('#bulkDeleteCoursesIdsContainer').empty();
                        $('#bulkDeleteCoursesForm').submit();
                    }
                });
            });
        </script>
    @endpush

@endsection