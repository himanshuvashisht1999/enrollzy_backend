@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Add Project Category</h6>
            </div>
            <div class="card-body">
                <form id="addProjectCategoryForm" action="{{ route('admin.projectCategory.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="name"> Name</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                placeholder="Name">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="description"> Description</label>
                            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.projectCategory.index') }}">Cancel </a>
                <button class="btn btn-primary btn-sm" form="addProjectCategoryForm" type="submit">Add Project
                    Category</button>
            </div>
        </div>
    </div>
@endsection
