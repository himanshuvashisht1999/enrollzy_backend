@extends('admin.layouts.master')

@section('title', 'Identity import preview')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Identity import: preview</h4>
        <p class="text-muted mb-0">Nothing has been changed yet. Rows with errors are skipped; the rest are applied when you confirm.</p>
    </div>
    <a href="{{ route('admin.quality.identity.index') }}" class="btn btn-light border">Cancel</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="fs-3 fw-bold text-success">{{ $ok }}</div><div class="text-muted small">rows will change</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="fs-3 fw-bold text-danger">{{ $bad }}</div><div class="text-muted small">rows have errors (skipped)</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><div class="fs-3 fw-bold">{{ $same }}</div><div class="text-muted small">rows already match</div></div></div></div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-3"><div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Line</th><th>Row</th><th>Changes</th><th>Problems</th></tr></thead>
        <tbody>
        @foreach($rows as $r)
            @if($r['errors'] || $r['changes'])
            <tr class="{{ $r['errors'] ? 'table-danger' : '' }}">
                <td class="ps-4">{{ $r['line'] }}</td>
                <td>{{ $r['name'] ?? $r['id'] }}<div class="small text-muted">{{ $r['level'] }} #{{ $r['id'] }}</div></td>
                <td>@foreach($r['changes'] as $col => $c)<div class="small"><code>{{ $col }}</code>: <span class="text-muted">{{ $c['from'] ?? '(empty)' }}</span> &rarr; <strong>{{ $c['to'] }}</strong></div>@endforeach</td>
                <td class="text-danger small">@foreach($r['errors'] as $e)<div>{{ $e }}</div>@endforeach</td>
            </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div></div>

@if($ok)
<form method="POST" action="{{ route('admin.quality.identity.import.confirm') }}">@csrf <input type="hidden" name="token" value="{{ $token }}">
    <button class="btn btn-success"><i class="fas fa-check me-1"></i> Apply {{ $ok }} change(s)</button></form>
@else
<div class="alert alert-info">There is nothing to apply.</div>
@endif
@endsection
