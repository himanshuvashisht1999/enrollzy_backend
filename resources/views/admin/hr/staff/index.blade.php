@extends('admin.layouts.master')

@section('title', 'Staff Management')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .role-change { font-size: 0.85rem; padding: 0.25rem 0.5rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">All Staff Members</h6>
            <a class="btn btn-primary btn-sm rounded-pill px-3" href="{{ route('admin.hr.staff.create') }}">
                <i class="fas fa-plus me-1"></i> Add Staff
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                @if(auth()->user()->is_admin && !isset(auth()->user()->organization_id))
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Filter by Organization</label>
                    @php $organizations = \App\Models\Organization::all(); @endphp
                    <select id="filter_organization" class="form-select form-select-sm rounded-3">
                        <option value="">All Organizations</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Filter by Role</label>
                    @php $roles = \Spatie\Permission\Models\Role::all(); @endphp
                    <select id="filter_role" class="form-select form-select-sm rounded-3">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="staffTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Name</th>
                            <th>Roles</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var table = $('#staffTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.hr.staff.index') }}",
                data: function (d) {
                    d.role = $('#filter_role').val();
                    d.organization_id = $('#filter_organization').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'name', name: 'name'},
                {data: 'roles', name: 'roles'},
                {data: 'department', name: 'department'},
                {data: 'designation', name: 'designation'},
                {data: 'status', name: 'status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end'},
            ]
        });

        $('#filter_role, #filter_organization').on('change', function () {
            table.ajax.reload();
        });

        $(document).on('change', '.role-change', function() {
            var userId = $(this).data('user-id');
            var roleId = $(this).val();
            $.ajax({
                url: "{{ route('admin.hr.change-staff-role') }}",
                method: 'POST',
                data: {
                    staff_id: userId,
                    role_id: roleId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status == 1) {
                        Swal.fire('Success', response.message, 'success');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });
    });
</script>
@endpush
