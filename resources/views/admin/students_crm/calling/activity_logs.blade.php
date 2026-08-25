@extends('admin.layouts.master')
@section('title', 'Lead Activity Logs')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Lead Activity Logs</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="filterForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label>Action Type</label>
                                    <select name="action_type" id="action_type" class="form-select">
                                        <option value="">All Actions</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="reassigned">Reassigned</option>
                                        <option value="status_update">Status Update</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Staff</label>
                                    <select name="staff_id" id="staff_id" class="form-select">
                                        <option value="">All Staff</option>
                                        @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>From Date</label>
                                    <input type="date" name="from_date" id="from_date" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label>To Date</label>
                                    <input type="date" name="to_date" id="to_date" class="form-control">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" id="filterBtn" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap w-100" id="activityTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Lead</th>
                                        <th>Staff</th>
                                        <th>Action Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
@endpush

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#activityTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.students-crm.lead-activity-logs.index') }}",
                data: function (d) {
                    d.action_type = $('#action_type').val();
                    d.staff_id = $('#staff_id').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },
            columns: [
                {data: 'date', name: 'created_at'},
                {data: 'lead_name', name: 'customer.name'},
                {data: 'staff_name', name: 'admin.name'},
                {data: 'action_type_html', name: 'action_type'},
                {data: 'description', name: 'description'}
            ],
            order: [[0, 'desc']]
        });

        $('#filterBtn').click(function() {
            table.draw();
        });
    });
</script>
@endpush
