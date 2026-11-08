@extends('admin.layouts.master')

@section('title', 'Profile Changes')

@php
    use App\Models\ExpertProfileChange as C;
    use App\Support\Experts\ProfileChanges as P;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Profile Changes</h4>
        <p class="text-muted mb-0">Experts edit their profile and add documents on the website (expert dashboard). Nothing changes on their public profile until you approve it here.</p>
    </div>
    <a href="{{ route('expert-applications.index') }}" class="btn btn-light border">Applications</a>
</div>

@if($docs->isNotEmpty())
{{-- new documents from approved experts: checked on each expert's Documents page --}}
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-1">New documents to check <span class="badge bg-warning text-dark">{{ $docs->count() }}</span></h6>
    <p class="small text-muted">Open the file, then verify or reject it on the expert's Documents page. Verified ones (not ID) can show on the public profile.</p>
    @foreach($docs as $d)
        <div class="d-flex justify-content-between align-items-center border-top py-2 flex-wrap gap-2">
            <div>
                <span class="fw-semibold">{{ $d->expert->name }}</span>
                <span class="text-muted">· {{ \App\Models\ExpertDocument::TYPES[$d->type] ?? $d->type }}: {{ $d->title }}{{ $d->issued_by ? ', ' . $d->issued_by : '' }}{{ $d->year ? ', ' . $d->year : '' }}</span>
                <div class="small text-muted">added {{ $d->created_at->format('d M Y, h:i A') }}</div>
            </div>
            <div class="text-nowrap">
                <a href="{{ route('expert-documents.file', $d) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border"><i class="fas fa-external-link-alt me-1"></i>Open</a>
                <a href="{{ route('experts.documents', $d->expert_id) }}" class="btn btn-sm btn-primary">Check</a>
            </div>
        </div>
    @endforeach
</div></div>
@endif

<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach(C::STATUSES as $k => $label)
        <a href="{{ route('expert-profile-changes.index', ['status' => $k]) }}" class="btn btn-sm {{ $status === $k ? 'btn-primary' : 'btn-light border' }}">{{ $label }} <span class="badge bg-white text-dark ms-1">{{ $counts[$k] ?? 0 }}</span></a>
    @endforeach
</div>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Expert</th><th>Changes</th><th>{{ $status === 'pending' ? 'Sent' : 'Decided' }}</th><th class="text-end pe-4"></th></tr></thead>
        <tbody>
        @forelse($changes as $c)
            @php $fields = collect(array_keys($c->changes ?? []))->map(fn ($k) => P::FIELDS[$k][0] ?? $k); if ($c->photo) $fields->prepend('Photo'); @endphp
            <tr>
                <td class="ps-4"><span class="fw-semibold">{{ $c->expert->name ?? 'Expert #' . $c->expert_id }}</span><div class="small text-muted">{{ $c->expert->contact_number ?? '' }}</div></td>
                <td class="small">{{ $fields->take(5)->implode(', ') }}{{ $fields->count() > 5 ? ' +' . ($fields->count() - 5) . ' more' : '' }}@if($fields->isEmpty())<span class="text-muted">—</span>@endif</td>
                <td class="small text-nowrap">{{ ($status === 'pending' ? $c->updated_at : ($c->reviewed_at ?? $c->updated_at))?->format('d M Y, h:i A') }}@if($c->reviewer)<div class="text-muted">{{ $c->reviewer->name }}</div>@endif</td>
                <td class="text-end pe-4"><a href="{{ route('expert-profile-changes.show', $c) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-primary' : 'btn-light border' }}">{{ $status === 'pending' ? 'Review' : 'Open' }}</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-5">Nothing here.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $changes->links() }}</div>
@endsection
