@extends('layouts.app')
@section('push_css')
    <!-- Custom styles for this page -->
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Institute</h6>
                @can('institute-browse')
                    <a class="btn btn-primary btn-sm" href="javascript:;" data-toggle="modal" data-target="#addInstitute">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Institute
                    </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @can('institute-browse')
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="no-sort" style="position: inherit !important"><input type="checkbox"
                                            name="ids">
                                    </th>
                                    <th>Id</th>
                                    <th>Name</th>
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
    @can('institute-add')
        {{-- Add New banner form --}}
        <div class="modal fade" id="addInstitute" tabindex="-1" role="dialog" aria-labelledby="addInstitute"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content modal-sm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add Institute</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="addInstituteForm" name="addInstituteForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="name"> Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="name">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" id="AddInsBtn" href="javascript:;"> Save </a>
                    </div>
                </div>
            </div>
        </div>
        {{-- Add New banner form --}}
    @endcan
    @can('institute-edit')
        {{-- Edit banner form --}}
        <div class="modal fade" id="editInstituteModal" tabindex="-1" role="dialog" aria-labelledby="editInstituteModal"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content modal-sm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit Institute</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editInstituteForm" name="editInstituteForm">
                            @csrf
                            <input type="hidden" name="institute_id" id="institute_id">
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="name"> Name</label>
                                    <input type="text" class="form-control" name="name" id="edit_name" placeholder="name">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" id="updateInstituteBtn" href="javascript:;"> Update </a>
                    </div>
                </div>
            </div>
        </div>
        {{-- Edit banner form --}}
    @endcan
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
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
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
        //   ---------------------------jQuery end here
        // -----------Add Category jquery Start------------------------------
        $('#AddInsBtn').click(function() {
            formdata = $('#addInstituteForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.institute.save') }}",
                data: formdata,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // ----------- jquery End here------------------------------
        // -----------Edit Category jquery Start------------------------------
        $('#dataTable').on('click', '.editInstitutebtn', function(e) {
            e.preventDefault();
            var id = $(this).val();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.institute.getIns') }}",
                dataType: "json",
                data: {
                    'id': id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.status == 1) {
                        $('#institute_id').val(response.data.id);
                        $("#edit_name").val(response.data.name);
                        $('#editInstituteModal').modal('show');
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -----------  jquery End here------------------------------
        // -----------Update Category jquery Start------------------------------
        $('#updateInstituteBtn').click(function(e) {
            e.preventDefault();
            formdata = $('#editInstituteForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.institute.update') }}",
                data: formdata,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // ----------- jquery End here------------------------------
        // ------------Delete Category jquery -------------
        $('#dataTable').on('click', '.confirm-button', function(event) {
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
        // ------------Delete Category jquery end here -------------
    </script>
@endsection
