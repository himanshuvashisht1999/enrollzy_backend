@extends('admin.layouts.master')

@section('title', 'Careers')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Careers</h4>
        <small class="text-muted">Doctor, CA, engineer…: each has a page on the website, shows as a popular career in its classes, and is listed under the streams that open it.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.career-roadmap-sub-modules.index') }}" class="btn btn-outline-secondary"><i class="fas fa-map-signs me-1"></i> Roadmaps</a>
        <a href="{{ route('admin.careers.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New career</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="mb-3 d-flex flex-wrap gap-2"><input type="search" name="search" value="{{ $q }}" class="form-control" style="max-width:320px" placeholder="Search careers…"><select name="category" class="form-select custom-select2" style="max-width:260px" onchange="this.form.submit()"><option value="">All categories</option>@foreach($categories as $cc)<option value="{{ $cc->id }}" @selected($cat === $cc->id)>{{ $cc->name }}</option>@endforeach</select><button class="btn btn-outline-secondary">Search</button><a href="{{ route('admin.career-categories.index') }}" class="btn btn-link">Manage categories</a></form>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th style="width:70px">Order</th><th>Career</th><th>Category</th><th>Stream needed</th><th>Popular in</th><th>Exams</th><th style="width:90px">Status</th><th style="width:110px"></th></tr></thead>
                <tbody>
                @forelse($careers as $c)
                    <tr>
                        <td>{{ $c->sort_order }}</td>
                        <td><b>{{ $c->name }}</b><div class="small text-muted">{{ $c->summary }}</div></td>
                        <td class="small">{!! $c->category ? e($c->category->name) : '<span class="text-danger">None</span>' !!}</td>
                        <td class="small">{{ $c->stream }}</td>
                        <td class="small">{{ collect($c->show_for)->map(fn ($id) => $classes[$id] ?? null)->filter()->implode(', ') ?: '—' }}</td>
                        <td class="small">{{ implode(', ', $c->exams ?? []) ?: '—' }}</td>
                        <td>@if($c->status)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Hidden</span>@endif</td>
                        <td class="text-end"><a href="{{ route('admin.careers.edit', $c->id) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted">No careers yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
