@extends('admin.layouts.master')

@section('title', 'Identity review')

@php
    $legal = \App\Support\Quality\IdentityFields::LEGAL_TYPES;
    $roles = \App\Support\Quality\IdentityFields::CAMPUS_ROLES;
    $units = \App\Support\Quality\IdentityFields::UNIT_TYPES;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Identity review</h4>
        <p class="text-muted mb-0">Which legal entity is this? Rankings and approvals are matched on AISHE code and NIRF institute id, so fix these first. Each row saves as soon as you leave a field.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.quality.groups.index') }}" class="btn btn-light border">Groups</a>
        <a href="{{ route('admin.quality.moves.index') }}" class="btn btn-light border">Split a brand</a>
        <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Dashboard</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body py-3">
    <form method="POST" action="{{ route('admin.quality.identity.import') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-5"><label class="form-label fw-bold mb-1">Bulk update from CSV</label><input type="file" name="file" class="form-control" accept=".csv,.txt" required></div>
        <div class="col-md-3"><button class="btn btn-primary">Preview changes</button> <a href="{{ route('admin.quality.identity.template') }}" class="btn btn-link">Template</a></div>
        <div class="col-md-4 small text-muted">Nothing is changed until you confirm the preview. A blank cell leaves the current value alone.</div>
    </form>
</div></div>

<ul class="nav nav-pills mb-3">
    @foreach($levels as $k => $l)<li class="nav-item"><a class="nav-link {{ $level === $k ? 'active' : '' }}" href="{{ route('admin.quality.identity.index', ['level' => $k]) }}">{{ $l['label'] }}</a></li>@endforeach
</ul>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="level" value="{{ $level }}">
    <div class="col-md-3"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name"></div>
    <div class="col-md-3"><select name="missing" class="form-select" onchange="this.form.submit()"><option value="">Everything</option>
        @if($level !== 'department')<option value="aishe" @selected(request('missing') === 'aishe')>Missing AISHE code</option>@endif
        <option value="nirf" @selected(request('missing') === 'nirf')>Missing NIRF institute id</option>
        @if($level === 'organisation')<option value="legal" @selected(request('missing') === 'legal')>Missing legal entity type</option>@endif
        @if($level === 'campus')<option value="role" @selected(request('missing') === 'role')>Missing campus role</option>@endif</select></div>
    @if($level === 'organisation')
        <div class="col-md-3"><select name="type" class="form-select" onchange="this.form.submit()"><option value="">All organisation types</option>
            @foreach($orgTypes as $id => $n)<option value="{{ $id }}" @selected(request('type') == $id)>{{ $n }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="group" class="form-select" onchange="this.form.submit()"><option value="">All groups</option>
            @foreach($groups as $id => $n)<option value="{{ $id }}" @selected(request('group') == $id)>{{ $n }}</option>@endforeach</select></div>
    @endif
    <div class="col-auto"><button class="btn btn-light border">Filter</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="identityTable">
        <thead class="bg-light"><tr>
            <th class="ps-4">{{ $level === 'organisation' ? 'Organisation' : ($level === 'campus' ? 'Campus' : 'Department / School') }}</th>
            @foreach($cols as $c)<th>{{ ucfirst(str_replace(['_id', '_'], ['', ' '], $c)) }}</th>@endforeach
            <th style="width:30px"></th>
        </tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr data-level="{{ $level }}" data-id="{{ $r->id }}">
                <td class="ps-4"><a href="{{ route('admin.quality.entity', [$level, $r->id]) }}" class="fw-bold">{{ $r->{$def['name']} }}</a>
                    @if($level !== 'organisation')<div class="small text-muted">{{ $parents[$r->organisation_id] ?? '' }}</div>@endif</td>
                @foreach($cols as $c)
                    <td>
                    @if($c === 'legal_entity_type')
                        <select data-col="{{ $c }}" class="form-select form-select-sm"><option value="">-</option>@foreach($legal as $k => $l)<option value="{{ $k }}" @selected($r->$c === $k)>{{ $l }}</option>@endforeach</select>
                    @elseif($c === 'group_id')
                        <select data-col="{{ $c }}" class="form-select form-select-sm"><option value="">-</option>@foreach($groups as $k => $l)<option value="{{ $k }}" @selected($r->$c == $k)>{{ $l }}</option>@endforeach</select>
                    @elseif($c === 'campus_role')
                        <select data-col="{{ $c }}" class="form-select form-select-sm"><option value="">-</option>@foreach($roles as $k => $l)<option value="{{ $k }}" @selected($r->$c === $k)>{{ $l }}</option>@endforeach</select>
                    @elseif($c === 'academic_unit_type')
                        <select data-col="{{ $c }}" class="form-select form-select-sm">@foreach($units as $k => $l)<option value="{{ $k }}" @selected($r->$c === $k)>{{ $l }}</option>@endforeach</select>
                    @elseif($c === 'inherits_org_accreditation')
                        <input type="checkbox" data-col="{{ $c }}" class="form-check-input" @checked($r->$c)>
                    @elseif($c === 'affiliating_organisation_id')
                        <input type="number" data-col="{{ $c }}" value="{{ $r->$c }}" class="form-control form-control-sm" style="width:110px" placeholder="org id">
                    @else
                        <input type="text" data-col="{{ $c }}" value="{{ $r->$c }}" class="form-control form-control-sm" style="min-width:130px" maxlength="32">
                    @endif
                    </td>
                @endforeach
                <td class="status-cell"></td>
            </tr>
        @empty
            <tr><td colspan="{{ count($cols) + 2 }}" class="text-center text-muted py-5">Nothing matches.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $rows->links() }}</div>
@endsection

@push('scripts')
<script>
    const idBase = @json(url('admin/quality/identity'));
    const csrf = @json(csrf_token());
    document.getElementById('identityTable').addEventListener('change', async (e) => {
        const el = e.target.closest('[data-col]');
        if (!el) return;
        const tr = el.closest('tr'), cell = tr.querySelector('.status-cell');
        const body = {}; body[el.dataset.col] = el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value;
        el.classList.remove('is-invalid'); cell.innerHTML = '<span class="spinner-border spinner-border-sm text-muted"></span>';
        try {
            const res = await fetch(`${idBase}/${tr.dataset.level}/${tr.dataset.id}`, {
                method: 'PUT', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify(body)
            });
            if (res.ok) { cell.innerHTML = '<i class="fas fa-check text-success"></i>'; return; }
            const j = await res.json().catch(() => ({}));
            el.classList.add('is-invalid');
            const msg = j.errors ? Object.values(j.errors).flat().join(' ') : (j.message || 'Could not save');
            cell.innerHTML = '<i class="fas fa-exclamation-circle text-danger" title="' + msg.replace(/"/g, '&quot;') + '"></i>';
            if (window.toastr) toastr.error(msg);
        } catch (err) { cell.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i>'; }
    });
</script>
@endpush
