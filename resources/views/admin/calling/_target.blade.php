{{-- A month's target card. $progress from CommandCenter::progress, $label, $compact (calls and admissions only), $team. --}}
@php
    $t = $progress['t'];
    $pct = \App\Support\Calling\CommandCenter::class;
@endphp
@if(!$t)
    <div class="alert warn mb14"><div style="flex:1"><b>🎯 {{ $label }} for {{ $progress['month'] }} not set yet.</b><br>
        <span class="sm">{{ !empty($team) ? 'Set each person\'s target on Assign Admission Target; the team target is their total.' : 'Your manager sets it on Assign Admission Target. Calls still count toward the month.' }} ({{ number_format($progress['done']['calls']) }} calls so far)</span></div>
        @if(!empty($team))<a class="btn sm" href="{{ route('admin.target-leads.index') }}">Set targets</a>@endif
    </div>
@else
    @php $cp = $pct::pct($progress['done']['calls'], $t['calls']); $ap = $pct::pct($progress['done']['adm'], $t['adm']); @endphp
    <div class="card target mb14">
        <div class="hd" style="border-bottom-color:#dbe6ff"><h3>🎯 {{ $label }} — {{ $progress['month'] }}</h3>
            <span class="pill {{ $progress['on_track'] ? 'st-ok' : 'st-warn' }}">{{ $progress['on_track'] ? 'On track' : 'Behind by ' . number_format($progress['behind']) . ' calls' }}</span>
            <div class="grow"></div>@if(!empty($team))<span class="xs muted">Total of {{ $t['people'] }} {{ Str::plural('person', $t['people']) }}' targets</span>@endif</div>
        <div class="bd"><div class="grid {{ !empty($compact) ? 'g2' : 'g4' }}">
            <div><div class="xs muted">Calls</div><div class="big">{{ number_format($progress['done']['calls']) }}<span class="sm muted"> / {{ number_format($t['calls']) }}</span></div>
                <div class="bar lg"><i style="width:{{ min(100, $cp) }}%"></i></div><div class="xs muted">{{ $cp }}% · {{ number_format($progress['calls_left']) }} to go</div></div>
            <div><div class="xs muted">Admissions</div><div class="big">{{ $progress['done']['adm'] }}<span class="sm muted"> / {{ $t['adm'] }}</span></div>
                <div class="bar lg ok"><i style="width:{{ min(100, $ap) }}%"></i></div><div class="xs muted">{{ $ap }}% · {{ $progress['adm_left'] }} to go</div></div>
            @if(empty($compact))
                <div><div class="xs muted">Required today</div><div class="big">{{ $progress['daily'] }}</div><div class="xs muted">calls a day to finish on time ({{ $progress['left'] }} working days left; Sundays and holidays excluded)</div></div>
                <div><div class="xs muted">Expected by today</div><div class="big">{{ number_format($progress['expected']) }}</div><div class="xs muted">pro-rata of {{ number_format($t['calls']) }} over {{ $progress['total'] }} working days</div></div>
            @endif
        </div></div>
    </div>
@endif
