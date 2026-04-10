@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">

    <style>

        /* Custom File Input Styling */
.custom-file-input {
    display: none; /* Hide the default file input */
}

.custom-file-label {
    display: block;
    padding: 10px 20px;
    font-size: 16px;
    background-color: #ffc107; /* Yellow background */
    color: #fff;
    border-radius: 4px;
    border: 1px solid #ccc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.custom-file-label:hover {
    background-color: #e0a800; /* Darker yellow on hover */
}

.custom-file-label:active {
    background-color: #cc8e00; /* Even darker yellow when clicked */
}

.custom-file-input:focus ~ .custom-file-label {
    border-color: #ffc107;
    box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25);
}

/* Optional: To change the text on file selection */
.custom-file-input:lang(en)~.custom-file-label::after {
    content: "Browse";
}

.form-text {
    font-size: 14px;
    color: #6c757d;
}
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        @can('staff-add')
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Create Staff</h6>
                </div>
                <div class="card-body">
                    <form id="AddStaffForm" method="POST" action="{{ route('admin.staff.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 form-group mb-4">
                                <label for="name">User Name
                                    <span class="badge badge-sm badge-success d-none" id="successValidate"> Available </span>
                                    <span class="badge badge-sm badge-danger d-none" id="errorValidate"> Not Available </span>
                                </label>
                                <input type="text" class="form-control" name="username" placeholder="User Name"
                                    id="validateUsername" value="{{ old('username') }}">
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="name"> Name</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                    placeholder="Name">
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="email"> Email </label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                    placeholder="Email">
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="phone"> Mobile </label>
                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                                    placeholder="Mobile">
                            </div>
                            <div class="col-md-3 form-group mb-4">
                            <label for="joining_date">Select Role</label>
                                <select name="rolename" class="form-control">
                                    <option selected disabled> Select Role </option>
                                    @foreach ($roles as $role)
                                        <option 
                                            value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="dob">Date of Birth</label>
                                <input type="date" class="form-control" name="dob" id="dob"
                                    value="{{ old('dob') ?? date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="joining_date">Date of Joining</label>
                                <input type="date" class="form-control" name="joining_date" id="joining_date"
                                    value="{{ old('joining_date') ?? date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="probation_end_date">Probation End Date</label>
                                <input type="date" class="form-control" name="probation_end_date" id="probation_end_date"
                                    value="{{ old('probation_end_date') }}">
                                <span>Leave Blank if not Applicable</span>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="">Pay Type</label>
                                <select name="pay_based" class="form-control" id="pay_based" onchange="toggleSalaryFields()">
                                    <option selected disabled> Pay Type </option>
                                    <option value="monthly">Monthly</option>
                                    <option value="hourly">Hourly</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4" id="">
                                <label for="hourlySalary">Salary</label>
                                <input type="text" class="form-control" name="salary" id="hourlySalary" placeholder="Salary" value="{{ old('salary') }}">
                            </div>

                            <div class="col-md-3 form-group mb-4" id="">
                                <label for="">Shift Hours (*use 50 for half)</label>
                                <input type="text" class="form-control" name="shift_hours" id="shift_hours" placeholder="9 or 9.50" value="{{ old('shift_hours') }}" required>
                            </div>

                            <div class="col-md-3 form-group mb-4">
                                <label for="Department">Department</label>
                                <select name="department_id" class="form-control" id="department_id">
                                    <option selected disabled> Select Department </option>
                                    @foreach ($department as $dep)
                                        <option 
                                            value="{{ $dep->id }}">{{ $dep->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="Designation">Designation</label>
                                <select name="designation_id" class="form-control" id="designation_id">
                                    <option selected disabled> Select Designation </option>
                                    @foreach ($designation as $desg)
                                        <option 
                                            value="{{ $desg->id }}">{{ $desg->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="working_days">Working Days</label>
                                <select name="working_days[]" multiple id="workingDays" class="form-control">
                                    <option value="monday" {{ in_array('monday', old('working_days', [])) ? 'selected' : '' }}>
                                        Monday</option>
                                    <option value="tuesday"
                                        {{ in_array('tuesday', old('working_days', [])) ? 'selected' : '' }}>Tuesday</option>
                                    <option value="wednesday"
                                        {{ in_array('wednesday', old('working_days', [])) ? 'selected' : '' }}>Wednesday
                                    </option>
                                    <option value="thursday"
                                        {{ in_array('thursday', old('working_days', [])) ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday"
                                        {{ in_array('friday', old('working_days', [])) ? 'selected' : '' }}>
                                        Friday</option>
                                    <option value="saturday"
                                        {{ in_array('saturday', old('working_days', [])) ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday"
                                        {{ in_array('sunday', old('working_days', [])) ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-4">
                                <label for="gender"> Gender</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="" selected disabled>Select Gender</option>
                                    <option {{ old('gender') == 'male' ? 'selected' : '' }} value="male"> Male</option>
                                    <option {{ old('gender') == 'female' ? 'selected' : '' }} value="female">Female</option>
                                    <option {{ old('gender') == 'other' ? 'selected' : '' }} value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="marital_status">Marital Status</label>
                                <select name="marital_status" class="form-control">
                                    <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}> Single
                                    </option>
                                    <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>
                                        Married</option>
                                    <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>
                                        Divorced</option>
                                    <option value="widower" {{ old('marital_status') == 'widower' ? 'selected' : '' }}>
                                        Widower</option>
                                    <option value="widow" {{ old('marital_status') == 'widow' ? 'selected' : '' }}> Widow
                                    </option>
                                    <option value="saperate" {{ old('marital_status') == 'saperate' ? 'selected' : '' }}>
                                        Saperate </option>
                                    <option value="engaged" {{ old('marital_status') == 'engaged' ? 'selected' : '' }}>Engaged
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="gender"> Employment Type</label>
                                <select name="employment_type" class="form-control">
                                    <option value="" selected disabled>Select Employment Type</option>
                                    <option {{ old('employment_type') == 'full_time' ? 'selected' : '' }} value="full_time">
                                        Full Time</option>
                                    <option {{ old('employment_type') == 'part_time' ? 'selected' : '' }} value="part_time">
                                        Part Time</option>
                                    <option {{ old('employment_type') == 'contract' ? 'selected' : '' }} value="contract">
                                        Contract</option>
                                    <option {{ old('employment_type') == 'internship' ? 'selected' : '' }} value="internship">
                                        Internship</option>
                                    <option {{ old('employment_type') == 'trainee' ? 'selected' : '' }} value="trainee">
                                        Trainee</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="status"> Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option {{ old('status') == 'active' ? 'selected' : '' }} value="active"> Active</option>
                                    <option {{ old('status') == 'inactive' ? 'selected' : '' }} value="inactive">Inactive
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 form-group mb-4">
                                <label for="profile_image">Photo</label>
                                <div class="custom-file">
                                    <!-- Hidden input field -->
                                    <input type="file" class="custom-file-input edit_image" id="profile_image" name="profile_image" 
                                        value="{{ old('profile_image')}}">
                                    <label class="custom-file-label" for="profile_image">Choose a file</label>
                                </div>
                                <small id="fileHelp" class="form-text text-muted">Upload an image (jpeg, png, jpg, gif) not exceeding 2MB.</small>
                            </div>
                            <div class="col-md-2 form-group mb-4" id="holder">
                                <label for="name"> Preview</label>
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label for="password"> Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Password"
                                    autocomplete="disabled" value="{{ old('password') }}">
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label for="password">Confirm Password</label>
                                <input type="password" class="form-control" name="password_confirmation"
                                    placeholder="Password" autocomplete="disabled"
                                    value="{{ old('password_confirmation') }}">
                            </div>
                            <div class="col-md-12 form-group mb-4">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address" value="{{ old('address') }}">
                            </div>
                            <div class="col-md-12 form-group mb-4">
                                <label for="address">Description / About</label>
                                <textarea class="form-control" name="about" id="description">{{ old('about') }}</textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-sm btn-primary" form="AddStaffForm"> Save </button>
                </div>
            </div>
        @endcan
    </div>
@endsection
@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="{{ URL::asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>





    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // When department is selected
    $('#department_id').on('change', function() {
        var department_id = $(this).val();  // Get selected department ID

        if (department_id) {
            // AJAX request to fetch designations
            $.ajax({
                url: '/admin/roless/get-designations/' + department_id,// URL to fetch designations
                type: 'GET',
                dataType: 'json',
                
                success: function(data) {
                        // Clear current options
                    $('#designation_id').empty();
                        $('#designation_id').html('<option selected disabled>Select Designation</option>');

                        // Add new options
                        data.designations.forEach(function(designation) {
                            $('#designation_id').append('<option value="' + designation.id + '">' + designation.name + '</option>');
                        });
                    }
            });
        } else {
            // If no department selected, clear the designations dropdown
            $('#designation_id').empty();
            $('#designation_id').append('<option selected disabled>Select Designation</option>');
        }
    });
</script>

<script>
    function toggleSalaryFields() {
        var payBased = document.getElementById("pay_based").value;
        var monthlySalaryDiv = document.getElementById("monthlySalaryDiv");
        var hourlySalaryDiv = document.getElementById("hourlySalaryDiv");
        var shiftHoursDiv = document.getElementById("shifthours");
        var monthlySalaryInput = document.getElementById("monthlySalary");
        var hourlySalaryInput = document.getElementById("hourlySalary");
        var shiftHoursInput = document.getElementById("shift_hours");

        // Hide both salary input fields initially and disable them
        monthlySalaryDiv.style.display = "none";
        hourlySalaryDiv.style.display = "none";
        shiftHoursDiv.style.display = "none";

        // Disable the inputs
        monthlySalaryInput.disabled = true;
        hourlySalaryInput.disabled = true;
        shiftHoursInput.disabled = true;

        // Show/hide and enable/disable fields based on selected pay type
        if (payBased === "monthly") {
            monthlySalaryDiv.style.display = "block";  // Show Monthly Salary input
            monthlySalaryInput.disabled = false;  // Enable Monthly Salary input

            // Disable and hide the other fields
            shiftHoursDiv.style.display = "none"; // Hide Shift Hours for monthly pay
            hourlySalaryDiv.style.display = "none"; // Hide Hourly Salary input
        } else if (payBased === "hourly") {
            hourlySalaryDiv.style.display = "block";  // Show Hourly Salary input
            hourlySalaryInput.disabled = false;  // Enable Hourly Salary input
            shiftHoursDiv.style.display = "block"; // Show Shift Hours input for hourly pay
            shiftHoursInput.disabled = false; // Enable Shift Hours input

            // Disable and hide the other fields
            monthlySalaryDiv.style.display = "none"; // Hide Monthly Salary input
            monthlySalaryInput.disabled = true;  // Disable Monthly Salary input
        }
    }

// Initialize based on the current selection (in case the page is reloaded)
window.onload = function() {
    toggleSalaryFields();
}
</script>
    <!-- Page level custom scripts -->
    <script>




        $('#photo').filemanager('image');
        $(document).ready(function() {
            $('#description').summernote();
            $('#workingDays').select2({
                theme: 'bootstrap4',
            });
        });
        //   -------------------------------------- jQuery end here
        $('#validateUsername').keyup(function(e) {
            e.preventDefault();
            $('#successValidate').addClass('d-none');
            $('#errorValidate').addClass('d-none');
            if ($(this).val().trim().length > 2 && $(this).val().trim() !== "") {
                var username = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.staff.validate_username') }}",
                    datatype: 'json',
                    data: {
                        'username': username,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status == 1) {
                            $('#successValidate').removeClass('d-none');
                            $('#errorValidate').addClass('d-none');
                        } else if (response.status == 0) {
                            $('#successValidate').addClass('d-none');
                            $('#errorValidate').removeClass('d-none');
                        }
                    },
                });
            }
        });

        $(document).ready(function() {
            // Validate the DOB and Joining Date
            $("#joining_date", ).change(function() {
                const dob = new Date($("#dob").val());
                const doj = new Date($(this).val());
                // Calculate the age difference between DOB and DOJ (Date of Joining)
                const age = (doj - dob) / (1000 * 3600 * 24 * 365.25); // Convert milliseconds to years
                if (age < 16) {
                    alert("Date of Joining must be at least 16 years after Date of Birth.");
                    $("#joining_date").val(''); // Clear the invalid date
                    return false;
                }
            });

            // Validate the Joining Date and Probation End Date
            $("#probation_end_date").change(function() {
                const doj = new Date($("#joining_date").val());
                const probationEndDate = new Date($(this).val());

                if (probationEndDate <= doj) {
                    alert("Probation End Date must be at least 1 day after Date of Joining.");
                    $("#probation_end_date").val(''); // Clear the invalid date
                }
            });
        });
    </script>
@endsection
