@extends('layouts.app')
@section('push_css')
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Add Roles</h6>
            </div>
            @can('roles-add')
                <div class="card-body">
                    <form id="addRoleForm" action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="form-group col-md-6">
                                <label for="Name"> Role Name</label>
                                <input class="form-control" id="Name" type="text" name="name" placeholder="Name" />
                            </div>
                            <hr>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
                    <button type="submit" form="addRoleForm" class="btn btn-success btn-sm">Add Role</button>
                </div>
            @endcan
        </div>
    </div>
@endsection
@section('push_script')
    <script>
        $(document).ready(function() {
            $('.select-all-group').on('change', function() {
                let groupName = $(this).attr('id').replace('selectAll', '');
                $('.group-checkbox-' + groupName).prop('checked', $(this).prop('checked'));
            });
        });
    </script>
@endsection
