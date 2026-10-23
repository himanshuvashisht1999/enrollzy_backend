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
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">{{ ($isTopLevel ?? false) ? 'Total CRM Leads' : 'Your Lead Pool' }}</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($totalLeads) }}</h3>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted small">
                        <span>{{ ($isTopLevel ?? false) ? 'Total student database' : 'Total quota assigned to you' }}</span>
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
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">{{ ($isTopLevel ?? false) ? 'Leads Assigned' : 'Delegated to Team' }}</h6>
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
                        <span>{{ ($isTopLevel ?? false) ? 'Assigned to staff queue' : 'Delegated to your subordinates' }}</span>
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
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">{{ ($isTopLevel ?? false) ? 'Unassigned / Pending' : 'Available to Assign' }}</h6>
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
                        <span>{{ ($isTopLevel ?? false) ? 'Awaiting initial assignment' : 'Ready to distribute to team' }}</span>
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
                                <label class="form-label small fw-bold text-muted">Lead Pool / Call Status Filter</label>
                                <select name="call_status_id" id="filter_call_status_id" class="form-select rounded-3">
                                    <option value="">🌟 Fresh Leads Only (Never Assigned &amp; Never Contacted)</option>
                                    <optgroup label="♻️ Recycled / Previously Disposed Leads">
                                        <option value="all">All Disposed Statuses</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </optgroup>
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
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Leads Assignment Log</h5>
                        <p class="text-muted small mb-0">View previous batch assignments, audit distributions, or inspect allocations.</p>
                    </div>
                    <span class="badge bg-light text-muted border px-2.5 py-1.5 rounded-3">
                        Total Batches: {{ $assignmentsSummary->total() }}
                    </span>
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
                                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 view-batch-btn"
                                                    data-staff-id="{{ $summary->staff_id }}" 
                                                    data-staff-name="{{ $summary->staff->name ?? 'Unknown' }}" 
                                                    data-batch-date="{{ $summary->batch_date }}"
                                                    data-formatted-date="{{ \Carbon\Carbon::parse($summary->batch_date)->format('d M Y, h:i A') }}">
                                                    <i class="fas fa-eye me-1"></i> View
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

                    @if($assignmentsSummary->hasPages())
                        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap pt-3 border-top">
                            <div class="small text-muted mb-2 mb-md-0">
                                Showing {{ $assignmentsSummary->firstItem() ?? 0 }} to {{ $assignmentsSummary->lastItem() ?? 0 }} of {{ $assignmentsSummary->total() }} batches
                            </div>
                            <div>
                                {{ $assignmentsSummary->withQueryString()->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Side: Staff Leaderboard and active queue status -->
        <div class="col-lg-5">
            <!-- Staff Workload monitor -->
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Team Workload Monitor</h5>
                            <p class="text-muted small mb-0">Overview of assigned queues and progress status across active staff.</p>
                        </div>
                        <span class="badge bg-light text-muted border px-2.5 py-1.5 rounded-3">
                            {{ count($staffStats) }} Staff
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchStaffWorkload" class="form-control bg-light border-start-0" placeholder="Filter staff by name or role...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-2">
                    <div class="d-flex flex-column gap-3 staff-workload-scroll-container" style="max-height: 520px; overflow-y: auto; padding-right: 4px;">
                        @forelse($staffStats as $sId => $stats)
                            @php
                                $s = $stats['staff'];
                                $assigned = $stats['assigned'];
                                $worked = $stats['worked'];
                                $pending = $stats['pending'];
                                $progress = $assigned > 0 ? round(($worked / $assigned) * 100) : 0;
                            @endphp
                            <div class="border-bottom pb-3 last-border-none staff-workload-item" data-staff-name="{{ strtolower($s->name) }}" data-staff-role="{{ strtolower($s->role) }}">
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

                <!-- Modal Pagination Controls -->
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <div class="small text-muted" id="modalPaginationInfo">Loading...</div>
                    <div class="btn-group btn-group-sm" id="modalPaginationControls">
                        <button type="button" class="btn btn-outline-secondary rounded-start-pill px-3" id="modalPrevPageBtn" disabled>
                            <i class="fas fa-chevron-left me-1"></i> Prev
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-end-pill px-3" id="modalNextPageBtn" disabled>
                            Next <i class="fas fa-chevron-right ms-1"></i>
                        </button>
                    </div>
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
    .staff-workload-scroll-container::-webkit-scrollbar {
        width: 6px;
    }
    .staff-workload-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .staff-workload-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .staff-workload-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Live filter counts on filters change
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

    // Filter staff in Workload Monitor
    $('#searchStaffWorkload').on('keyup input', function() {
        let q = $(this).val().toLowerCase().trim();
        $('.staff-workload-item').each(function() {
            let name = $(this).data('staff-name') || '';
            let role = $(this).data('staff-role') || '';
            if (name.indexOf(q) !== -1 || role.indexOf(q) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Batch Details Modal Pagination State
    let activeBatchStaffId = null;
    let activeBatchDate = null;
    let activeBatchCurrentPage = 1;
    let activeBatchLastPage = 1;

    function loadBatchDetailsPage(staffId, batchDate, page) {
        $('#batchLeadsTableBody').html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i><br>Loading leads...</td></tr>');
        $('#modalPrevPageBtn').prop('disabled', true);
        $('#modalNextPageBtn').prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.students-crm.lead-assign.batch-details') }}",
            type: "GET",
            data: {
                staff_id: staffId,
                batch_date: batchDate,
                page: page
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

                    let pag = res.pagination;
                    activeBatchCurrentPage = pag.current_page;
                    activeBatchLastPage = pag.last_page;

                    let fromItem = ((pag.current_page - 1) * pag.per_page) + 1;
                    let toItem = Math.min(pag.current_page * pag.per_page, pag.total);
                    $('#modalPaginationInfo').text(`Showing ${fromItem}–${toItem} of ${pag.total} leads (Page ${pag.current_page} of ${pag.last_page})`);

                    $('#modalPrevPageBtn').prop('disabled', pag.current_page <= 1);
                    $('#modalNextPageBtn').prop('disabled', pag.current_page >= pag.last_page);
                } else {
                    $('#batchLeadsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-muted">No details found for this batch.</td></tr>');
                    $('#modalPaginationInfo').text('0 leads');
                    $('#modalPrevPageBtn').prop('disabled', true);
                    $('#modalNextPageBtn').prop('disabled', true);
                }
            },
            error: function() {
                $('#batchLeadsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Failed to fetch batch details.</td></tr>');
                $('#modalPaginationInfo').text('Error loading data');
            }
        });
    }

    // View Batch Details Modal Trigger
    $('.view-batch-btn').on('click', function() {
        activeBatchStaffId = $(this).data('staff-id');
        activeBatchDate = $(this).data('batch-date');
        activeBatchCurrentPage = 1;

        let staffName = $(this).data('staff-name');
        let formattedDate = $(this).data('formatted-date');

        $('#modalBatchStaffName').text('Leads Assigned to ' + staffName);
        $('#modalBatchDate').text('Batch Allocated on: ' + formattedDate);
        $('#batchDetailsModal').modal('show');

        loadBatchDetailsPage(activeBatchStaffId, activeBatchDate, 1);
    });

    // Modal Pagination Prev/Next Handlers
    $('#modalPrevPageBtn').on('click', function() {
        if (activeBatchCurrentPage > 1) {
            loadBatchDetailsPage(activeBatchStaffId, activeBatchDate, activeBatchCurrentPage - 1);
        }
    });

    $('#modalNextPageBtn').on('click', function() {
        if (activeBatchCurrentPage < activeBatchLastPage) {
            loadBatchDetailsPage(activeBatchStaffId, activeBatchDate, activeBatchCurrentPage + 1);
        }
    });

    // Form submit validation & double-submission prevention
    $('#leadAssignForm').on('submit', function(e) {
        let staffId = $('select[name="staff_id"]').val();
        let startNum = parseInt($('#start_number').val()) || 0;
        let endNum = parseInt($('#end_number').val()) || 0;

        if (!staffId) {
            e.preventDefault();
            Swal.fire({
                title: 'Staff Required',
                text: 'Please select a staff member to assign the leads to.',
                icon: 'warning',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        if (startNum <= 0 || endNum < startNum) {
            e.preventDefault();
            Swal.fire({
                title: 'Invalid Range',
                text: 'Please ensure Start Index is at least 1 and End Index is greater than or equal to Start Index.',
                icon: 'warning',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        let submitBtn = $('#submitAssignBtn');
        if (submitBtn.data('submitting')) {
            e.preventDefault();
            return false;
        }

        submitBtn.data('submitting', true).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Assigning Leads...');
        return true;
    });
});
</script>
@endpush
