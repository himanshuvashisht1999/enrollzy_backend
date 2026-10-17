@extends('admin.layouts.master')

@php $isEdit = $body->exists; @endphp
@section('title', $isEdit ? 'Edit Conducting Body' : 'Add Conducting Body')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ $isEdit ? 'Edit' : 'Add' }} Conducting Body</h4>
    <a href="{{ route('admin.exam-module.conducting-bodies.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>
<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
    <form action="{{ $isEdit ? route('admin.exam-module.conducting-bodies.update', $body) : route('admin.exam-module.conducting-bodies.store') }}" method="POST">
        @csrf @if($isEdit) @method('PUT') @endif
        <div class="row g-4">
            <div class="col-md-8"><label class="form-label fw-bold">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $body->name) }}" required></div>
            <div class="col-md-4"><label class="form-label fw-bold">Short Name</label><input type="text" name="short_name" class="form-control" value="{{ old('short_name', $body->short_name) }}"></div>
            <div class="col-md-8"><label class="form-label fw-bold">Website</label><input type="url" name="website" class="form-control" value="{{ old('website', $body->website) }}" placeholder="https://"></div>
            <div class="col-md-4"><label class="form-label fw-bold d-block">Status</label>
                <div class="form-check form-switch mt-2"><input type="hidden" name="status" value="0"><input class="form-check-input" type="checkbox" name="status" id="status" value="1" @checked(old('status', $body->status))><label class="form-check-label" for="status">Active</label></div></div>
            <div class="col-12"><button class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Save</button></div>
        </div>
    </form>
</div></div>
@endsection
