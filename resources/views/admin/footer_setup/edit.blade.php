@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-bold">Edit Footer Menu</h2>
            <a href="{{ route('admin.footer-setup.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>



    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.footer-setup.update', $footerMenu->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $footerMenu->title) }}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">URL</label>
                        <input type="text" name="url" class="form-control" value="{{ old('url', $footerMenu->url) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Parent Column</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- No Parent (This will be a Column Header) --</option>
                            @foreach($parentMenus as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $footerMenu->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $footerMenu->sort_order) }}">
                    </div>

                    <div class="col-md-3 mb-3 d-flex align-items-center mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ $footerMenu->status ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="status">Active</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Update Menu</button>
            </form>
        </div>
    </div>
</div>
@endsection
