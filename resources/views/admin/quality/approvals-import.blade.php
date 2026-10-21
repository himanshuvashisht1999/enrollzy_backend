@extends('admin.layouts.master')

@section('title', 'Import approvals')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Import statutory approvals &amp; intake</h4>
        <p class="text-muted mb-0">Enter approvals for many programmes at once. Download a template, fill in the year, intake and approval number, upload it, check the preview, then confirm. Nothing is saved until you confirm, existing records are never overwritten, and each import can be undone as a batch.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.quality.approvals.coverage') }}" class="btn btn-light border">Approval coverage</a>
        <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Dashboard</a>
    </div>
</div>

@if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <div class="fw-bold mb-2">1. Get a template</div>
            <form method="GET" action="{{ route('admin.quality.approvals.import.template') }}" class="row g-2 align-items-end">
                <div class="col-md-5"><label class="form-label small mb-1">Regulator</label>
                    <select name="body" class="form-select"><option value="">Blank (examples only)</option>
                        @foreach($bodies as $b)<option value="{{ $b->id }}">{{ $b->short_name }}@if($b->disciplines()->exists()) (programmes missing it)@endif</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label small mb-1">Academic year</label><input name="year" value="{{ $defaultYear }}" class="form-control" pattern="\d{4}-\d{2}"></div>
                <div class="col-md-4"><button class="btn btn-primary w-100"><i class="fas fa-download me-1"></i> Download CSV</button></div>
            </form>
            <div class="small text-muted mt-3">Choosing a regulator gives you every programme in its disciplines that has no current approval, already listed with its organisation, campus and id. You only fill in the intake, number and dates. Leave a row's intake blank and it is still imported as an approval without intake; delete rows you do not want.</div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <div class="fw-bold mb-2">2. Upload and preview</div>
            <form method="POST" action="{{ route('admin.quality.approvals.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" class="form-control mb-2" accept=".csv,.txt" required>
                <button class="btn btn-primary">Preview (saves nothing)</button>
            </form>
            <div class="small text-muted mt-3">Required columns: <code>id</code>, <code>body</code> (e.g. NMC), <code>academic_year</code> (2025-26). Optional: <code>level</code> (default course), <code>sanctioned_intake</code>, <code>approval_number</code>, <code>valid_from</code>, <code>valid_to</code>, <code>status</code>, <code>source_url</code>, <code>notes</code>. Other columns are ignored. Up to {{ number_format(\App\Services\Quality\ApprovalImporter::MAX_ROWS) }} rows.</div>
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
                    <a class="btn btn-sm btn-light border" href="{{ route('admin.quality.review', ['kind' => 'approval', 'status' => 'unverified', 'batch' => $b->import_batch]) }}">Review</a>
                    @if($b->active)<form action="{{ route('admin.quality.approvals.import.undo') }}" method="POST" class="d-inline" data-confirm="Remove all {{ $b->active }} approvals created by this batch?{{ $b->verified ? ' ' . $b->verified . ' of them have been verified.' : '' }}">@csrf<input type="hidden" name="batch" value="{{ $b->import_batch }}"><button class="btn btn-sm btn-light border text-danger">Undo batch</button></form>@else<span class="badge bg-secondary-subtle text-secondary">undone</span>@endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No imports yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
