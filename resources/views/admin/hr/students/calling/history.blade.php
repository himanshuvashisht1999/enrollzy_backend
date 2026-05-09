@extends('admin.layouts.master')

@section('title', 'Calling History')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Student Outreach History & Timeline</h6>
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
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#historyTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hr.students.calling-history.index') }}",
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
    });
</script>
@endpush
