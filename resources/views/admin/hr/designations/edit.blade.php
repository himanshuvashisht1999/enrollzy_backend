@extends('admin.layouts.master')

@section('title', 'Edit Designation')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 col-md-6 mx-auto">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Edit Designation Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.designations.update', encrypt($designation->id)) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select rounded-3" required>
                        @foreach($department as $dep)
                            <option value="{{ $dep->id }}" {{ $designation->department_id == $dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Designation Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="name" value="{{ old('name', $designation->name) }}" required>
                </div>
                <div class="text-end">
                    <a href="{{ route('admin.hr.designations.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Designation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
