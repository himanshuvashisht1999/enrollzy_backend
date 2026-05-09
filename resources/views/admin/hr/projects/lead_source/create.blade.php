@extends('admin.layouts.master')

@section('title', 'New Lead Source')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="fas fa-bullhorn text-primary"></i>
                        </div>
                        <h5 class="m-0 fw-bold">Add Lead Source</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.hr.projects.lead-sources.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Source Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="fas fa-tag text-muted"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 rounded-end-pill @error('name') is-invalid @enderror" placeholder="e.g. Google Ads, Referral" required value="{{ old('name') }}">
                            </div>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ route('admin.hr.projects.lead-sources.index') }}" class="btn btn-light rounded-pill px-4">
                                <i class="fas fa-arrow-left me-1 small"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                Save Source <i class="fas fa-check ms-1 small"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
