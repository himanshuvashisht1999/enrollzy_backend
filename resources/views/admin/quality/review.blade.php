@extends('admin.layouts.master')

@section('title', 'Review queue')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Review queue</h4>
        <p class="text-muted mb-0">Verify records one by one on the entity page, or tick several here. A record needs its year and a source link or evidence file before it can be verified.</p>
    </div>
    <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Rankings &amp; Accreditation</a>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link {{ $kind === 'ranking' ? 'active' : '' }}" href="{{ route('admin.quality.review', ['kind' => 'ranking']) }}">Rankings <span class="badge bg-light text-dark">{{ $counts['ranking'] }}</span></a></li>
    <li class="nav-item"><a class="nav-link {{ $kind === 'accreditation' ? 'active' : '' }}" href="{{ route('admin.quality.review', ['kind' => 'accreditation']) }}">Accreditations <span class="badge bg-light text-dark">{{ $counts['accreditation'] }}</span></a></li>
</ul>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="kind" value="{{ $kind }}">
    <div class="col-md-2"><select name="status" class="form-select" onchange="this.form.submit()">
        @foreach(['unverified' => 'Unverified', 'verified' => 'Verified', 'rejected' => 'Rejected'] as $k => $l)<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="body" class="form-select" onchange="this.form.submit()"><option value="">All bodies</option>
        @foreach($bodies as $id => $n)<option value="{{ $id }}" @selected(request('body') == $id)>{{ $n }}</option>@endforeach</select></div>
    <div class="col-md-2"><select name="origin" class="form-select" onchange="this.form.submit()"><option value="">Any origin</option>
        <option value="legacy" @selected(request('origin') === 'legacy')>Migrated</option><option value="manual" @selected(request('origin') === 'manual')>Entered by hand</option></select></div>
    <div class="col-md-2"><select name="type" class="form-select" onchange="this.form.submit()"><option value="">Any level</option>
        @foreach(\App\Support\Quality\Levels::LABELS as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach</select></div>
</form>

@if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

<form method="POST" action="{{ route('admin.quality.review.verify') }}">
    @csrf <input type="hidden" name="kind" value="{{ $kind }}">
    <div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light"><tr>
                <th class="ps-4" style="width:40px"><input type="checkbox" class="form-check-input" onclick="document.querySelectorAll('.rowcheck').forEach(c => c.checked = this.checked)"></th>
                <th>Entity</th><th>{{ $kind === 'ranking' ? 'Ranking' : 'Accreditation' }}</th><th>{{ $kind === 'ranking' ? 'Year' : 'Validity' }}</th><th>Source</th><th>Origin</th>
            </tr></thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    <td class="ps-4"><input type="checkbox" name="ids[]" value="{{ $r->id }}" class="form-check-input rowcheck"></td>
                    <td><a href="{{ route('admin.quality.entity', [$r->entity_type, $r->entity_id]) }}">{{ $names[$r->entity_type][$r->entity_id] ?? ucfirst($r->entity_type) . ' #' . $r->entity_id }}</a>
                        <div class="small text-muted">{{ \App\Support\Quality\Levels::LABELS[$r->entity_type] }}</div></td>
                    @if($kind === 'ranking')
                        <td>{{ $r->body->short_name ?: $r->body->name }} · {{ $r->category->name }}<div class="fw-bold">{{ $r->rank_label }}</div></td>
                        <td>@if($r->ranking_year){{ $r->ranking_year }}@else<span class="badge bg-warning-subtle text-warning">unknown</span>@endif</td>
                    @else
                        <td>{{ $r->body->short_name }} {{ $r->grade }}@if($r->certificate_number)<div class="small text-muted">{{ $r->certificate_number }}</div>@endif</td>
                        <td>{{ $r->valid_from?->format('d M Y') ?? '?' }} &rarr; {{ $r->valid_to?->format('d M Y') ?? 'open' }}</td>
                    @endif
                    <td>@if($r->source_url)<a href="{{ $r->source_url }}" target="_blank" rel="noopener">link</a>@elseif($r->evidence_file)<a href="{{ route('admin.quality.evidence', [$kind, $r->id]) }}" target="_blank">file</a>@else<span class="text-muted">none</span>@endif</td>
                    <td>@if($r->legacy_source)<span class="badge bg-secondary-subtle text-secondary" title="{{ $r->legacy_source }}">migrated</span>@else<span class="badge bg-info-subtle text-info">manual</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Nothing to review here.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        @can('quality-verify')<button class="btn btn-success" @disabled($status === 'verified')><i class="fas fa-check me-1"></i> Verify selected</button>@endcan
        <div>{{ $rows->links() }}</div>
    </div>
</form>
@endsection
