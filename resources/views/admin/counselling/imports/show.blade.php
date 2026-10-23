@extends('admin.layouts.master')

@section('title', 'Import #' . $batch->id)

@php
    $open = $batch->status === 'previewed';
    $tone = ['matched' => 'bg-success-subtle text-success', 'warning' => 'bg-warning-subtle text-warning', 'error' => 'bg-danger-subtle text-danger'];
    $fieldLabel = ['organisation' => 'College', 'course' => 'Course', 'quota' => 'Quota', 'category' => 'Category', 'exam' => 'Exam'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold">{{ \App\Models\Counselling\ImportBatch::TYPES[$batch->type] }} import #{{ $batch->id }}
            <span class="badge {{ ['previewed' => 'bg-info', 'confirmed' => 'bg-success', 'undone' => 'bg-warning text-dark', 'cancelled' => 'bg-secondary'][$batch->status] }} fs-6 align-middle">{{ ucfirst($batch->status === 'previewed' ? 'preview' : $batch->status) }}</span></h4>
        <div class="text-muted small">{{ $batch->cycle->counselling->name }} · {{ $batch->cycle->title }}@if($batch->round) · {{ $batch->round->name }}@endif
            · {{ $batch->file_name }} · {{ $batch->mode === 'replace' ? 'Replace' : 'Append' }}@if($batch->type === 'seats') · {{ $batch->make_live ? 'will be made live' : 'will not be made live' }}@endif</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.counselling.imports.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Imports</a>
        @if($batch->error_rows + $batch->warning_rows > 0)<a href="{{ route('admin.counselling.imports.errors', $batch) }}" class="btn btn-outline-secondary"><i class="fas fa-download me-1"></i> Problem rows (CSV)</a>@endif
        @if($open)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelImportDialog">Cancel</button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmImportDialog" @disabled($batch->matched_rows === 0)><i class="fas fa-check me-1"></i> Confirm and save {{ number_format($batch->matched_rows) }} row{{ $batch->matched_rows === 1 ? '' : 's' }}</button>
        @elseif($batch->status === 'confirmed')
            @can('cns-import-undo')
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#undoImportDialog">Undo import</button>
            @endcan
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach([['', 'All rows', $batch->total_rows, 'bg-light'], ['matched', 'Ready to save', $batch->matched_rows, 'bg-success-subtle'], ['warning', 'Need confirmation', $batch->warning_rows, 'bg-warning-subtle'], ['error', 'Errors (skipped)', $batch->error_rows, 'bg-danger-subtle']] as [$st, $label, $n, $bg])
    <div class="col-6 col-md-3"><a href="{{ route('admin.counselling.imports.show', [$batch, 'status' => $st ?: null]) }}" class="text-decoration-none">
        <div class="rounded-4 p-3 {{ $bg }} {{ request('status', '') === $st ? 'border border-dark' : '' }}"><div class="small text-muted">{{ $label }}</div><div class="fs-4 fw-bold text-dark">{{ number_format($n) }}</div></div></a></div>
    @endforeach
</div>

@if($batch->status === 'confirmed')
    <div class="alert alert-success">{{ number_format($batch->saved_rows) }} rows saved {{ $batch->confirmed_at?->diffForHumans() }}.
        @if($batch->type === 'seats') <a href="{{ route('admin.counselling.seats.index', $batch->round) }}">Open the seat matrix versions</a>.
        @elseif($batch->type === 'cutoffs') <a href="{{ route('admin.counselling.cutoffs.index', $batch->round) }}">Open the cutoffs</a>.
        @else <a href="{{ route('admin.counselling.participants.index', $batch->cycle) }}">Open the participants</a>. @endif</div>
@endif

@if($open && count($unresolved['warnings']))
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Suggested matches to confirm <span class="badge bg-warning text-dark">{{ count($unresolved['warnings']) }}</span></h6>
        <form action="{{ route('admin.counselling.imports.accept', $batch) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-success">Accept all</button></form>
    </div>
    <p class="small text-muted">These names are close to a record but not identical. Accepting teaches the importer, so the next file matches automatically.</p>
    @foreach($unresolved['warnings'] as $w)
        <div class="d-flex justify-content-between align-items-center border-top py-2 flex-wrap gap-2">
            <div><span class="badge bg-light text-dark border">{{ $fieldLabel[$w['type']] }}</span>
                <strong>"{{ $w['raw'] }}"</strong> <i class="fas fa-arrow-right mx-1 text-muted"></i> <strong>{{ $w['suggest']['name'] }}</strong>
                <span class="small text-muted">({{ $w['suggest']['score'] }}% similar, {{ $w['rows'] }} row{{ $w['rows'] === 1 ? '' : 's' }})</span></div>
            <form action="{{ route('admin.counselling.imports.accept', $batch) }}" method="POST">@csrf
                <input type="hidden" name="type" value="{{ $w['type'] }}"><input type="hidden" name="raw" value="{{ $w['raw'] }}"><button class="btn btn-sm btn-success">Accept</button></form>
        </div>
    @endforeach
</div></div>
@endif

@if($open && count($unresolved['errors']))
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold">Names that could not be matched <span class="badge bg-danger">{{ count($unresolved['errors']) }}</span></h6>
    <p class="small text-muted">Pick the right record. It is remembered, and every row using that name is re-checked. If the record does not exist yet, add it to its master first: the importer will not create it.</p>
    @foreach($unresolved['errors'] as $i => $u)
        <form action="{{ route('admin.counselling.imports.fix', $batch) }}" method="POST" class="row g-2 align-items-center border-top py-2">
            @csrf
            <input type="hidden" name="type" value="{{ $u['type'] }}"><input type="hidden" name="raw" value="{{ $u['raw'] }}">
            <div class="col-md-4"><span class="badge bg-light text-dark border">{{ $fieldLabel[$u['type']] }}</span> <strong>"{{ $u['raw'] }}"</strong>
                <div class="small text-muted">{{ $u['rows'] }} row{{ $u['rows'] === 1 ? '' : 's' }} · {{ \Illuminate\Support\Str::limit($u['message'], 90) }}</div></div>
            <div class="col-md-6"><select name="target_id" class="form-select custom-select2 fix-select" data-type="{{ $u['type'] }}" required></select></div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Use this</button></div>
        </form>
    @endforeach
</div></div>
@endif

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-3">Row</th><th>Status</th>@foreach(\App\Services\Counselling\Importer::DEFS[$batch->type]['columns'] as $c)<th class="small">{{ str_replace('_', ' ', $c) }}</th>@endforeach<th>Problems</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td class="ps-3 text-muted">{{ $r->row_no }}</td>
                @php
                    $notParticipant = $r->status === 'error' && collect($r->messages ?? [])->contains(fn ($m) => str_starts_with($m, 'Not a participating institute'));
                    $statusLabel = $notParticipant ? 'Not a participating institute' : ['matched' => 'Ready', 'warning' => 'Needs confirmation', 'error' => 'Error'][$r->status];
                @endphp
                <td><span class="badge {{ $tone[$r->status] }}">{{ $statusLabel }}</span>@if(count($r->messages ?? []) > 1)<div class="small text-muted mt-1">{{ count($r->messages) }} problems</div>@endif</td>
                @foreach(\App\Services\Counselling\Importer::DEFS[$batch->type]['columns'] as $c)<td class="small">{{ \Illuminate\Support\Str::limit($r->raw[$c] ?? '', 40) }}</td>@endforeach
                {{-- every problem of the row, one per line --}}
                <td class="small {{ $r->status === 'error' ? 'text-danger' : 'text-warning' }}">@if(count($r->messages ?? []) > 1)<ul class="mb-0 ps-3">@foreach($r->messages as $m)<li>{{ $m }}</li>@endforeach</ul>@else{{ $r->messages[0] ?? '' }}@endif</td>
            </tr>
        @empty
            <tr><td colspan="20" class="text-center text-muted py-4">No rows.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $rows->links() }}</div>
{{-- In-page confirmations (no browser pop-up) --}}
@if($open)
    @include('admin.counselling._confirm_dialog', ['id' => 'cancelImportDialog', 'title' => 'Cancel this import?', 'action' => route('admin.counselling.imports.cancel', $batch),
        'lines' => ['Nothing has been saved yet, so nothing is lost. The uploaded file stays in the import history as cancelled.'], 'label' => 'Yes, cancel the import', 'btnClass' => 'btn-danger'])
    @include('admin.counselling._confirm_dialog', ['id' => 'confirmImportDialog', 'title' => 'Save ' . number_format($batch->matched_rows) . ' row(s)?', 'action' => route('admin.counselling.imports.confirm', $batch),
        'lines' => array_values(array_filter([
            number_format($batch->matched_rows) . ' ready row(s) are saved under this import, so they can be undone together later.',
            ($batch->warning_rows + $batch->error_rows) ? number_format($batch->warning_rows + $batch->error_rows) . ' row(s) with warnings or errors are skipped: download the problem rows to fix and re-upload them.' : null,
            $batch->mode === 'replace' && $batch->type !== 'seats' ? 'Existing rows are REPLACED by this file. Undo puts them back.' : null,
        ])), 'label' => 'Yes, save', 'btnClass' => 'btn-success'])
@elseif($batch->status === 'confirmed')
    @can('cns-import-undo')
    @include('admin.counselling._confirm_dialog', ['id' => 'undoImportDialog', 'title' => 'Undo this import?', 'action' => route('admin.counselling.imports.undo', $batch),
        'lines' => $batch->undoSummary(), 'label' => 'Yes, undo the import', 'btnClass' => 'btn-warning'])
    @endcan
@endif
@endsection

@push('scripts')
<script>
    const LOOKUP = @json(route('admin.counselling.imports.lookup'));
    $(function () {
        $('.fix-select').each(function () {
            const type = this.dataset.type;
            $(this).select2({ width: '100%', placeholder: 'Search the master...',
                ajax: { url: LOOKUP, dataType: 'json', delay: 250, data: p => ({ q: p.term || '', type, batch: @json($batch->id) }), processResults: d => d } });
        });
    });
</script>
@endpush
