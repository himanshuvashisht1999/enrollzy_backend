@extends('admin.layouts.master')

@section('title', 'Customer Fields')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Dynamic Customer Profile Fields</h6>
            <a class="btn btn-primary btn-sm rounded-pill px-3" href="{{ route('admin.hr.customer-fields.create') }}">
                <i class="fas fa-plus me-1"></i> Add Field
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="fieldTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Field Label</th>
                            <th>Database Name</th>
                            <th>Is Required</th>
                            <th>Status</th>
                            <th>Sequence</th>
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
        $('#fieldTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hr.customer-fields.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'label', name: 'label' },
                { data: 'name', name: 'name' },
                { data: 'is_required', name: 'is_required' },
                { data: 'status', name: 'status' },
                { data: 'sequence', name: 'sequence' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
