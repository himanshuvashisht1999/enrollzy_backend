@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Edit Project Users</h6>
            </div>
            <div class="card-body">
                <form id="updateClientForm" action="{{ route('admin.client.update', encrypt($client->id)) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" name="name"
                                value="{{ old('name') ?? $client->name }}" placeholder="Enter Name">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email"
                                value="{{ old('email') ?? $client->email }}" placeholder="Enter Email">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" name="phone"
                                value="{{ old('phone') ?? $client->phone }}" placeholder="Enter Phone Number">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" name="address"
                                value="{{ old('address') ?? $client->address }}" placeholder="Enter Address">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="landmark">Landmark</label>
                            <input type="text" class="form-control" name="landmark"
                                value="{{ old('landmark') ?? $client->landmark }}" placeholder="Enter Landmark">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" name="city"
                                value="{{ old('city') ?? $client->city }}" placeholder="Enter City">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="state">State</label>
                            <input type="text" class="form-control" name="state"
                                value="{{ old('state') ?? $client->state }}" placeholder="Enter State">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="pin_code">Pin Code</label>
                            <input type="text" class="form-control" name="pin_code"
                                value="{{ old('pin_code') ?? $client->pin_code }}" placeholder="Enter Pin Code">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="profile_image">Profile Image</label>
                            <input type="text" class="form-control" name="profile_image"
                                value="{{ old('profile_image') ?? $client->profile_image }}"
                                placeholder="Enter Profile Image URL">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="description">About / Description / Notes</label>
                            <textarea name="description" id="description" class="form-control" placeholder="Enter Description">{{ old('description') ?? $client->description }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.leadSource.index') }}">Cancel </a>
                <button class="btn btn-success btn-sm" form="updateClientForm" type="submit">Update Project Users</button>
            </div>
        </div>
    </div>
@endsection
