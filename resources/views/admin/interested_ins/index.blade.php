@extends('admin.layouts.master')

@section('title', 'Interested In Master')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Interested In List</h6>
            <a href="{{ route('admin.interested-ins.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">Add New</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered w-100" id="master-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Status</th>
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
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#master-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.interested-ins.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });

    function deleteItem(id) {
        if(confirm('Are you sure you want to delete this?')) {
            $.ajax({
                url: "{{ url('admin.interested-ins') }}/" + id,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if(res.status) {
                        toastr.success(res.message);
                        $('#master-table').DataTable().ajax.reload();
                    }
                }
            });
        }
    }
</script>
@endpush

