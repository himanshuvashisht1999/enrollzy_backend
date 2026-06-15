@extends('admin.layouts.master')
@section('title', 'Add Header Menu')
@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('admin.header-menus.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Menus</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.header-menus.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Menu Title *</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Explore, Data Analyst" value="{{ old('title') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">URL (Optional)</label>
                    <input type="text" name="url" class="form-control" placeholder="http://..." value="{{ old('url') }}">
                    <small class="text-muted">Leave blank if this is a parent menu with a dropdown.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Parent Menu</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- No Parent (Main Menu) --</option>
                        @foreach($parentMenus as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                            @foreach($parent->children as $child)
                                <option value="{{ $child->id }}">&nbsp;&nbsp;&nbsp;↳ {{ $child->title }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <small class="text-muted">Select a parent to make this a sub-menu or link.</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" checked>
                        <label class="form-check-label" for="statusSwitch">Active</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Menu</button>
        </form>
    </div>
</div>
@endsection
