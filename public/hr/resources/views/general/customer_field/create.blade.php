@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <form id="addLeaveTypeForm" method="POST" action="{{ route('admin.customer_field.store') }}">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Create Customer Field</h6>
                </div>
                <div class="card-body row">
                    @csrf
                    <div class="col-md-6 form-group">
                        <label for="" id="">Label</label>
                        <input type="text" class="form-control" name="label" placeholder="Label">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="" id="">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Name">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="" id="">Sequence</label>
                        <input type="number" class="form-control" name="sequence" placeholder="Sequence" value="1">
                    </div>


                    <div class="col-md-6 form-group">
                        <label for="is_required">Is Required</label>
                        <select name="is_required" id="is_required" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <label for="department">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="card-footer text-right">
                <a href="{{ route('admin.customer_field.list') }}" class="btn btn-secondary btn-sm">Cancel </a>
                <button type="submit" form="addLeaveTypeForm" class="btn btn-primary btn-sm">Add Customer Field</a>
            </div>
        </div>
    </div>
@endsection
