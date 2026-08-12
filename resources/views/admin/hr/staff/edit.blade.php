@extends('admin.layouts.master')

@section('title', 'Edit Staff Member')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <form id="EditStaffForm" method="POST" action="{{ route('admin.hr.staff.update', encrypt($staff->id)) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">Edit Staff Member: {{ $staff->name }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Basic Info --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">User Name</label>
                        <input type="text" class="form-control rounded-3" name="username" value="{{ old('username', $staff->username) }}" placeholder="Unique username" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="name" value="{{ old('name', $staff->name) }}" placeholder="Employee full name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-3" name="email" value="{{ old('email', $staff->email) }}" placeholder="email@example.com" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="phone" value="{{ old('phone', $staff->phone) }}" placeholder="10-digit mobile" required>
                    </div>

                    {{-- HR & Roles --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Assign Role <span class="text-danger">*</span></label>
                        <select name="rolename" class="form-select rounded-3" required>
                            <option value="" disabled>Select Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ $staff->hasRole($role->name) ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="department_id" class="form-select rounded-3" required>
                            <option value="" disabled>Select Department</option>
                            @foreach ($department as $dep)
                                <option value="{{ $dep->id }}" {{ $staff->department_id == $dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
                        <select name="designation_id" id="designation_id" class="form-select rounded-3" required>
                            <option value="" disabled>Select Designation</option>
                            @foreach ($designation as $desg)
                                <option value="{{ $desg->id }}" {{ $staff->designation_id == $desg->id ? 'selected' : '' }}>{{ $desg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Employment Type <span class="text-danger">*</span></label>
                        <select name="employment_type" class="form-select rounded-3" required>
                            <option value="full_time" {{ $staff->employment_type == 'full_time' ? 'selected' : '' }}>Full Time</option>
                            <option value="part_time" {{ $staff->employment_type == 'part_time' ? 'selected' : '' }}>Part Time</option>
                            <option value="contract" {{ $staff->employment_type == 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="internship" {{ $staff->employment_type == 'internship' ? 'selected' : '' }}>Internship</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Staff Type</label>
                        <select name="staff_type_id" class="form-select rounded-3">
                            <option value="">Select Staff Type</option>
                            @foreach ($staffTypes as $type)
                                <option value="{{ $type->id }}" {{ $staff->staff_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dates --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" class="form-control rounded-3" name="dob" value="{{ old('dob', $staff->dob) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date of Joining</label>
                        <input type="date" class="form-control rounded-3" name="joining_date" value="{{ old('joining_date', $staff->joining_date) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Probation End Date</label>
                        <input type="date" class="form-control rounded-3" name="probation_end_date" value="{{ old('probation_end_date', $staff->probation_end_date) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Salary / Pay Rate <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-3" name="salary" value="{{ old('salary', $staff->salary) }}" placeholder="e.g. 50000" required>
                    </div>

                    {{-- Work Schedule --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Pay Type</label>
                        <select name="pay_based" class="form-select rounded-3">
                            <option value="monthly" {{ $staff->pay_based == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="hourly" {{ $staff->pay_based == 'hourly' ? 'selected' : '' }}>Hourly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Shift Hours</label>
                        <input type="text" class="form-control rounded-3" name="shift_hours" value="{{ old('shift_hours', $staff->shift_hours) }}" placeholder="e.g. 8 or 9.5">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Working Days</label>
                        @php $selDays = explode(',', $staff->working_days); @endphp
                        <select name="working_days[]" id="working_days" multiple class="form-select select2">
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                <option value="{{ $day }}" {{ in_array($day, $selDays) ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Personal --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select rounded-3">
                            <option value="male" {{ $staff->gender == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ $staff->gender == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ $staff->gender == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Marital Status</label>
                        <select name="marital_status" class="form-select rounded-3">
                            <option value="single" {{ $staff->marital_status == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ $staff->marital_status == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="divorced" {{ $staff->marital_status == 'divorced' ? 'selected' : '' }}>Divorced</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="active" {{ $staff->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $staff->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Profile Photo</label>
                        <input type="file" class="form-control rounded-3" name="profile_image" accept="image/*">
                        @if($staff->profile_image)
                            <div class="mt-2">
                                <img src="{{ asset($staff->profile_image) }}" class="rounded shadow-sm" style="height: 40px; width: 40px; object-fit: cover;">
                            </div>
                        @endif
                    </div>

                    {{-- Security --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control rounded-3" name="password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" class="form-control rounded-3" name="password_confirmation">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control rounded-3" rows="2">{{ old('address', $staff->address) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">About / Notes</label>
                        <textarea name="about" id="description" class="form-control">{{ old('about', $staff->about) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end py-3 border-0">
                <a href="{{ route('admin.hr.staff.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Update Staff Member</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });
        if ($('#description').length) {
            initializeTinyMCE('#description', 300);
        }

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
    });
</script>
@endpush
