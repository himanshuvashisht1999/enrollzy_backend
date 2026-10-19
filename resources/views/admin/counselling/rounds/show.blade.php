@extends('admin.layouts.master')

@section('title', $cycle->counselling->name . ' · ' . $round->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold">{{ $round->name }} <span class="text-muted fs-6">{{ $cycle->counselling->name }} · {{ $cycle->title }}</span></h4>
        <span class="badge bg-light text-dark border">{{ $round->type->name }}</span>
        <span class="badge {{ in_array($round->round_status, ['postponed', 'cancelled']) ? 'bg-danger-subtle text-danger' : 'bg-light text-dark border' }}">{{ ucfirst($round->round_status) }}</span>
        @if($round->status_note)<span class="text-muted small ms-1">{{ $round->status_note }}</span>@endif
    </div>
    <div class="d-flex gap-2">
        @if($previous)<a href="{{ route('admin.counselling.rounds.show', $previous) }}" class="btn btn-light border"><i class="fas fa-chevron-left me-1"></i> {{ $previous->name }}</a>@endif
        <a href="{{ route('admin.counselling.cycles.show', $cycle) }}" class="btn btn-light border">Cycle</a>
        @if($next)<a href="{{ route('admin.counselling.rounds.show', $next) }}" class="btn btn-light border">{{ $next->name }} <i class="fas fa-chevron-right ms-1"></i></a>@endif
    </div>
</div>

{{-- Effective settings: what students see after inheritance --}}
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Effective settings</h6>
    <dl class="row small mb-0">
        <dt class="col-md-3">Fresh registration</dt>
        <dd class="col-md-9">{{ $round->effectiveFreshRegistration() ? 'Allowed' : 'Not allowed' }}
            <span class="badge {{ $round->fresh_registration_allowed === null ? 'bg-light text-dark border' : 'bg-warning-subtle text-warning' }}">{{ $round->fresh_registration_allowed === null ? 'Inherited from round type' : 'Overridden in this round' }}</span></dd>
        <dt class="col-md-3">Willingness options</dt>
        <dd class="col-md-9">{{ collect($round->effectiveWillingness())->map(fn ($w) => \App\Models\Counselling\CounsellingCycle::WILLINGNESS[$w] ?? $w)->implode(', ') ?: '-' }}
            <span class="badge {{ $round->willingness_options === null ? 'bg-light text-dark border' : 'bg-warning-subtle text-warning' }}">{{ $round->willingness_options === null ? 'Inherited from cycle' : 'Overridden in this round' }}</span></dd>
        <dt class="col-md-3">Earlier choices carry forward</dt>
        <dd class="col-md-9">{{ $round->choices_carry_forward === null ? 'Not specified' : ($round->choices_carry_forward ? 'Yes' : 'No') }}</dd>
        <dt class="col-md-3">Quotas</dt>
        <dd class="col-md-9">{{ $round->quotas ? \App\Models\Counselling\Quota::whereIn('id', $round->quotas)->pluck('name')->implode(', ') : 'All quotas of the cycle' }}</dd>
    </dl>
</div></div>

@include('admin.counselling._events', ['cycle' => $cycle, 'round' => $round, 'events' => $round->events, 'storeUrl' => route('admin.counselling.events.store-round', $round)])

@can('cns-counsellings-edit')
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Round settings</h6>
    <form action="{{ route('admin.counselling.rounds.update', $round) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.counselling.rounds._fields')
        <button class="btn btn-primary mt-3"><i class="fas fa-save me-1"></i> Save round</button>
    </form>
</div></div>
@endcan

@include('admin.counselling._blocks', ['cycle' => $cycle, 'round' => $round])

@include('admin.counselling.rounds._results', ['round' => $round, 'cycle' => $cycle])
@endsection
