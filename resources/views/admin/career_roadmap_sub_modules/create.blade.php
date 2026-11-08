@extends('admin.layouts.master')

@section('title', $item->parent_id ? 'Add Roadmap Item' : 'New Career Roadmap')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $item->parent_id ? 'Add an item' : 'New roadmap' }}</h4>
    <a href="{{ route('admin.career-roadmap-sub-modules.index', ['class' => $item->stage_id]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to the roadmaps</a>
</div>
<form action="{{ route('admin.career-roadmap-sub-modules.store') }}" method="POST">
    @csrf
    @include('admin.career_roadmap_sub_modules._form')
</form>
@endsection
