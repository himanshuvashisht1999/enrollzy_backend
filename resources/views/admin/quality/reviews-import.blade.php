@extends('admin.layouts.master')

@section('title', 'Import reviews')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Import reviews &amp; ratings</h4>
        <p class="text-muted mb-0">Enter many reviews at once. Download the template, paste in the reviews, upload it, check the preview, then confirm. Nothing is saved until you confirm, a review that is already recorded is skipped, and each import can be undone as a batch. The average rating is calculated from these rows, and only verified reviews feed the public figure.</p>
    </div>
    <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Dashboard</a>
</div>

@if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <div class="fw-bold mb-2">1. Get the template and the ids</div>
            <a href="{{ route('admin.quality.reviews.import.template') }}" class="btn btn-primary mb-3"><i class="fas fa-download me-1"></i> Blank template (with examples)</a>
            <form method="GET" action="{{ route('admin.quality.reviews.import.ids') }}" class="row g-2 align-items-end">
                <div class="col-md-8"><label class="form-label small mb-1">Need the ids for one organisation?</label><select name="organisation" id="rvOrg" class="form-select" required></select></div>
                <div class="col-md-4"><button class="btn btn-light border w-100">Download id list</button></div>
            </form>
            <div class="small text-muted mt-3">Every review belongs to one thing: a programme, department, campus or the organisation itself. The id list gives you the id of each, so you can paste the right one into the <code>id</code> column (and set <code>level</code>, which defaults to course).</div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <div class="fw-bold mb-2">2. Upload and preview</div>
            <form method="POST" action="{{ route('admin.quality.reviews.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" class="form-control mb-2" accept=".csv,.txt" required>
                <button class="btn btn-primary">Preview (saves nothing)</button>
            </form>
            <div class="small text-muted mt-3">
                Required columns: <code>id</code> and <code>rating</code> (1 to 5; <code>4</code>, <code>4.5</code>, <code>4/5</code> or <code>4 stars</code>).
                Optional: <code>level</code>, <code>reviewer_name</code>, <code>reviewer_type</code> (student, alumni, parent, faculty, recruiter), the five sub-ratings <code>infrastructure</code>, <code>campus_life</code>, <code>academics</code>, <code>placements</code>, <code>value_for_money</code>, <code>title</code>, <code>comment</code>, <code>reviewed_on</code> (or <code>review_year</code>), <code>source</code>, <code>source_url</code>, <code>is_published</code> (yes/no), <code>notes</code>.
                A review without a <code>source</code> or link can be imported but cannot be verified until one is added.
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
                    <a class="btn btn-sm btn-light border" href="{{ route('admin.quality.review', ['kind' => 'review', 'status' => 'unverified', 'batch' => $b->import_batch]) }}">Review</a>
                    @if($b->active)<form action="{{ route('admin.quality.reviews.import.undo') }}" method="POST" class="d-inline" onsubmit="return confirm('Remove all {{ $b->active }} reviews created by this batch?{{ $b->verified ? ' ' . $b->verified . ' of them have been verified.' : '' }}')">@csrf<input type="hidden" name="batch" value="{{ $b->import_batch }}"><button class="btn btn-sm btn-light border text-danger">Undo batch</button></form>@else<span class="badge bg-secondary-subtle text-secondary">undone</span>@endif
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
    $('#rvOrg').select2({
        width: '100%', minimumInputLength: 2, placeholder: 'Type 2+ letters of the organisation name',
        ajax: { url: @json(route('admin.quality.reviews.import.organisations')), dataType: 'json', delay: 250, data: p => ({ q: p.term }), processResults: d => ({ results: d }) }
    });
</script>
@endpush
