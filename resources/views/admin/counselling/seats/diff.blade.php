@extends('admin.layouts.master')

@section('title', 'Seats changed')

@php
    $label = fn ($r) => ($r->organisation->name ?? '#' . $r->organisation_id) . ' · ' . ($r->course->name ?? '#' . $r->course_id) . ' · ' . ($r->quota->name ?? '') . ' · '
        . ($r->category->name ?? '') . ($r->gender ? ' · ' . ucfirst($r->gender) . ' only' : '') . ($r->pwd ? ' · PwD' : '');
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Seats changed since v{{ $older->version_no }} <span class="text-muted fs-6">v{{ $version->version_no }} · {{ $version->round->cycle->counselling->name }} · {{ $version->round->name }}</span></h4>
        <p class="text-muted mb-0"><span class="text-success">{{ $diff['added']->count() }} added</span> · <span class="text-danger">{{ $diff['removed']->count() }} removed</span> · <span class="text-warning">{{ $diff['changed']->count() }} changed</span></p>
    </div>
    <a href="{{ route('admin.counselling.seats.index', $version->round) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Versions</a>
</div>

@foreach([['Added', 'success', $diff['added']], ['Removed', 'danger', $diff['removed']]] as [$title, $tone, $items])
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold text-{{ $tone }}">{{ $title }} <span class="badge bg-secondary">{{ $items->count() }}</span></h6>
    @forelse($items->take(300) as $r)
        <div class="d-flex justify-content-between border-top py-1 small"><span>{{ $label($r) }}</span><span class="fw-semibold">{{ $r->seats }} seats</span></div>
    @empty <p class="text-muted small mb-0">None.</p> @endforelse
    @if($items->count() > 300)<p class="small text-muted mt-2 mb-0">Showing the first 300 of {{ $items->count() }}.</p>@endif
</div></div>
@endforeach

<div class="card border-0 shadow-sm rounded-4"><div class="card-body">
    <h6 class="fw-bold text-warning">Changed <span class="badge bg-secondary">{{ $diff['changed']->count() }}</span></h6>
    @forelse($diff['changed']->take(300) as $c)
        <div class="d-flex justify-content-between border-top py-1 small"><span>{{ $label($c['row']) }}</span><span class="fw-semibold">{{ $c['from'] }} → {{ $c['to'] }}</span></div>
    @empty <p class="text-muted small mb-0">None.</p> @endforelse
</div></div>
@endsection
