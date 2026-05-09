@extends('admin.layouts.master')

@section('title', 'Edit Department')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 col-md-6 mx-auto">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Edit Department Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.departments.update', encrypt($department->id)) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="name" value="{{ old('name', $department->name) }}" required>
                </div>
                <div class="text-end">
                    <a href="{{ route('admin.hr.departments.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
