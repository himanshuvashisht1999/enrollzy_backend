@extends('admin.layouts.master')

@section('title', 'Approval coverage')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Approval coverage</h4>
        <p class="text-muted mb-0">Programmes that fall under a regulator (by discipline) and whether a current approval is recorded for them. A programme counts as covered when it has an active, non-rejected approval that has not passed its end date.</p>
    </div>
    <div class="d-flex gap-2">
        @can('quality-masters-manage')<a href="{{ route('admin.quality.masters.index', 'accreditation-bodies') }}" class="btn btn-light border">Edit which disciplines each body governs</a>@endcan
        @can('quality-import')<a href="{{ route('admin.quality.approvals.import') }}" class="btn btn-primary"><i class="fas fa-file-csv me-1"></i> Import approvals (CSV)</a>@endcan
        <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Dashboard</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Regulator</th><th>Disciplines it governs</th><th class="text-end">Programmes</th><th class="text-end">With approval</th><th class="text-end">Missing</th><th style="width:160px">Covered</th><th></th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            @php $pct = $r['total'] ? round($r['covered'] / $r['total'] * 100) : 0; @endphp
            <tr class="{{ $selected && $selected->id === $r['body']->id ? 'table-active' : '' }}">
                <td class="ps-4 fw-bold">{{ $r['body']->short_name }}<div class="small text-muted fw-normal">{{ $r['body']->name }}</div></td>
                <td class="small">{{ implode(', ', array_slice($r['disciplines'], 0, 6)) }}@if(count($r['disciplines']) > 6) <span class="text-muted">+{{ count($r['disciplines']) - 6 }} more</span>@endif</td>
                <td class="text-end">{{ number_format($r['total']) }}</td>
                <td class="text-end text-success">{{ number_format($r['covered']) }}</td>
                <td class="text-end fw-bold {{ $r['missing'] ? 'text-danger' : 'text-success' }}">{{ number_format($r['missing']) }}</td>
                <td><div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:{{ $pct }}%"></div></div><div class="small text-muted">{{ $pct }}%</div></td>
                <td class="text-end pe-3">@if($r['missing'])<a class="btn btn-sm btn-light border" href="{{ route('admin.quality.approvals.coverage', ['body' => $r['body']->id]) }}">Show missing</a>@endif</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">No regulator has disciplines mapped yet. Open Masters &rarr; Accreditation &amp; approval bodies and choose the disciplines each body governs.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>

@if($unmapped)
    <div class="alert alert-light border small">Not checked because no disciplines are mapped: <strong>{{ implode(', ', $unmapped) }}</strong>.</div>
@endif

@if($selected && $list)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold">{{ $selected->short_name }}: programmes with no current approval ({{ number_format($list->total()) }})</span>
            <form method="GET" class="d-flex gap-2"><input type="hidden" name="body" value="{{ $selected->id }}"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Organisation name"><button class="btn btn-sm btn-light border">Filter</button></form>
        </div>
        <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0">
            <thead class="bg-light"><tr><th class="ps-4">Organisation</th><th>Campus</th><th>Programme</th><th></th></tr></thead>
            <tbody>
            @foreach($list as $r)
                <tr><td class="ps-4">{{ $r->organisation }}</td><td>{{ $r->campus ?: '-' }}</td><td>{{ $r->unit ?: ($r->course ?: 'Programme #' . $r->id) }}</td>
                    <td class="text-end pe-3"><a class="btn btn-sm btn-primary" href="{{ route('admin.quality.entity', ['course', $r->id]) }}">Add approval</a></td></tr>
            @endforeach
            </tbody>
        </table></div>
        <div class="p-3">{{ $list->links() }}</div>
    </div>
@endif
@endsection
