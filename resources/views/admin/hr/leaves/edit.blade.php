@extends('admin.layouts.master')

@section('title', 'Leave Application Details')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm mb-4 border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Leave Application Info</h6>
            <a class="btn btn-outline-info btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#viewLeavePolicyModal" href="javascript:;">
                <i class="fas fa-eye me-1"></i> View Leave Policy Text
            </a>
        </div>
        <div class="card-body">
            <form class="row g-3" id="updateLeaveForm" method="POST" action="{{ route('admin.hr.leaves.update', encrypt($leave->id)) }}">
                @csrf
                @method('PATCH')
                
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Subject / Title</label>
                    <div class="d-flex align-items-center">
                        <input type="text" class="form-control rounded-3 bg-light" readonly value="{{ $leave->subject }}">
                        <span class="ms-2">{!! GetStatusBadge($leave->status) !!}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Application Date</label>
                    <input type="text" class="form-control rounded-3 bg-light" readonly value="{{ date('d M, Y h:i A', strtotime($leave->apply_date)) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">From</label>
                    <input type="text" class="form-control rounded-3 bg-light" readonly value="{{ date('d M, Y', strtotime($leave->date_from)) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Till</label>
                    <input type="text" class="form-control rounded-3 bg-light" readonly value="{{ date('d M, Y', strtotime($leave->date_till)) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Return Date</label>
                    <input type="text" class="form-control rounded-3 bg-light" readonly value="{{ date('d M, Y', strtotime($leave->return_date)) }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Reason for Absence</label>
                    <div class="p-3 bg-light rounded-3 border">
                        {{ $leave->content }}
                    </div>
                </div>

                @if (auth()->user()->is_admin)
                <div class="col-md-12 mt-4 pt-3 border-top">
                    <h6 class="fw-bold mb-3 text-secondary">Review Application (Admin Actions)</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Update Status</label>
                            <select name="status" id="statusChange" class="form-select rounded-3">
                                <option {{ $leave->status == 'pending' ? 'selected' : '' }} value="pending">Pending</option>
                                <option {{ $leave->status == 'approved' ? 'selected' : '' }} value="approved">Approve</option>
                                <option {{ $leave->status == 'rejected' ? 'selected' : '' }} value="rejected">Reject</option>
                                <option {{ $leave->status == 'unapprove' ? 'selected' : '' }} value="unapprove">Unapproved (Leave Taken)</option>
                            </select>
                        </div>
                        <div class="col-md-6 {{ $leave->status == 'unapprove' ? '' : 'd-none' }}" id="fineField">
                            <label class="form-label fw-semibold">Fine / Penalty Amount</label>
                            <input type="number" step="0.01" class="form-control rounded-3" name="fine" value="{{ $penalty }}">
                            <div class="form-text small">Calculated penalty for exceeding monthly limit.</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Admin Remarks</label>
                            <textarea name="log" class="form-control rounded-3" rows="3" placeholder="Add a comment for the applicant...">{{ old('log') }}</textarea>
                        </div>
                    </div>
                </div>
                @endif
            </form>

            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold mb-3">Attachments</h6>
                <div class="row g-3">
                    @php $leaveFile = array_filter(explode(',', $leave->files)); @endphp
                    @forelse ($leaveFile as $file)
                        @php
                            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $fileUrl = asset($file);
                        @endphp
                        <div class="col-md-3">
                            <div class="card h-100 border rounded-3 overflow-hidden">
                                @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                    <img src="{{ $fileUrl }}" alt="Attachment" class="card-img-top" style="height: 150px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="fas {{ $extension == 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-alt text-primary' }} fa-3x"></i>
                                    </div>
                                @endif
                                <div class="card-body p-2 text-center bg-white">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                        View {{ strtoupper($extension) }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small ps-3">No attachments provided.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold mb-3">Action History (Logs)</h6>
                <div class="timeline-simple ps-3">
                    @forelse ($logData as $log)
                        <div class="mb-3 ps-3 border-start border-2 border-primary position-relative">
                            <div class="fw-bold small">{!! GetStatusBadge($log['status']) !!} <span class="text-muted ms-2">{{ date('d M, Y h:i A', strtotime($log['timestamp'])) }}</span></div>
                            <div class="text-dark py-1">{{ $log['admin_message'] }}</div>
                            @if($log['fine'] > 0)
                                <div class="badge bg-danger-subtle text-danger small">Fine Deducted: {{ $log['fine'] }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted small">No processing history yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-0 py-3 text-end">
            <a href="{{ route('admin.hr.leaves.index') }}" class="btn btn-light rounded-pill px-4 me-2">Back to List</a>
            @if (auth()->user()->is_admin)
                <button type="submit" form="updateLeaveForm" class="btn btn-primary rounded-pill px-4">Save Changes</button>
            @endif
        </div>

        {{-- View Leave Policy Modal --}}
        <div class="modal fade" id="viewLeavePolicyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Leave Policy Text</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {!! $leavePolicyText !!}
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
        $('#statusChange').on('change', function () {
            if ($(this).val() === 'unapprove') {
                $('#fineField').removeClass('d-none');
            } else {
                $('#fineField').addClass('d-none');
            }
        });
    });
</script>
@endpush
