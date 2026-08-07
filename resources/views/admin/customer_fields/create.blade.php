@extends('admin.layouts.master')

@section('title', 'Add Student Field')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Define New Custom Field</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.customer-fields.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Database Name (No spaces) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. gstin_number, qualification" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Display Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control rounded-3" placeholder="e.g. GSTIN Number, Qualification" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Is Mandatory?</label>
                        <select name="is_required" class="form-select rounded-3">
                            <option value="0">Optional</option>
                            <option value="1">Required</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Display Sequence</label>
                        <input type="number" name="sequence" class="form-control rounded-3" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Field Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.customer-fields.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Create Field</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

