@extends('admin.layouts.master')

@section('title', 'Edit Holiday')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple { border: 1px solid #dee2e6; border-radius: 0.5rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <form id="editHolidayForm" method="POST" action="{{ route('admin.hr.holidays.update', encrypt($holiday->id)) }}">
            @csrf
            @method('PATCH')
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Edit Holiday Information</h6>
            </div>
            <div class="card-body">
                @php
                    $selDeparts = explode(',', $holiday->department_ids);
                    $selDesigns = explode(',', $holiday->designation_ids);
                    $selStaffs  = explode(',', $holiday->staff_ids);
                @endphp
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Holiday Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-3" name="date" value="{{ old('date', $holiday->date) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="name" value="{{ old('name', $holiday->name) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Target Departments</label>
                        <select name="department[]" id="departmentSelect" multiple class="form-select select2">
                            @foreach ($department as $dep)
                                <option value="{{ $dep->id }}" {{ in_array($dep->id, $selDeparts) ? 'selected' : '' }}>{{ $dep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Target Designations</label>
                        <select name="designation[]" id="designationSelect" multiple class="form-select select2">
                            @foreach ($designation as $des)
                                <option value="{{ $des->id }}" {{ in_array($des->id, $selDesigns) ? 'selected' : '' }}>{{ $des->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Specific Users</label>
                        <select name="working_days[]" multiple id="workingDays" class="form-select select2">
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ in_array($u->id, $selStaffs) ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Description / Notes</label>
                        <textarea name="description" class="form-control rounded-3" rows="3">{{ old('description', $holiday->description) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end py-3 border-0">
                <a href="{{ route('admin.hr.holidays.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Update Holiday</button>
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
            width: '100%'
        });
    });
</script>
@endpush
