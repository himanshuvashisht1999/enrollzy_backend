@extends('admin.layouts.master')

@section('title', 'My Dashboard')

{{-- Team Lead home (CommandCenterController::teamLead): team target, approvals, follow-ups, team attendance, notices. --}}
@php
    use App\Support\Calling\CommandCenter as CC;
    $mine = $today[$me->id];
    $statusLabel = ['calling' => 'Calling', 'idle' => 'Idle', 'break' => 'On break', 'offline' => 'Offline'];
    $codeClr = ['W' => '#16a34a', 'OUT' => '#64748b', 'NP' => '#94a3b8', 'L' => '#7c3aed', 'WO' => '#2563eb'];
    $team = array_values(array_diff($ids, [$me->id]));
@endphp

@section('content')
@include('admin.calling._styles')
<div class="cc">
    @include('admin.calling._top', ['title' => 'My Dashboard', 'sub' => 'Team target, attendance, approvals and notices'])

    @include('admin.calling._target', ['label' => 'Team target', 'compact' => true, 'team' => true])

    <div class="grid g6 mb14">
        <div class="card p kpi"><div class="l">Team calls today</div><div class="v">{{ $teamToday['calls'] }}</div><div class="s">{{ $teamToday['conn'] }} connected · {{ CC::pct($teamToday['conn'], $teamToday['calls']) }}%</div></div>
        <a class="card p kpi {{ $escalations->count() ? 'bad' : 'ok' }}" href="#escalations" style="text-decoration:none;color:inherit"><div class="l">Escalations</div><div class="v">{{ $escalations->count() }}</div><div class="s">{{ $escalations->count() ? 'waiting for you' : 'nothing waiting' }}</div></a>
        <div class="card p kpi warn"><div class="l">Approvals</div><div class="v">{{ $pending }}</div><div class="s">leave {{ Str::plural('request', $pending) }} waiting</div></div>
        <div class="card p kpi bad"><div class="l">Overdue follow-ups</div><div class="v">{{ number_format($overdueTeam) }}</div><div class="s">across your team</div></div>
        <div class="card p kpi"><div class="l">My calls today</div><div class="v">{{ $mine['calls'] }}<span class="sm muted"> / {{ $daily }}</span></div><div class="s">{{ $mine['conn'] }} connected</div></div>
        <div class="card p kpi ok"><div class="l">Pool to allocate</div><div class="v">{{ number_format($queues[$me->id]['fresh']) }}</div><div class="s">leads with you, never called</div></div>
    </div>

    <div class="row mb14">
        <a class="btn pri" href="{{ route('admin.calling.monitor') }}">📡 Live monitor</a>
        <a class="btn" href="{{ route('admin.hr.leaves.index') }}">✅ Approvals ({{ $pending }})</a>
        <a class="btn" href="{{ route('admin.students-crm.lead-assign.index') }}">🔀 Allocate leads</a>
        <a class="btn" href="{{ route('admin.calling.performance') }}">📊 Team performance</a>
        <a class="btn" href="{{ route('admin.students-crm.calling-module.index') }}">🎧 Calling module</a>
    </div>

    <div class="grid g2 mb14">
        <div class="card">
            <div class="hd"><h3>Team attendance today</h3><div class="grow"></div><a href="{{ route('admin.hr.attendance.index') }}" class="sm">Report</a></div>
            <div class="tbl"><table><thead><tr><th>Member</th><th>Status</th><th>In</th><th>Out</th><th>Live</th></tr></thead><tbody>
            @foreach($ids as $id)
                @php $p = $cc->person($id); $l = $live[$id]; @endphp
                <tr><td><div class="row" style="flex-wrap:nowrap">@include('admin.calling._av', ['p' => $p, 'size' => 'sm']){{ $p['name'] }}@if($id === $me->id) <span class="xs muted">(you)</span>@endif</div></td>
                    <td><span style="color:{{ $codeClr[$l['code']] ?? '#64748b' }};font-weight:700">{{ $l['label'] }}</span>@if($l['late']) <span class="pill st-warn">Late</span>@endif</td>
                    <td>{{ $l['in']?->format('g:i A') ?? '—' }}</td><td>{{ $l['out']?->format('g:i A') ?? '—' }}</td>
                    <td><span class="pill st-{{ $l['status'] }} {{ $l['status'] === 'calling' ? 'blink' : '' }}"><span class="dot"></span>{{ $statusLabel[$l['status']] }}</span></td></tr>
            @endforeach
            </tbody></table></div>
        </div>
        <div class="card">
            <div class="hd"><h3>⏰ Follow-ups by member</h3><div class="grow"></div><a href="{{ route('admin.students-crm.calling-dashboard.index') }}" class="sm">Calling dashboard</a></div>
            <div class="bd">
                @forelse(collect($team)->sortByDesc(fn ($id) => $queues[$id]['overdue']) as $id)
                    @php $p = $cc->person($id); $q = $queues[$id]; $over = $q['overdue'] > (int) $cc->rules()->calling_overdue_max; @endphp
                    <div class="row sp" style="padding:7px 0;border-bottom:1px solid var(--line)">
                        <div class="row" style="flex-wrap:nowrap">@include('admin.calling._av', ['p' => $p, 'size' => 'sm'])<div><b>{{ $p['name'] }}</b><div class="xs muted">{{ $q['owned'] }} leads · {{ $q['fresh'] }} never called · {{ $q['today'] }} due today</div></div></div>
                        <span class="pill {{ $over ? 'st-bad' : ($q['overdue'] ? 'st-warn' : 'st-ok') }}">{{ $q['overdue'] }} overdue</span>
                    </div>
                @empty
                    <div class="empty">No one reports to you yet</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid g2 mb14">
        <div class="card" id="escalations">
            <div class="hd"><h3>🚨 Escalations</h3><span class="muted sm">{{ $escalations->count() }} open</span><div class="grow"></div><span class="xs muted">Call from your phone, then submit a comment</span></div>
            <div class="bd">
                @forelse($escalations as $e)
                    @php $p = $cc->person((int) $e->raised_by); $age = \Carbon\Carbon::parse($e->created_at); $num = $e->phone ?: $e->mobile; @endphp
                    <div style="padding:9px 0;border-bottom:1px solid var(--line)">
                        <div class="row sp">
                            <div class="row" style="flex-wrap:nowrap">@include('admin.calling._av', ['p' => $p, 'size' => 'sm'])
                                <div><b>{{ $e->lead ?: 'Lead #' . $e->customer_id }}</b><div class="xs muted">from {{ $p['name'] }} · {{ $age->diffForHumans() }}</div></div></div>
                            <div class="row">
                                <span class="pill {{ $age->lt(now()->subDay()) ? 'st-bad' : 'st-warn' }}">{{ $age->lt(now()->subDay()) ? 'Over a day' : 'Today' }}</span>
                                {{-- calls are dialled by hand: the eye shows the lead's number --}}
                                @if($num)
                                    <span class="esc-num sm" data-num="{{ $num }}" style="font-weight:600;letter-spacing:.5px">••••••{{ substr($num, -2) }}</span>
                                    <button type="button" class="btn sm esc-eye" title="Show number" aria-label="Show number"><i class="fas fa-eye"></i></button>
                                @else
                                    <span class="xs muted">No number</span>
                                @endif
                            </div>
                        </div>
                        <div class="sm" style="margin:6px 0 0 36px;white-space:pre-wrap">{{ $e->reason }}</div>
                        <details style="margin:6px 0 0 36px"><summary class="sm" style="cursor:pointer;color:var(--pri)">Submit comment</summary>
                            <form method="POST" action="{{ route('admin.calling.escalations.resolve', $e->id) }}" class="row" style="margin-top:6px">@csrf
                                <input type="text" name="resolution" class="form-control form-control-sm" style="flex:1;min-width:180px" maxlength="500" placeholder="What was discussed with the lead" required>
                                <button class="btn sm ok">Submit</button>
                            </form>
                        </details>
                    </div>
                @empty
                    <div class="empty">No escalations waiting. Your team can escalate a lead from the call form.</div>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="hd"><h3>⭐ Important leads</h3><span class="muted sm">{{ $important->count() }} {{ $important->count() >= 15 ? 'latest' : '' }}</span><div class="grow"></div><span class="xs muted">Flagged on the call form</span></div>
            <div class="tbl"><table><thead><tr><th>Lead</th><th>With</th><th>Marked</th><th></th></tr></thead><tbody>
            @forelse($important as $i)
                @php $o = $cc->person((int) $i->owner); @endphp
                <tr><td><b>{{ $i->lead ?: 'Lead #' . $i->customer_id }}</b></td>
                    <td><div class="row" style="flex-wrap:nowrap">@include('admin.calling._av', ['p' => $o, 'size' => 'sm']){{ $o['first'] }}</div></td>
                    <td class="sm muted">{{ \Carbon\Carbon::parse($i->created_at)->diffForHumans() }} by {{ $cc->person((int) $i->marked_by)['first'] }}</td>
                    <td class="n"><a class="btn sm" href="{{ route('admin.students-crm.calling-dashboard.index', ['filter_name' => $i->customer_id, 'staff_id' => 'all']) }}">📞 Call</a></td></tr>
            @empty
                <tr><td colspan="4"><div class="empty">No important leads in your team</div></td></tr>
            @endforelse
            </tbody></table></div>
        </div>
    </div>

    <div class="grid g2">
        @include('admin.calling._board')
        <div class="card">
            <div class="hd"><h3>🕘 My attendance</h3><div class="grow"></div><a href="{{ route('admin.hr.attendance.index') }}" class="sm">Open</a></div>
            <div class="bd">
                @php $l = $live[$me->id]; @endphp
                <div class="row sp mb14">
                    <div><div class="xs muted">Today</div><b>{{ $l['label'] }}</b>@if($l['late']) <span class="pill st-warn">Late</span>@endif
                        <div class="xs muted">{{ $l['in'] ? 'In ' . $l['in']->format('g:i A') . ($l['out'] ? ' · Out ' . $l['out']->format('g:i A') : '') : '' }}</div></div>
                    @include('admin.calling._punch')
                </div>
                <div class="mini"><div><b>{{ $att['present'] }}</b><span>Present</span></div><div><b>{{ $att['late'] }}</b><span>Late</span></div><div><b>{{ $att['leave'] }}</b><span>Leave</span></div><div><b class="{{ $att['absent'] ? 'no' : '' }}">{{ $att['absent'] }}</b><span>Absent</span></div></div>
            </div>
        </div>
    </div>
</div>
<script>
// escalations: the eye shows / hides the lead's number
document.querySelectorAll('#escalations .esc-eye').forEach(function (b) {
    b.addEventListener('click', function () {
        var n = b.previousElementSibling, shown = b.classList.toggle('on');
        n.textContent = shown ? n.dataset.num : '••••••' + n.dataset.num.slice(-2);
        b.querySelector('i').className = shown ? 'fas fa-eye-slash' : 'fas fa-eye';
        b.title = shown ? 'Hide number' : 'Show number';
    });
});
</script>
@include('admin.calling._scripts', ['refresh' => true])
@endsection
