@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .document-container {
            background: #3342c1ad;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            /* Add margin between rows if needed */
        }

        .document-container img {
            max-width: 100%;
            height: 200px;
        }

        .document-container iframe {
            width: 100%;
            height: 200px;
            /* Adjust height as needed */
        }

        .delete-button {
            position: relative;
            bottom: 15px;
            left: 15px;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Edit Staff</h6>
            </div>
            @can('staff-read')
                <div class="card-body">
                    <form id="editStaffForm" method="POST" action="{{ route('admin.staff.update', encrypt($staff->id)) }}">
                        @csrf
                        @method('PATCH')
                        <div class="row">
                            <input type="hidden" value="{{ $staff->id }}" id="staffId" name="staffId">
                            <div class="col-md-3 form-group mb-4">
                                <label for="name">User Name</label>
                                <span class="badge badge-sm badge-success d-none" id="successValidate"> Available </span>
                                <span class="badge badge-sm badge-danger d-none" id="errorValidate"> Not Available </span>
                                <input type="text" class="form-control" name="username" placeholder="Username"
                                    value="{{ old('username') ?? $staff->username }}" id="validateUsername">
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="name"> Name</label>
                                <input type="text" class="form-control" name="name" placeholder="Name"
                                    value="{{ old('name') ?? $staff->name }}">
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="email"> Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Email"
                                    value="{{ old('email') ?? $staff->email }}">
                                
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="phone"> Mobile</label>
                                <input type="text" class="form-control" name="phone" placeholder="Mobile"
                                    value="{{ old('phone') ?? $staff->phone }}">
                                
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="dob">Date of Birth</label>
                                <input type="date" class="form-control" name="dob" id="dob"
                                    value="{{ old('dob') ?? ($staff->dob ?? date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="joining_date"> Date of Joining</label>
                                <input type="date" class="form-control" name="joining_date" id="joining_date"
                                    value="{{ old('joining_date') ?? ($staff->joining_date ?? date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="probation_end_date"> Probation End Date </label>
                                <input type="date" class="form-control" name="probation_end_date" id="probation_end_date"
                                    value="{{ old('probation_end_date') ?? $staff->probation_end_date }}">
                                <span>Leave Blank if not Applicable</span>
                            </div>



                            <div class="col-md-3 form-group mb-4">
                                <label for="">Pay Type</label>
                                <select name="pay_based" class="form-control" id="pay_based" onchange="toggleSalaryFields()">
                                    <option {{ old('pay_based', $staff->pay_based) == 'monthly' ? 'selected' : '' }} value="monthly">Monthly</option>
                                    <option {{ old('pay_based', $staff->pay_based) == 'hourly' ? 'selected' : '' }} value="hourly">Hourly</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4" id="" >
                                <label for="hourlySalary">Salary</label>
                                <input type="text" class="form-control" name="salary" value="{{ old('salary') ?? $staff->salary }}" id="hourlySalary" placeholder="Salary" value="{{ old('salary') }}">
                            </div>

                            <div class="col-md-3 form-group mb-4" id="">
                                <label for="">Shift Hours (*use 50 for half)</label>
                                <input type="text" class="form-control" name="shift_hours" id="shift_hours" placeholder="9 or 9.50" value="{{ old('shift_hours') ?? $staff->shift_hours }}" required>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="Department">Department</label>
                                <select name="department_id" class="form-control" id="department_id">
                                    <option selected disabled> Select Department </option>
                                    @foreach ($department as $dep)
                                        <option {{ old('department_id', $staff->department_id) == $dep->id ? 'selected' : '' }}
                                            value="{{ $dep->id }}">{{ $dep->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="Designation">Designation</label>
                                <select name="designation_id" class="form-control" id="designation_id">
                                    @foreach ($designation as $design)
                                        <option
                                            {{ old('designation_id', $staff->designation_id) == $design->id ? 'selected' : '' }}
                                            value="{{ $design->id }}">{{ $design->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="working_days">Working Days</label>
                                <select name="working_days[]" multiple id="workingDays" class="form-control">
                                    @php
                                        $workingDays = explode(',', $staff->working_days);
                                    @endphp
                                    <option {{ in_array('monday', $workingDays) ? 'selected' : '' }} value="monday">Monday
                                    </option>
                                    <option {{ in_array('tuesday', $workingDays) ? 'selected' : '' }} value="tuesday">Tuesday
                                    </option>
                                    <option {{ in_array('wednesday', $workingDays) ? 'selected' : '' }} value="wednesday">
                                        Wednesday</option>
                                    <option {{ in_array('thursday', $workingDays) ? 'selected' : '' }} value="thursday">
                                        Thursday</option>
                                    <option {{ in_array('friday', $workingDays) ? 'selected' : '' }} value="friday">Friday
                                    </option>
                                    <option {{ in_array('saturday', $workingDays) ? 'selected' : '' }} value="saturday">
                                        Saturday</option>
                                    <option {{ in_array('sunday', $workingDays) ? 'selected' : '' }} value="sunday">Sunday
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="gender"> Gender</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="" selected disabled>Select Gender</option>
                                    <option {{ old('gender', $staff->gender) == 'male' ? 'selected' : '' }} value="male">
                                        Male</option>
                                    <option {{ old('gender', $staff->gender) == 'female' ? 'selected' : '' }} value="female">
                                        Female</option>
                                    <option {{ old('gender', $staff->gender) == 'other' ? 'selected' : '' }} value="other">
                                        Other</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="marital_status">Marital Status</label>
                                <select name="marital_status" class="form-control">
                                    <option value="single"
                                        {{ old('marital_status', $staff->marital_status) == 'single' ? 'selected' : '' }}>
                                        Single </option>
                                    <option value="married"
                                        {{ old('marital_status', $staff->marital_status) == 'married' ? 'selected' : '' }}>
                                        Married</option>
                                    <option value="divorced"
                                        {{ old('marital_status', $staff->marital_status) == 'divorced' ? 'selected' : '' }}>
                                        Divorced</option>
                                    <option value="widower"
                                        {{ old('marital_status', $staff->marital_status) == 'widower' ? 'selected' : '' }}>
                                        Widower</option>
                                    <option value="widow"
                                        {{ old('marital_status', $staff->marital_status) == 'widow' ? 'selected' : '' }}> Widow
                                    </option>
                                    <option value="saperate"
                                        {{ old('marital_status', $staff->marital_status) == 'saperate' ? 'selected' : '' }}>
                                        Saperate </option>
                                    <option value="engaged"
                                        {{ old('marital_status', $staff->marital_status) == 'engaged' ? 'selected' : '' }}>
                                        Engaged </option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="gender"> Employment Type</label>
                                <select name="employment_type" class="form-control">
                                    <option value="" selected disabled>Select Employment Type</option>
                                    <option
                                        {{ old('employment_type', $staff->employment_type) == 'full_time' ? 'selected' : '' }}
                                        value="full_time"> Full Time</option>
                                    <option
                                        {{ old('employment_type', $staff->employment_type) == 'part_time' ? 'selected' : '' }}
                                        value="part_time"> Part Time</option>
                                    <option
                                        {{ old('employment_type', $staff->employment_type) == 'contract' ? 'selected' : '' }}
                                        value="contract"> Contract</option>
                                    <option
                                        {{ old('employment_type', $staff->employment_type) == 'internship' ? 'selected' : '' }}
                                        value="internship"> Internship</option>
                                    <option
                                        {{ old('employment_type', $staff->employment_type) == 'trainee' ? 'selected' : '' }}
                                        value="trainee"> Trainee</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-4">
                                <label for="status"> Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="" selected disabled>Select Status</option>
                                    <option {{ old('status', $staff->status) == 'active' ? 'selected' : '' }} value="active">
                                        Active</option>
                                    <option {{ old('status', $staff->status) == 'inactive' ? 'selected' : '' }}
                                        value="inactive">Inactive
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="name"> Photo</label>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="photo" data-input="thumbnail" data-preview="holder"
                                            class="btn btn-warning text-white">
                                            <i class="fa fa-picture-o"></i> Choose
                                        </a>
                                    </span>
                                    <input id="thumbnail" class="form-control edit_image" type="text"
                                        name="profile_image" value="{{ old('profile_image') ?? $staff->profile_image }}">
                                </div>
                            </div>
                            <div class="col-md-2 form-group mb-4" id="holder">
                                <label for="name"> Preview</label>
                                <img src="{{ env('APP_URL') }}/storage/{{ old('profile_image') ?? $staff->profile_image }}"
                                    alt="" width="100%">
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label for="password"> Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Password"
                                    autocomplete="disabled">
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label for="password">Confirm Password</label>
                                <input type="password" class="form-control" name="password_confirmation"
                                    placeholder="Password" autocomplete="disabled">
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label for="notice_period_start">Notice Period Start Date</label>
                                <input type="date" class="form-control" name="notice_period_start"
                                    value="{{ old('notice_period_start') ?? $staff->notice_period_start }}">
                                <span>Leave blank if not Applicable</span>
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label for="notice_period_end">Notice Period End Date</label>
                                <input type="date" class="form-control" name="notice_period_end"
                                    value="{{ old('notice_period_end') ?? $staff->notice_period_end }}">
                            </div>
                            <div class="col-md-12 form-group mb-4">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address"
                                    value="{{ old('address') ?? $staff->address }}">
                            </div>
                            <div class="col-md-12 form-group mb-4">
                                <label for="address">Description / About</label>
                                <textarea class="form-control" name="about" id="description">{{ old('about') ?? $staff->about }}</textarea>
                            </div>
                        </div>
                    </form>
                </div>
                @can('staff-edit')
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-sm btn-success" form="editStaffForm">Update</button>
                    </div>
                @endcan
            @endcan
        </div>
        <div class="card shadow my-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Upload Documents</h6>
            </div>
            @can('staff-read')
                <div class="card-body">
                    <form id="uploadDocument" method="POST"
                        action="{{ route('admin.staff.update_document', encrypt($staff->id)) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 form-group mb-4">
                                <label for="address">Upload Image or Documents (*PDF, *Docs)</label>
                                <input type="file" multiple class="form-control" name="files[]">
                            </div>
                        </div>
                    </form>
                    <div class="container">
                        @php
                            $docs = $staff->documents
                                ? array_filter(array_map('trim', explode(',', $staff->documents)))
                                : [];
                        @endphp
                        @if (count($docs) > 0)
                            <div class="row">
                                @foreach ($docs as $file)
                                    @php
                                        // Get the file extension
                                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                                        // Prepare the file URL

                                        $fileUrl = URL::asset('storage/' . $file);
                                    @endphp
                                    <div class="col-lg-4 mb-4">
                                        <div class="document-container">
                                            <form action="{{ route('admin.staff.destroy_doc', ['url' => basename($file)]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger mb-2 delete-button"
                                                    style="float: right">&times;</button>
                                            </form>
                                            @if (in_array($extension, ['jpg', 'jpeg', 'png']))
                                                <!-- Image File -->
                                                <img src="{{ $fileUrl }}" alt="Image">
                                            @elseif ($extension == 'pdf')
                                                <!-- PDF File -->
                                                <iframe src="{{ $fileUrl }}" frameborder="0"></iframe>
                                            @elseif (in_array($extension, ['doc', 'docx']))
                                                <!-- Document File -->
                                                <a href="{{ $fileUrl }}" target="_blank"
                                                    class="btn btn-sm btn-primary">View Document <i class="fa fa-eye"></i></a>
                                            @else
                                                <!-- Default case or unsupported file type -->
                                                <p>Unsupported file type</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @can('staff-edit')
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-success" form="uploadDocument">Upload Doc</button>
                    </div>
                @endcan
            @endcan
        </div>
        @can('staff-edit')
            {{-- All models will be share  --}}
            <div class="modal fade" id="updateEmailModel" tabindex="-1" role="dialog" aria-labelledby="updateEmailModel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" id="EmailModelSection">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Email Address</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="emailEntryForm" name="emailEntryForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 form-group mb-4">
                                        <label for="name"> Email Address</label>
                                        <input type="text" class="form-control" name="email"
                                            placeholder="email address" value="{{ $staff->email }}">
                                        <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary" id="sendEmailFormBtn" href="javascript:;"> Send Mail </a>
                        </div>
                    </div>
                    <div class="modal-content d-none" id="EmailOTPSection">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Email Address</h5>
                        </div>
                        <div class="modal-body">
                            <form id="emailOTPForm" name="emailOTPForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 form-group mb-4">
                                        <label for="name"> OTP</label>
                                        <input type="text" class="form-control" name="otp">
                                        <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <a class="btn btn-primary" id="ResendBtnEmail" href="javascript:;"> Resend Mail </a>
                            <a class="btn btn-primary" id="verifyOtpBtn" href="javascript:;"> Verify </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="updateMobileModel" tabindex="-1" role="dialog"
                aria-labelledby="updateMobileModel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" id="MobileModelSection">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Mobile Number</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="mobileEntryForm" name="mobileEntryForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 form-group mb-4">
                                        <label for="name"> Mobile Number</label>
                                        <input type="text" class="form-control" name="phone"
                                            placeholder="mobile number" value="{{ $staff->phone }}">
                                        <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary" id="sendMobileFormBtn" href="javascript:;"> Send OTP </a>
                        </div>
                    </div>
                    <div class="modal-content d-none" id="MobileOTPSection">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Mobile Number</h5>
                        </div>
                        <div class="modal-body">
                            <form id="mobileOTPForm" name="mobileOTPForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 form-group mb-4">
                                        <label for="name"> OTP</label>
                                        <input type="text" class="form-control" name="otp">
                                        <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <a class="btn btn-primary" id="ResendBtnMobile" href="javascript:;"> Resend OTP </a>
                            <a class="btn btn-primary" id="verifyMobileOtpBtn" href="javascript:;"> Verify </a>
                        </div>
                    </div>
                </div>
            </div>
            {{-- All models will be share  --}}
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
                    var selectedDesignationId = '{{ old('designation_id', $staff->designation_id) }}';
                        // Clear current options
                    $('#designation_id').empty();
                        $('#designation_id').html('<option selected disabled>Select Designation</option>');

                        // Add new options
                        data.designations.forEach(function(designation) {
                            var isSelected = (selectedDesignationId == designation.id) ? 'selected' : '';
                            $('#designation_id').append('<option value="' + designation.id + '" ' + isSelected + '>' + designation.name + '</option>');
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
        // ----------- Email Updateding jQuery start
        $('#sendEmailFormBtn').click(function() {
            var formData = $('#emailEntryForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.staff.emailSend') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        $('#EmailModelSection').addClass('d-none');
                        $('#EmailOTPSection').removeClass('d-none');
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -------------------------------------------
        $('#ResendBtnEmail').click(function() {
            $('#EmailModelSection').removeClass('d-none');
            $('#EmailOTPSection').addClass('d-none');
        });
        // -------------------------------------------
        $('#verifyOtpBtn').click(function() {
            var formData = $('#emailOTPForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.staff.emailVerify') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -------------------------------  end jQuery code here
        // ----------- Mobile Updateding jQuery start
        $('#sendMobileFormBtn').click(function() {
            var formData = $('#mobileEntryForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.staff.mobileSend') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        $('#MobileModelSection').addClass('d-none');
                        $('#MobileOTPSection').removeClass('d-none');
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -------------------------------------------
        $('#ResendBtnMobile').click(function() {
            $('#MobileModelSection').removeClass('d-none');
            $('#MobileOTPSection').addClass('d-none');
        });
        // -------------------------------------------
        $('#verifyMobileOtpBtn').click(function() {
            var formData = $('#mobileOTPForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.staff.mobileVerify') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -----------Add new Staff jquery Start------------------------------
        $('#validateUsername').keyup(function(e) {
            e.preventDefault();
            $('#successValidate').addClass('d-none');
            $('#errorValidate').addClass('d-none');
            if ($(this).val().trim().length > 2 && $(this).val().trim() !== "") {
                var username = $(this).val();
                var staff_id = $('#staffId').val();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.staff.validate_username') }}",
                    datatype: 'json',
                    data: {
                        'username': username,
                        'staff_id': staff_id,
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
        // -----------Add new Staff jquery Start------------------------------
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
