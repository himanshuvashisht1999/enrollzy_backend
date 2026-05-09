@extends('admin.layouts.master')

@section('title', 'Apply for Leave')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm mb-4 border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Leave Application Form</h6>
            <a class="btn btn-outline-info btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#viewLeavePolicyModal" href="javascript:;">
                <i class="fas fa-eye me-1"></i> View Leave Policies
            </a>
        </div>
        <div class="card-body">
            <form class="row g-3" id="applyLeaveForm" method="POST" action="{{ route('admin.hr.leaves.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                    <select name="leave_type_id" id="leave_type" class="form-select rounded-3" required>
                        <option value="">Select Leave Type</option>
                        @foreach ($leaveSetting as $lStng)
                            <option value="{{ $lStng->id }}" {{ old('leave_type_id') == $lStng->id ? 'selected' : '' }}> {{ $lStng->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="subject" value="{{ old('subject') }}" placeholder="Briefly describe the reason" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Leave From <span class="text-danger">*</span></label>
                    <input type="date" class="form-control rounded-3" name="date_from" id="date_from" value="{{ old('date_from') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Leave Till <span class="text-danger">*</span></label>
                    <input type="date" class="form-control rounded-3" name="date_till" id="date_till" value="{{ old('date_till') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Return Date</label>
                    <input type="date" class="form-control rounded-3 bg-light" name="return_date" id="return_date" value="{{ old('return_date') }}" readonly>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Detailed Reason <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control rounded-3" rows="4" placeholder="Explain your reason for absence..." required>{{ old('content') }}</textarea>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Attachments (Optional)</label>
                    <input type="file" multiple name="files[]" class="form-control rounded-3">
                    <div class="form-text text-muted small">You can select multiple files (medical certificates, etc.)</div>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white py-3 text-end border-0">
            <a href="{{ route('admin.hr.leaves.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
            <button type="submit" form="applyLeaveForm" class="btn btn-primary rounded-pill px-4">Submit Application</button>
        </div>

        {{-- View Leave Policy Modal --}}
        <div class="modal fade" id="viewLeavePolicyModal" tabindex="-1" aria-labelledby="viewLeavePolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">All Leave Policies</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body overflow-auto" style="max-height: 400px;">
                        <div class="list-group list-group-flush">
                            @forelse($policies as $Policy)
                                <div class="list-group-item border-0 py-3 mb-2 bg-light rounded-3">
                                    <h6 class="fw-bold mb-2">{{ $Policy->name ?? 'Policy' }}</h6>
                                    <div class="text-muted small">
                                        {!! $Policy->policy !!}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">No specific policies assigned.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#date_till').on('change', function() {
            var dateTillVal = $(this).val();
            if (dateTillVal) {
                var dateTill = new Date(dateTillVal);
                dateTill.setDate(dateTill.getDate() + 1);
                var returnDate = dateTill.toISOString().split('T')[0];
                $('#return_date').val(returnDate);
            }
        });
    });
</script>
@endpush
