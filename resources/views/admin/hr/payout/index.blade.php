@extends('admin.layouts.master')

@section('title', 'Payment History')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">All Generated Payouts</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="payoutTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Amount</th>
                            <th>Generated At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
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
        $('#payoutTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hr.payout.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'month', name: 'month'},
                {data: 'year', name: 'year'},
                {data: 'amount', name: 'amount', render: function(d) { return '<span class="fw-bold">' + d + '</span>'; }},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[5, 'desc']],
            language: { search: "_INPUT_", searchPlaceholder: "Search payments..." }
        });
    });
</script>
@endpush
