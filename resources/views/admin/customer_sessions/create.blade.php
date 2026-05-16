@extends('admin.layouts.master')

@section('title', 'Add New Session')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Add New Session</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.customer-sessions.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Session Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. 2024-25" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <a href="{{ route('admin.customer-sessions.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Save Session</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

