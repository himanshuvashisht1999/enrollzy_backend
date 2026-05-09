@extends('admin.layouts.master')

@section('title', 'Leave Settings')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Leave Types & Configuration</h6>
            <a class="btn btn-primary btn-sm rounded-pill px-3" href="{{ route('admin.hr.leave-settings.create') }}">
                <i class="fas fa-plus me-1"></i> Add Leave Type
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="leaveSettingsTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Leave Type</th>
                            <th>Allotment</th>
                            <th>Yearly</th>
                            <th>Monthly</th>
                            <th>Pay Status</th>
                            <th>Departments</th>
                            <th>Designations</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
        $('#leaveSettingsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hr.leave-settings.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'name', name: 'name'},
                {data: 'allotment_type', name: 'allotment_type'},
                {data: 'yearly_leave', name: 'yearly_leave'},
                {data: 'monthly_leave', name: 'monthly_leave'},
                {data: 'pay_status', name: 'pay_status'},
                {data: 'department', name: 'department'},
                {data: 'designation', name: 'designation'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end'},
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search leave types...",
                paginate: {
                    next: '<i class="fas fa-chevron-right"></i>',
                    previous: '<i class="fas fa-chevron-left"></i>'
                }
            }
        });
    });
</script>
@endpush
