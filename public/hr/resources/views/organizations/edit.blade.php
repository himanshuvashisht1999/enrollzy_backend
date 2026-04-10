@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .document-container {
            background: #3342c1ad;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            /* Add margin between rows if needed */
        }

        .document-container img {
            max-width: 100%;
            height: 200px;
        }

        .document-container iframe {
            width: 100%;
            height: 200px;
            /* Adjust height as needed */
        }

        .delete-button {
            position: relative;
            bottom: 15px;
            left: 15px;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Edit organization</h6>
            </div>
                <div class="card-body">
                    <form id="editorganizationForm" method="POST" action="{{ route('admin.organization.update', $organization->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="row">
                            <input type="hidden" value="{{ $organization->id }}" id="organizationId" name="organizationId">
                            <div class="col-md-4 form-group mb-4">
                                <label for="name"> Name</label>
                                <input type="text" class="form-control" name="name" placeholder="Name"
                                    value="{{ old('name') ?? $organization->name }}">
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="email"> Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Email"
                                    value="{{ old('email') ?? $organization->email }}">
                                
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="phone"> Mobile</label>
                                <input type="text" class="form-control" name="phone" placeholder="Mobile"
                                    value="{{ old('phone') ?? $organization->phone }}">
                                
                            </div>
                            <div class="col-md-12 form-group mb-4">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address"
                                    value="{{ old('address') ?? $organization->address }}">
                            </div>
                        </div>
                    </form>
                </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.organization.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-sm btn-success" form="editorganizationForm">Update</button>
                    </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="{{ URL::asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>

@endsection
