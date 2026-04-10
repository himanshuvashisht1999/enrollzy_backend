@extends('layouts.app')
@section('push_css')
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Whatsapp Template</h6>
                    
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.whatsapp_template.create') }}">
                        <i class="fas fa-plus fa-sm text-white-50">
                        </i> Whatsapp Template </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name }} </td>
                                        
                                        <td class="text-capitalize">
                                            {!! GetStatusBadge($item->status) !!}
                                        </td>
                                        <td>{{ date('h:i A - d M, Y ', strtotime($item->created_at)) }}</td>
                                        <td class="d-flex">
                                                <a href="{{ route('admin.whatsapp_template.edit', encrypt($item->id)) }}"
                                                    class="btn btn-sm edit_banner_btn">
                                                    <i class="fa fa-edit text-success"></i>
                                                </a> |
                                                <form method="POST"
                                                    action="{{ route('admin.whatsapp_template.destroy', encrypt($item->id)) }}"
                                                    class="m-0 p-0">
                                                    @csrf
                                                    <input name="_method" type="hidden" value="DELETE">
                                                    <button type="submit" class="btn btn-sm confirm-button"><i
                                                            class="fa fa-trash text-danger"></i></button>
                                                </form>
                                                 |<a href="{{ route('admin.whatsapp_template.sendMessage', encrypt($item->id)) }}"
                                                    class="btn btn-sm edit_banner_btn">
                                                    <i class="fa fa-paper-plane text-success"></i>
                                                </a>
                                        </td>
                                    </tr>
                                @endforeach
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

    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "columnDefs": [{
                    "targets": 'no-sort',
                    "orderable": false,
                }],
            });
        });
        // ------------Delete banner -------------
        $('.confirm-button').click(function(event) {
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
