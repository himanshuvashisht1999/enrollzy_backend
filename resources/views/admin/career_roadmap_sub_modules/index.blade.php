@extends('admin.layouts.master')

@section('title', 'Career Roadmaps')

@php
    use App\Models\CareerRoadmapSubModule as M;
    $rtypes = M::ROADMAP_TYPES;
    $itypes = M::ITEM_TYPES;
@endphp

@section('content')
<style>
    .crm-tree { list-style: none; margin: 0; padding-left: 0; }
    .crm-tree .crm-tree { padding-left: 26px; border-left: 2px solid #e8edf5; margin-left: 10px; }
    .crm-row { display: flex; align-items: center; gap: 10px; padding: 6px 8px; border-radius: 8px; }
    .crm-row:hover { background: #f6f8fc; }
    .crm-row:target, .crm-row.is-target { background: #fff6d6; }
    .crm-row .ord { width: 64px; flex: none; }
    .crm-row .ttl { flex: 1; min-width: 0; }
    .crm-row .off { opacity: .5; text-decoration: line-through; }
    .crm-row .acts { flex: none; display: flex; gap: 6px; }
    .crm-badge { font-size: 11px; font-weight: 600; border-radius: 999px; padding: 2px 8px; background: #eef2f9; color: #45546f; white-space: nowrap; }
    .crm-badge.rt { background: #e8f0ff; color: #1748a8; }
    .crm-badge.ln { background: #e2f5ee; color: #12805c; }
    .crm-badge.warn { background: #fff1e4; color: #b35a00; }
    .crm-road { border: 1px solid #e3e9f3; border-radius: 12px; margin-bottom: 14px; }
    .crm-road > .crm-row { background: #f8faff; border-radius: 12px 12px 0 0; padding: 10px 12px; border-bottom: 1px solid #e3e9f3; }
    .crm-road > .crm-tree { padding: 8px 8px 8px 30px; margin: 0; border: 0; }
    .crm-savebar { position: sticky; bottom: 0; background: #fff; border-top: 1px solid #e3e9f3; padding: 10px 0; z-index: 5; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Career Roadmaps</h4>
        <small class="text-muted">Each class has roadmaps (top level); each roadmap has steps and items. The order here is the order on the website.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.careers.index') }}" class="btn btn-outline-primary"><i class="fas fa-user-tie me-1"></i> Careers</a>
        @if($class)<a href="{{ route('admin.career-roadmap-sub-modules.create', ['stage_id' => $class->id]) }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New roadmap in {{ $class->title }}</a>@endif
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="card border-0 shadow-sm mb-3"><div class="card-body p-3">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <select name="class" class="form-select custom-select2" onchange="this.form.submit()">
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" @selected($class && $class->id === $c->id)>{{ $c->title }}{{ $c->category ? ' — ' . $c->category->name : '' }}{{ $c->status ? '' : ' (inactive)' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5"><input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search every class by title…"></div>
        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary">Search</button></div>
    </form>
</div></div>

@if($search !== '')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <h6 class="mb-3">{{ $results->count() }} {{ $results->count() === 1 ? 'match' : 'matches' }} for “{{ $search }}” <a href="{{ route('admin.career-roadmap-sub-modules.index') }}" class="small ms-2">Clear</a></h6>
        @forelse($results as $r)
            <div class="crm-row border-bottom">
                <div class="ttl"><b class="{{ $r->status ? '' : 'off' }}">{{ $r->title }}</b><div class="small text-muted">{{ $r->stage->title ?? '' }}{{ $r->path ? ' › ' . $r->path : '' }}</div></div>
                <span class="crm-badge {{ $r->parent_id ? '' : 'rt' }}">{{ $r->parent_id ? ($itypes[$r->item_type] ?? 'Item') : ($rtypes[$r->roadmap_type] ?? 'Roadmap') }}</span>
                <div class="acts"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.career-roadmap-sub-modules.edit', $r->id) }}">Edit</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.career-roadmap-sub-modules.index', ['class' => $r->stage_id]) }}#item-{{ $r->id }}">Show in tree</a></div>
            </div>
        @empty
            <p class="text-muted mb-0">Nothing found.</p>
        @endforelse
    </div></div>
@elseif($class)
    <form method="POST" action="{{ route('admin.career-roadmap-sub-modules.reorder') }}" id="order-form">
        @csrf
        @forelse($roadmaps as $road)
            <div class="crm-road">
                @include('admin.career_roadmap_sub_modules._tree_row', ['r' => $road, 'top' => true])
                @if(isset($tree[$road->id]))
                    @include('admin.career_roadmap_sub_modules._tree', ['parent' => $road->id])
                @endif
            </div>
        @empty
            <div class="alert alert-info">No roadmaps in {{ $class->title }} yet. <a href="{{ route('admin.career-roadmap-sub-modules.create', ['stage_id' => $class->id]) }}">Add the first one</a>.</div>
        @endforelse
        @if($roadmaps->isNotEmpty())
            <div class="crm-savebar d-flex justify-content-between align-items-center">
                <small class="text-muted">Change the numbers (lower comes first), then save. Struck-through rows are inactive.</small>
                <button class="btn btn-success"><i class="fas fa-save me-1"></i> Save order</button>
            </div>
        @endif
    </form>
@endif

<script>
    // the row the page was opened for (after a save) stands out
    document.addEventListener('DOMContentLoaded', function () {
        var t = location.hash && document.querySelector(location.hash);
        if (t) { t.classList.add('is-target'); t.scrollIntoView({ block: 'center' }); }
    });
</script>
@endsection
