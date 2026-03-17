@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">
                                Departments
                                @if(isset($campus))
                                    <small class="text-muted">for {{ $campus->campus_name }}
                                        ({{ $organisation->name ?? '' }})</small>
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
                            @else
                                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New Department
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">


                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Organisation</th>
                                        <th>Campus</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($departments as $department)
                                        <tr>
                                            <td>{{ $department->id }}</td>
                                            <td>{{ $department->department_name }}</td>
                                            <td>{{ $department->department_code }}</td>
                                            <td>{{ $department->department_type }}</td>
                                            <td>{{ $department->organisation->name ?? 'N/A' }}</td>
                                            <td>{{ $department->campus->campus_name ?? 'N/A' }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $department->status === 'Active' ? 'success' : ($department->status === 'Inactive' ? 'secondary' : 'warning') }}">
                                                    {{ $department->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.organisation-courses.index', ['organisation_id' => $department->organisation_id, 'campus_id' => $department->campus_id, 'department_id' => $department->id]) }}"
                                                        class="btn btn-warning btn-sm" title="Manage Courses">
                                                        <i class="fas fa-book"></i>
                                                    </a>
                                                    <a href="{{ route('admin.departments.edit', $department->id) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.departments.destroy', $department->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this department?');">
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
                                            <td colspan="8" class="text-center">No departments found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $departments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(form) {
                if (confirm('Are you sure you want to delete this department?')) {
                    form.submit();
                }
            }
        </script>
    @endpush
@endsection