@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Update Holiday</h6>
            </div>
            <div class="card-body">
                <form class="row" id="updateDeclaredHolidaysForm"
                    action="{{ route('admin.holidays.update', encrypt($holiday->id)) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-6 form-group">
                        <label for="name"> Holiday Name</label>
                        <input type="text" class="form-control" name="name"
                            value="{{ old('name') ?? $holiday->name }}" placeholder="Name">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="date">Date </label>
                        <input type="date" class="form-control" name="date"
                            value="{{ old('date') ?? ($holiday->date ?? date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="department">Department</label>
                        <select name="department[]" class="form-control" multiple id="departmentSelect">
                            @foreach ($department as $depart)
                                <option value="{{ $depart->id }}"
                                    {{ in_array($depart->id, old('department', explode(',', $holiday->department_ids))) ? 'selected' : '' }}>
                                    {{ $depart->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="designation">Designation</label>
                        <select name="designation[]" class="form-control" multiple id="designationSelect">
                            @foreach ($designation as $designat)
                                <option value="{{ $designat->id }}"
                                    {{ in_array($designat->id, old('designation', explode(',', $holiday->designation_ids))) ? 'selected' : '' }}>
                                    {{ $designat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="role_for">Users</label>
                        <select name="working_days[]" multiple id="workingDays" class="form-control">
                            @foreach ($users as $designat)
                                <option value="{{ $designat->id }}"
                                    {{ in_array($designat->id, old('designation', explode(',', $holiday->staff_ids))) ? 'selected' : '' }}>
                                    {{ $designat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="description"> Description</label>
                        <textarea name="description" id="description" class="form-control">{{ old('description') ?? $holiday->description }}</textarea>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.holidays.index') }}">Cancel </a>
                <button class="btn btn-primary btn-sm" form="updateDeclaredHolidaysForm" type="submit">Update Holiday</button>
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
@endsection

