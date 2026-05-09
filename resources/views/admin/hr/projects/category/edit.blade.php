@extends('admin.layouts.master')

@section('title', 'Edit Project Category')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="fas fa-folder-open text-warning"></i>
                        </div>
                        <h5 class="m-0 fw-bold">Edit Project Category</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.hr.projects.project-categories.update', encrypt($category->id)) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. Web Development" required value="{{ old('name', $category->name) }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control rounded-4" rows="3" placeholder="Brief description of this category...">{{ old('description', $category->description) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ route('admin.hr.projects.project-categories.index') }}" class="btn btn-light rounded-pill px-4">
                                <i class="fas fa-arrow-left me-1 small"></i> Back
                            </a>
                            <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                                Update Category <i class="fas fa-save ms-1 small"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
