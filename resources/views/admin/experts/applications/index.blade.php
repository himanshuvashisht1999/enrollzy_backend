@extends('admin.layouts.master')

@section('title', 'Expert Applications')

@php
    use App\Models\ExpertApplication as A;
    $badge = fn ($s) => match ($s) {
        'pending' => 'bg-warning-subtle text-warning-emphasis', 'under_review' => 'bg-info-subtle text-info-emphasis',
        'needs_changes' => 'bg-danger-subtle text-danger', 'approved' => 'bg-success-subtle text-success', default => 'bg-secondary-subtle text-secondary',
    };
    $open = collect(A::REVIEW)->sum(fn ($s) => $counts[$s] ?? 0);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Expert Applications</h4>
        <p class="text-muted mb-0">People who asked to join as an expert from the website ("Become an expert"). Check the form and documents, write to them, then approve or reject.</p>
    </div>
    <a href="{{ route('expertise-areas.index') }}" class="btn btn-light border">Expertise areas</a>
</div>

{{-- re-apply rules --}}
<div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body py-3">
    <form action="{{ route('expert-applications.settings') }}" method="POST" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-3"><label class="form-label small fw-bold mb-1">Applications per person (in all)</label>
            <input type="number" name="expert_max_applications" value="{{ $limits['max'] }}" min="1" max="20" class="form-control form-control-sm" required></div>
        <div class="col-md-3"><label class="form-label small fw-bold mb-1">Days to wait after "not approved"</label>
            <input type="number" name="expert_reapply_wait_days" value="{{ $limits['wait_days'] }}" min="0" max="365" class="form-control form-control-sm" required></div>
        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100">Save rules</button></div>
        <div class="col-md-4 small text-muted">A person can apply {{ $limits['max'] }} {{ Str::plural('time', $limits['max']) }} in all, and again {{ $limits['wait_days'] }} days after a rejection. "Allow another application" on a rejected one lets them apply at once.</div>
    </form>
</div></div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('expert-applications.index') }}" class="btn btn-sm {{ !request()->has('status') ? 'btn-primary' : 'btn-light border' }}">To review <span class="badge bg-white text-dark ms-1">{{ $open }}</span></a>
    @foreach(A::STATUSES as $k => $label)
        <a href="{{ route('expert-applications.index', ['status' => $k] + array_filter(['q' => $q])) }}" class="btn btn-sm {{ $status === $k ? 'btn-primary' : 'btn-light border' }}">{{ $label }} <span class="badge bg-white text-dark ms-1">{{ $counts[$k] ?? 0 }}</span></a>
    @endforeach
    <a href="{{ route('expert-applications.index', ['status' => ''] + array_filter(['q' => $q])) }}" class="btn btn-sm {{ request()->has('status') && !$status ? 'btn-primary' : 'btn-light border' }}">All</a>
    <form method="GET" class="ms-auto d-flex gap-2">
        @if(request()->has('status'))<input type="hidden" name="status" value="{{ $status }}">@endif
        <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Name, mobile or email">
        <button class="btn btn-sm btn-light border">Search</button>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Applicant</th><th>Expertise</th><th>Documents</th><th>Attempt</th><th>Sent</th><th>Status</th><th class="text-end pe-4"></th></tr></thead>
        <tbody>
        @forelse($apps as $a)
            <tr>
                <td class="ps-4">
                    <a href="{{ route('expert-applications.show', $a) }}" class="fw-semibold text-decoration-none">{{ $a->name }}</a>
                    <div class="small text-muted">{{ $a->mobile }} · {{ $a->email }}</div>
                    <div class="small text-muted">{{ collect([$a->designation, $a->organisation])->filter()->implode(', ') }}{{ $a->city ? ' · ' . $a->city : '' }}</div>
                </td>
                <td class="small">{{ implode(', ', array_slice($a->expertise_areas ?? [], 0, 3)) }}{{ count($a->expertise_areas ?? []) > 3 ? ' +' . (count($a->expertise_areas) - 3) : '' }}</td>
                <td class="small">{{ $a->documents_count }}@if($a->pending_docs_count)<span class="badge bg-warning-subtle text-warning-emphasis ms-1">{{ $a->pending_docs_count }} to check</span>@endif</td>
                <td class="small">{{ $a->attempt_no }} of {{ $limits['max'] }}</td>
                <td class="small text-nowrap">{{ $a->submitted_at?->format('d M Y') ?? '-' }}</td>
                <td><span class="badge {{ $badge($a->status) }}">{{ A::STATUSES[$a->status] ?? $a->status }}</span></td>
                <td class="text-end pe-4"><a href="{{ route('expert-applications.show', $a) }}" class="btn btn-sm btn-light border">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">No applications here.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $apps->links() }}</div>
@endsection
