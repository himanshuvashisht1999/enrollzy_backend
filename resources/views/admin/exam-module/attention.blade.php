@extends('admin.layouts.master')

@section('title', 'Needs Attention')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Needs Attention</h4>
        <p class="text-muted mb-0">Stale or unverified data that students may rely on.</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group">
            @foreach(['all' => 'All', 'exam' => 'Exams', 'counselling' => 'Counselling'] as $k => $l)
                <a href="{{ route('admin.exam-module.attention', ['scope' => $k]) }}" class="btn btn-sm {{ $scope === $k ? 'btn-primary' : 'btn-light border' }}">{{ $l }}</a>
            @endforeach
        </div>
        <a href="{{ route('admin.exam-module.exams.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Exams</a>
    </div>
</div>

@if($scope !== 'counselling')
<h5 class="fw-bold text-muted mb-3">Exams</h5>
@foreach([
    ['Results past their expected date, not declared', $overdueResults],
    ['Open or upcoming events not verified in the last 7 days', $unverified],
] as [$title, $items])
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">{{ $title }} <span class="badge bg-secondary">{{ $items->count() }}</span></h6>
    @forelse($items as $ev)
        <div class="d-flex justify-content-between align-items-center border-top py-2 flex-wrap gap-2">
            <div>
                <a href="{{ route('admin.exam-module.cycles.show', $ev->cycle) }}" class="fw-semibold text-decoration-none">{{ $ev->cycle?->exam?->name ?? 'Exam' }} {{ $ev->cycle?->title }}</a>
                <span class="text-muted"> · {{ $ev->type?->name ?? 'Event' }}{{ $ev->label ? ' · ' . $ev->label : '' }}</span>
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
            <div><span class="fw-semibold">{{ $c->exam?->name ?? 'Exam' }} {{ $c->title }}</span> <span class="small text-muted">last updated {{ $c->updated_at->diffForHumans() }}</span></div>
            <a href="{{ route('admin.exam-module.cycles.edit', $c) }}" class="btn btn-sm btn-light border">Edit</a>
        </div>
    @empty
        <p class="text-muted small mb-0">Nothing to do.</p>
    @endforelse
</div></div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
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
@endif

@if($scope !== 'exam')
@can('cns-counsellings-browse')
<h5 class="fw-bold text-muted mb-3 mt-2">Counselling</h5>
@foreach([
    ['Results past their expected date, not declared', $cOverdueResults],
    ['Open or upcoming events not verified in the last 7 days', $cUnverified],
] as [$title, $items])
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">{{ $title }} <span class="badge bg-secondary">{{ $items->count() }}</span></h6>
    @forelse($items as $ev)
        @php $target = $ev->round ?? $ev->counsellingCycle; $url = $ev->round ? route('admin.counselling.rounds.show', $ev->round) : route('admin.counselling.cycles.show', $ev->counsellingCycle); @endphp
        <div class="d-flex justify-content-between align-items-center border-top py-2 flex-wrap gap-2">
            <div>
                <a href="{{ $url }}" class="fw-semibold text-decoration-none">{{ $ev->counsellingCycle?->counselling?->name ?? 'Counselling' }} {{ $ev->counsellingCycle?->title }}@if($ev->round) · {{ $ev->round->name }}@endif</a>
                <span class="text-muted"> · {{ $ev->type?->name ?? 'Event' }}{{ $ev->label ? ' · ' . $ev->label : '' }}</span>
                <div class="small text-muted">{{ $ev->dateText() }} · {{ $ev->last_verified_at ? 'verified ' . $ev->last_verified_at->diffForHumans() : 'never verified' }}</div>
            </div>
            <div class="d-flex gap-2">
                @can('cns-counsellings-verify')
                <form action="{{ route('admin.counselling.events.verify', $ev) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-success">Mark as verified</button></form>
                @endcan
                <a href="{{ $url }}" class="btn btn-sm btn-light border">Edit</a>
            </div>
        </div>
    @empty
        <p class="text-muted small mb-0">Nothing to do.</p>
    @endforelse
</div></div>
@endforeach

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">Current cycles with dates that need a new value or links that need an update <span class="badge bg-secondary">{{ $cNeedsDates->count() }}</span></h6>
    @forelse($cNeedsDates as $x)
        <div class="d-flex justify-content-between align-items-center border-top py-2">
            <div><a href="{{ route('admin.counselling.cycles.show', $x['cycle']) }}" class="fw-semibold text-decoration-none">{{ $x['cycle']->counselling?->name ?? 'Counselling' }} {{ $x['cycle']->title }}</a>
                <div class="small text-muted">{{ implode('; ', $x['todo']) }}</div></div>
            <a href="{{ route('admin.counselling.cycles.show', $x['cycle']) }}" class="btn btn-sm btn-light border">Open</a>
        </div>
    @empty
        <p class="text-muted small mb-0">Nothing to do.</p>
    @endforelse
</div></div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">Public counsellings with no current cycle for this year or later <span class="badge bg-secondary">{{ $cNoCurrent->count() }}</span></h6>
    @forelse($cNoCurrent as $c)
        <div class="d-flex justify-content-between border-top py-2">
            <span class="fw-semibold">{{ $c->name }}</span>
            <a href="{{ route('admin.counselling.counsellings.show', $c) }}" class="btn btn-sm btn-light border">Open</a>
        </div>
    @empty
        <p class="text-muted small mb-0">Nothing to do.</p>
    @endforelse
</div></div>
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">Choice filling is open but there is no live seat matrix <span class="badge bg-secondary">{{ $cNoSeatMatrix->count() }}</span></h6>
    @forelse($cNoSeatMatrix as $r)
        <div class="d-flex justify-content-between align-items-center border-top py-2">
            <span class="fw-semibold">{{ $r->cycle?->counselling?->name ?? 'Counselling' }} {{ $r->cycle?->title }} · {{ $r->name }}</span>
            <a href="{{ route('admin.counselling.seats.index', $r) }}" class="btn btn-sm btn-light border">Seat matrix</a>
        </div>
    @empty <p class="text-muted small mb-0">Nothing to do.</p> @endforelse
</div></div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">Completed rounds with no cutoffs after 14 days <span class="badge bg-secondary">{{ $cNoCutoffs->count() }}</span></h6>
    @forelse($cNoCutoffs as $r)
        <div class="d-flex justify-content-between align-items-center border-top py-2">
            <span class="fw-semibold">{{ $r->cycle?->counselling?->name ?? 'Counselling' }} {{ $r->cycle?->title }} · {{ $r->name }}</span>
            <a href="{{ route('admin.counselling.cutoffs.index', $r) }}" class="btn btn-sm btn-light border">Cutoffs</a>
        </div>
    @empty <p class="text-muted small mb-0">Nothing to do.</p> @endforelse
</div></div>
@endcan
@endif
@endsection
