@extends('admin.layouts.master')

@section('title', 'Add Staff Member')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <form id="AddStaffForm" method="POST" action="{{ route('admin.hr.staff.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">New Staff Member Registration</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Basic Info --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">User Name <span id="usernameStatus"></span></label>
                        <input type="text" class="form-control rounded-3" name="username" id="validateUsername" value="{{ old('username') }}" placeholder="Unique username" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="name" value="{{ old('name') }}" placeholder="Employee full name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-3" name="email" value="{{ old('email') }}" placeholder="email@example.com" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="phone" value="{{ old('phone') }}" placeholder="10-digit mobile" required>
                    </div>

                    {{-- HR & Roles --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Assign Role <span class="text-danger">*</span></label>
                        <select name="rolename" class="form-select rounded-3" required>
                            <option value="" selected disabled>Select Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="department_id" class="form-select rounded-3" required>
                            <option value="" selected disabled>Select Department</option>
                            @foreach ($department as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
                        <select name="designation_id" id="designation_id" class="form-select rounded-3" required>
                            <option value="" selected disabled>Select Designation</option>
                            {{-- Populated via AJAX --}}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Employment Type <span class="text-danger">*</span></label>
                        <select name="employment_type" class="form-select rounded-3" required>
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>

                    {{-- Dates --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-3" name="dob" id="dob" value="{{ old('dob') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date of Joining <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-3" name="joining_date" id="joining_date" value="{{ old('joining_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Probation End Date</label>
                        <input type="date" class="form-control rounded-3" name="probation_end_date" id="probation_end_date" value="{{ old('probation_end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Salary / Pay Rate</label>
                        <input type="number" class="form-control rounded-3" name="salary" value="{{ old('salary') }}" placeholder="e.g. 50000">
                    </div>

                    {{-- Work Schedule --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Pay Type</label>
                        <select name="pay_based" class="form-select rounded-3">
                            <option value="monthly">Monthly</option>
                            <option value="hourly">Hourly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Shift Hours</label>
                        <input type="text" class="form-control rounded-3" name="shift_hours" placeholder="e.g. 8 or 9.5" value="{{ old('shift_hours', 9) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Working Days</label>
                        <select name="working_days[]" id="working_days" multiple class="form-select select2">
                            <option value="monday" selected>Monday</option>
                            <option value="tuesday" selected>Tuesday</option>
                            <option value="wednesday" selected>Wednesday</option>
                            <option value="thursday" selected>Thursday</option>
                            <option value="friday" selected>Friday</option>
                            <option value="saturday">Saturday</option>
                            <option value="sunday">Sunday</option>
                        </select>
                    </div>

                    {{-- Personal --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select rounded-3">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Marital Status</label>
                        <select name="marital_status" class="form-select rounded-3">
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="divorced">Divorced</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                     <div class="col-md-3">
                        <label class="form-label fw-semibold">Profile Photo</label>
                        <input type="file" class="form-control rounded-3" name="profile_image" accept="image/*">
                    </div>

                    {{-- Security --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control rounded-3" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control rounded-3" name="password_confirmation" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control rounded-3" rows="2">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">About / Notes</label>
                        <textarea name="about" id="description" class="form-control">{{ old('about') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end py-3 border-0">
                <a href="{{ route('admin.hr.staff.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Create Staff Member</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%', placeholder: 'Select options' });

        if ($('#description').length) {
            initializeTinyMCE('#description', 300);
        }

        // AJAX for Designations
        $('#department_id').on('change', function() {
            var department_id = $(this).val();
            if (department_id) {
                $.ajax({
                    url: "{{ route('admin.hr.get-designations') }}",
                    type: 'POST',
                    data: { department_ids: [department_id], _token: '{{ csrf_token() }}' },
                    success: function(data) {
                        $('#designation_id').empty().append('<option selected disabled>Select Designation</option>');
                        data.forEach(function(item) {
                            $('#designation_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    }
                });
            }
        });

        // Username Validation
        $('#validateUsername').on('keyup', function() {
            var username = $(this).val();
            if (username.length > 2) {
                // Simplified status indicator
                $('#usernameStatus').html('<i class="fas fa-spinner fa-spin ms-2"></i>');
                // You can add an AJAX check here if you have a validation route
            }
        });
    });
</script>
@endpush
