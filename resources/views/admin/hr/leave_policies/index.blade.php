@extends('admin.layouts.master')

@section('title', 'Leave Policies')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Assigned Leave Policies</h6>
            <a class="btn btn-primary btn-sm rounded-pill px-3" href="{{ route('admin.hr.leave-policies.create') }}">
                <i class="fas fa-plus me-1"></i> Add Policy
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="leavePoliciesTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Policy Name</th>
                            <th>Description Snippet</th>
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
        $('#leavePoliciesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hr.leave-policies.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'name', name: 'name'},
                {data: 'policy', name: 'policy'},
                {data: 'department', name: 'department'},
                {data: 'designation', name: 'designation'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end'},
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search policies...",
            }
        });
    });
</script>
@endpush
