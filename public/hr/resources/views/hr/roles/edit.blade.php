@extends('layouts.app')
@section('push_css')
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Edit Role</h6>
            </div>
            @can('roles-read')
                <div class="card-body">
                    <form id="editRoleForm" action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="row g-3">
                            <div class="form-group col-md-6">
                                <label for="floatingInputGrid">Name</label>
                                <input class="form-control" id="floatingInputGrid" type="text" name="name"
                                    value="{{ $role->name }}" placeholder="Name" />
                            </div>
                            @if($role->name != 'admin')
                            <div class="col-md-6 form-group">
                                <label for="Department">Department</label>
                                <select name="department_id" id="departmentSelect" class="form-control">
                                    <option selected disabled>Select Department</option>
                                    @foreach ($department as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="Designation">Designation</label>
                                <select name="designation_id[]" id="designationSelect" multiple class="form-control">
                                    <option selected disabled>Select Designation</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="role_for">Users</label>
                                <select name="working_days[]" multiple id="workingDays" class="form-control">
                                    <!-- Users will be loaded here dynamically -->
                                </select>
                            </div>
                            @endif
                            @php
                                $groupedPermissions = $permission->groupBy(function ($permission) {
                                    $segments = explode('-', $permission->name);
                                    // If there are more than two segments, group by the first two
                                    return count($segments) > 2 ? $segments[0] . '-' . $segments[1] : $segments[0];
                                });
                            @endphp
                            @foreach ($groupedPermissions as $group => $permissions)
                                <div class="col-md-4 mb-3">
                                    <h5>
                                        <input type="checkbox" id="selectAll{{ ucfirst(str_replace('-', '', $group)) }}"
                                            class="select-all-group" />
                                        <label
                                            for="selectAll{{ ucfirst(str_replace('-', '', $group)) }}">{{ ucfirst(str_replace('-', ' ', $group)) }}
                                            Permissions</label>
                                    </h5>
                                    @foreach ($permissions as $item)
                                        <div style="margin-left: 25px">
                                            <input type="checkbox" name="permission[]" value="{{ $item->id }}"
                                                id="dataId{{ $item->id }}"
                                                class="group-checkbox-{{ ucfirst(str_replace('-', '', $group)) }}"
                                                {{ in_array($item->id, $rolePermissions) ? 'checked' : '' }} />
                                            <label for="dataId{{ $item->id }}">{{ $item->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
                @can('roles-edit')
                    <div class="card-footer">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
                        <button type="submit" form="editRoleForm" class="btn btn-success btn-sm">Update Role</button>
                    </div>
                @endcan
            @endcan
        </div>
    </div>
@endsection
@section('push_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Load designations based on selected department
        $('#departmentSelect').on('change', function() {
            var departmentId = $(this).val(); // Get the selected department ID

            if (departmentId) {
                $.ajax({
                    url: '/admin/roless/get-designations/' + departmentId,// URL to fetch designations
                    method: 'GET',
                    success: function(data) {
                        // Clear current options
                        $('#designationSelect').html('<option selected disabled>Select Designation</option>');

                        // Add new options
                        data.designations.forEach(function(designation) {
                            $('#designationSelect').append('<option value="' + designation.id + '">' + designation.name + '</option>');
                        });
                    }
                });
            }
        });

        // Load users based on selected designation
        $('#designationSelect').on('change', function() {
            var designationId = $(this).val(); // Get the selected designation ID

            if (designationId) {
                $.ajax({
                    url: '/admin/roless/get-users/' + designationId, // URL to fetch users
                    method: 'GET',
                    success: function(data) {
                        // Clear current options
                        $('#workingDays').html('');

                        // Add new options
                        data.users.forEach(function(user) {
                            $('#workingDays').append('<option value="' + user.id + '">' + user.name + '</option>');
                        });
                    }
                });
            }
        });
    });
</script>
    <script>
        $(document).ready(function() {
            $('#workingDays').select2({
                theme: 'bootstrap4',
            });
        });
        $(document).ready(function() {
            // Toggle all checkboxes in a group when the master checkbox is clicked
            $('.select-all-group').on('change', function() {
                let groupName = $(this).attr('id').replace('selectAll', '');
                $('.group-checkbox-' + groupName).prop('checked', $(this).prop('checked'));
            });
            // Ensure master checkbox is checked if all checkboxes in the group are checked
            $('.group-checkbox').on('change', function() {
                let groupName = $(this).attr('class').split(' ').find(cls => cls.startsWith(
                    'group-checkbox-')).replace('group-checkbox-', '');
                let allChecked = $('.group-checkbox-' + groupName).length === $('.group-checkbox-' +
                    groupName + ':checked').length;
                $('#selectAll' + groupName).prop('checked', allChecked);
            });
        });
    </script>
@endsection
