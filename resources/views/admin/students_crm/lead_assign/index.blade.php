@extends('admin.layouts.master')

@section('title', 'Lead Assignment Dashboard')

@section('content')
<div class="container-fluid">

    <!-- Analytics Header Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary">
                        <i class="fas fa-database fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Total CRM Leads</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($totalLeads) }}</h3>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted small">
                        <span>Total student database</span>
                        <span>100%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success">
                        <i class="fas fa-user-check fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Leads Assigned</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($totalAssigned) }}</h3>
                    </div>
                </div>
                <div class="mt-3">
                    @php
                        $assignedPercent = $totalLeads > 0 ? round(($totalAssigned / $totalLeads) * 100, 1) : 0;
                    @endphp
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $assignedPercent }}%;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted small">
                        <span>Assigned to staff queue</span>
                        <span>{{ $assignedPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-warning">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Unassigned / Pending</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($totalPending) }}</h3>
                    </div>
                </div>
                <div class="mt-3">
                    @php
                        $pendingPercent = $totalLeads > 0 ? round(($totalPending / $totalLeads) * 100, 1) : 0;
                    @endphp
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingPercent }}%;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted small">
                        <span>Awaiting assignment</span>
                        <span>{{ $pendingPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="row g-4">
        <!-- Left Side: Assign leads tool and History overview -->
        <div class="col-lg-7">
            <!-- Assignment Tool Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Systematic Lead Assigner</h5>
                        <p class="text-muted small mb-0">Filter leads and assign index ranges to selected team members.</p>
                    </div>
                    <span class="badge bg-light text-primary px-3 py-2 rounded-3 border">
                        <i class="fas fa-sliders-h me-1"></i> Interactive
                    </span>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.students-crm.lead-assign.store') }}" method="POST" id="leadAssignForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Category Pool</label>
                                <select name="category_id" id="filter_category_id" class="form-select rounded-3">
                                    <option value="">All Categories</option>
                                    @php
                                        if (!function_exists('renderCategoryOptions')) {
                                            function renderCategoryOptions($categories, $level = 0) {
                                                foreach ($categories as $cat) {
                                                    echo '<option value="'.$cat->id.'">';
                                                    echo str_repeat("— ", $level).$cat->name;
                                                    echo '</option>';
                                                    if ($cat->childrenRecursive && $cat->childrenRecursive->count()) {
                                                        renderCategoryOptions($cat->childrenRecursive, $level + 1);
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    @php renderCategoryOptions($categories); @endphp
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Call Status Filter</label>
                                <select name="call_status_id" id="filter_call_status_id" class="form-select rounded-3">
                                    <option value="">No Filter (Default)</option>
                                    <option value="all">All Statuses</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Live Filter Counts Display -->
                            <div class="col-12 mt-2">
                                <div class="p-3 bg-light rounded-3 border d-flex justify-content-around align-items-center text-center">
                                    <div>
                                        <small class="text-muted d-block small fw-bold text-uppercase">Pool Size</small>
                                        <span class="fw-bold text-dark fs-5" id="live_total_count"><i class="fas fa-spinner fa-spin text-muted"></i></span>
                                    </div>
                                    <div class="vr" style="height: 30px;"></div>
                                    <div>
                                        <small class="text-muted d-block small fw-bold text-uppercase">Assigned</small>
                                        <span class="fw-bold text-success fs-5" id="live_assigned_count"><i class="fas fa-spinner fa-spin text-muted"></i></span>
                                    </div>
                                    <div class="vr" style="height: 30px;"></div>
                                    <div>
                                        <small class="text-muted d-block small fw-bold text-uppercase">Available</small>
                                        <span class="fw-bold text-warning fs-5" id="live_pending_count"><i class="fas fa-spinner fa-spin text-muted"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label small fw-bold text-muted">Start Index</label>
                                <input type="number" name="start_number" id="start_number" class="form-control rounded-3" min="1" required placeholder="e.g. 1" value="1">
                            </div>
                            
                            <div class="col-md-4 mt-3">
                                <label class="form-label small fw-bold text-muted">End Index</label>
                                <input type="number" name="end_number" id="end_number" class="form-control rounded-3" min="1" required placeholder="e.g. 50">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label small fw-bold text-muted">Assign to Staff</label>
                                <select name="staff_id" class="form-select rounded-3" required>
                                    <option value="">Select Staff</option>
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->role }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold" id="submitAssignBtn">
                                    <i class="fas fa-user-plus me-2"></i> Assign Leads
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lead Assignment History List -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0">Leads Assignment Log</h5>
                    <p class="text-muted small mb-0">View previous batch assignments, audit distributions, or revoke allocations.</p>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 rounded-start">Staff Name</th>
                                    <th class="border-0">Leads Count</th>
                                    <th class="border-0">Allocation Date</th>
                                    <th class="border-0 text-end rounded-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignmentsSummary as $summary)
                                    @php
                                        $timestamp = strtotime($summary->batch_date);
                                    @endphp
                                    <tr id="batch-row-{{ $summary->staff_id }}-{{ $timestamp }}">
                                        <td class="fw-semibold">{{ $summary->staff->name ?? 'Unknown Staff' }}</td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 rounded-pill">
                                                {{ $summary->total_leads }} Leads
                                            </span>
                                        </td>
                                        <td class="text-muted small">
                                            {{ \Carbon\Carbon::parse($summary->batch_date)->format('d M, Y h:i A') }}
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 me-2 view-batch-btn"
                                                    data-staff-id="{{ $summary->staff_id }}" 
                                                    data-staff-name="{{ $summary->staff->name ?? 'Unknown' }}" 
                                                    data-batch-date="{{ $summary->batch_date }}"
                                                    data-formatted-date="{{ \Carbon\Carbon::parse($summary->batch_date)->format('d M Y, h:i A') }}">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 revoke-batch-btn" 
                                                    data-staff-id="{{ $summary->staff_id }}" 
                                                    data-batch-date="{{ $summary->batch_date }}">
                                                    <i class="fas fa-undo-alt me-1"></i> Revoke
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-history fs-1 text-muted opacity-25 mb-3"></i>
                                            <p class="mb-0">No lead allocation logs found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Staff Leaderboard and active queue status -->
        <div class="col-lg-5">
            <!-- Staff Workload monitor -->
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0">Team Workload Monitor</h5>
                    <p class="text-muted small mb-0">Overview of assigned queues and progress status across active staff.</p>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-4">
                        @forelse($staffStats as $sId => $stats)
                            @php
                                $s = $stats['staff'];
                                $assigned = $stats['assigned'];
                                $worked = $stats['worked'];
                                $pending = $stats['pending'];
                                $progress = $assigned > 0 ? round(($worked / $assigned) * 100) : 0;
                            @endphp
                            <div class="border-bottom pb-3 last-border-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">{{ $s->name }}</h6>
                                        <small class="text-muted">{{ ucfirst($s->role) }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border px-2 py-1 rounded-3 small">
                                            {{ $worked }}/{{ $assigned }} Worked
                                        </span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-3 small ms-1">
                                            {{ $pending }} Pending
                                        </span>
                                    </div>
                                </div>
                                <div class="progress rounded-pill" style="height: 8px;">
                                    <div class="progress-bar bg-success progress-bar-striped" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1 text-muted" style="font-size: 0.75rem;">
                                    <span>Work completion rate</span>
                                    <span class="fw-semibold">{{ $progress }}%</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fs-1 text-muted opacity-25 mb-3"></i>
                                <p class="mb-0">No active staff members found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: View Batch Details -->
<div class="modal fade" id="batchDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="modalBatchStaffName">Allocation Details</h5>
                    <small class="text-muted" id="modalBatchDate">Batch Date: </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Phone Number</th>
                                <th>City</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody id="batchLeadsTableBody">
                            <!-- Populated dynamically via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
</div>
    </div>
</div>

@endsection

@push('css')
<style>
    #content {
        margin-left: 270px !important;
    }
    .last-border-none:last-child {
        border-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Fetch live counts on filters change
    function fetchLiveCounts() {
        let catId = $('#filter_category_id').val();
        let statusId = $('#filter_call_status_id').val();

        $('#live_total_count').html('<i class="fas fa-spinner fa-spin text-muted"></i>');
        $('#live_assigned_count').html('<i class="fas fa-spinner fa-spin text-muted"></i>');
        $('#live_pending_count').html('<i class="fas fa-spinner fa-spin text-muted"></i>');

        $.ajax({
            url: "{{ route('admin.students-crm.lead-assign.get-counts') }}",
            type: "GET",
            data: {
                category_id: catId,
                call_status_id: statusId
            },
            success: function(res) {
                $('#live_total_count').text(res.total.toLocaleString());
                $('#live_assigned_count').text(res.assigned.toLocaleString());
                $('#live_pending_count').text(res.pending.toLocaleString());
                
                // Adjust max bounds for inputs
                $('#start_number').attr('max', res.pending);
                $('#end_number').attr('max', res.pending);
                if(res.pending > 0) {
                    $('#end_number').val(Math.min(res.pending, 50));
                } else {
                    $('#end_number').val('');
                }
            },
            error: function() {
                $('#live_total_count').text('N/A');
                $('#live_assigned_count').text('N/A');
                $('#live_pending_count').text('N/A');
            }
        });
    }

    // Trigger on page load and dropdown change
    fetchLiveCounts();
    $('#filter_category_id, #filter_call_status_id').on('change', fetchLiveCounts);

    // View Batch Details Modal Trigger
    $('.view-batch-btn').on('click', function() {
        let staffId = $(this).data('staff-id');
        let staffName = $(this).data('staff-name');
        let batchDate = $(this).data('batch-date');
        let formattedDate = $(this).data('formatted-date');

        $('#modalBatchStaffName').text('Leads Assigned to ' + staffName);
        $('#modalBatchDate').text('Batch Allocated on: ' + formattedDate);
        $('#batchLeadsTableBody').html('<tr><td colspan="5" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i><br>Loading details...</td></tr>');
        
        $('#batchDetailsModal').modal('show');

        $.ajax({
            url: "{{ route('admin.students-crm.lead-assign.batch-details') }}",
            type: "GET",
            data: {
                staff_id: staffId,
                batch_date: batchDate
            },
            success: function(res) {
                if(res.status == 1 && res.leads.length > 0) {
                    let html = '';
                    res.leads.forEach(l => {
                        html += `<tr>
                            <td class="fw-semibold">${l.id}</td>
                            <td>${l.name}</td>
                            <td class="text-muted">${l.phone}</td>
                            <td>${l.city}</td>
                            <td><span class="badge bg-light text-dark border">${l.category}</span></td>
                        </tr>`;
                    });
                    $('#batchLeadsTableBody').html(html);
                } else {
                    $('#batchLeadsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-muted">No details found for this batch.</td></tr>');
                }
            },
            error: function() {
                $('#batchLeadsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Failed to fetch batch details.</td></tr>');
            }
        });
    });

    // Revoke Batch Allocation Trigger
    $('.revoke-batch-btn').on('click', function() {
        let staffId = $(this).data('staff-id');
        let batchDate = $(this).data('batch-date');
        let button = $(this);

        Swal.fire({
            title: 'Revoke Lead Allocation?',
            text: "This will remove the assigned leads from this staff member's queue and return them to the unassigned pool.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Revoke Batch',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Revoking...');
                $.ajax({
                    url: "{{ route('admin.students-crm.lead-assign.revoke') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        staff_id: staffId,
                        batch_date: batchDate
                    },
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire(
                                'Revoked!',
                                res.message,
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            button.prop('disabled', false).html('<i class="fas fa-undo-alt me-1"></i> Revoke');
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        button.prop('disabled', false).html('<i class="fas fa-undo-alt me-1"></i> Revoke');
                        Swal.fire('Error', 'Failed to revoke lead batch. Please try again.', 'error');
                    }
                });
            }
        });
    });

    // Form submit validation
    $('#leadAssignForm').on('submit', function(e) {
        let catId = $('#filter_category_id').val();
        let statusId = $('#filter_call_status_id').val();
        
        if (!catId && !statusId) {
            e.preventDefault();
            Swal.fire({
                title: 'Filter Required',
                text: 'Please select a Category Pool or a Call Status Filter before assigning leads.',
                icon: 'warning',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
    });
});
</script>
@endpush
