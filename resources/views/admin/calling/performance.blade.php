@extends('admin.layouts.master')

@section('title', 'Team Performance')

{{-- Team Performance (CommandCenterController::performance): scorecard, calls by hour, disposition mix, funnel, leaderboard. --}}
@php
    use App\Support\Calling\CommandCenter as CC;
    $rangeLabel = CC::RANGES[$range];
    $tgt = function ($mm, $t) {
        if (!$t) return '<span class="pill st-warn">No target set</span>';
        return '<div class="xs">' . number_format($mm['calls']) . '/' . number_format($t['calls']) . ' · ' . $mm['adm'] . '/' . $t['adm'] . '</div>'
            . '<div class="bar"><i style="width:' . min(100, CC::pct($mm['calls'], $t['calls'])) . '%"></i></div>'
            . '<div class="bar ok" style="margin-top:3px"><i style="width:' . min(100, CC::pct($mm['adm'], $t['adm'])) . '%"></i></div>';
    };
    // calls by hour (today), stacked per person
    $H = $hourly['hours']; $W = 560; $HT = 190; $bw = $W / max(1, count($H));
    $stack = array_map(fn ($i) => array_sum(array_map(fn ($s) => $s[$i], $hourly['series'])), array_keys($H));
    $max = max(5, ...($stack ?: [0]));
    $funnelMax = max(1, $funnel[0][1] ?? 1);
@endphp

@section('content')
@include('admin.calling._styles')
<div class="cc">
    @include('admin.calling._top', ['title' => 'Team Performance', 'sub' => 'Productivity, conversion and target tracking', 'rangeRoute' => 'admin.calling.performance'])

    <div class="grid g4 mb14">
        <div class="card p kpi"><div class="l">Calls · {{ $rangeLabel }}</div><div class="v">{{ number_format($tot['calls']) }}</div><div class="s">{{ number_format($tot['uniq']) }} unique leads</div></div>
        <div class="card p kpi ok"><div class="l">Connected</div><div class="v">{{ number_format($tot['conn']) }} <span class="sm muted">{{ CC::pct($tot['conn'], $tot['calls']) }}%</span></div><div class="s">{{ $tot['pos'] }} positive {{ Str::plural('call', $tot['pos']) }}</div></div>
        <div class="card p kpi vio"><div class="l">Applications</div><div class="v">{{ $tot['apps'] }}</div><div class="s">{{ number_format($tot['fups']) }} follow-ups scheduled</div></div>
        <div class="card p kpi warn"><div class="l">Admissions</div><div class="v">{{ $tot['adm'] }}</div><div class="s">{{ CC::pct($tot['adm'], $tot['uniq']) }}% of leads called</div></div>
    </div>

    <div class="card mb14">
        <div class="hd"><h3>Scorecard — {{ $rangeLabel }}</h3><div class="grow"></div><a class="btn sm" href="{{ route('admin.calling.performance', ['range' => $range, 'export' => 'csv']) }}">⬇ Export CSV</a></div>
        <div class="tbl"><table><thead><tr><th>Person</th><th class="n">Calls</th><th class="n">Unique</th><th class="n">Connected</th><th class="n">Connect %</th><th class="n">Positive</th><th class="n">Follow-ups set</th><th class="n">Apps</th><th class="n">Admissions</th><th class="n">Overdue</th><th style="min-width:180px">Month target (calls · adm)</th></tr></thead>
        <tbody>
        @foreach($rows as $r)
            @php $cp = CC::pct($r['m']['conn'], $r['m']['calls']); @endphp
            <tr><td><div class="row" style="flex-wrap:nowrap">@include('admin.calling._av', ['p' => $r['p'], 'size' => 'sm'])<div><b>{{ $r['p']['name'] }}</b><div class="xs muted">{{ $r['p']['role'] }}</div></div></div></td>
                <td class="n">{{ number_format($r['m']['calls']) }}</td><td class="n">{{ number_format($r['m']['uniq']) }}</td><td class="n">{{ number_format($r['m']['conn']) }}</td>
                <td class="n">@if($r['m']['calls'])<span class="pill {{ $cp >= 35 ? 'st-ok' : 'st-warn' }}">{{ $cp }}%</span>@else — @endif</td>
                <td class="n">{{ $r['m']['pos'] }}</td><td class="n">{{ number_format($r['m']['fups']) }}</td><td class="n">{{ $r['m']['apps'] }}</td><td class="n"><b>{{ $r['m']['adm'] }}</b></td>
                <td class="n">{{ $r['q']['overdue'] }}</td><td>{!! $tgt($r['mm'], $r['t']) !!}</td></tr>
        @endforeach
            <tr class="total"><td>Team total</td><td class="n">{{ number_format($tot['calls']) }}</td><td class="n">{{ number_format($tot['uniq']) }}</td><td class="n">{{ number_format($tot['conn']) }}</td><td class="n">{{ CC::pct($tot['conn'], $tot['calls']) }}%</td>
                <td class="n">{{ $tot['pos'] }}</td><td class="n">{{ number_format($tot['fups']) }}</td><td class="n">{{ $tot['apps'] }}</td><td class="n">{{ $tot['adm'] }}</td><td class="n">{{ $rows->sum(fn ($r) => $r['q']['overdue']) }}</td><td>{!! $tgt($totM, $teamTarget) !!}</td></tr>
        </tbody></table></div>
    </div>

    <div class="grid g2 mb14">
        <div class="card"><div class="hd"><h3>Calls by hour (today)</h3><div class="grow"></div>
            <div class="legend">@foreach($ids as $id)@php $p = $cc->person($id); @endphp<span><i style="background:{{ $p['color'] }}"></i>{{ $p['first'] }}</span>@endforeach</div></div>
            <div class="bd">
                <svg viewBox="0 0 {{ $W }} {{ $HT + 24 }}" width="100%" role="img" aria-label="Calls per hour today">
                    @foreach([0, .5, 1] as $f)@php $y = $HT - $f * $HT; @endphp<line x1="0" x2="{{ $W }}" y1="{{ $y }}" y2="{{ $y }}" stroke="#e5e9f2"/><text x="2" y="{{ max(10, $y - 3) }}" font-size="10" fill="#94a3b8">{{ round($max * $f) }}</text>@endforeach
                    @foreach($H as $i => $h)
                        @php $y = $HT; @endphp
                        @foreach($ids as $id)
                            @php $v = $hourly['series'][$id][$i]; $hh = $v / $max * $HT; $y -= $hh; @endphp
                            @if($v)<rect x="{{ $i * $bw + $bw * .2 }}" y="{{ $y }}" width="{{ $bw * .6 }}" height="{{ $hh }}" rx="3" fill="{{ $cc->person($id)['color'] }}"><title>{{ $cc->person($id)['name'] }}: {{ $v }} calls at {{ sprintf('%02d', $h) }}:00</title></rect>@endif
                        @endforeach
                        <text x="{{ $i * $bw + $bw / 2 }}" y="{{ $HT + 16 }}" font-size="10.5" text-anchor="middle" fill="#64748b">{{ sprintf('%02d', $h) }}:00</text>
                    @endforeach
                </svg>
                @if(!array_sum($stack))<div class="xs muted text-center">No calls logged yet today.</div>@endif
            </div></div>
        <div class="card"><div class="hd"><h3>Disposition mix — {{ $rangeLabel }}</h3></div><div class="bd">@include('admin.calling._donut', ['items' => $disposition])</div></div>
    </div>

    <div class="grid g2">
        <div class="card"><div class="hd"><h3>Conversion funnel (leads they own now)</h3></div>
            <div class="bd funnel">
                @foreach($funnel as [$label, $n])
                    <div class="fr"><span>{{ $label }}</span><div class="fb" style="width:{{ max(2, $n / $funnelMax * 100) }}%"></div><b class="text-end">{{ number_format($n) }}</b></div>
                @endforeach
                <p class="xs muted mb-0 mt-2">Hot = the lead quality set on the student. Application and admission = the APPLICATION DONE / ADMISSION DONE call actions.</p>
            </div></div>
        <div class="card"><div class="hd"><h3>🏆 Leaderboard — this month</h3></div>
            <div class="bd">
                @foreach($leaders as $i => $r)
                    <div class="row" style="padding:8px 0;border-bottom:1px solid var(--line);flex-wrap:nowrap"><b style="width:24px">{{ ['🥇', '🥈', '🥉'][$i] ?? $i + 1 }}</b>@include('admin.calling._av', ['p' => $r['p'], 'size' => 'sm'])
                        <div style="flex:1"><b>{{ $r['p']['name'] }}</b><div class="xs muted">{{ number_format($r['mm']['calls']) }} calls · {{ number_format($r['mm']['conn']) }} connected · {{ $r['mm']['apps'] }} applications</div></div>
                        <div style="text-align:right"><b>{{ $r['mm']['adm'] }}</b><div class="xs muted">admissions</div></div></div>
                @endforeach
                <p class="xs muted mb-0 mt-2">Ranked by admissions, then connected calls.</p>
            </div></div>
    </div>
</div>
@include('admin.calling._scripts', ['noPrompt' => true])
@endsection
