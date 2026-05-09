@extends('admin.layouts.master')

@section('title', 'Applied Leaves')

@push('css')
<style>
    .fa-primary { color: #4e73df; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm mb-4 border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Applied Leaves</h6>
            <div>
                @if (auth()->user()->is_admin)
                    <a class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#changePolicyModal" href="javascript:;">
                        <i class="fas fa-edit me-1"></i> Edit Policy Text
                    </a>
                @endif
                <a class="btn btn-outline-info btn-sm rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#viewLeavePolicyModal" href="javascript:;">
                    <i class="fas fa-eye me-1"></i> View Policies
                </a>
                <a class="btn btn-primary btn-sm rounded-pill px-3" href="{{ route('admin.hr.leaves.create') }}">
                    <i class="fas fa-plus me-1"></i> Apply Leave
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="leavesTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>From to Till</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leave as $val)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $val->employee->name ?? 'System' }}</div>
                                    <small class="text-muted">{{ $val->employee->email ?? '' }}</small>
                                </td>
                                <td>{{ Str::limit($val->subject, 40) }}</td>
                                <td>
                                    <span class="text-primary">{{ date('d M, Y', strtotime($val->date_from)) }}</span>
                                    <span class="text-mutedmx-1"> - </span>
                                    <span class="text-primary">{{ date('d M, Y', strtotime($val->date_till)) }}</span>
                                </td>
                                <td>{{ date('d M, Y', strtotime($val->return_date)) }}</td>
                                <td>{!! GetStatusBadge($val->status) !!}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.hr.leaves.edit', encrypt($val->id)) }}" class="btn btn-light btn-sm rounded-circle">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Update Leave Policy Modal --}}
        <div class="modal fade" id="changePolicyModal" tabindex="-1" aria-labelledby="changePolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="changePolicyModalLabel">Update Leave Policy Text</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea name="leave_policy" id="leave_policy_editor" class="form-control">{!! $leavePolicyText !!}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4" id="updatePolicyBtn">Update Policy</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- View Leave Policy Modal --}}
        <div class="modal fade" id="viewLeavePolicyModal" tabindex="-1" aria-labelledby="viewLeavePolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="viewLeavePolicyModalLabel">Assigned Leave Policies</h5>
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
        if ($('#leave_policy_editor').length) {
            initializeTinyMCE('#leave_policy_editor', 400);
        }

        $('#updatePolicyBtn').click(function(e) {
            e.preventDefault();
            var leavePolicyContent = tinymce.get('leave_policy_editor').getContent();
            if (leavePolicyContent.trim() === "") {
                alert('Leave policy cannot be empty');
                return;
            }
            
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');

            $.ajax({
                url: "{{ route('admin.settings.update') }}", // Using main project's setting update route
                type: 'POST',
                data: {
                    leave_policy: leavePolicyContent,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function(xhr) {
                    alert('Error updating policy');
                    $('#updatePolicyBtn').prop('disabled', false).text('Update Policy');
                }
            });
        });
    });
</script>
@endpush
