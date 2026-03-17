@extends('admin.layouts.master')

@section('title', 'Add New Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Category Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">Category Name</label>
                        <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Engineering, Arts, Business">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="mt-3 text-center">
            <a href="{{ route('categories.index') }}" class="text-muted">← Back to List</a>
        </div>
    </div>
</div>
@endsection
