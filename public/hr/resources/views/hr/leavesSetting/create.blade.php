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
            <form id="addLeaveTypeForm" method="POST" action="{{ route('admin.leaveSetting.store') }}">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">General </h6>
                </div>
                <div class="card-body row">
                    @csrf
                    <div class="col-md-4 form-group">
                        <label for="name">Leave Type Name </label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="allotment_type">Allotment Type</label>
                        <select name="allotment_type" class="form-control">
                            <option {{ old('allotment_type') == 'monthly' ? 'selected' : '' }} value="monthly">Monthly
                            </option>
                            <option {{ old('allotment_type') == 'yearly' ? 'selected' : '' }} value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group d-none" id="yearly_allotment">
                        <label for="yearly_leave">Yearly Leave</label>
                        <input type="number" class="form-control" name="yearly_leave"
                            value="{{ old('yearly_leave') ?? 0 }}" min="0">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="monthly_leave" id="monthlySpan">Monthly Leave</label>
                        <input type="number" class="form-control" name="monthly_leave"
                            value="{{ old('monthly_leave') ?? 0 }}" min="0">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="pay_status">Pay Status</label>
                        <select name="pay_status" class="form-control">
                            <option value="paid" {{ old('pay_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ old('pay_status') == 'unpaid' ? 'selected' : '' }}>Un Paid
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="" id="">Penalty</label>
                        <input type="number" class="form-control" name="monthly_penalty"
                            value="" min="0">
                    </div>
                </div>
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Entitlement </h6>
                </div>
                <div class="card-body row">
                    <div class="col-md-2 form-group">
                        <label for="effective_after">Effective After ( *Days )</label>
                        <input type="text" name="effective_after" class="form-control"
                            value="{{ old('effective_after') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="unused_leave">UnUsed Leave will be.. </label>
                        <select name="unused_leave" class="form-control">
                            <option value="carry_forward" {{ old('unused_leave') == 'carry_forward' ? 'selected' : '' }}>
                                Carry
                                Forward</option>
                            <option value="lapse" {{ old('unused_leave') == 'lapse' ? 'selected' : '' }}>Lapse</option>
                            <option value="paid" {{ old('unused_leave') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="over_utilization">Over Utilization </label>
                        <select name="over_utilization" class="form-control">
                            <option value="not_allow" {{ old('over_utilization') == 'not_allow' ? 'selected' : '' }}>Do
                                Not
                                Allow</option>
                            <option value="paid_allow" {{ old('over_utilization') == 'paid_allow' ? 'selected' : '' }}>
                                Allow & Paid</option>
                            <option value="unpaid_allow" {{ old('over_utilization') == 'unpaid_allow' ? 'selected' : '' }}>
                                Allow & UnPaid</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="allow_in_probation">Allowed in Probation Period </label>
                        <input type="checkbox" class="form-control" name="allow_in_probation"
                            {{ old('allow_in_probation') == 'on' ? 'checked' : '' }}>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="allow_in_noticePeroid">Allowed in Notice Period </label>
                        <input type="checkbox" class="form-control" name="allow_in_noticePeroid"
                            {{ old('allow_in_noticePeroid') == 'on' ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Applicability </h6>
                </div>
                <div class="card-body row">
                    <div class="col-md-3 form-group">
                        <label for="gender">Gender</label>
                        <select name="gender[]" class="form-control" multiple id="genderSelect">
                            <option value="male" {{ in_array('male', old('gender', [])) ? 'selected' : '' }}> Male
                            </option>
                            <option value="female" {{ in_array('female', old('gender', [])) ? 'selected' : '' }}> Female
                            </option>
                            <option value="other" {{ in_array('other', old('gender', [])) ? 'selected' : '' }}> Other
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="marital_status">Merital Status</label>
                        <select name="marital_status[]" class="form-control" multiple id="maritalSelect">
                            <option value="single" {{ in_array('single', old('marital_status', [])) ? 'selected' : '' }}>
                                Single
                            </option>
                            <option value="married" {{ in_array('married', old('marital_status', [])) ? 'selected' : '' }}>
                                Married
                            </option>
                            <option value="divorced"
                                {{ in_array('divorced', old('marital_status', [])) ? 'selected' : '' }}> Divorced
                            </option>
                            <option value="widower" {{ in_array('widower', old('marital_status', [])) ? 'selected' : '' }}>
                                Widower
                            </option>
                            <option value="widow" {{ in_array('widow', old('marital_status', [])) ? 'selected' : '' }}>
                                Widow</option>
                            <option value="saperate"
                                {{ in_array('saperate', old('marital_status', [])) ? 'selected' : '' }}>Saperate
                            </option>
                            <option value="engaged"
                                {{ in_array('engaged', old('marital_status', [])) ? 'selected' : '' }}>Engaged
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="department">Department</label>
                        <select name="department[]" class="form-control" multiple id="departmentSelect">
                            @foreach ($department as $depart)
                                <option value="{{ $depart->id }}"
                                    {{ in_array($depart->id, old('department', [])) ? 'selected' : '' }}>
                                    {{ $depart->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="designation">Designation</label>
                        <select name="designation[]" class="form-control" multiple id="designationSelect">
                            @foreach ($designation as $designat)
                                <option value="{{ $designat->id }}"
                                    {{ in_array($designat->id, old('designation', [])) ? 'selected' : '' }}>
                                    {{ $designat->name }}
                                </option>
                            @endforeach
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
