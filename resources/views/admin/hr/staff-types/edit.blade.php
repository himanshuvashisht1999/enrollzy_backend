@extends('admin.layouts.master')

@section('title', 'Edit Staff Type')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Edit Staff Type</h6>
            <a class="btn btn-secondary btn-sm rounded-pill px-3" href="{{ route('admin.hr.staff-types.index') }}">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.staff-types.update', encrypt($staffType->id)) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $staffType->name) }}" required placeholder="Enter Staff Type Name">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1" {{ $staffType->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $staffType->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Update Staff Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
