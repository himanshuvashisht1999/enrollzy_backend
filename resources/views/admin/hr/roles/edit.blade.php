@extends('admin.layouts.master')

@section('title', 'Edit Role Permissions')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .permission-group { border: 1px solid #eee; padding: 15px; border-radius: 10px; height: 100%; transition: all 0.3s; }
    .permission-group:hover { border-color: #0d6efd; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .permission-group h6 { border-bottom: 2px solid #f8f9fa; padding-bottom: 10px; margin-bottom: 15px; }
    .form-check-label { cursor: pointer; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <form id="editRoleForm" action="{{ route('admin.hr.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">Access Control for: {{ $role->name }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6 border-end p-4">
                        <label class="form-label fw-bold">Role Name</label>
                        <input class="form-control rounded-3" type="text" name="name" value="{{ $role->name }}" required />
                        

                    </div>
                    
                    <div class="col-md-6 p-4">
                        <div class="alert alert-info border-0 rounded-4 small">
                            <i class="fas fa-info-circle me-2"></i> Selecting permissions here will update the <strong>{{ $role->name }}</strong> role and all users assigned to it.
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    @php
                        $groupedPermissions = $permission->groupBy('module_title')->sortKeys();
                    @endphp

                    @foreach ($groupedPermissions as $group => $permissions)
                    <div class="col-xl-3 col-md-4 col-sm-6">
                        <div class="permission-group">
                            <h6 class="d-flex justify-content-between align-items-center">
                                <span class="text-capitalize">{{ $group ?: 'Other' }}</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input select-all-group" type="checkbox" id="selectAll{{ md5($group) }}">
                                </div>
                            </h6>
                            @foreach ($permissions as $item)
                            <div class="form-check mb-2">
                                <input class="form-check-input group-checkbox-{{ md5($group) }}" 
                                       type="checkbox" name="permission[]" value="{{ $item->id }}" 
                                       id="perm{{ $item->id }}"
                                       {{ in_array($item->id, $rolePermissions) ? 'checked' : '' }}>
                                <label class="form-check-label text-muted small" for="perm{{ $item->id }}">
                                    {{ ucfirst(last(explode('-', $item->name))) }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-white text-end py-3 border-0">
                <a href="{{ route('admin.hr.roles.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Sync Permissions</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%', placeholder: 'Select users' });

        // Select All in Group
        $('.select-all-group').on('change', function() {
            let groupID = $(this).attr('id').replace('selectAll', '');
            $('.group-checkbox-' + groupID).prop('checked', $(this).prop('checked'));
        });

        // AJAX for Designations
        $('#department_id').on('change', function() {
            var department_id = $(this).val();
            if (department_id) {
                $.ajax({
                    url: "{{ route('admin.hr.get-designations') }}",
                    type: 'POST',
                    data: { department_ids: [department_id], _token: '{{ csrf_token() }}' },
                    success: function(data) {
                        $('#designation_id').empty().append('<option value="">Select Designation</option>');
                        data.forEach(function(item) {
                            $('#designation_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    }
                });
            }
        });

        // AJAX for Users
        $('#designation_id').on('change', function() {
            var designation_id = $(this).val();
            if (designation_id) {
                $.ajax({
                    url: "{{ route('admin.hr.get-users') }}",
                    type: 'POST',
                    data: { designation_ids: [designation_id], _token: '{{ csrf_token() }}' },
                    success: function(data) {
                        $('#working_days').empty();
                        data.forEach(function(item) {
                            $('#working_days').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    }
                });
            }
        });
    });
</script>
@endpush
