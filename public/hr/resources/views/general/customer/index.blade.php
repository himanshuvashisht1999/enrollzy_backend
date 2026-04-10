@extends('layouts.app')
@section('push_css')
    <!-- Custom styles for this page -->
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Customers</h6>
                <div>
                    @can('customer-category-add')
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#importCustomerModal">
                            <i class="fas fa-file-import fa-sm text-white-50"></i> Import
                        </button>
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.customer.create') }}">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Customer
                        </a>
                    @endcan
                </div>
                
            </div>
            <div class="card-body">
                @can('customer-browse')
                    <form id="sort_customer" class="row" action="" name="sort_customer">
                        @csrf
                        <div class="form-group col-lg-3">
                            <input type="text" class="form-control" name="name" value="{{ request('name') }}"
                                placeholder="Customer name">
                        </div>
                        <div class="form-group col-lg-3">
                            <input type="text" class="form-control" name="phone" value="{{ request('phone') }}"
                                placeholder="Mobile number">
                        </div>
                        <div class="form-group col-lg-3">
                            <input type="text" class="form-control" name="email" value="{{ request('email') }}"
                                placeholder="Email">
                        </div>
                        <div class="col-lg-3">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <a href="{{ route('admin.customer.list') }}" class="btn btn-info">Reset</a>
                        </div>
                    </form>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @can('customer-browse')
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Category</th>
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
    {{-- Import Customers Modal --}}
<div class="modal fade" id="importCustomerModal" tabindex="-1" role="dialog" aria-labelledby="importCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.customer.importCustomer') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importCustomerModalLabel">Import Customers from Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>
                        Please use the
                        <a href="{{ route('admin.customer.downloadCustomerSample') }}" target="_blank">
                            sample file
                        </a>
                        as a reference for columns.
                    </p>

                    <div class="form-group">
                        <label for="customer_import_file">Select Excel file</label>
                        <input type="file"
                               name="file"
                               id="customer_import_file"
                               class="form-control"
                               required
                               accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">
                            Allowed types: .xlsx, .xls, .csv
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
@section('push_script')
    <script src="{{ URL::asset('admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                lazyLoad: true,
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'category_id',
                        name: 'category_id'
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
                ],
            });
        });
    </script>
@endsection
