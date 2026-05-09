@extends('admin.layouts.master')

@section('title', 'Add Project User')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">New Project User Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.projects.clients.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone') }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Full Address</label>
                        <textarea name="address" class="form-control rounded-3" rows="2">{{ old('address') }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Landmark</label>
                        <input type="text" name="landmark" class="form-control rounded-3" value="{{ old('landmark') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">City</label>
                        <input type="text" name="city" class="form-control rounded-3" value="{{ old('city') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">State</label>
                        <input type="text" name="state" class="form-control rounded-3" value="{{ old('state') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Pin Code</label>
                        <input type="text" name="pin_code" class="form-control rounded-3" value="{{ old('pin_code') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Additional Notes</label>
                        <textarea name="description" class="form-control rounded-4" rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.hr.projects.clients.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
