@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <form id="updateLeaveTypeForm" method="POST" action="{{ route('admin.customer_field.update', $data->id) }}">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"> Edit Customer Field </h6>
                </div>
                <div class="card-body row">
                    @csrf
                    @method('PATCH')

                    <div class="col-md-6 form-group"> 
                        <label for="" id="">Label</label>
                        <input type="text" value="{{$data->label}}" class="form-control" name="label" placeholder="label">
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <label for="" id="">Name</label>
                        <input type="text" value="{{$data->name}}" class="form-control" name="name" placeholder="Name">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="" id="">Sequence</label>
                        <input type="number" class="form-control" name="sequence" placeholder="Sequence" value="{{$data->sequence}}">
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="is_required">Is Required</label>
                        <select name="is_required" id="is_required" class="form-control">
                            <option value="1" {{$data->is_required == 1 ? 'selected' : ''}}>Yes</option>
                            <option value="0" {{$data->is_required == 0 ? 'selected' : ''}}>No</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <label for="department">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="active" {{$data->status == 'active' ? 'selected' : ''}}>Active</option>
                            <option value="inactive" {{$data->status == 'inactive' ? 'selected' : ''}}>Inactive</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="card-footer text-right">
                <a href="{{ route('admin.customer_field.list') }}" class="btn btn-secondary btn-sm">Cancel </a>
                <button type="submit" form="updateLeaveTypeForm" class="btn btn-primary btn-sm">Update Customer Field</a>
            </div>
        </div>
    </div>
@endsection
