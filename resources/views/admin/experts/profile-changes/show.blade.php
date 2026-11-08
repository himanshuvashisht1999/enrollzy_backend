@extends('admin.layouts.master')

@section('title', 'Profile change: ' . $e->name)

@php
    use App\Models\ExpertProfileChange as C;
    use App\Support\Experts\ProfileChanges as P;
    $badge = ['pending' => 'bg-warning-subtle text-warning-emphasis', 'approved' => 'bg-success-subtle text-success', 'rejected' => 'bg-danger-subtle text-danger'][$c->status] ?? 'bg-secondary-subtle text-secondary';
    $photoNow = $e->profile_photo_url ?: ($e->img ? asset($e->img) : null);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold">{{ $e->name }}: profile change</h4>
        <span class="badge {{ $badge }}">{{ C::STATUSES[$c->status] ?? $c->status }}</span>
        <span class="text-muted small ms-2">sent {{ $c->updated_at->format('d M Y, h:i A') }}@if($c->reviewed_at) · decided {{ $c->reviewed_at->format('d M Y') }}{{ $c->reviewer ? ' by ' . $c->reviewer->name : '' }}@endif</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('expert-profile-changes.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Profile changes</a>
        <a href="{{ route('experts.edit', $e) }}" class="btn btn-light border">Expert profile</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">What changes <span class="text-muted fw-normal small">(now on the profile → requested)</span></h6>
            @if($c->photo)
                <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
                    <div class="text-muted small" style="width:150px">Photo</div>
                    @if($photoNow)<img src="{{ $photoNow }}" alt="Current" width="72" height="72" class="rounded-circle border" style="object-fit:cover">@else<span class="text-muted">none</span>@endif
                    <i class="fas fa-arrow-right text-muted"></i>
                    <img src="{{ route('expert-profile-changes.photo', $c) }}" alt="Requested" width="72" height="72" class="rounded-circle border border-primary" style="object-fit:cover">
                </div>
            @endif
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th style="width:22%">Field</th><th style="width:39%">Now on the profile</th><th style="width:39%">Requested</th></tr></thead>
                <tbody>
                @forelse((array) $c->changes as $k => $new)
                    @php [$label, $kind] = P::FIELDS[$k] ?? [$k, 'text']; $now = P::live($e, $k); $moved = $c->status === 'pending' && P::show($now, $kind) !== P::show($c->previous[$k] ?? null, $kind); @endphp
                    <tr>
                        <td class="small fw-semibold">{{ $label }}</td>
                        <td class="small text-muted" style="white-space:pre-line">{{ P::show($now, $kind) }}@if($moved)<div class="text-warning small">changed by staff since the request</div>@endif</td>
                        <td class="small" style="white-space:pre-line"><span class="bg-success-subtle px-1 rounded">{{ P::show($new, $kind) }}</span></td>
                    </tr>
                @empty
                    @unless($c->photo)<tr><td colspan="3" class="text-muted small">No field changes.</td></tr>@endunless
                @endforelse
                </tbody>
            </table>
        </div></div>
    </div>

    <div class="col-lg-4">
        @if($c->status === 'pending')
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Decide</h6>
            <form action="{{ route('expert-profile-changes.approve', $c) }}" method="POST" class="mb-3">
                @csrf
                <textarea name="note" rows="2" maxlength="1000" class="form-control form-control-sm mb-2" placeholder="Note to the expert (optional)"></textarea>
                <button class="btn btn-success btn-sm w-100" data-confirm="Apply these changes to {{ $e->name }}'s public profile?"><i class="fas fa-check me-1"></i> Approve and publish</button>
            </form>
            <hr>
            <form action="{{ route('expert-profile-changes.reject', $c) }}" method="POST">
                @csrf
                <textarea name="note" rows="3" maxlength="1000" class="form-control form-control-sm mb-2" placeholder="Why not (the expert sees this, required)">{{ old('note') }}</textarea>
                <button class="btn btn-outline-danger btn-sm w-100">Reject</button>
            </form>
            <p class="small text-muted mt-3 mb-0">To accept only part of it, reject with a note saying what to change; the expert edits and sends again. Or approve and correct the rest on the expert's profile.</p>
        </div></div>
        @elseif($c->note)
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-2">Note to the expert</h6>
            <p class="small mb-0" style="white-space:pre-line">{{ $c->note }}</p>
        </div></div>
        @endif
    </div>
</div>
@endsection
