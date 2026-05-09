@extends('admin.layouts.master')

@section('title', 'Create Role')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 col-md-6 mx-auto">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">New Access Role</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.roles.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Role Display Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="name" value="{{ old('name') }}" placeholder="e.g. Sales Executive" required>
                    <small class="text-muted">Avoid spaces in the identifier (it will be auto-formatted).</small>
                </div>
                <div class="text-end">
                    <a href="{{ route('admin.hr.roles.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
