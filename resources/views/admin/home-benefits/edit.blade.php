@extends('admin.layouts.master')

@section('title', 'Edit Benefit Card')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.home-benefits.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.home-benefits.update', $homeBenefit->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title (Main Heading)</label>
                    <input type="text" name="title" class="form-control" value="{{ $homeBenefit->title }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $homeBenefit->sort_order }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1" {{ $homeBenefit->status ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$homeBenefit->status ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Content (Optional)</label>
                    <textarea name="content" class="form-control" rows="4">{{ $homeBenefit->content }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Update Benefit</button>
            </div>
        </form>
    </div>
</div>
@endsection
