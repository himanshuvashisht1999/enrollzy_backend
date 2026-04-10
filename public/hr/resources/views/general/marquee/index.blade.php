@extends('layouts.app')
@section('push_css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Flash</h6>
                <a class="btn btn-primary btn-sm" href="{{ route('admin.marque.create') }}">
                    <i class="fas fa-plus fa-sm text-white-50">
                    </i> Marquee
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Content</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($marque as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->content }} </td>
                                    <td>{{ date('h:i A - d M, Y ', strtotime($item->created_at)) }}</td>
                                    <td class="d-flex">
                                        <a href="{{ route('admin.marque.edit', encrypt($item->id)) }}" class="btn btn-sm"
                                            value="{{ $item->id }}"><i class="fa fa-edit text-success"></i></a> |
                                        <form method="POST"
                                            action="{{ route('admin.marque.destroy', encrypt($item->id)) }}"
                                            class="m-0 p-0">
                                            @csrf
                                            <input name="_method" type="hidden" value="DELETE">
                                            <button type="submit" class="btn btn-sm confirm-button"><i
                                                    class="fa fa-trash text-danger"></i></button>
                                        </form>
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
        // -----------Add new flash jquery Start------------------------------
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
