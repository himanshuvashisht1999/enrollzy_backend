{{-- Disposition mix: $items [[name, count], ...] as a donut with a legend. --}}
@php
    $total = array_sum(array_column($items, 1));
    $palette = ['#2563eb', '#16a34a', '#d97706', '#7c3aed', '#db2777', '#0891b2', '#94a3b8'];
    $r = 52; $c = 2 * M_PI * $r; $off = 0;
@endphp
@if(!$total)
    <div class="empty">No calls in this range</div>
@else
    <div class="row" style="gap:22px;align-items:center;flex-wrap:nowrap">
        <svg viewBox="0 0 140 140" width="150" height="150" role="img" aria-label="Calls by status" style="flex:none">
            <circle cx="70" cy="70" r="{{ $r }}" fill="none" stroke="#edf0f6" stroke-width="22"/>
            @foreach($items as $i => [$name, $n])
                @php $len = $c * $n / $total; @endphp
                <circle cx="70" cy="70" r="{{ $r }}" fill="none" stroke="{{ $palette[$i % count($palette)] }}" stroke-width="22" stroke-dasharray="{{ $len }} {{ $c - $len }}" stroke-dashoffset="{{ -$off }}" transform="rotate(-90 70 70)"><title>{{ $name }}: {{ $n }}</title></circle>
                @php $off += $len; @endphp
            @endforeach
            <text x="70" y="68" text-anchor="middle" font-size="22" font-weight="700" fill="#0f172a">{{ number_format($total) }}</text>
            <text x="70" y="86" text-anchor="middle" font-size="11" fill="#64748b">calls</text>
        </svg>
        <div style="flex:1;min-width:0">
            @foreach($items as $i => [$name, $n])
                <div class="row sp sm" style="padding:3px 0;flex-wrap:nowrap"><span><i style="display:inline-block;width:10px;height:10px;border-radius:3px;background:{{ $palette[$i % count($palette)] }};margin-right:6px"></i>{{ $name }}</span><span><b>{{ number_format($n) }}</b> <span class="muted">{{ \App\Support\Calling\CommandCenter::pct($n, $total) }}%</span></span></div>
            @endforeach
        </div>
    </div>
@endif
