@extends('admin.layouts.master')

@section('title', 'Add Department')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 col-md-6 mx-auto">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">New Department Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.departments.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="name" value="{{ old('name') }}" placeholder="e.g. Sales, Marketing, HR" required>
                </div>
                <div class="text-end">
                    <a href="{{ route('admin.hr.departments.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
