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
                <h6 class="m-0 font-weight-bold text-primary">All Customer Category</h6>
                @can('customer-category-add')
                    <a class="btn btn-primary btn-sm" href="javascript:;" data-toggle="modal" data-target="#addCustomerCategory">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Category
                    </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @can('customer-category-browse')
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="no-sort" style="position: inherit !important">Id</th>
                                    <th>Cat Id</th>
                                    <th>Name</th>
                                    <th>Customer Type</th>
                                    <th>Parent</th>
                                    <th>Status</th>
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
    @can('customer-category-add')
        {{-- Add New banner form --}}
        <div class="modal fade" id="addCustomerCategory" tabindex="-1" role="dialog" aria-labelledby="addCustomerCategory"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add Category</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="addCusCatForm" name="addCusCatForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="name"> Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="name">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="status"> Customer Type</label>
                                    <select name="customer_type" class="form-control">
                                        <!-- <option value="" selected disabled>select category</option> -->
                                        <option value="Standard">Standard</option>
                                        <option value="Credit">Credit</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="status">Parent</label>
                                    <select name="parent_id" class="form-control">
                                        <option value="0" selected>No Parent</option>
                                        @foreach($categories as $single)
                                        <option value="{{$single->id}}">{{$single->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="customer_type"> Status</label>
                                    <select name="status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" id="AddcatBtn" href="javascript:;"> Save </a>
                    </div>
                </div>
            </div>
        </div>
        {{-- Add New banner form --}}
    @endcan
    @can('customer-category-read')
        {{-- Edit banner form --}}
        <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModal"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit Category</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editCategoryForm" name="editCategoryForm">
                            @csrf
                            <input type="hidden" name="cusCatId" id="cusCatId">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="name"> Name</label>
                                    <input type="text" class="form-control" name="name" id="edit_name" placeholder="name">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="customer_type"> Customer Type</label>
                                    <select name="customer_type" id="customer_type" class="form-control">
                                        <option value="" selected disabled></option>
                                        <option value="Standard">Standard</option>
                                        <option value="Credit">Credit</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="status">Parent</label>
                                    <select name="parent_id" id="parent_id" class="form-control">
                                        <option value="0" selected>No Parent</option>
                                        @foreach($categories as $single)
                                        <option value="{{$single->id}}">{{$single->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="customer_type"> Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        @can('customer-category-edit')
                            <a class="btn btn-primary" id="updateCategoryBtn" href="javascript:;"> Update </a>
                        @endcan
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
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'customer_type',
                        name: 'customer_type'
                    },
                    {
                        data: 'parent_id',
                        name: 'parent_id'
                    },
                    {
                        data: 'status',
                        name: 'status'
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
        $('#AddcatBtn').click(function() {
            formdata = $('#addCusCatForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer_category.save') }}",
                data: formdata,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        // $('#addCustomerCategory').modal('hide').find('form')[0].reset();
                        // $('#dataTable').DataTable().ajax.reload();
                        window.location.reload();
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");

                    }
                },
            });
        });
        // ----------- jquery End here------------------------------
        // -----------Edit Category jquery Start------------------------------
        $('#dataTable').on('click', '.edit_category_btn', function(e) {
            e.preventDefault();
            var id = $(this).val();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer_category.getCat') }}",
                dataType: "json",
                data: {
                    'id': id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.status == 1) {
                        $('#cusCatId').val(response.data.id);
                        $("#edit_name").val(response.data.name);
                        $("#customer_type").val(response.data.customer_type).change();
                        $("#status").val(response.data.status).change();
                        $("#parent_id").val(response.data.parent_id).change();
                        $('#editCategoryModal').modal('show');
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -----------  jquery End here------------------------------
        // -----------Update Category jquery Start------------------------------
        $('#updateCategoryBtn').click(function(e) {
            e.preventDefault();
            formdata = $('#editCategoryForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer_category.update') }}",
                data: formdata,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        // $('#editCategoryModal').modal('hide').find('form')[0].reset();
                        // $('#dataTable').DataTable().ajax.reload();
                        window.location.reload();
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
        // ------------Delete Category jquery -------------
    </script>
@endsection
