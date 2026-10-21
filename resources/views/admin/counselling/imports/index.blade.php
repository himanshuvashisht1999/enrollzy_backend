@extends('admin.layouts.master')

@section('title', 'Import centre')

@php
    $badge = fn ($s) => match ($s) {
        'confirmed' => 'bg-success-subtle text-success', 'previewed' => 'bg-info-subtle text-info',
        'undone' => 'bg-warning-subtle text-warning', default => 'bg-secondary-subtle text-secondary',
    };
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Import centre</h4>
        <p class="text-muted mb-0">Participating institutes, seat matrix and cutoffs arrive by CSV: preview first, nothing is saved until you confirm, any import can be undone.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.counsellings.index') }}" class="btn btn-light border">Counselling</a>
        <a href="{{ route('admin.counselling.imports.create') }}" class="btn btn-primary"><i class="fas fa-upload me-1"></i> New import</a>
    </div>
</div>

<form method="GET" class="mb-3">
    <div class="btn-group">
        <a href="{{ route('admin.counselling.imports.index') }}" class="btn btn-sm {{ !request('type') ? 'btn-primary' : 'btn-light border' }}">All</a>
        @foreach(\App\Models\Counselling\ImportBatch::TYPES as $k => $l)
            <a href="{{ route('admin.counselling.imports.index', ['type' => $k]) }}" class="btn btn-sm {{ request('type') === $k ? 'btn-primary' : 'btn-light border' }}">{{ $l }}</a>
        @endforeach
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">#</th><th>What</th><th>Counselling</th><th>File</th><th>Rows</th><th>Status</th><th>By</th><th class="text-end pe-4"></th></tr></thead>
        <tbody>
        @forelse($batches as $b)
            <tr>
                <td class="ps-4">{{ $b->id }}</td>
                <td>{{ \App\Models\Counselling\ImportBatch::TYPES[$b->type] }}<div class="small text-muted">{{ $b->mode === 'replace' ? 'Replace' : 'Append' }}</div></td>
                <td class="small">{{ $b->cycle->counselling->name ?? '-' }} · {{ $b->cycle->title ?? '' }}@if($b->round)<div class="text-muted">{{ $b->round->name }}</div>@endif</td>
                <td class="small">{{ $b->file_name }}<div class="text-muted">{{ $b->created_at->format('d M Y, h:i A') }}</div></td>
                <td class="small">
                    {{ number_format($b->total_rows) }} total
                    <div><span class="text-success">{{ $b->matched_rows }} ok</span> · <span class="text-warning">{{ $b->warning_rows }} warn</span> · <span class="text-danger">{{ $b->error_rows }} err</span>@if($b->status === 'confirmed') · saved {{ $b->saved_rows }}@endif</div>
                </td>
                <td><span class="badge {{ $badge($b->status) }}">{{ ucfirst($b->status) }}</span></td>
                <td class="small">{{ $b->user->name ?? '-' }}</td>
                <td class="text-end pe-4 text-nowrap">
                    <a href="{{ route('admin.counselling.imports.show', $b) }}" class="btn btn-sm btn-light border">{{ $b->status === 'previewed' ? 'Continue' : 'View' }}</a>
                    @if($b->status === 'confirmed')
                        @can('cns-import-undo')
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#undoImport{{ $b->id }}">Undo</button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-5">No imports yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mb-4">{{ $batches->links() }}</div>

<div class="card border-0 shadow-sm rounded-4"><div class="card-body">
    <h6 class="fw-bold">Names the importer has learned <span class="badge bg-secondary">{{ $aliases->count() }}</span></h6>
    <p class="small text-muted">Created when you pick the right record for a name in a preview. Delete a wrong one and the name will need matching again.</p>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead class="bg-light"><tr><th>Type</th><th>CSV says</th><th>Means</th><th></th></tr></thead>
        <tbody>
        @forelse($aliases as $a)
            <tr>
                <td class="text-capitalize">{{ $a->type }}</td><td>{{ $a->alias }}</td><td>{{ $names[$a->type][$a->target_id] ?? '(deleted record #' . $a->target_id . ')' }}</td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#removeAlias{{ $a->id }}" title="Remove"><i class="fas fa-trash-alt"></i></button></td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-muted small">Nothing learned yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div></div>
{{-- In-page confirmations (no browser pop-up) --}}
@can('cns-import-undo')
@foreach($batches as $b)
    @if($b->status === 'confirmed')
        @include('admin.counselling._confirm_dialog', ['id' => 'undoImport' . $b->id, 'title' => 'Undo import #' . $b->id . '?', 'action' => route('admin.counselling.imports.undo', $b),
            'lines' => $b->undoSummary(), 'label' => 'Yes, undo the import', 'btnClass' => 'btn-warning'])
    @endif
@endforeach
@endcan
@foreach($aliases as $a)
    @include('admin.counselling._confirm_dialog', ['id' => 'removeAlias' . $a->id, 'title' => 'Remove this learned name?', 'action' => route('admin.counselling.imports.aliases.destroy', $a), 'method' => 'DELETE',
        'lines' => ['"' . $a->alias . '" will no longer be matched automatically. The next import that uses it will ask you to match it again.'], 'label' => 'Yes, remove', 'btnClass' => 'btn-danger'])
@endforeach
@endsection
