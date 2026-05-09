@extends('admin.layouts.master')

@section('title', 'Customer List')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">All Customers</h6>
            <a class="btn btn-primary btn-sm rounded-pill px-3" href="{{ route('admin.hr.customers.index.create') }}">
                <i class="fas fa-plus me-1"></i> New Customer
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="customerTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact Info</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
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
        $('#customerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hr.customers.index.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { 
                    data: null, 
                    render: function(data) {
                        return `<div class="small fw-bold">${data.email || 'No email'}</div><div class="small text-muted">${data.phone || 'No phone'}</div>`;
                    }
                },
                { data: 'category_name', name: 'category_name' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: { search: "_INPUT_", searchPlaceholder: "Search customers..." }
        });
    });
</script>
@endpush
