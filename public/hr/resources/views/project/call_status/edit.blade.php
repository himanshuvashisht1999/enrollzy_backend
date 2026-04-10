@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Update Calling Status</h6>
            </div>
            <div class="card-body">
                <form id="updateCallingStatusForm" action="{{ route('admin.call_status.update', encrypt($status->id)) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="name"> Calling Status Title *</label>
                            <input type="text" class="form-control" name="name"
                                value="{{ old('name') ?? $status->name }}" placeholder="Name">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="name"> Is Date Require *</label>
                            <select name="date_require" class="form-control">
                                <option {{ old('date_require') ?? $status->date_require == 'yes' ? 'selected' : '' }} value="yes">Yes
                                </option>
                                <option {{ old('date_require') ?? $status->date_require == 'no' ? 'selected' : '' }} value="no">No
                                </option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.call_status.index') }}">Cancel </a>
                <button class="btn btn-success btn-sm" form="updateCallingStatusForm" type="submit">Update Calling
                    Status</button>
            </div>
        </div>
    </div>
@endsection
