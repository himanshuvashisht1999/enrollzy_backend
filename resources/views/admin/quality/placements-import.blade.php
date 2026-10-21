@extends('admin.layouts.master')

@section('title', 'Import placement figures')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Import placement figures</h4>
        <p class="text-muted mb-0">Enter placement figures for many programmes at once. Download a template, fill in the numbers, upload it, check the preview, then confirm. Nothing is saved until you confirm, existing figures are never overwritten, and each import can be undone as a batch. The placement percentage and the department / campus / organisation totals are calculated, so you only enter the raw numbers.</p>
    </div>
    <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Dashboard</a>
</div>

@if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <div class="fw-bold mb-2">1. Get a template</div>
            <form method="GET" action="{{ route('admin.quality.placements.import.template') }}" class="row g-2 align-items-end">
                <div class="col-md-6"><label class="form-label small mb-1">Organisation (optional)</label>
                    <select name="organisation" id="plOrg" class="form-select"></select></div>
                <div class="col-md-3"><label class="form-label small mb-1">Academic year</label><input name="year" value="{{ $defaultYear }}" class="form-control" pattern="\d{4}-\d{2}"></div>
                <div class="col-md-3"><button class="btn btn-primary w-100"><i class="fas fa-download me-1"></i> CSV</button></div>
            </form>
            <div class="small text-muted mt-3">The template lists that organisation's programmes (or, with no organisation chosen, up to {{ number_format(\App\Services\Quality\PlacementImporter::MAX_ROWS) }} programmes) that have <strong>no figures yet for that year</strong>, with their id, organisation, campus and name already filled in. You only type the numbers. Delete the rows you have no figures for.</div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <div class="fw-bold mb-2">2. Upload and preview</div>
            <form method="POST" action="{{ route('admin.quality.placements.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" class="form-control mb-2" accept=".csv,.txt" required>
                <button class="btn btn-primary">Preview (saves nothing)</button>
            </form>
            <div class="small text-muted mt-3">
                Required columns: <code>id</code> (the programme id) and <code>academic_year</code> (2024-25).
                Figures: <code>students_graduating</code>, <code>students_placed</code>, <code>students_higher_studies</code>, <code>median_salary</code>, <code>highest_salary</code>, <code>average_salary</code>, <code>recruiters_count</code>.
                Optional: <code>placement_source</code>, <code>source_url</code>, <code>notes</code>, <code>level</code> (default course).
                Salaries can be <code>650000</code>, <code>6,50,000</code> or <code>6.5 LPA</code>; a small bare number is refused instead of guessed.
            </div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 fw-bold">Import history</div>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Batch</th><th>When</th><th>By</th><th class="text-end">Created</th><th class="text-end">Still active</th><th class="text-end">Verified</th><th class="text-end pe-4"></th></tr></thead>
        <tbody>
        @forelse($batches as $b)
            <tr>
                <td class="ps-4"><code>{{ $b->import_batch }}</code></td>
                <td>{{ \Illuminate\Support\Carbon::parse($b->at)->format('d M Y H:i') }}</td>
                <td>{{ $users[$b->by_user] ?? '-' }}</td>
                <td class="text-end">{{ number_format($b->total) }}</td>
                <td class="text-end">{{ number_format($b->active) }}</td>
                <td class="text-end">{{ number_format($b->verified) }}</td>
                <td class="text-end pe-4">
                    <a class="btn btn-sm btn-light border" href="{{ route('admin.quality.review', ['kind' => 'placement', 'status' => 'unverified', 'batch' => $b->import_batch]) }}">Review</a>
                    @if($b->active)<form action="{{ route('admin.quality.placements.import.undo') }}" method="POST" class="d-inline" data-confirm="Remove all {{ $b->active }} placement rows created by this batch?{{ $b->verified ? ' ' . $b->verified . ' of them have been verified.' : '' }}">@csrf<input type="hidden" name="batch" value="{{ $b->import_batch }}"><button class="btn btn-sm btn-light border text-danger">Undo batch</button></form>@else<span class="badge bg-secondary-subtle text-secondary">undone</span>@endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No imports yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection

@push('scripts')
<script>
    $('#plOrg').select2({
        width: '100%', minimumInputLength: 2, placeholder: 'All programmes (or type 2+ letters)', allowClear: true,
        ajax: { url: @json(route('admin.quality.placements.import.organisations')), dataType: 'json', delay: 250, data: p => ({ q: p.term }), processResults: d => ({ results: d }) }
    });
</script>
@endpush
