@extends('admin.layouts.master')

@section('title', 'Career Categories')

@php use App\Models\Career; @endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Career categories</h4>
        <small class="text-muted">Medical, engineering, commerce…: the filter and the groups on the website's careers page. Pick a career's category on the career itself.</small>
    </div>
    <a href="{{ route('admin.careers.index') }}" class="btn btn-outline-secondary"><i class="fas fa-user-tie me-1"></i> Careers</a>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-2 small fw-bold text-muted mb-2 d-none d-md-flex">
            <div class="col-md-1">Order</div><div class="col-md-3">Name</div><div class="col-md-2">Web address</div><div class="col-md-2">Icon</div><div class="col-md-2">Careers</div><div class="col-md-2"></div>
        </div>
        @foreach($categories as $c)
            <form method="POST" action="{{ route('admin.career-categories.update', $c->id) }}" class="row g-2 align-items-center py-2 border-top">
                @csrf @method('PUT')
                <div class="col-md-1"><input type="number" class="form-control form-control-sm" name="sort_order" value="{{ $c->sort_order }}" aria-label="Order"></div>
                <div class="col-md-3"><input class="form-control form-control-sm" name="name" value="{{ $c->name }}" required aria-label="Name"></div>
                <div class="col-md-2"><input class="form-control form-control-sm" name="slug" value="{{ $c->slug }}" aria-label="Web address"></div>
                <div class="col-md-2"><select class="form-select form-select-sm custom-select2" name="icon" aria-label="Icon"><option value="">Default</option>@foreach(Career::ICONS as $k => $l)<option value="{{ $k }}" @selected($c->icon === $k)>{{ $l }}</option>@endforeach</select></div>
                <div class="col-md-2 small"><a href="{{ route('admin.careers.index', ['category' => $c->id]) }}">{{ $c->careers_count }} {{ $c->careers_count === 1 ? 'career' : 'careers' }}</a>
                    <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="status" value="1" id="st-{{ $c->id }}" @checked($c->status)><label class="form-check-label" for="st-{{ $c->id }}">Show</label></div></div>
                <div class="col-md-2 d-flex gap-1 justify-content-end">
                    <button class="btn btn-sm btn-primary">Save</button>
                    <button class="btn btn-sm btn-outline-danger" type="submit" form="del-{{ $c->id }}" title="Delete">×</button>
                </div>
            </form>
            <form id="del-{{ $c->id }}" method="POST" action="{{ route('admin.career-categories.destroy', $c->id) }}" onsubmit="return confirm('Delete {{ addslashes($c->name) }}? Its careers stay, without a category.')">@csrf @method('DELETE')</form>
        @endforeach

        <form method="POST" action="{{ route('admin.career-categories.store') }}" class="row g-2 align-items-center pt-3 border-top">
            @csrf
            <div class="col-md-1"><input type="number" class="form-control form-control-sm" name="sort_order" value="{{ (int) $categories->max('sort_order') + 10 }}" aria-label="Order"></div>
            <div class="col-md-3"><input class="form-control form-control-sm" name="name" placeholder="New category, e.g. Agriculture" required aria-label="New category name"></div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="slug" placeholder="made from the name" aria-label="Web address"></div>
            <div class="col-md-2"><select class="form-select form-select-sm custom-select2" name="icon" aria-label="Icon"><option value="">Default</option>@foreach(Career::ICONS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-2"><input type="hidden" name="status" value="1"></div>
            <div class="col-md-2 d-grid"><button class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add</button></div>
        </form>
    </div>
</div>
@endsection
