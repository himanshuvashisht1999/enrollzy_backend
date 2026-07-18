@extends('admin.layouts.master')

@section('title', 'Edit Mentor Mentee Level')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Mentor Mentee Level</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.mentor.mentee_levels.update', $menteeLevel->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Mentee Level Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $menteeLevel->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', $menteeLevel->status) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $menteeLevel->status) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Commission Percentage (Optional)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control @error('commission_percentage') is-invalid @enderror" name="commission_percentage" value="{{ old('commission_percentage', $menteeLevel->commission_percentage ?? '') }}" placeholder="e.g. 10">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('commission_percentage')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Mentee Level</button>
                            <a href="{{ route('admin.mentor.mentee_levels.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





