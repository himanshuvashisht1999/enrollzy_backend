@extends('admin.layouts.master')

@section('title', 'Graduate Survey')

{{-- Graduate survey responses (GraduateSurveyController::index): one row per mobile, newest submission first. --}}
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Graduate Survey</h4>
            <div class="text-muted small">Answers from the survey page <a href="{{ config('cns.frontend_url') }}/survey/graduates" target="_blank" rel="noopener">enrollzy.com/survey/graduates</a></div>
        </div>
        <div class="ms-auto">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv me-1"></i>Export {{ number_format($totals['filtered']) }} to CSV</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach([['All responses', $totals['all'], 'primary'], ['New today', $totals['today'], 'success'], ['Not contacted yet', $totals['new'], 'warning'], ['Matching filters', $totals['filtered'], 'info']] as [$l, $v, $c])
            <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid var(--bs-{{ $c }}) !important"><div class="card-body py-3">
                <div class="text-uppercase text-muted fw-semibold" style="font-size:11px;letter-spacing:.05em">{{ $l }}</div>
                <div class="fs-3 fw-bold">{{ number_format($v) }}</div>
            </div></div></div>
        @endforeach
    </div>

    <form method="GET" class="card border-0 shadow-sm mb-3"><div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-3"><label class="form-label small fw-semibold mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Name, mobile, email, course, place"></div>
            <div class="col-6 col-lg-2"><label class="form-label small fw-semibold mb-1">Graduation</label>
                <select name="stream" class="form-select form-select-sm"><option value="">All</option>@foreach($streams as $k => $v)<option value="{{ $k }}" @selected(request('stream') === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><label class="form-label small fw-semibold mb-1">Plan</label>
                <select name="aim" class="form-select form-select-sm"><option value="">All</option>@foreach($aims as $k => $v)<option value="{{ $k }}" @selected(request('aim') === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><label class="form-label small fw-semibold mb-1">Current state</label>
                <select name="current_state" class="form-select form-select-sm"><option value="">All</option>@foreach($states as $v)<option @selected(request('current_state') === $v)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-1"><label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm"><option value="">All</option>@foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-1"><label class="form-label small fw-semibold mb-1">From</label><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm"></div>
            <div class="col-6 col-lg-1"><label class="form-label small fw-semibold mb-1">To</label><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm"></div>
            <div class="col-12 d-flex flex-wrap gap-2 align-items-center mt-2">
                <select name="student" class="form-select form-select-sm" style="width:auto">
                    <option value="">Students and new people</option><option value="yes" @selected(request('student') === 'yes')>Already a student in CRM</option><option value="no" @selected(request('student') === 'no')>Not in CRM yet</option>
                </select>
                <button class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i>Apply</button>
                <a href="{{ route('admin.graduate-surveys.index') }}" class="btn btn-light btn-sm">Reset</a>
                @if($byAim->count())
                    <div class="ms-lg-auto d-flex flex-wrap gap-1">@foreach($byAim as $l => $n)<span class="badge bg-light text-dark border fw-normal">{{ $l }}: <b>{{ $n }}</b></span>@endforeach</div>
                @endif
            </div>
        </div>
    </div></form>

    <div class="card border-0 shadow-sm"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr>
                <th>Student</th><th>Graduation</th><th>Plan</th><th>Preferred course</th><th>Preferred place / focus</th><th>Status</th><th>Submitted</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    <td><div class="fw-semibold">{{ $r->name }}</div>
                        <div class="small text-muted">{{ $r->phone }}{{ $r->current_state ? ' · ' . $r->current_state : '' }}</div>
                        @if($r->customer_id)<span class="badge bg-info-subtle text-info-emphasis border fw-normal" style="font-size:10.5px">Student #{{ $r->customer_id }}</span>@endif
                    </td>
                    <td><div>{{ $r->stream_label }}</div><div class="small text-muted">{{ $r->grad_status_label }}</div></td>
                    <td>{{ $r->aim_label }}</td>
                    <td class="small">{{ $r->preferred_course ?: '—' }}</td>
                    <td class="small" style="max-width:260px">{{ $r->preferred_place ?: '' }}@if($r->preferred_place && $r->focus)<br>@endif<span class="text-muted">{{ $r->focus }}</span>@if(!$r->preferred_place && !$r->focus)—@endif</td>
                    <td><span class="badge {{ ['new' => 'bg-warning text-dark', 'contacted' => 'bg-primary', 'converted' => 'bg-success', 'not_interested' => 'bg-secondary'][$r->status] ?? 'bg-light' }}">{{ $statuses[$r->status] ?? $r->status }}</span></td>
                    <td class="small text-nowrap">{{ \Carbon\Carbon::parse($r->updated_at)->format('d M Y, h:i A') }}@if($r->submissions > 1)<div class="text-muted">{{ $r->submissions }} times</div>@endif</td>
                    <td class="text-end"><a href="{{ route('admin.graduate-surveys.show', $r->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No survey responses{{ request()->query() ? ' match these filters' : ' yet' }}.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($rows->hasPages())<div class="card-footer bg-white">{{ $rows->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</div>
@endsection
