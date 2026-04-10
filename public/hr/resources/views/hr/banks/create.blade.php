@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <form id="addLeaveTypeForm" method="POST" action="{{ route('admin.banks.store') }}">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Create Bank Account </h6>
                </div>
                <div class="card-body row">
                    @csrf
                    <div class="col-md-3 form-group">
                        <label for="name">Bank Account Name </label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Add Name" required>
                    </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="status"> Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option  value="" desabled> Select Status</option>
                                    <option  value="active"> Active</option>
                                    <option  value="inactive">Inactive
                                    </option>
                                </select>
                            </div>
                </div>
            </form>
            <div class="card-footer text-right">
                <a href="{{ route('admin.leaveSetting.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
                <button type="submit" form="addLeaveTypeForm" class="btn btn-primary btn-sm">Add Type</a>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Call the function to handle the initial state when the page loads
            toggleLeaveFields();
            // Event listener for allotment_type select change
            $("select[name='allotment_type']").change(function() {
                toggleLeaveFields();
            });
            // Function to toggle the visibility of leave fields and labels
            function toggleLeaveFields() {
                var allotmentType = $("select[name='allotment_type']").val(); // Get selected allotment type
                if (allotmentType == "monthly") {
                    // Show monthly leave, hide yearly leave, and change label
                    $("#yearly_allotment").addClass("d-none"); // Hide yearly leave input
                    $("label[for='monthly_leave']").text("Monthly Leave"); // Change label back to "Monthly Leave"
                } else if (allotmentType == "yearly") {
                    // Show yearly leave, change label for monthly leave
                    $("#yearly_allotment").removeClass("d-none"); // Show yearly leave input
                    $("label[for='monthly_leave']").text("Monthly Limit"); // Change label to "Monthly Limit"
                } else {
                    // Default state if no allotment type selected
                    $("#yearly_allotment").addClass("d-none"); // Hide yearly leave input
                    $("label[for='monthly_leave']").text("Monthly Leave"); // Reset label to "Monthly Leave"
                }
            }
        });
        // ---------------------- jQuery end here
        $(document).ready(function() {
            $('#genderSelect').select2({
                theme: 'bootstrap4',
            });
            $('#maritalSelect').select2({
                theme: 'bootstrap4',
            });
            $('#departmentSelect').select2({
                theme: 'bootstrap4',
            });
            $('#designationSelect').select2({
                theme: 'bootstrap4',
            });
            $('#staff').select2({
                theme: 'bootstrap4',
            });
        });
    </script>




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
