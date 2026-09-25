@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">
                                Departments <span class="badge bg-primary ms-2 fs-6">{{ $departments->total() }}</span>
                                @if(isset($campus))
                                    <small class="text-muted">for {{ $campus->campus_name }}
                                        ({{ $organisation->name ?? '' }})</small>
                                @elseif(isset($organisation))
                                    <small class="text-muted">for {{ $organisation->name }}</small>
                                @endif
                            </h4>
                        </div>
                        <div>
                            @if(isset($campus) && isset($organisation))
                                <a href="{{ route('admin.organisations.campuses.index', $organisation->id) }}"
                                    class="btn btn-secondary btn-sm me-2">
                                    <i class="fas fa-arrow-left"></i> Back to Campuses
                                </a>
                                <a href="{{ route('admin.departments.create', ['organisation_id' => $organisation->id, 'campus_id' => $campus->id]) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New Department
                                </a>
                            @elseif(isset($organisation))
                                <a href="{{ route('admin.organisations.index') }}"
                                    class="btn btn-secondary btn-sm me-2">
                                    <i class="fas fa-arrow-left"></i> Back to Organisations
                                </a>
                                <a href="{{ route('admin.departments.create', ['organisation_id' => $organisation->id]) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New Department
                                </a>
                            @else
                                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New Department
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>
                                    <i class="fas fa-trash-alt me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                                </button>
                                @if(isset($campus))
                                    <button type="button" id="deleteAllCampusDeptsBtn" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-1"></i> Delete All in this Campus
                                    </button>
                                @endif
                            </div>
                        </div>

                        <form id="bulkDeleteForm" action="{{ route('admin.departments.bulk-destroy') }}" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="delete_all_for_campus" id="deleteAllForCampusInput" value="0">
                            <input type="hidden" name="campus_id" value="{{ $campus->id ?? '' }}">
                            <div id="bulkDeleteIdsContainer"></div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="text-center">
                                            <input type="checkbox" id="selectAllDepartments" class="form-check-input" title="Select / Unselect All">
                                        </th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th class="text-center">Courses</th>
                                        <th>Organisation</th>
                                        <th>Campus</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($departments as $department)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="ids[]" value="{{ $department->id }}" class="form-check-input dept-checkbox">
                                            </td>
                                            <td>{{ $department->id }}</td>
                                            <td>{{ $department->department_name }}</td>
                                            <td>{{ $department->department_code }}</td>
                                            <td>{{ $department->department_type }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.organisation-courses.index', array_filter(['organisation_id' => $department->organisation_id, 'campus_id' => $department->campus_id, 'department_id' => $department->id])) }}"
                                                    class="badge bg-warning text-dark text-decoration-none px-2 py-1" title="View Courses">
                                                    <i class="fas fa-graduation-cap me-1"></i>{{ $department->courses_count }}
                                                </a>
                                            </td>
                                            <td>{{ $department->organisation->name ?? 'N/A' }}</td>
                                            <td>{{ $department->campus->campus_name ?? 'Direct' }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $department->status === 'Active' ? 'success' : ($department->status === 'Inactive' ? 'secondary' : 'warning') }}">
                                                    {{ $department->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.departments.edit', $department->id) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.departments.destroy', $department->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this department? All its courses will also be deleted.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="confirmDelete(this.form)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">No departments found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $departments->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            function confirmDelete(form) {
                if (confirm('Are you sure you want to delete this department? All its courses will also be deleted.')) {
                    form.submit();
                }
            }

            $(document).ready(function() {
                const $selectAll = $('#selectAllDepartments');
                const $checkboxes = $('.dept-checkbox');
                const $bulkBtn = $('#bulkDeleteBtn');
                const $countSpan = $('#selectedCount');

                function updateBulkButton() {
                    const checkedCount = $('.dept-checkbox:checked').length;
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
                    $checkboxes.prop('checked', $(this).is(':checked'));
                    updateBulkButton();
                });

                $(document).on('change', '.dept-checkbox', function() {
                    updateBulkButton();
                });

                $bulkBtn.on('click', function() {
                    const checkedCount = $('.dept-checkbox:checked').length;
                    if (checkedCount === 0) {
                        alert('Please select at least one department to delete.');
                        return;
                    }

                    if (confirm(`Are you sure you want to delete the selected ${checkedCount} department(s)? All their associated courses will also be deleted.`)) {
                        const $container = $('#bulkDeleteIdsContainer');
                        $container.empty();
                        $('#deleteAllForCampusInput').val('0');

                        $('.dept-checkbox:checked').each(function() {
                            $container.append(`<input type="hidden" name="ids[]" value="${$(this).val()}">`);
                        });

                        $('#bulkDeleteForm').submit();
                    }
                });

                $('#deleteAllCampusDeptsBtn').on('click', function() {
                    if (confirm('Are you sure you want to delete ALL departments in this campus? All their associated courses will also be deleted. This cannot be undone.')) {
                        $('#deleteAllForCampusInput').val('1');
                        $('#bulkDeleteIdsContainer').empty();
                        $('#bulkDeleteForm').submit();
                    }
                });
            });
        </script>
    @endpush
@endsection