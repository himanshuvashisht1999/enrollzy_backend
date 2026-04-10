@extends('layouts.app')
@section('push_css')
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Project Users</h6>
                <a class="btn btn-sm btn-primary" href="{{ route('admin.client.create') }}"> <i
                        class="fas fa-plus fa-sm text-white-50">
                    </i> Project Users
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Created At</th>
                                <th class="no-sort">Action</th>
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
@section('push_script')
    <script src="{{ URL::asset('admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                    },
                    {
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'email',
                        name: 'email',
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
        $('#dataTable').on('click', '.confirm-button', function(e) {
            var form = $(this).closest("form");
            event.preventDefault();
            swal({
                    title: `Are you sure?`,
                    text: "It will gone forever",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                })
                .then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                        swal("Done! Data has been deleted!", {
                            icon: "success",
                            button: false,
                        });
                    } else {
                        swal("Cancelled", "Your Data is safe 🤗", "error");
                    }
                });
        });
    </script>
@endsection
