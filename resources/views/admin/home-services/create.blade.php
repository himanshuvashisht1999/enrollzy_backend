@extends('admin.layouts.master')

@section('title', 'Add New Specialized Course')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.home-services.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.home-services.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. 1-Year Medical Course">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0" required text-center>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required placeholder="A focused one-year fast-track program..."></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Footer Text (Highlight Line)</label>
                    <input type="text" name="footer_text" class="form-control" placeholder="e.g. Make Sure Your Child Is In The Race.">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Create Course</button>
            </div>
        </form>
    </div>
</div>
@endsection
