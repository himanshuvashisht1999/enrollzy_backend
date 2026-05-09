@extends('admin.layouts.master')

@section('title', 'Edit Project User')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="fas fa-user-edit text-warning"></i>
                        </div>
                        <h5 class="m-0 fw-bold">Edit Project User</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.hr.projects.clients.update', encrypt($client->id)) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="name" class="form-control border-start-0 rounded-end-pill" placeholder="Enter name" required value="{{ old('name', $client->name) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" class="form-control border-start-0 rounded-end-pill" placeholder="+1234567890" value="{{ old('phone', $client->phone) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 rounded-end-pill" placeholder="client@example.com" value="{{ old('email', $client->email) }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Address / Company Info</label>
                                <textarea name="address" class="form-control rounded-4" rows="3" placeholder="Enter optional details...">{{ old('address', $client->address) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5 pt-2">
                            <a href="{{ route('admin.hr.projects.clients.index') }}" class="btn btn-light rounded-pill px-4">
                                <i class="fas fa-arrow-left me-1 small"></i> Back
                            </a>
                            <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                                Update User <i class="fas fa-save ms-1 small"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
