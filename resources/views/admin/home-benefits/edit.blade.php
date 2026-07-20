@extends('admin.layouts.master')

@section('title', 'Edit Scholarship')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.home-benefits.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.home-benefits.update', $homeBenefit->id) }}" method="POST" enctype="multipart/form-data">
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
                <div class="col-md-6">
                    <label class="form-label">Reward Amount <small class="text-muted">(e.g. Upto INR 30,000)</small></label>
                    <input type="text" name="reward_amount" class="form-control" value="{{ $homeBenefit->reward_amount }}" placeholder="e.g. Upto INR 30,000">
                </div>
                <div class="col-12">
                    <label class="form-label">Icon Image</label>
                    @if($homeBenefit->icon)
                        <div class="mb-2">
                            <img src="{{ asset($homeBenefit->icon) }}" alt="Icon" width="50" height="50">
                        </div>
                    @endif
                    <input type="file" name="icon" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Update Benefit</button>
            </div>
        </form>
    </div>
</div>
@endsection
