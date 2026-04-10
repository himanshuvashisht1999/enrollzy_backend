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
            <form id="updateLeaveTypeForm" method="POST" action="{{ route('admin.leaveSetting.update', encrypt($lSetting->id)) }}">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"> Edit General </h6>
                </div>
                <div class="card-body row">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-3 form-group">
                        <label for="name">Leave Type Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $lSetting->name) }}"
                            required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="allotment_type">Allotment Type</label>
                        <select name="allotment_type" class="form-control" id="allotmentTypeSelect" disabled>
                            <option value="monthly"
                                {{ old('allotment_type', $lSetting->allotment_type) == 'monthly' ? 'selected' : '' }}>
                                Monthly
                            </option>
                            <option value="yearly"
                                {{ old('allotment_type', $lSetting->allotment_type) == 'yearly' ? 'selected' : '' }}>
                                Yearly
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group d-none" id="yearly_allotment">
                        <label for="yearly_leave">Yearly Leave</label>
                        <input type="number" class="form-control" name="yearly_leave"
                            value="{{ old('yearly_leave', $lSetting->yearly_leave ?? 0) }}" min="0">
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="monthly_leave" id="monthlySpan">Monthly Leave</label>
                        <input type="number" class="form-control" name="monthly_leave"
                            value="{{ old('monthly_leave', $lSetting->monthly_leave ?? 0) }}" min="0">
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="pay_status">Pay Status</label>
                        <select name="pay_status" class="form-control">
                            <option value="paid"
                                {{ old('pay_status', $lSetting->pay_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid"
                                {{ old('pay_status', $lSetting->pay_status) == 'unpaid' ? 'selected' : '' }}>Un Paid
                            </option>
                        </select>
                    </div>
                </div>

                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"> Edit Entitlement </h6>
                </div>

                <div class="card-body row">
                    <div class="col-md-2 form-group">
                        <label for="effective_after">Effective After ( *Days )</label>
                        <input type="text" name="effective_after" class="form-control"
                            value="{{ old('effective_after', $lSetting->effective_after) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="unused_leave">UnUsed Leave will be.. </label>
                        <select name="unused_leave" class="form-control" disabled>
                            <option value="carry_forward"
                                {{ old('unused_leave', $lSetting->unused_leave) == 'carry_forward' ? 'selected' : '' }}>
                                Carry Forward
                            </option>
                            <option value="lapse"
                                {{ old('unused_leave', $lSetting->unused_leave) == 'lapse' ? 'selected' : '' }}>
                                Lapse
                            </option>
                            <option value="paid"
                                {{ old('unused_leave', $lSetting->unused_leave) == 'paid' ? 'selected' : '' }}>
                                Paid
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="over_utilization">Over Utilization</label>
                        <select name="over_utilization" class="form-control">
                            <option value="not_allow"
                                {{ old('over_utilization', $lSetting->over_utilization) == 'not_allow' ? 'selected' : '' }}>
                                Do Not Allow
                            </option>
                            <option value="paid_allow"
                                {{ old('over_utilization', $lSetting->over_utilization) == 'paid_allow' ? 'selected' : '' }}>
                                Allow & Paid
                            </option>
                            <option value="unpaid_allow"
                                {{ old('over_utilization', $lSetting->over_utilization) == 'unpaid_allow' ? 'selected' : '' }}>
                                Allow & UnPaid
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="allow_in_probation">Allowed in Probation Period</label>
                        <input type="checkbox" class="form-control" name="allow_in_probation"
                            {{ old('allow_in_probation') === 'on' || $lSetting->allow_in_probation == 'on' ? 'checked' : '' }}>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="allow_in_noticePeroid">Allowed in Notice Period</label>
                        <input type="checkbox" class="form-control" name="allow_in_noticePeroid"
                            {{ old('allow_in_noticePeroid') === 'on' || $lSetting->allow_in_noticePeroid == 'on' ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"> Edit Applicability </h6>
                </div>
                <div class="card-body row">
                    <div class="col-md-3 form-group">
                        <label for="gender">Gender</label>
                        <select name="gender[]" class="form-control" multiple id="genderSelect">
                            <option value="male"
                                {{ in_array('male', old('gender', explode(',', $lSetting->gender))) ? 'selected' : '' }}>
                                Male </option>
                            <option value="female"
                                {{ in_array('female', old('gender', explode(',', $lSetting->gender))) ? 'selected' : '' }}>
                                Female</option>
                            <option value="other"
                                {{ in_array('other', old('gender', explode(',', $lSetting->gender))) ? 'selected' : '' }}>
                                Other </option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="marital_status">Merital Status</label>
                        <select name="marital_status[]" class="form-control" multiple id="maritalSelect">
                            <option value="single"
                                {{ in_array('single', old('marital_status', explode(',', $lSetting->marital_status))) ? 'selected' : '' }}>
                                Single
                            </option>
                            <option value="married"
                                {{ in_array('married', old('marital_status', explode(',', $lSetting->marital_status))) ? 'selected' : '' }}>
                                Married
                            </option>
                            <option value="divorced"
                                {{ in_array('divorced', old('marital_status', explode(',', $lSetting->marital_status))) ? 'selected' : '' }}>
                                Divorced
                            </option>
                            <option value="widower"
                                {{ in_array('widower', old('marital_status', explode(',', $lSetting->marital_status))) ? 'selected' : '' }}>
                                Widower
                            </option>
                            <option value="widow"
                                {{ in_array('widow', old('marital_status', explode(',', $lSetting->marital_status))) ? 'selected' : '' }}>
                                Widow</option>
                            <option value="saperate"
                                {{ in_array('saperate', old('marital_status', explode(',', $lSetting->marital_status))) ? 'selected' : '' }}>
                                Saperate
                            </option>
                            <option value="engaged"
                                {{ in_array('engaged', old('marital_status', explode(',', $lSetting->marital_status))) ? 'selected' : '' }}>
                                Engaged
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="department">Department</label>
                        <select name="department[]" class="form-control" multiple id="departmentSelect">
                            @foreach ($department as $depart)
                                <option value="{{ $depart->id }}"
                                    {{ in_array($depart->id, old('department', explode(',', $lSetting->department_ids))) ? 'selected' : '' }}>
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
                                    {{ in_array($designat->id, old('designation', explode(',', $lSetting->designation_ids))) ? 'selected' : '' }}>
                                    {{ $designat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
            <div class="card-footer text-right">
                <a href="{{ route('admin.leaveSetting.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
                <button type="submit" form="updateLeaveTypeForm" class="btn btn-primary btn-sm">Update Type</a>
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
@endsection
