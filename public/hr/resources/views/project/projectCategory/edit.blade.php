@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Edit Project Category</h6>
            </div>
            <div class="card-body">
                <form id="updateProjectCategoryForm"
                    action="{{ route('admin.projectCategory.update', encrypt($projectCat->id)) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="name"> Name</label>
                            <input type="text" class="form-control" name="name"
                                value="{{ old('name') ?? $projectCat->name }}" placeholder="Name">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="description"> Description</label>
                            <textarea name="description" class="form-control">{{ old('name') ?? $projectCat->description }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.projectCategory.index') }}">Cancel </a>
                <button class="btn btn-success btn-sm" form="updateProjectCategoryForm" type="submit">Update Project
                    Category</button>
            </div>
        </div>
    </div>
@endsection
