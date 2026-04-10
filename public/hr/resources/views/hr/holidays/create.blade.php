@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Add Holiday</h6>
            </div>
            <div class="card-body">
                <form class="row" id="addDeclaredHolidaysForm" action="{{ route('admin.holidays.store') }}"
                    method="POST">
                    @csrf
                    <div class="col-md-6 form-group">
                        <label for="date">Date </label>
                        <input type="date" class="form-control" name="date"
                            value="">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="name"> Holiday Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                            placeholder="Name">
                    </div>
                            <div class="col-md-4 form-group">
                                <label for="Department">Department</label>
                                <select name="department_id[]" id="departmentSelect" multiple class="form-control">
                                    <option selected disabled>Select Department</option>
                                    @foreach ($department as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label for="Designation">Designation</label>
                                <select name="designation_id[]" id="designationSelect" multiple class="form-control">
                                    <option selected disabled>Select Designation</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="role_for">Users</label>
                                <select name="working_days[]" multiple id="workingDays" class="form-control">
                                    <!-- Users will be loaded here dynamically -->
                                </select>
                            </div>
                    <div class="col-md-12 form-group">
                        <label for="description"> Description</label>
                        <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.holidays.index') }}">Cancel </a>
                <button class="btn btn-primary btn-sm" form="addDeclaredHolidaysForm" type="submit">Add Holiday</button>
            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#departmentIds').select2({
                theme: 'bootstrap4'
            });
            $('#designationIds').select2({
                theme: 'bootstrap4'
            });
            $('#employmentType').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endsection
