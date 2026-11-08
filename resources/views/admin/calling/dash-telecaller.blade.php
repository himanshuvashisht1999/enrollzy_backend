@extends('admin.layouts.master')

@section('title', 'My Dashboard')

{{-- Telecaller home (CommandCenterController::telecaller): target, today, follow-ups, rank, notices, attendance, leave. --}}
@php
    use App\Support\Calling\CommandCenter as CC;
    $t = $today;
    $dp = CC::pct($t['calls'], $daily);
@endphp

@section('content')
@include('admin.calling._styles')
<div class="cc">
    @include('admin.calling._top', ['title' => 'My Dashboard', 'sub' => 'Target, attendance, follow-ups and notices'])

    @include('admin.calling._target', ['label' => 'Your target', 'compact' => false])

    <div class="grid g5 mb14">
        <div class="card p kpi"><div class="l">Calls today</div><div class="v">{{ $t['calls'] }}<span class="sm muted"> / {{ $daily }}</span></div><div class="bar {{ $dp >= 100 ? 'ok' : '' }}" style="margin-top:6px"><i style="width:{{ min(100, $dp) }}%"></i></div></div>
        <div class="card p kpi ok"><div class="l">Connected today</div><div class="v">{{ $t['conn'] }}</div><div class="s">{{ CC::pct($t['conn'], $t['calls']) }}% connect · {{ $t['uniq'] }} leads</div></div>
        <div class="card p kpi bad"><div class="l">Overdue</div><div class="v">{{ $queue['overdue'] }}</div><div class="s">follow-ups — call these first</div></div>
        <div class="card p kpi warn"><div class="l">Due today</div><div class="v">{{ $queue['today'] }}</div><div class="s">{{ $queue['fresh'] }} new {{ Str::plural('lead', $queue['fresh']) }} never called</div></div>
        <div class="card p kpi vio"><div class="l">Team rank (month)</div><div class="v">#{{ $rank }}<span class="sm muted"> of {{ $peers }}</span></div><div class="s">{{ $month['adm'] }} {{ Str::plural('admission', $month['adm']) }} · {{ $month['conn'] }} connected</div></div>
    </div>

    <div class="row mb14">
        <a class="btn pri" href="{{ route('admin.students-crm.calling-module.index') }}">🎧 Start calling</a>
        <a class="btn" href="{{ route('admin.students-crm.calling-dashboard.index') }}">⏰ Follow-ups</a>
        <a class="btn" href="{{ route('admin.hr.leaves.create') }}">🌴 Apply leave</a>
        <a class="btn" href="{{ route('admin.hr.attendance.index') }}">🕘 My attendance</a>
    </div>

    <div class="grid g2 mb14">
        @include('admin.calling._board')
        <div class="grid" style="gap:14px;align-content:start">
            <div class="card">
                <div class="hd"><h3>🕘 Attendance</h3><div class="grow"></div><a href="{{ route('admin.hr.attendance.index') }}" class="sm">Open</a></div>
                <div class="bd">
                    <div class="row sp mb14">
                        <div><div class="xs muted">Today</div><b>{{ $live['label'] }}</b>@if($live['late']) <span class="pill st-warn">Late</span>@endif
                            <div class="xs muted">{{ $live['in'] ? 'In ' . $live['in']->format('g:i A') . ($live['out'] ? ' · Out ' . $live['out']->format('g:i A') : '') : '' }}{{ $live['breaks'] ? ' · ' . $live['breaks'] . ' ' . Str::plural('break', $live['breaks']) . ' (' . CC::hm($live['break_sec']) . ')' : '' }}</div></div>
                        @include('admin.calling._punch')
                    </div>
                    <div class="mini"><div><b>{{ $att['present'] }}</b><span>Present</span></div><div><b>{{ $att['late'] }}</b><span>Late</span></div><div><b>{{ $att['leave'] }}</b><span>Leave</span></div><div><b class="{{ $att['absent'] ? 'no' : '' }}">{{ $att['absent'] }}</b><span>Absent</span></div></div>
                </div>
            </div>
            <div class="card">
                <div class="hd"><h3>🌴 Leave & holidays</h3><div class="grow"></div><a href="{{ route('admin.hr.leaves.create') }}" class="sm">Apply leave</a></div>
                <div class="bd">
                    <div class="mini mb14">
                        @foreach($leave['types'] as $lt)<div><b>{{ $lt['left'] }}</b><span>{{ $lt['name'] }} left</span></div>@endforeach
                        <div><b>{{ $leave['pending'] }}</b><span>Pending</span></div>
                    </div>
                    @forelse($holidays as $h)
                        <div class="row sp sm" style="padding:4px 0"><span>🎉 {{ $h['name'] }}</span><span class="{{ $h['in'] <= 3 ? 'no' : 'muted' }}">{{ $h['date']->format('D, j M') }} · {{ $h['in'] === 0 ? 'today' : ($h['in'] === 1 ? 'tomorrow' : 'in ' . $h['in'] . ' days') }}</span></div>
                    @empty
                        <div class="muted sm">No upcoming holidays</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="grid g2 mb14">
        <div class="card">
            <div class="hd"><h3>⭐ My important leads</h3><span class="muted sm">{{ $important->count() }}</span><div class="grow"></div><span class="xs muted">Flag a lead on the call form</span></div>
            <div class="bd">
                @forelse($important as $i)
                    <div class="row sp" style="padding:6px 0;border-bottom:1px solid var(--line)"><div><b>{{ $i->lead ?: 'Lead #' . $i->customer_id }}</b><div class="xs muted">marked {{ \Carbon\Carbon::parse($i->created_at)->diffForHumans() }}</div></div>
                        <a class="btn sm" href="{{ route('admin.students-crm.calling-dashboard.index', ['filter_name' => $i->customer_id]) }}">📞 Call</a></div>
                @empty
                    <div class="empty">No important leads yet</div>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="hd"><h3>🚨 My escalations</h3><span class="muted sm">{{ $escalated->count() }} open</span><div class="grow"></div><span class="xs muted">Closed when your team lead responds</span></div>
            <div class="bd">
                @forelse($escalated as $e)
                    <div style="padding:6px 0;border-bottom:1px solid var(--line)"><div class="row sp"><b>{{ $e->lead ?: 'Lead #' . $e->customer_id }}</b><span class="xs muted">{{ \Carbon\Carbon::parse($e->created_at)->diffForHumans() }}{{ $e->team_lead_id ? ' · with ' . $cc->person((int) $e->team_lead_id)['first'] : '' }}</span></div>
                        <div class="sm muted">{{ $e->reason }}</div></div>
                @empty
                    <div class="empty">Nothing escalated</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card">
        <div class="hd"><h3>My month so far</h3></div>
        <div class="bd">
            <div class="mini mb14"><div><b>{{ number_format($month['calls']) }}</b><span>Calls</span></div><div><b>{{ CC::pct($month['conn'], $month['calls']) }}%</b><span>Connect</span></div><div><b>{{ $month['apps'] }}</b><span>Applications</span></div><div><b>{{ $month['adm'] }}</b><span>Admissions</span></div></div>
            @include('admin.calling._donut', ['items' => $disposition])
        </div>
    </div>
</div>
@include('admin.calling._scripts')
@endsection
