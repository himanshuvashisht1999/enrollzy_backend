@extends('admin.layouts.master')

@section('title', 'Add New Benefit Card')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.home-benefits.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.home-benefits.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title (Main Heading)</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Top-Tier Guidance and Mentorship">
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
                    <label class="form-label">Content (Optional - shown on solid orange cards)</label>
                    <textarea name="content" class="form-control" rows="4" placeholder="With limited students per batch, we ensure..."></textarea>
                    <small class="text-muted">If you provide content, this card will appear as a solid orange card (featured card).</small>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Create Benefit</button>
            </div>
        </form>
    </div>
</div>
@endsection
