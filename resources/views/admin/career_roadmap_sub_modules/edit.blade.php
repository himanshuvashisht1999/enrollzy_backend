@extends('admin.layouts.master')

@section('title', 'Edit ' . $item->title)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">{{ $item->title }}</h4>
        <small class="text-muted">{{ $item->parent_id ? 'Item' : 'Roadmap' }} in {{ $item->stage->title ?? '' }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.career-roadmap-sub-modules.create', ['parent_id' => $item->id]) }}" class="btn btn-outline-success"><i class="fas fa-plus"></i> Add an item under this</a>
        <a href="{{ route('admin.career-roadmap-sub-modules.index', ['class' => $item->stage_id]) }}#item-{{ $item->id }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to the roadmaps</a>
    </div>
</div>
<form action="{{ route('admin.career-roadmap-sub-modules.update', $item->id) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.career_roadmap_sub_modules._form')
</form>
<form action="{{ route('admin.career-roadmap-sub-modules.destroy', $item->id) }}" method="POST" class="text-end" onsubmit="return confirm('Delete “{{ addslashes($item->title) }}” and everything under it? This cannot be undone. To hide it instead, switch off “Show on the website”.')">
    @csrf
    @method('DELETE')
    <button class="btn btn-link text-danger"><i class="fas fa-trash me-1"></i> Delete</button>
</form>
@endsection
