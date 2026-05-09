@extends('admin.layouts.master')

@section('title', 'Add Leave Type')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple { border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 2px; }
    .select2-container .select2-selection--multiple .select2-selection__choice { background-color: #4e73df; border: none; color: #fff; border-radius: 4px; padding: 2px 8px; margin-top: 4px; }
    .select2-container .select2-selection--multiple .select2-selection__choice__remove { color: #fff; margin-right: 5px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <form id="addLeaveTypeForm" method="POST" action="{{ route('admin.hr.leave-settings.store') }}">
            @csrf
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-cog me-2"></i>General Configuration</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Leave Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="name" value="{{ old('name') }}" placeholder="e.g. Sick Leave, Annual Leave" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Allotment Type</label>
                        <select name="allotment_type" id="allotment_type" class="form-select rounded-3">
                            <option {{ old('allotment_type') == 'monthly' ? 'selected' : '' }} value="monthly">Monthly</option>
                            <option {{ old('allotment_type') == 'yearly' ? 'selected' : '' }} value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-none" id="yearly_allotment_container">
                        <label class="form-label fw-semibold">Yearly Leave Count</label>
                        <input type="number" class="form-control rounded-3" name="yearly_leave" value="{{ old('yearly_leave', 0) }}" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" id="monthlyLabel">Monthly Leave Count</label>
                        <input type="number" class="form-control rounded-3" name="monthly_leave" value="{{ old('monthly_leave', 0) }}" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Pay Status</label>
                        <select name="pay_status" class="form-select rounded-3">
                            <option value="paid" {{ old('pay_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ old('pay_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Penalty Per Extra Day</label>
                        <input type="number" step="0.01" class="form-control rounded-3" name="monthly_penalty" value="{{ old('monthly_penalty', 0) }}" min="0">
                        <div class="form-text small">Amount to deduct per day if limit exceeded.</div>
                    </div>
                </div>
            </div>

            <div class="card-header bg-white py-3 border-top">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-user-shield me-2"></i>Entitlement & Rules</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Effective After (Days)</label>
                        <input type="number" name="effective_after" class="form-control rounded-3" value="{{ old('effective_after', 0) }}">
                        <div class="form-text small">Days of employment before eligible.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Unused Leave Handling</label>
                        <select name="unused_leave" class="form-select rounded-3">
                            <option value="carry_forward" {{ old('unused_leave') == 'carry_forward' ? 'selected' : '' }}>Carry Forward</option>
                            <option value="lapse" {{ old('unused_leave') == 'lapse' ? 'selected' : '' }}>Lapse / Reset</option>
                            <option value="paid" {{ old('unused_leave') == 'paid' ? 'selected' : '' }}>Encash / Paid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Over Utilization</label>
                        <select name="over_utilization" class="form-select rounded-3">
                            <option value="not_allow" {{ old('over_utilization') == 'not_allow' ? 'selected' : '' }}>Do Not Allow</option>
                            <option value="paid_allow" {{ old('over_utilization') == 'paid_allow' ? 'selected' : '' }}>Allow & Paid</option>
                            <option value="unpaid_allow" {{ old('over_utilization') == 'unpaid_allow' ? 'selected' : '' }}>Allow & Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="allow_in_probation" id="allow_in_probation" {{ old('allow_in_probation') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="allow_in_probation">Allow in Probation</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="allow_in_noticePeroid" id="allow_in_noticePeroid" {{ old('allow_in_noticePeroid') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="allow_in_noticePeroid">Allow in Notice Period</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-header bg-white py-3 border-top">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-users me-2"></i>Applicability Filters</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender[]" class="form-select select2" multiple>
                            <option value="male" {{ in_array('male', old('gender', [])) ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ in_array('female', old('gender', [])) ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ in_array('other', old('gender', [])) ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Marital Status</label>
                        <select name="marital_status[]" class="form-select select2" multiple>
                            <option value="single" {{ in_array('single', old('marital_status', [])) ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ in_array('married', old('marital_status', [])) ? 'selected' : '' }}>Married</option>
                            <option value="divorced" {{ in_array('divorced', old('marital_status', [])) ? 'selected' : '' }}>Divorced</option>
                            <option value="widowed" {{ in_array('widowed', old('marital_status', [])) ? 'selected' : '' }}>Widowed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Departments</label>
                        <select name="department[]" class="form-select select2" multiple required>
                            @foreach ($department as $depart)
                                <option value="{{ $depart->id }}" {{ in_array($depart->id, old('department', [])) ? 'selected' : '' }}>
                                    {{ $depart->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Designations</label>
                        <select name="designation[]" class="form-select select2" multiple required>
                            @foreach ($designation as $designat)
                                <option value="{{ $designat->id }}" {{ in_array($designat->id, old('designation', [])) ? 'selected' : '' }}>
                                    {{ $designat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white py-3 text-end border-0">
                <a href="{{ route('admin.hr.leave-settings.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Create Leave Type</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select options",
            allowClear: true,
            width: '100%'
        });

        function toggleLeaveFields() {
            var allotmentType = $("#allotment_type").val();
            if (allotmentType == "monthly") {
                $("#yearly_allotment_container").addClass("d-none");
                $("#monthlyLabel").text("Monthly Leave Count");
            } else {
                $("#yearly_allotment_container").removeClass("d-none");
                $("#monthlyLabel").text("Monthly Accrual Limit");
            }
        }

        toggleLeaveFields();
        $("#allotment_type").change(toggleLeaveFields);
    });
</script>
@endpush
