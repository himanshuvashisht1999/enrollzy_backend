@extends('admin.layouts.master')

@section('title', 'Import placement figures: preview')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Import placement figures: preview</h4>
        <p class="text-muted mb-0">{{ $filename }} &middot; <strong>Nothing has been saved yet.</strong> Rows with errors and rows that already exist are skipped; the clean new rows are created when you confirm.</p>
    </div>
    <a href="{{ route('admin.quality.placements.import') }}" class="btn btn-light border">Cancel</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="fs-3 fw-bold text-success">{{ number_format($summary['new']) }}</div><div class="text-muted small">will be created @if($summary['graduating'])&middot; {{ number_format($summary['placed']) }} of {{ number_format($summary['graduating']) }} placed ({{ round($summary['placed'] / $summary['graduating'] * 100, 1) }}%) @endif</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="fs-3 fw-bold text-danger">{{ number_format($summary['error']) }}</div><div class="text-muted small">rows have errors (skipped)</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="fs-3 fw-bold">{{ number_format($summary['exists']) }}</div><div class="text-muted small">already recorded (left as is)</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="fs-3 fw-bold {{ $summary['warnings'] ? 'text-warning' : '' }}">{{ number_format($summary['warnings']) }}</div><div class="text-muted small">rows with a warning &middot; {{ number_format($summary['rows']) }} read</div></div></div></div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-3"><div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Line</th><th>Programme / entity</th><th>Year</th><th>Figures</th><th>Result</th></tr></thead>
        <tbody>
        @foreach($show as $r)
            <tr class="{{ $r['status'] === 'error' ? 'table-danger' : ($r['status'] === 'exists' ? 'table-light' : '') }}">
                <td class="ps-4">{{ $r['line'] }}</td>
                <td>{{ $names[$r['level'] . '|' . $r['id']] ?? ($r['level'] . ' #' . $r['id']) }}<div class="small text-muted">{{ $r['level'] }} #{{ $r['id'] }}</div></td>
                <td>{{ $r['year'] ?? '-' }}</td>
                <td class="small">@if($r['status'] === 'new'){{ $r['data']['students_placed'] !== null ? number_format($r['data']['students_placed']) : '-' }} / {{ $r['data']['students_graduating'] !== null ? number_format($r['data']['students_graduating']) : '-' }} placed
                    @if($r['data']['students_graduating'] && $r['data']['students_placed'] !== null) <strong>({{ round($r['data']['students_placed'] / $r['data']['students_graduating'] * 100, 1) }}%)</strong>@endif
                    <div class="text-muted">median {{ \App\Services\Quality\Aggregates::money($r['data']['median_salary']) }}, highest {{ \App\Services\Quality\Aggregates::money($r['data']['highest_salary']) }}</div>@endif</td>
                <td class="small">
                    @if($r['status'] === 'new')<span class="text-success">Will be created</span>@else<span class="{{ $r['status'] === 'error' ? 'text-danger' : 'text-muted' }}">@foreach($r['errors'] as $e)<div>{{ $e }}</div>@endforeach</span>@endif
                    @foreach($r['warnings'] as $w)<div class="text-warning">{{ $w }}</div>@endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@if($summary['error'] > 200 || $summary['exists'] > 50 || $summary['new'] > 50)
    <div class="small text-muted mb-3">Showing the first 200 errors, 50 existing and 50 new rows. The import itself covers every row.</div>
@endif

@if($summary['new'])
<form method="POST" action="{{ route('admin.quality.placements.import.confirm') }}" onsubmit="return confirm('Create {{ $summary['new'] }} placement row(s) now? You can undo the whole batch afterwards.')">
    @csrf <input type="hidden" name="token" value="{{ $token }}">
    <button class="btn btn-success"><i class="fas fa-check me-1"></i> Create {{ number_format($summary['new']) }} row(s)</button>
    @if($summary['error'])<span class="text-muted small ms-2">The {{ number_format($summary['error']) }} rows with errors will be skipped. Fix them in the file and import again if you need them.</span>@endif
</form>
@else
<div class="alert alert-info">There is nothing to create from this file.</div>
@endif
@endsection
