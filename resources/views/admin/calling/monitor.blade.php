@extends('admin.layouts.master')

@section('title', 'Live Monitor')

{{-- Live Monitor (CommandCenterController::monitor): admin home, also for team leads (their team). Live status is
     derived from the CRM: punched in or not, an open break, or how long since the last logged call. --}}
@php
    use App\Support\Calling\CommandCenter as CC;
    $cnt = fn ($s) => collect($live)->where('status', $s)->count();
    $statusLabel = ['calling' => 'Calling', 'idle' => 'Idle', 'break' => 'On break', 'offline' => 'Offline'];
    $codes = collect($live)->pluck('code');
    $rangeLabel = CC::RANGES[$range];
@endphp

@section('content')
@include('admin.calling._styles')
<div class="cc">
    @include('admin.calling._top', ['title' => 'Live Monitor', 'sub' => 'Real-time status, follow-ups and alerts' . ($role === 'teamlead' ? ' for your team' : ''), 'rangeRoute' => 'admin.calling.monitor'])

    <div class="grid g6 mb14">
        <div class="card p kpi ok"><div class="l">Calling now</div><div class="v">{{ $cnt('calling') }}</div><div class="s">{{ $cnt('idle') }} idle (no call in {{ $rules->calling_idle_min }} min)</div></div>
        <div class="card p kpi"><div class="l">On break · offline</div><div class="v">{{ $cnt('break') }} · {{ $cnt('offline') }}</div><div class="s">of {{ count($ids) }} in the team</div></div>
        <div class="card p kpi vio"><div class="l">Calls · {{ $rangeLabel }}</div><div class="v">{{ number_format($tot['calls']) }}</div><div class="s">{{ number_format($tot['conn']) }} connected</div></div>
        <div class="card p kpi {{ CC::pct($tot['conn'], $tot['calls']) < 35 && $tot['calls'] ? 'warn' : 'ok' }}"><div class="l">Connect rate</div><div class="v">{{ CC::pct($tot['conn'], $tot['calls']) }}%</div><div class="s">{{ $tot['pos'] }} positive {{ Str::plural('call', $tot['pos']) }}</div></div>
        <div class="card p kpi"><div class="l">Unique leads</div><div class="v">{{ number_format($tot['uniq']) }}</div><div class="s">{{ number_format($tot['fups']) }} follow-ups scheduled</div></div>
        @php $od = array_sum(array_column($queues, 'overdue')); @endphp
        <div class="card p kpi {{ $od > 30 ? 'bad' : 'warn' }}"><div class="l">Overdue follow-ups</div><div class="v">{{ number_format($od) }}</div><div class="s">{{ $tot['adm'] }} {{ Str::plural('admission', $tot['adm']) }} {{ strtolower($rangeLabel) }}</div></div>
    </div>

    @include('admin.calling._target', ['label' => 'Team target', 'compact' => true, 'team' => true])

    <div class="card p mb14"><div class="row" style="gap:18px"><b>🕘 Attendance today</b>
        <span class="sm">Present <b class="yes">{{ $codes->filter(fn ($c) => in_array($c, ['W', 'OUT']))->count() }}</b>/{{ count($ids) }}</span>
        <span class="sm">Late <b class="part">{{ collect($live)->where('late', true)->count() }}</b></span>
        <span class="sm">On leave <b>{{ $codes->filter(fn ($c) => $c === 'L')->count() }}</b></span>
        <span class="sm">Not punched in <b class="{{ $codes->filter(fn ($c) => $c === 'NP')->count() ? 'no' : '' }}">{{ $codes->filter(fn ($c) => $c === 'NP')->count() }}</b></span>
        <div class="grow"></div><span class="sm">Pending approvals <b>{{ $pending }}</b></span>
        <a class="btn sm" href="{{ route('admin.hr.attendance.index') }}">Attendance</a><a class="btn sm" href="{{ route('admin.hr.leaves.index') }}">Approvals</a>
        @if($role === 'admin')<button type="button" class="btn sm" data-bs-toggle="modal" data-bs-target="#ccRules">⚙ Rules</button>@endif
    </div></div>

    <div class="grid g3 mb14">
        @foreach($ids as $id)
            @php
                $p = $cc->person($id); $l = $live[$id]; $mm = $m[$id]; $td = $today[$id]; $dt = $daily[$id]; $pc = CC::pct($td['calls'], $dt);
                $self = $id === $me->id;
            @endphp
            <div class="card agent">
                <div class="head">@include('admin.calling._av', ['p' => $p])
                    <div style="flex:1;min-width:0"><div class="nm">{{ $p['name'] }}@if($self) <span class="xs muted">(you)</span>@endif</div>
                        <div class="muted xs">{{ $p['role'] }} · {{ $l['in'] ? 'In ' . $l['in']->format('g:i A') : 'not punched in' }}@if($l['late']) · <span class="part">late</span>@endif</div></div>
                    <div style="text-align:right"><span class="pill st-{{ $l['status'] }} {{ $l['status'] === 'calling' ? 'blink' : '' }}"><span class="dot"></span>{{ $statusLabel[$l['status']] }}</span>
                        <div class="xs muted" style="margin-top:3px">{{ $l['status'] !== 'offline' && $l['since'] ? CC::hm($l['since']->diffInSeconds(now())) : '' }}</div></div>
                </div>
                <div class="cur">
                    @if($l['status'] === 'offline')<div class="muted sm">⚪ {{ $l['offline_reason'] }}</div>
                    @elseif($l['status'] === 'break')<div class="muted sm">☕ {{ ucfirst($l['break']['type'] ?? '') }} break since {{ $l['break']['since']->format('g:i A') }} — {{ $l['breaks'] }} {{ Str::plural('break', $l['breaks']) }} today, {{ CC::hm($l['break_sec']) }} in all</div>
                    @elseif($l['last'])<div class="row sp"><b>{{ $l['last']['lead'] }}</b><span class="xs muted">{{ $l['last']['at']->format('g:i A') }}</span></div><div class="xs muted">Last call · {{ $l['last']['status'] }} · {{ $l['last']['at']->diffForHumans() }}</div>
                    @else<div class="muted sm">No call logged yet today · {{ $queues[$id]['fresh'] }} new and {{ $queues[$id]['today'] }} due leads waiting</div>@endif
                </div>
                <div class="mini"><div><b>{{ $mm['calls'] }}</b><span>Calls</span></div><div><b>{{ CC::pct($mm['conn'], $mm['calls']) }}%</b><span>Connect</span></div><div><b>{{ $mm['uniq'] }}</b><span>Leads</span></div><div><b>{{ $mm['adm'] }}</b><span>Admissions</span></div></div>
                <div><div class="row sp xs muted"><span>Today {{ $td['calls'] }}/{{ $dt }}</span><span>{{ $queues[$id]['overdue'] }} overdue · break {{ CC::hm($l['break_sec']) }}</span></div>
                    <div class="bar {{ $pc >= 100 ? 'ok' : ($pc < 40 ? 'warn' : '') }}"><i style="width:{{ min(100, $pc) }}%"></i></div></div>
                <div class="row"><a class="btn sm" href="{{ route('admin.students-crm.calling-history.index', ['staff_id' => $id]) }}">🗂 Calls</a>
                    <a class="btn sm" href="{{ route('admin.students-crm.calling-dashboard.index', ['staff_id' => $id]) }}">⏰ Follow-ups</a></div>
            </div>
        @endforeach
    </div>

    <div class="grid g2">
        <div class="card"><div class="hd"><h3>⚠️ Alerts & exceptions</h3><span class="muted sm">{{ count($alerts) }} active</span></div>
            <div class="bd">
                @forelse($alerts as [$lvl, $text, $link])
                    <div class="alert {{ $lvl }}"><div style="flex:1">{{ $text }}</div>@if($link)<a class="btn sm" href="{{ $link }}">Open</a>@endif</div>
                @empty
                    <div class="empty">All clear ✅</div>
                @endforelse
            </div></div>
        <div class="card"><div class="hd"><h3>🟢 Live activity</h3><span class="pill st-calling blink"><span class="dot"></span>Today</span><div class="grow"></div><span class="xs muted">refreshes every minute</span></div>
            <div class="bd feed">
                @forelse($feed as $f)
                    @php $fp = $cc->person($f['id']); @endphp
                    <div class="it"><span class="t">{{ $f['t']->format('g:i A') }}</span>@include('admin.calling._av', ['p' => $fp, 'size' => 'sm'])<div><b>{{ $fp['name'] }}</b> {!! $f['html'] !!}</div></div>
                @empty
                    <div class="empty">No activity yet today</div>
                @endforelse
            </div></div>
    </div>
</div>

@if($role === 'admin')
<div class="modal fade" id="ccRules" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4">
    <form method="POST" action="{{ route('admin.calling.rules') }}">@csrf
        <div class="modal-header border-0"><h5 class="modal-title fw-bold">Monitor rules</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            @if($errors->any())<div class="alert alert-danger small py-2">{{ $errors->first() }}</div>@endif
            <div class="row g-3">
                <div class="col-6"><label class="form-label small fw-semibold">Shift starts</label><input type="time" name="calling_shift_start" class="form-control" value="{{ old('calling_shift_start', $rules->calling_shift_start) }}" required></div>
                <div class="col-6"><label class="form-label small fw-semibold">Late after (minutes of grace)</label><input type="number" name="calling_grace_min" min="0" max="240" class="form-control" value="{{ old('calling_grace_min', $rules->calling_grace_min) }}" required></div>
                <div class="col-6"><label class="form-label small fw-semibold">Idle after (minutes with no call)</label><input type="number" name="calling_idle_min" min="5" max="480" class="form-control" value="{{ old('calling_idle_min', $rules->calling_idle_min) }}" required></div>
                <div class="col-6"><label class="form-label small fw-semibold">Alert when a break passes (minutes)</label><input type="number" name="calling_break_min" min="5" max="480" class="form-control" value="{{ old('calling_break_min', $rules->calling_break_min) }}" required></div>
                <div class="col-6"><label class="form-label small fw-semibold">Overdue follow-ups limit</label><input type="number" name="calling_overdue_max" min="0" max="1000" class="form-control" value="{{ old('calling_overdue_max', $rules->calling_overdue_max) }}" required></div>
                <div class="col-6"><label class="form-label small fw-semibold">Daily calls when no target is set</label><input type="number" name="calling_daily_target" min="1" max="1000" class="form-control" value="{{ old('calling_daily_target', $rules->calling_daily_target) }}" required></div>
            </div>
            <p class="small text-muted mt-3 mb-0">Which call statuses count as "connected" is set on Calling Status.</p>
        </div>
        <div class="modal-footer border-0"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save rules</button></div>
    </form>
</div></div></div>
@if($errors->any())@push('scripts')<script>bootstrap.Modal.getOrCreateInstance(document.getElementById('ccRules')).show();</script>@endpush @endif
@endif
@include('admin.calling._scripts', ['refresh' => true])
@endsection
