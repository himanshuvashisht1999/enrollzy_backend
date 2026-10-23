@extends('admin.layouts.master')

@section('title', 'Seat matrix')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Seat matrix <span class="text-muted fs-6">{{ $round->cycle->counselling->name }} · {{ $round->cycle->title }} · {{ $round->name }}</span></h4>
        <p class="text-muted mb-0">Every revision is kept; exactly one version is live. New versions come in through the CSV import.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.rounds.show', $round) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Round</a>
        @can('cns-import-manage')<a href="{{ route('admin.counselling.imports.create', ['type' => 'seats', 'cycle' => $round->cycle_id, 'round' => $round->id]) }}" class="btn btn-primary"><i class="fas fa-file-csv me-1"></i> Import new version</a>@endcan
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Version</th><th>Label</th><th>Published</th><th>Rows</th><th>Change from previous</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($versions as $v)
            <tr>
                <td class="ps-4 fw-semibold">v{{ $v->version_no }}</td>
                <td>{{ $v->label ?: '-' }}@if($v->source_url) <a href="{{ $v->source_url }}" target="_blank" rel="noopener" class="small ms-1">source</a>@endif</td>
                <td class="small">{{ $v->published_on?->format('d M Y') ?? '-' }}<div class="text-muted">imported {{ $v->imported_at?->diffForHumans() ?? '-' }}</div></td>
                <td>{{ number_format($v->row_count) }}</td>
                <td class="small">
                    @if($diffs[$v->id])
                        <a href="{{ route('admin.counselling.seats.diff', $v) }}" class="text-decoration-none">
                            <span class="text-success">+{{ $diffs[$v->id]['added'] }}</span> /
                            <span class="text-danger">-{{ $diffs[$v->id]['removed'] }}</span> /
                            <span class="text-warning">~{{ $diffs[$v->id]['changed'] }}</span></a>
                    @else <span class="text-muted">first version</span> @endif
                </td>
                <td>@if($v->is_live)<span class="badge bg-success">Live</span>@else<span class="badge bg-light text-dark border">Not live</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    <a href="{{ route('admin.counselling.seats.show', $v) }}" class="btn btn-sm btn-light border"><i class="fas fa-eye text-info"></i></a>
                    @can('cns-counsellings-edit')
                        @unless($v->is_live)
                        <form action="{{ route('admin.counselling.seats.live', $v) }}" method="POST" class="d-inline" data-confirm="Make v{{ $v->version_no }} the live seat matrix?">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success">Make live</button></form>
                        @endunless
                        <form action="{{ route('admin.counselling.seats.destroy', $v) }}" method="POST" class="d-inline" data-confirm="Delete v{{ $v->version_no }} and its {{ $v->row_count }} rows?{{ $v->is_live ? ' It is live: the latest remaining version will become live.' : '' }}">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">No seat matrix yet. Import the first version.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
