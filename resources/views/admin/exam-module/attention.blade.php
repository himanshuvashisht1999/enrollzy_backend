@extends('admin.layouts.master')

@section('title', 'Needs Attention')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Needs Attention</h4>
        <p class="text-muted mb-0">Stale or unverified data that students may rely on.</p>
    </div>
    <a href="{{ route('admin.exam-module.exams.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Exams</a>
</div>

@php
    $eventRow = function ($ev) {
        return $ev;
    };
@endphp

@foreach([
    ['Results past their expected date, not declared', $overdueResults],
    ['Open or upcoming events not verified in the last 7 days', $unverified],
] as [$title, $items])
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">{{ $title }} <span class="badge bg-secondary">{{ $items->count() }}</span></h6>
    @forelse($items as $ev)
        <div class="d-flex justify-content-between align-items-center border-top py-2 flex-wrap gap-2">
            <div>
                <a href="{{ route('admin.exam-module.cycles.show', $ev->cycle) }}" class="fw-semibold text-decoration-none">{{ $ev->cycle->exam->name }} {{ $ev->cycle->title }}</a>
                <span class="text-muted"> · {{ $ev->type->name }}{{ $ev->label ? ' · ' . $ev->label : '' }}</span>
                <div class="small text-muted">{{ $ev->dateText() }} · {{ $ev->last_verified_at ? 'verified ' . $ev->last_verified_at->diffForHumans() : 'never verified' }}</div>
            </div>
            <div class="d-flex gap-2">
                @can('exm-exams-verify')
                <form action="{{ route('admin.exam-module.events.verify', $ev) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-success">Mark as verified</button></form>
                @endcan
                <a href="{{ route('admin.exam-module.cycles.show', $ev->cycle) }}" class="btn btn-sm btn-light border">Edit</a>
            </div>
        </div>
    @empty
        <p class="text-muted small mb-0">Nothing to do.</p>
    @endforelse
</div></div>
@endforeach

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">Cycles postponed for more than 14 days with no update <span class="badge bg-secondary">{{ $stalePostponed->count() }}</span></h6>
    @forelse($stalePostponed as $c)
        <div class="d-flex justify-content-between border-top py-2">
            <div><span class="fw-semibold">{{ $c->exam->name }} {{ $c->title }}</span> <span class="small text-muted">last updated {{ $c->updated_at->diffForHumans() }}</span></div>
            <a href="{{ route('admin.exam-module.cycles.edit', $c) }}" class="btn btn-sm btn-light border">Edit</a>
        </div>
    @empty
        <p class="text-muted small mb-0">Nothing to do.</p>
    @endforelse
</div></div>

<div class="card border-0 shadow-sm rounded-4"><div class="card-body">
    <h6 class="fw-bold">Public exams with no current cycle for this year or later <span class="badge bg-secondary">{{ $noCurrent->count() }}</span></h6>
    @forelse($noCurrent as $e)
        <div class="d-flex justify-content-between border-top py-2">
            <span class="fw-semibold">{{ $e->name }}</span>
            <a href="{{ route('admin.exam-module.exams.show', $e) }}" class="btn btn-sm btn-light border">Open</a>
        </div>
    @empty
        <p class="text-muted small mb-0">Nothing to do.</p>
    @endforelse
</div></div>
@endsection
