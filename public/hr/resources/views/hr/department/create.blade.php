@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Add Department</h6>
            </div>
            @can('department-add')
                <div class="card-body">
                    <form id="addDepartForm" action="{{ route('admin.department.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name"> Department Name</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                    placeholder="Name">
                            </div>
                           
                        </div>
                    </form>
                </div>
                <div class="card-footer text-right">
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.department.index') }}">Cancel </a>
                    <button class="btn btn-primary btn-sm" form="addDepartForm" type="submit">Add Department</button>
                </div>
            @endcan
        </div>
    </div>
@endsection
