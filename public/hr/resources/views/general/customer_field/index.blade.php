@extends('layouts.app')
@section('push_css')
    <!-- Custom styles for this page -->
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Customer Fields</h6>
                @can('customer-category-add')
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.customer_field.create') }}">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Customer Field
                    </a>
                @endcan
            </div>
            <div class="card-body">
                @can('customer-browse')
                    <form id="sort_customer" class="row" action="" name="sort_customer">
                        @csrf
                        <div class="form-group col-lg-3">
                            <input type="text" class="form-control" name="name" value="{{ request('name') }}"
                                placeholder="Customer field name">
                        </div>
                        <div class="form-group col-lg-3">
                            <input type="text" class="form-control" name="label" value="{{ request('label') }}"
                                placeholder="Label">
                        </div>
                        
                        <div class="col-lg-3">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <a href="{{ route('admin.customer_field.list') }}" class="btn btn-info">Reset</a>
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
                                    <th>Label</th>
                                    <th>Name</th>
                                    <th>Sequence</th>
                                    <th>Is Required</th>
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
                        data: 'label',
                        name: 'label'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'sequence',
                        name: 'sequence'
                    },
                    {
                        data: 'is_required',
                        name: 'is_required'
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
