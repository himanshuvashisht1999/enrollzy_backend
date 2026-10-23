@extends('admin.layouts.master')

@section('title', 'Calling History')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Student Outreach History & Timeline</h6>
            <div class="d-flex gap-2 align-items-center">
                @if(auth()->user()->role != 'staff')
                <select id="staffFilter" class="form-select form-select-sm rounded-pill" style="width: 200px;">
                    <option value="">All Staff</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>
                @endif
                <button class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-excel me-1"></i> Import Data
                </button>
            </div>
        </div>

        
        <div class="card-body border-bottom bg-light">
            <form id="filterForm" class="row">
                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="from_date" id="fromDateFilter" class="form-control rounded-3">
                </div>
                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="to_date" id="toDateFilter" class="form-control rounded-3">
                </div>
                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">Reminder Date</label>
                    <input type="date" name="reminder_date" id="reminderDateFilter" class="form-control rounded-3">
                </div>
                
                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">Category</label>
                    <select name="category" class="form-select rounded-3" id="categoryFilter">
                        <option value="">Select Category</option>
                        @php
                            function renderCategoryOptions($categories, $level = 0) {
                                foreach ($categories as $cat) {
                                    echo '<option value="'.$cat->id.'">'.str_repeat("— ", $level).$cat->name.'</option>';
                                    if ($cat->childrenRecursive && $cat->childrenRecursive->count()) {
                                        renderCategoryOptions($cat->childrenRecursive, $level + 1);
                                    }
                                }
                            }
                        @endphp
                        @if(isset($categories))
                            @php renderCategoryOptions($categories); @endphp
                        @endif
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">Calling Status</label>
                    <select name="call_status_id" id="statusFilter" class="form-select rounded-3">
                        <option value="">Select Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">Calling Action</label>
                    <select name="call_action_id" id="actionFilter" class="form-select rounded-3">
                        <option value="">Select Action</option>
                        @foreach($actions as $action)
                            <option value="{{ $action->id }}">{{ $action->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">Country</label>
                    <select name="country" class="form-select rounded-3" id="countryFilter">
                        <option value="">Select Country</option>
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">State</label>
                    <select name="state" class="form-select rounded-3" id="stateFilter">
                        <option value="">Select State</option>
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">City</label>
                    <select name="city" class="form-select rounded-3" id="cityFilter">
                        <option value="">Select City</option>
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">Student Name</label>
                    <input type="text" name="filter_name" id="nameFilter" class="form-control rounded-3" placeholder="Search by Name">
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <label class="form-label small fw-bold">Phone Number</label>
                    <input type="text" name="filter_phone" id="phoneFilter" class="form-control rounded-3" placeholder="Search by Phone">
                </div>

                <div class="col-12 mt-2">
                    <button class="btn btn-primary px-4 rounded-pill btn-sm" type="submit" id="submitSearchButton"><i class="fas fa-search me-1"></i> Search</button>
                    <button type="button" class="btn btn-secondary px-4 rounded-pill btn-sm" id="resetBtn"><i class="fas fa-sync-alt me-1"></i> Reset</button>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="historyTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Status/Action</th>
                            <th>Staff Member</th>
                            <th>Call Date</th>
                            <th>Next Call</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Import Calling History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importForm" action="{{ route('admin.students-crm.calling-history.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pb-0">
                    <div class="mb-3 d-flex justify-content-between">
                        <label class="form-label small fw-bold">Upload Excel File <span class="text-danger">*</span></label>
                        <a href="{{ route('admin.students-crm.calling-history.sample') }}" class="text-success small fw-bold"><i class="fas fa-download"></i> Download Sample</a>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="file" id="file" class="form-control rounded-3" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info small rounded-3 mt-3">
                        <i class="fas fa-info-circle me-1"></i> Ensure columns: <b>phone_number, student_name, category_id, call_status_id, action_taken_id, reminder_date, comment</b>.<br>
                        Students will be automatically created or updated based on the phone number.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4" id="importBtn">Import</button>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Call Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pb-0">
                <table class="table table-bordered">
                    <tbody>
                        <tr><th>Student Name</th><td id="detailName"></td></tr>
                        <tr><th>Phone Number</th><td id="detailPhone"></td></tr>
                        <tr><th>Category</th><td id="detailCategory"></td></tr>
                        <tr><th>Call Status</th><td id="detailStatus"></td></tr>
                        <tr><th>Call Action</th><td id="detailAction"></td></tr>
                        <tr><th>Call Date</th><td id="detailDate"></td></tr>
                        <tr><th>Next Call Date</th><td id="detailNextCall"></td></tr>
                        <tr><th>Staff Member</th><td id="detailStaff"></td></tr>
                        <tr><th>Remarks</th><td id="detailRemarks"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#historyTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.students-crm.calling-history.index') }}",
                data: function(d) {
                    if ($('#staffFilter').length) d.staff_id = $('#staffFilter').val();
                    d.from_date = $('#fromDateFilter').val();
                    d.to_date = $('#toDateFilter').val();
                    d.reminder_date = $('#reminderDateFilter').val();
                    d.category = $('#categoryFilter').val();
                    d.call_status_id = $('#statusFilter').val();
                    d.call_action_id = $('#actionFilter').val();
                    d.country = $('#countryFilter').val();
                    d.state = $('#stateFilter').val();
                    d.city = $('#cityFilter').val();
                    d.filter_name = $('#nameFilter').val();
                    d.filter_phone = $('#phoneFilter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'customer_info', name: 'customer_info' },
                { 
                    data: null, 
                    render: function(data) {
                        return `<span class="badge bg-soft-info">${data.status_info}</span><br><small>${data.action_info}</small>`;
                    }
                },
                { data: 'staff_info', name: 'staff_info' },
                { data: 'call_date', name: 'call_date' },
                { data: 'date_required', name: 'date_required' },
                { data: 'comment', name: 'comment' }
            ],
            language: { search: "_INPUT_", searchPlaceholder: "Search history..." }
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        $('#resetBtn').on('click', function() {
            $('#filterForm')[0].reset();
            table.ajax.reload();
        });

        $('#staffFilter').on('change', function() {
            table.ajax.reload();
        });

        // Countries API
        const API_BASE = 'https://countriesnow.space/api/v0.1';
        loadCountries();

        $('#countryFilter').on('change', function () {
            const country = $(this).val();
            $('#stateFilter').html('<option value="">Select State</option>');
            $('#cityFilter').html('<option value="">Select City</option>');
            if (country) loadStates(country);
        });

        $('#stateFilter').on('change', function () {
            const country = $('#countryFilter').val();
            const state   = $(this).val();
            $('#cityFilter').html('<option value="">Select City</option>');
            if (country && state) loadCities(country, state);
        });

        function loadCountries() {
            $.get(API_BASE + '/countries', function(res){
                let html = '<option value="">Select Country</option>';
                res.data.forEach(c => {
                    html += `<option value="${c.country}">${c.country}</option>`;
                });
                $('#countryFilter').html(html);
            });
        }

        function loadStates(country) {
            $.ajax({
                type: 'POST',
                url: API_BASE + '/countries/states',
                contentType: 'application/json',
                data: JSON.stringify({ country }),
                success: function(res){
                    let html = '<option value="">Select State</option>';
                    res.data.states.forEach(s => {
                        html += `<option value="${s.name}">${s.name}</option>`;
                    });
                    $('#stateFilter').html(html);
                }
            });
        }

        function loadCities(country, state) {
            $.ajax({
                type: 'POST',
                url: API_BASE + '/countries/state/cities',
                contentType: 'application/json',
                data: JSON.stringify({ country, state }),
                success: function(res){
                    let html = '<option value="">Select City</option>';
                    res.data.forEach(city => {
                        html += `<option value="${city}">${city}</option>`;
                    });
                    $('#cityFilter').html(html);
                }
            });
        }

        // Show details modal
        $(document).on('click', '.show-details', function() {
            let rowData = $(this).data('row');
            if(typeof rowData === 'string') {
                rowData = JSON.parse(rowData);
            }
            
            $('#detailName').text(rowData.user_name || (rowData.customer ? rowData.customer.name : 'N/A'));
            $('#detailPhone').text(rowData.user_phone || (rowData.customer ? rowData.customer.phone : 'N/A'));
            $('#detailCategory').text(rowData.customer && rowData.customer.category ? rowData.customer.category.name : 'N/A');
            $('#detailStatus').text(rowData.calling_status ? rowData.calling_status.name : 'N/A');
            $('#detailAction').text(rowData.calling_action ? rowData.calling_action.name : 'N/A');
            $('#detailDate').text(rowData.created_at ? rowData.created_at.substring(0, 19).replace('T', ' ') : 'N/A');
            $('#detailNextCall').text(rowData.date_required ? rowData.date_required : 'N/A');
            $('#detailStaff').text(rowData.staff ? rowData.staff.name : 'N/A');
            $('#detailRemarks').text(rowData.comment ? rowData.comment : 'N/A');
            
            // The modal is automatically shown by data-bs-toggle="modal"
        });

        $('#importForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $('#importBtn').prop('disabled', true).text('Importing...');

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#importBtn').prop('disabled', false).text('Import');
                    if(res.status == 1) {
                        $('#importModal').modal('hide');
                        $('#importForm')[0].reset();
                        $('#historyTable').DataTable().ajax.reload();
                        Swal.fire('Success', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(err) {
                    $('#importBtn').prop('disabled', false).text('Import');
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });
    });

    function updateStatus(element, itemId) {
        const status = element.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!status) return;

            $.ajax({
                url: `/admin/students-crm/calling-history/history-update-status/${itemId}`,
                type: 'POST',
            data: {
                _token: csrfToken ? csrfToken.getAttribute('content') : '{{ csrf_token() }}',
                status: status
            },
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Status updated successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#historyTable').DataTable().ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to update status', 'error');
            }
        });
    }

    $(document).on('click', '.show-logs', function() {
        const logsJson = $(this).attr('data-logs') || '[]';
        let logs = [];
        try {
            logs = JSON.parse(logsJson);
        } catch (e) {
            console.error('Invalid logs JSON', e);
        }

        const tbody = $('#logsTableBody');
        tbody.empty();

        if (!logs.length) {
            tbody.append('<tr><td colspan="5" class="text-center">No logs found</td></tr>');
        } else {
            logs.forEach(function (log, index) {
                let actionName = log.calling_action ? log.calling_action.name : 'N/A';
                let updatedBy = log.user ? log.user.name : 'N/A';
                let dateFormatted = '';
                if (log.created_at) {
                    const d = new Date(log.created_at);
                    if (!isNaN(d)) {
                        dateFormatted = d.toLocaleString('en-GB', {
                            day: '2-digit', month: '2-digit', year: 'numeric',
                            hour: '2-digit', minute: '2-digit', hour12: true
                        });
                    }
                }

                tbody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${log.log_type || 'N/A'}</td>
                        <td>${actionName}</td>
                        <td>${updatedBy}</td>
                        <td>${dateFormatted}</td>
                    </tr>
                `);
            });
        }
    });
</script>

<div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" id="logsModalLabel">Calling History Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Log Type</th>
                                <th>Calling Action</th>
                                <th>Updated By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

