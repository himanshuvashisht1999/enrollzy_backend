@extends('layouts.app')
@section('push_css')
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Organizations</h6>
                @can('staff-add')
                    <a class="btn btn-sm btn-primary" href="{{ route('admin.organization.create') }}">
                        <i class="fas fa-plus fa-sm text-white-50">
                        </i> Organization
                    </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @can('staff-browse')
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Name </th>
                                    <th>Email </th>
                                    <th>Phone  </th>
                                    <th>Address</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script src="{{ URL::asset('admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <!-- Page level custom scripts -->
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
                        data: 'address',
                        name: 'address',
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
        // -------------------------------------------
        $(document).on('change', '.role-change', function() {
            var userId = $(this).data('user-id');
            var roleId = $(this).val();
            $.ajax({
                url: "{{ route('admin.staff.role_update') }}", // Your route to handle the role change
                method: 'POST',
                data: {
                    staff_id: userId,
                    role_id: roleId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        window.location.reload();
                    } else {
                        toastr["error"](response.message, "Error!");
                    }
                },
                error: function(response) {
                    toastr["error"](response.message, "Error!");
                }
            });
        });
        // -------------------------------------------
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
