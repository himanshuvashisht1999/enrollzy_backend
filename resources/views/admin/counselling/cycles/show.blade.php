@extends('admin.layouts.master')

@section('title', $cycle->counselling->name . ' ' . $cycle->title)

@php
    $badge = fn ($s) => match ($s) {
        'open', 'declared', 'completed' => 'bg-success-subtle text-success',
        'upcoming', 'expected' => 'bg-info-subtle text-info',
        'postponed', 'cancelled', 'withheld' => 'bg-danger-subtle text-danger',
        default => 'bg-secondary-subtle text-secondary',
    };
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold">{{ $cycle->counselling->name }} · {{ $cycle->title }}</h4>
        <span class="badge {{ $badge($cycle->cycle_status) }}">{{ ucfirst($cycle->cycle_status) }}</span>
        @if($cycle->is_current)<span class="badge bg-primary">Current</span>@endif
        <span class="badge {{ $cycle->is_public ? 'bg-success' : 'bg-secondary' }}">{{ $cycle->is_public ? 'Public' : 'Admin only' }}</span>
        @if($cycle->status_note)<span class="text-muted small ms-2">{{ $cycle->status_note }}</span>@endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.counsellings.show', $cycle->counselling) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
        @can('cns-counsellings-edit')
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#copyModal"><i class="fas fa-copy me-1"></i> Copy Cycle</button>
        <a href="{{ route('admin.counselling.cycles.edit', $cycle) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i> Edit Cycle</a>
        @endcan
    </div>
</div>

@if($todo = $cycle->needsNewDates())
    <div class="alert alert-warning"><strong>Needs a new date</strong> (date fields that are still empty, e.g. after Copy Cycle):
        <ul class="mb-0">@foreach($todo as $t)<li>{{ $t }}</li>@endforeach</ul></div>
@endif

{{-- Current round status bar (calculated) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Status shown to students (calculated)</h6>
    @if(in_array($cycle->cycle_status, ['postponed', 'cancelled']))
        <span class="badge {{ $badge($cycle->cycle_status) }}">{{ ucfirst($cycle->cycle_status) }}{{ $cycle->status_note ? ': ' . $cycle->status_note : '' }}</span>
        <div class="small text-muted mt-1">The cycle status overrides every date and round.</div>
    @elseif($bar)
        <div class="mb-2">Current round: <a href="{{ route('admin.counselling.rounds.show', $bar['round']) }}" class="fw-semibold text-decoration-none">{{ $bar['round']->name }}</a></div>
        <div class="d-flex flex-wrap gap-4">
            @foreach($bar['items'] as $label => $st)
                <div><div class="small text-muted">{{ $label }}</div><span class="badge {{ $badge($st['state']) }}">{{ $st['label'] }}</span></div>
            @endforeach
        </div>
    @else
        <span class="text-muted">Counselling schedule to be announced</span>
    @endif
</div></div>

{{-- Overview --}}
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Overview</h6>
        @can('cns-counsellings-verify')
        <form action="{{ route('admin.counselling.cycles.verify', $cycle) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-success">Mark cycle as verified</button></form>
        @endcan
    </div>
    <dl class="row small mb-0">
        <dt class="col-md-3">Exam cycles</dt><dd class="col-md-9">{{ $cycle->examCycles->map(fn ($c) => $c->exam->name . ' ' . $c->title)->implode(', ') ?: '-' }}</dd>
        <dt class="col-md-3">Willingness options</dt><dd class="col-md-9">{{ collect($cycle->willingness_options)->map(fn ($w) => \App\Models\Counselling\CounsellingCycle::WILLINGNESS[$w] ?? $w)->implode(', ') ?: '-' }}</dd>
        <dt class="col-md-3">Bulletin</dt>
        <dd class="col-md-9">
            @if($cycle->information_bulletin_url)<a href="{{ $cycle->information_bulletin_url }}" target="_blank" rel="noopener">URL</a>@endif
            @if($cycle->information_bulletin_pdf) <a href="{{ asset('storage/' . $cycle->information_bulletin_pdf) }}" target="_blank" rel="noopener">PDF</a>@endif
            @if(!$cycle->information_bulletin_url && !$cycle->information_bulletin_pdf) - @endif
        </dd>
        <dt class="col-md-3">Verified</dt><dd class="col-md-9">{{ $cycle->last_verified_at ? $cycle->last_verified_at->diffForHumans() : 'Not verified' }}</dd>
        <dt class="col-md-3">Reservation policy</dt><dd class="col-md-9">{!! $cycle->reservation_policy ? nl2br(e(\Illuminate\Support\Str::limit($cycle->reservation_policy, 400))) : '-' !!}</dd>
        <dt class="col-md-3">Allotment method</dt><dd class="col-md-9">{!! $cycle->seat_allotment_method ? nl2br(e(\Illuminate\Support\Str::limit($cycle->seat_allotment_method, 400))) : '-' !!}</dd>
    </dl>
    </div></div>

{{-- Cycle-level dates --}}
@include('admin.counselling._events', ['cycle' => $cycle, 'round' => null, 'events' => $cycle->events, 'storeUrl' => route('admin.counselling.events.store-cycle', $cycle)])

@include('admin.counselling._blocks', ['cycle' => $cycle, 'round' => null])

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h6 class="fw-bold mb-1">Participating institutes</h6>
        <span class="text-muted small">{{ number_format(\App\Models\Counselling\Participant::where('cycle_id', $cycle->id)->count()) }} college + course + quota rows. Feeds "Admission through" on college pages.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.participants.index', $cycle) }}" class="btn btn-sm btn-light border">View / edit</a>
        @can('cns-import-manage')<a href="{{ route('admin.counselling.imports.create', ['type' => 'participants', 'cycle' => $cycle->id]) }}" class="btn btn-sm btn-outline-primary">Import CSV</a>@endcan
    </div>
</div></div>

{{-- Rounds --}}
<div class="card border-0 shadow-sm rounded-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Rounds</h6>
        @can('cns-counsellings-edit')<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#roundModal"><i class="fas fa-plus me-1"></i> Add Round</button>@endcan
    </div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th>#</th><th>Round</th><th>Type</th><th>Quotas</th><th>Status</th><th>Fresh registration</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($cycle->rounds as $r)
            <tr>
                <td>{{ $r->sort_order }}</td>
                <td><a href="{{ route('admin.counselling.rounds.show', $r) }}" class="fw-semibold text-decoration-none">{{ $r->name }}</a></td>
                <td>{{ $r->type->name }}</td>
                <td class="small">{{ $r->quotas ? \App\Models\Counselling\Quota::whereIn('id', $r->quotas)->pluck('name')->implode(', ') : 'All' }}</td>
                <td><span class="badge {{ $badge($r->round_status) }}">{{ ucfirst($r->round_status) }}</span></td>
                <td>{{ $r->effectiveFreshRegistration() ? 'Allowed' : 'No' }}@if($r->fresh_registration_allowed === null) <span class="text-muted small">(inherited)</span>@endif</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.counselling.rounds.show', $r) }}" class="btn btn-sm btn-light border"><i class="fas fa-eye text-info"></i></a>
                    @can('cns-counsellings-edit')
                    <form action="{{ route('admin.counselling.rounds.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this round and its dates?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No rounds yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div></div>

@can('cns-counsellings-edit')
<div class="modal fade" id="copyModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('admin.counselling.cycles.copy', $cycle) }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Copy Cycle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="small text-muted">Copies eligibility, process, fees, documents (with templates), rules, rounds with their overrides, and the event list <strong>without dates</strong>. Does not copy the bulletin or verification. Date fields inside copied content are cleared and flagged "Needs a new date".</p>
            <div class="mb-3"><label class="form-label fw-bold">New year</label><input type="number" name="year" class="form-control" value="{{ $cycle->year + 1 }}" required></div>
            <div><label class="form-label fw-bold">Session label</label><input type="text" name="session_label" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Copy</button></div>
    </form>
</div></div></div>
<div class="modal fade" id="roundModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST" action="{{ route('admin.counselling.rounds.store', $cycle) }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Add Round</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">@include('admin.counselling.rounds._fields', ['round' => new \App\Models\Counselling\Round(['round_status' => 'scheduled'])])</div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Round</button></div>
    </form>
</div></div></div>
@endcan
@endsection
