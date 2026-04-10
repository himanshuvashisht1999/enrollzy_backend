@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Add Lead Source</h6>
            </div>
            <div class="card-body">
                <form id="addLeadSourceForm" action="{{ route('admin.leadSource.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="name"> Lead Source Name</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                placeholder="Name">
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.leadSource.index') }}">Cancel </a>
                <button class="btn btn-primary btn-sm" form="addLeadSourceForm" type="submit">Add Lead Source</button>
            </div>
        </div>
    </div>
@endsection
