@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Add Designation</h6>
            </div>
            @can('designation-add')
                <div class="card-body">
                    <form id="addDesgnsnForm" action="{{ route('admin.designation.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name"> Designation Name</label>
                                <input type="text" class="form-control" name="name" placeholder="Name"
                                    value="{{ old('name') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="name"> Department Name</label>
                                <select name="department_id" class="form-control">
                                <option value="" disabled selected>Select a department</option>  <!-- Dummy option -->
                                    @foreach ($department as $depart)
                                        <option
                                            value="{{ $depart->id }}"> {{ $depart->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-right">
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.designation.index') }}"> Cancel </a>
                    <button class="btn btn-primary btn-sm" type="submit" form="addDesgnsnForm">Add Designation</button>
                </div>
            @endcan
        </div>
    </div>
@endsection
