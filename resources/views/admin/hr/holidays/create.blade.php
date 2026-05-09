@extends('admin.layouts.master')

@section('title', 'Add Holiday')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple { border: 1px solid #dee2e6; border-radius: 0.5rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <form id="addHolidayForm" method="POST" action="{{ route('admin.hr.holidays.store') }}">
            @csrf
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">New Holiday Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Holiday Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-3" name="date" value="{{ old('date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="name" value="{{ old('name') }}" placeholder="e.g. Independence Day" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Target Departments</label>
                        <select name="department_id[]" id="departmentSelect" multiple class="form-select select2">
                            @foreach ($department as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Target Designations</label>
                        <select name="designation_id[]" id="designationSelect" multiple class="form-select select2">
                            {{-- Populated via AJAX --}}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Specific Users (Optional)</label>
                        <select name="working_days[]" multiple id="workingDays" class="form-select select2">
                            {{-- Populated via AJAX --}}
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Description / Notes</label>
                        <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Brief details about the holiday...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end py-3 border-0">
                <a href="{{ route('admin.hr.holidays.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Holiday</button>
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

        // Reuse AJAX logic from policies
        $('#departmentSelect').on('change', function() {
            var departmentIds = $(this).val();
            if (departmentIds && departmentIds.length > 0) {
                $.ajax({
                    url: "{{ route('admin.hr.get-designations') }}",
                    method: 'POST',
                    data: {
                        department_ids: departmentIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        $('#designationSelect').html('');
                        data.forEach(function(item) {
                            $('#designationSelect').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                        $('#designationSelect').trigger('change');
                    }
                });
            } else {
                $('#designationSelect').html('').trigger('change');
            }
        });

        $('#designationSelect').on('change', function() {
            var designationIds = $(this).val();
            if (designationIds && designationIds.length > 0) {
                $.ajax({
                    url: "{{ route('admin.hr.get-users') }}",
                    method: 'POST',
                    data: {
                        designation_ids: designationIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        $('#workingDays').html('');
                        data.forEach(function(item) {
                            $('#workingDays').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                        $('#workingDays').trigger('change');
                    }
                });
            } else {
                $('#workingDays').html('').trigger('change');
            }
        });
    });
</script>
@endpush
