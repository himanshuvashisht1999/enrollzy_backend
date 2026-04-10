@extends('layouts.app')
@section('push_css')
<link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
@endsection
@section('content')
<style>
    .toggle-phone {
    padding: 2px 10px;
    border-radius: 999px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    font-size: 12px;
    color: #374151;
    transition: all .2s;
}

.toggle-phone:hover {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
}
.open-status-modal{
    text-decoration: none;
    border: none;
    background: transparent;
    color: gray;
}

</style>
<div class="container-fluid">
   
    
    <div class="card shadow mb-4">

    <!-- Header -->
    <div class="card-header py-3 header-clean">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 font-weight-bold text-primary">Find Calling History</h5>
                <small class="text-muted">Use filters below to refine results</small>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="card-body">

        <form id="sort_product" class="row" action="{{ route('admin.calling.history') }}" name="sort_product">
            @csrf

            <!-- STATUS -->
            <div class="form-group col-lg-3">
                <label>Select Status</label>
                <select name="callingstatus" class="form-control m-2" id="callingstatus">
                    <option value="">Select Calling Status</option>
                    @foreach($CallingStatus as $categories)
                        <option value="{{$categories->id}}"
                            {{ $request->callingstatus == $categories->id ? 'selected' : '' }}>
                            {{$categories->name}}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- DATE (conditional) -->
            <div class="form-group col-lg-3" id="dateInputContainer" style="display:none;">
                <label>Select Date</label>
                <input type="date" name="dateInput" id="dateInput" class="form-control m-2">
            </div>

            <!-- STAFF -->
            <div class="form-group col-lg-3">
                <label>Select Staff</label>
                <select name="staff" class="form-control m-2">
                    <option value="">Select Staff</option>
                    @foreach($staff as $staffs)
                        <option value="{{$staffs->id}}"
                            {{ $request->staff == $staffs->id ? 'selected' : '' }}>
                            {{$staffs->name}}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- FROM -->
            <div class="form-group col-lg-3" id="datefrom">
                <label>From</label>
                <input type="date" name="from" class="form-control m-2" value="{{ $request->from ?? '' }}">
            </div>

            <!-- TO -->
            <div class="form-group col-lg-3" id="dateto">
                <label>To</label>
                <input type="date" name="to" class="form-control m-2" value="{{ $request->to ?? '' }}">
            </div>

            <input type="hidden" name="type" value="search" id="type">

            <!-- BUTTON BAR -->
            <div class="col-lg-12 mt-2 d-flex align-items-center flex-wrap gap-2">

                <button class="btn btn-primary mr-2"
                        type="submit"
                        onClick="submitButton('search')">
                    <i class="fas fa-search mr-1"></i> Search
                </button>

                <button class="btn btn-secondary mr-2"
                        type="submit"
                        onClick="submitButton('export')">
                    <i class="fas fa-file-export mr-1"></i> Export
                </button>

                <a href="{{ route('admin.calling.history') }}" class="btn btn-info mr-2">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>

                <a href="javascript:void(0)"
                   class="btn btn-outline-primary"
                   id="bulkUpdateBtn">
                    <i class="fas fa-users-cog mr-1"></i> Bulk Status Update
                </a>
                <a href="javascript:void(0)"
                class="btn btn-outline-success ml-2"
                id="bulkUploadExcelBtn">
                <i class="fas fa-file-excel mr-1"></i>
                Bulk Status Upload (Excel)
                </a>


            </div>

        </form>
    </div>
</div>

    @if($data)
    <div class="card shadow mb-4" id="DataTableTable">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Calling History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Id</th>
                            <th>User Type</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th style="width:30%">Comment</th>
                            <th>Status</th>
                            <th>Calling Action</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $key=>$item)
                        <tr>
                            <td>
                                <input type="checkbox" class="row-check" value="{{ $item->id }}">
                            </td>
                            <td>#{{$key+1}}</td>
                            <td>{{$item->user_type}}</td>
                            <td>{{$item->user_name}}</td>
                            <td>
                                <span class="phone-text" data-full="{{ $item->user_phone }}">
                                    {{ substr_replace($item->user_phone, '*****', 0, 5) }}
                                </span>

                                <button type="button"
                                        class="btn btn-link btn-sm toggle-phone">
                                    Show
                                </button>
                            </td>

                            <!-- <td>{{ $item->user_phone }}</td> -->
                            <td>{{$item->comment}}</td>
                            <?php
                                $calling_status = DB::table('calling_status')->where('id',$item->reason)->first();
                            ?>
                            <td>{{$calling_status?->name}}</td>
                            <td>

                                <select name="staff" class="form-control m-2" onchange="updateStatus(this, '{{ $item->id }}')">
                                        @foreach($CallingActions as $single_data)
                                            <option value="{{$single_data->id}}"
                                            {{ $item->calling_action_id == $single_data->id ? 'selected' : '' }}>{{$single_data->name}}</option>
                                        @endforeach
                                        <!-- <option value="active"
                                        {{ $item->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="closed"
                                        {{ $item->status == 'closed' ? 'selected' : '' }}>Closed</option>    -->
                                </select>
                            </td>
                            <?php
                                $history_logs = App\Models\CallingHistoryLog::with('user','calling_action')->where('history_id',$item->id)->orderBy('id','asc')->get();

                            ?>
                            <td>
                                <i class="fas fa-info text-primary show-logs" style="cursor: pointer;" data-logs='@json($history_logs)'></i>
                                <i class="fab fa-whatsapp text-success open-whatsapp ml-2"
                                style="cursor: pointer;"
                                data-phone="{{ $item->user_phone }}"
                                data-name="{{ $item->user_name }}">
                                </i>

                                <a class="btn btn-sm btn-primary open-status-modal"
                                    href="javascript:;"
                                    data-toggle="modal"
                                    data-target="#openUpdateCallingStatusModal"
                                    data-userid="{{$item->user_id}}"
                                    data-username="{{$item->user_name}}"
                                    data-userphone="{{$item->user_phone}}"
                                    data-category=""
                                    data-institute="" >
                                    <i class="fas fa-edit"></i>
                                </a>

                        
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
    <nav>
        <ul class="pagination pagination-l">
            {{ $data->links('pagination::bootstrap-4') }}
        </ul>
    </nav>
</div>
        </div>
    </div>
    @endif


</div>

{{-- ----------------------Modal for Add new Product----------------------------------------- --}}
<div class="modal fade" id="openUpdateCallingStatusModal" tabindex="-1" role="dialog"
    aria-labelledby="openUpdateCallingStatusModal" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="openAddProductModalData">Update Calling Status</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('admin.calling.create') }}" method="post" name="sort_product" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" class="form-control" name="group_id" value="{{$request->group}}">
                    <input type="hidden" class="form-control" name="user_id">
                    <input type="hidden" class="form-control" name="user_phone">
                    <input type="hidden" class="form-control" name="category">
                    <input type="hidden" class="form-control" name="institute">
                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label for="name"> Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Name" readonly>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label for="name">Call Status</label>
                            <select name="call_status" class="form-control" id="product_type" required>
                                <option selected disabled>Select</option>
                                @foreach($CallingStatus as $CallStatus)
                                    <option value="{{ $CallStatus->id }}" data-require-date="{{ $CallStatus->date_require }}">{{ $CallStatus->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden date field -->
                        <div class="col-lg-3 form-group" id="date-field" style="display: none;">
                            <label for="call_date">Reminder Date</label>
                            <input type="date" name="call_date" class="form-control" id="call_date">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label for="image"> Image</label>
                            <input type="file" class="form-control" name="image" placeholder="">
                        </div>
                        <div class="col-lg-12 form-group">
                            <label for="name"> Comments</label>
                            <textarea id="message" name="comment" class="form-control" rows="4" cols="50"
                                placeholder="Add Comments Here..."></textarea>
                        </div>

                        <div class="col-lg-6 form-group">
                            <label>
                                <input type="checkbox" id="is_whatsapp_message">
                                Want to send whatsapp message ?
                            </label>
                        </div>


                        <div id="whatsapp_fields" style="display:none;">

                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label for="name">Template</label>
                                    <select name="whatsapp_template_id" class="form-control" id="whatsapp_template_id">
                                        <option value="">Select</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" data-caption="{{ $template->caption }}" data-message="{{ $template->message }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="batch_gap">Caption</label>
                                    <input type="text" class="form-control" name="caption" value="" id="caption">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="image">Image</label>
                                    <input type="file" class="form-control" name="image_whatsapp" accept=".jpg, .jpeg, .png">
                                    <small id="fileHelp" class="form-text text-muted">
                                        Upload an image (jpg, jpeg, png) not exceeding 2MB.
                                    </small>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="start_time">Start Time</label>
                                    <input type="datetime-local" class="form-control" name="start_time" value="{{ now()->format('Y-m-d\TH:i') }}">
                                </div>     
                                
                                <div class="col-md-12 form-group">
                                    <label for="name"> Message</label>
                                    
                                    <textarea name="message" class="form-control"  id="message-editor"  placeholder="Enter message"></textarea>
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <input class="btn btn-primary" type="submit" id="addProductBtn" href="javascript:;">
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="bulkStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('admin.calling.calling_history_update') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Bulk Status Update</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <p>
                        <strong>Total Users Selected: </strong>
                        <span id="selectedCount">0</span>
                    </p>

                    <input type="hidden" name="ids" id="bulkIds">

                    <div class="form-group">
                        <label>Select Status</label>
                        <select name="calling_status" class="form-control" required>
                            <option value="">Select</option>
                            @foreach($CallingStatus as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Update</button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- Modal to show history logs --}}
<div class="modal fade" id="logsModal" tabindex="-1" role="dialog" aria-labelledby="logsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logsModalLabel">Calling History Logs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Log Type</th>
                                <th>Calling Action</th>
                                <th>Updated By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            {{-- Filled dynamically by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- WhatsApp Send Modal --}}
<div class="modal fade" id="whatsappModal" tabindex="-1" role="dialog" aria-labelledby="whatsappModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.calling.whatsapp_message_send') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="whatsappModalLabel">
                        Send WhatsApp Message
                        <small id="whatsappModalLabelUser" class="text-muted d-block" style="font-size: 0.8rem;"></small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="user_phone" id="whatsapp_user_phone">
                    <input type="hidden" name="user_name" id="whatsapp_user_name">

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="whatsapp_template_id">Template</label>
                            <select name="whatsapp_template_id" class="form-control" id="whatsapp_template_id">
                                <option value="">Select</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}"
                                        data-caption="{{ $template->caption }}"
                                        data-message="{{ $template->message }}">
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-8 form-group">
                            <label for="caption">Caption</label>
                            <input type="text" class="form-control" name="caption" id="caption">
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="image_whatsapp">Image</label>
                            <input type="file" class="form-control" name="image_whatsapp" id="image_whatsapp" accept=".jpg, .jpeg, .png">
                            <small class="form-text text-muted">
                                Upload an image (jpg, jpeg, png) not exceeding 2MB.
                            </small>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="start_time">Start Time</label>
                            <input type="datetime-local" class="form-control" name="start_time" id="start_time"
                                   value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="message-editor">Message</label>
                            <textarea name="message" class="form-control" id="message-editor" placeholder="Enter message"></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Send WhatsApp</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkExcelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('admin.calling.calling_history_upload_excel') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Bulk Status Upload (Excel)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info">
                        Upload Excel with columns:
                        <br><strong>phone</strong> & <strong>calling_status_id</strong>
                    </div>

                    <p>
                        <a href="{{ asset('samplecsv/bulk-calling-status.xlsx') }}"
                           class="btn btn-link">
                           Download Sample File
                        </a>
                    </p>

                    <div class="form-group">
                        <label>Upload Excel File</label>
                        <input type="file"
                               name="file"
                               class="form-control"
                               accept=".xlsx,.xls"
                               required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">
                        Upload & Update
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>




@endsection


<script>
let currentlyVisible = null;

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.toggle-phone');
    if (!btn) return;

    const row = btn.closest('td');
    const span = row.querySelector('.phone-text');

    const full = span.dataset.full;
    const masked = full.replace(full.substring(0, 5), '*****');

    const isShown = btn.dataset.shown === '1';

    // Hide previously visible phone (if another row)
    if (currentlyVisible && currentlyVisible !== span) {
        const prevFull = currentlyVisible.dataset.full;
        currentlyVisible.textContent = prevFull.replace(prevFull.substring(0, 5), '*****');
        currentlyVisible.nextElementSibling.textContent = 'Show';
        currentlyVisible.nextElementSibling.dataset.shown = '0';
    }

    if (!isShown) {
        span.textContent = full;
        btn.textContent = 'Hide';
        btn.dataset.shown = '1';
        currentlyVisible = span;
    } else {
        span.textContent = masked;
        btn.textContent = 'Show';
        btn.dataset.shown = '0';
        currentlyVisible = null;
    }
});


</script>
@push('push_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script>
    $(document).on('click', '.open-status-modal', function () {

        var userid     = $(this).data('userid');
        var username   = $(this).data('username');
        var userphone  = $(this).data('userphone');
        var category   = $(this).data('category');
        var institute  = $(this).data('institute');

        var modal = $('#openUpdateCallingStatusModal');
        modal.find('input[name="user_id"]').val(userid);
        modal.find('input[name="name"]').val(username);
        modal.find('input[name="user_phone"]').val(userphone);
        modal.find('input[name="category"]').val(category);
        modal.find('input[name="institute"]').val(institute);
    });

</script>
<script>
    // select all toggle
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(ch => ch.checked = this.checked);
});

// open modal
document.getElementById('bulkUpdateBtn').addEventListener('click', function () {

    let ids = [];
    document.querySelectorAll('.row-check:checked').forEach(ch => ids.push(ch.value));

    if (!ids.length) {
        alert("Please select at least one user.");
        return;
    }

    document.getElementById('bulkIds').value = JSON.stringify(ids);
    document.getElementById('selectedCount').innerText = ids.length;

    $('#bulkStatusModal').modal('show');
});

</script>
<script>
document.getElementById('bulkUploadExcelBtn').addEventListener('click', function () {
    $('#bulkExcelModal').modal('show');
});
</script>

<script>

document.getElementById('callingstatus').addEventListener('change', function() {
        var callingStatus = this.value;
        var dateInputContainer = document.getElementById('dateInputContainer');
        var dateFrom = document.getElementById('datefrom');
        var dateTo = document.getElementById('dateto');
        
        // If the selected value is 1, show the date input, otherwise hide it
        if (callingStatus == 1) {
            dateInputContainer.style.display = 'block';
            dateFrom.style.display = 'none';
            dateTo.style.display = 'none';
        } else {
            dateInputContainer.style.display = 'none';
            dateFrom.style.display = 'block';
            dateTo.style.display = 'block';
        }
    });

    // Initial check if the page is loaded with a callingstatus already selected
    window.onload = function() {
        var callingStatus = document.getElementById('callingstatus').value;
        var dateInputContainer = document.getElementById('dateInputContainer');
        var dateFrom = document.getElementById('datefrom');
        var dateTo = document.getElementById('dateto');
        if (callingStatus == 1) {
            dateInputContainer.style.display = 'block';
            dateFrom.style.display = 'none';
            dateTo.style.display = 'none';
        }
    }
function updateStatus(element, itemId) {
    const status = element.value;

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found!');
        return;
    }

    fetch(`/admin/calling/history-update-status/${itemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
        },
        body: JSON.stringify({ status: status }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status updated successfully!');
        } else {
            alert('Failed to update status. Please try again.');
        }
    })
    .catch(error => console.error('Error:', error));
}

</script>

<script>
// Handle click on info icon to show logs
document.querySelectorAll('.show-logs').forEach(function (icon) {
    icon.addEventListener('click', function () {
        const logsJson = this.dataset.logs || '[]';
        let logs = [];

        try {
            logs = JSON.parse(logsJson);
        } catch (e) {
            console.error('Invalid logs JSON', e);
        }

        const tbody = document.getElementById('logsTableBody');
        tbody.innerHTML = '';

        if (!logs.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No logs found</td></tr>';
        } else {
            logs.forEach(function (log, index) {

                let actionName = log.calling_action ? log.calling_action.name : 'N/A';
                let updatedBy = log.user ? log.user.name : 'N/A';
                let dateFormatted = '';
                if (log.created_at) {
                    const d = new Date(log.created_at);
                    if (!isNaN(d)) {
                        // ✅ only date:
                        // dateFormatted = d.toLocaleDateString('en-GB');          // 05/12/2025

                        // ✅ date + time:
                        dateFormatted = d.toLocaleString('en-GB', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }); // 05/12/2025, 04:16 PM
                    } else {
                        dateFormatted = log.created_at; // fallback
                    }
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${log.log_type ?? ''}</td>
                    <td>${actionName}</td>
                    <td>${updatedBy}</td>
                    <td>${dateFormatted}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        $('#logsModal').modal('show');
    });
});

</script>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Init CKEditor for WhatsApp message field
    if (document.getElementById('message-editor')) {
        CKEDITOR.replace('message-editor', {});
    }

    // Handle click on WhatsApp icon: open modal + set user data
    document.querySelectorAll('.open-whatsapp').forEach(function (icon) {
        icon.addEventListener('click', function () {
            var phone = this.dataset.phone || '';
            var name  = this.dataset.name || '';

            document.getElementById('whatsapp_user_phone').value = phone;
            document.getElementById('whatsapp_user_name').value  = name;

            // Show selected user in modal title (small text)
            var labelUser = document.getElementById('whatsappModalLabelUser');
            if (labelUser) {
                labelUser.textContent = name + (phone ? ' (' + phone + ')' : '');
            }

            // Reset template + caption + message when opening
            var templateSelect = document.getElementById('whatsapp_template_id');
            var captionInput   = document.getElementById('caption');
            if (templateSelect) templateSelect.value = '';
            if (captionInput) captionInput.value = '';
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].setData('');
            }

            $('#whatsappModal').modal('show');
        });
    });

    // When template changes → auto-fill caption + message
    var templateSelect = document.getElementById('whatsapp_template_id');
    var captionInput   = document.getElementById('caption');

    if (templateSelect) {
        templateSelect.addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];

            var caption = selectedOption.getAttribute('data-caption') || '';
            var message = selectedOption.getAttribute('data-message') || '';

            if (captionInput) {
                captionInput.value = caption;
            }

            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].setData(message);
            }
        });
    }
});

function submitButton(value){
    $('#type').val(value);
    
}
</script>


@endpush
