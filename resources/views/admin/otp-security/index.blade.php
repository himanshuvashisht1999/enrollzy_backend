{{-- OTP security (Admin\OtpSecurityController): the website's sign-in OTP requests, IPs that look like spam, numbers being
     flooded, and IPs restricted for good. Limits on the website: 2 OTPs a minute then a 120-second pause, 10 in 24 hours
     then none for 24 hours (per number; IPs are only watched and blocked by hand). --}}
@extends('admin.layouts.master')

@section('title', 'OTP Security')

@section('content')
@php
    $out = ['sent' => ['Sent', 'bg-success'], 'widget' => ['Sent (MSG91 widget)', 'bg-success'], 'paused' => ['Refused: 120-second pause', 'bg-warning text-dark'],
        'daily' => ['Refused: 24-hour limit', 'bg-danger'], 'blocked' => ['Refused: IP blocked', 'bg-dark']];
@endphp
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 text-dark fw-bold">OTP Security</h4>
            <div class="text-muted small">Website sign-in OTPs. Limits per mobile number: 2 within a minute, then a 120-second pause; 10 in 24 hours, then none for 24 hours. An IP has no limit of its own (a school or college network shares one): watch the suspicious list and block it here. The log keeps the last 30 days; older requests are deleted automatically (blocked IPs stay blocked).</div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{!! implode('<br>', array_map('e', $errors->all())) !!}</div>@endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">OTPs sent, last 24 h</div><div class="fs-3 fw-bold">{{ (int) $today->sent }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Requests refused, last 24 h</div><div class="fs-3 fw-bold text-danger">{{ (int) $today->refused }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Suspicious IPs</div><div class="fs-3 fw-bold text-warning">{{ $suspicious->count() }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Blocked permanently</div><div class="fs-3 fw-bold">{{ $blocked->count() }}</div></div></div></div>
    </div>

    {{-- suspicious --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold"><i class="fas fa-exclamation-triangle text-warning me-1"></i> Suspicious IPs <span class="text-muted fw-normal small">(not blocked yet)</span></div>
        <div class="card-body p-0">
            @if($suspicious->isEmpty())
                <p class="text-muted small p-3 mb-0">Nothing suspicious. An IP shows here when it hits the 24-hour limit on 3+ days, asks OTPs for 5+ numbers in a day, keeps trying 20+ times while restricted, or requests OTPs on 5 of the last 7 days.</p>
            @else
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light text-muted text-uppercase"><tr><th class="ps-3">IP</th><th>Why</th><th class="text-end">Sent 24 h / 7 d</th><th class="text-end">Refused 7 d</th><th>Last seen</th><th class="text-end pe-3">Action</th></tr></thead>
                    <tbody>
                    @foreach($suspicious as $r)
                        <tr>
                            <td class="ps-3 font-monospace"><a href="{{ route('admin.otp-security.index', ['ip' => $r->ip]) }}#log">{{ $r->ip }}</a></td>
                            <td>@foreach($r->flags as $f)<div class="text-danger">• {{ $f }}</div>@endforeach</td>
                            <td class="text-end">{{ (int) $r->sent_24h }} / {{ (int) $r->sent_7d }}</td>
                            <td class="text-end">{{ (int) $r->refused_7d }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->last_seen)->diffForHumans() }}</td>
                            <td class="text-end pe-3">
                                <form action="{{ route('admin.otp-security.block') }}" method="POST" class="d-inline" onsubmit="return confirm('Restrict {{ $r->ip }} permanently? It will see only the restriction notice on the website.');">
                                    @csrf<input type="hidden" name="ip" value="{{ $r->ip }}"><input type="hidden" name="reason" value="{{ implode('; ', $r->flags) }}">
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-ban me-1"></i> Block permanently</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- blocked --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold"><i class="fas fa-ban me-1"></i> Blocked permanently</div>
                <div class="card-body">
                    <form action="{{ route('admin.otp-security.block') }}" method="POST" class="row g-2 mb-3">
                        @csrf
                        <div class="col-md-4"><input name="ip" class="form-control form-control-sm" placeholder="IP address" required></div>
                        <div class="col-md-5"><input name="reason" class="form-control form-control-sm" placeholder="Reason (optional)" maxlength="500"></div>
                        <div class="col-md-3"><button class="btn btn-sm btn-danger w-100" onclick="return confirm('Restrict this IP permanently?')">Block IP</button></div>
                    </form>
                    @if($blocked->isEmpty())
                        <p class="text-muted small mb-0">No IP is blocked.</p>
                    @else
                        <div class="table-responsive"><table class="table table-sm align-middle mb-0 small">
                            <thead class="text-muted"><tr><th>IP</th><th>Reason</th><th>Blocked</th><th></th></tr></thead>
                            <tbody>
                            @foreach($blocked as $b)
                                <tr>
                                    <td class="font-monospace"><a href="{{ route('admin.otp-security.index', ['ip' => $b->ip]) }}#log">{{ $b->ip }}</a></td>
                                    <td>{{ $b->reason ?: '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($b->created_at)->format('d M Y, h:i A') }}@if($b->admin_name)<div class="text-muted">by {{ $b->admin_name }}</div>@endif</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.otp-security.unblock', $b->id) }}" method="POST" onsubmit="return confirm('Unblock {{ $b->ip }}?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary">Unblock</button></form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table></div>
                    @endif
                </div>
            </div>
        </div>
        {{-- flooded numbers --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold"><i class="fas fa-mobile-alt me-1"></i> Numbers getting many OTPs <span class="text-muted fw-normal small">(24 h)</span></div>
                <div class="card-body">
                    <p class="text-muted small">10+ requests, or requests from 3+ IPs: someone may be flooding this person's phone. The number's own limit stops the SMS either way.</p>
                    @forelse($targets as $t)
                        <div class="d-flex justify-content-between border-bottom py-2 small"><span class="font-monospace">+91 {{ $t->mobile }}</span><span>{{ $t->n }} requests · {{ $t->ips }} {{ \Illuminate\Support\Str::plural('IP', $t->ips) }}</span></div>
                    @empty
                        <p class="text-muted small mb-0">None.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- every IP, last 30 days --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">All IPs, last 30 days</div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0 small">
            <thead class="bg-light text-muted text-uppercase"><tr><th class="ps-3">IP</th><th class="text-end">Sent 24 h</th><th class="text-end">Sent 7 d</th><th class="text-end">Refused 7 d</th><th class="text-end">Days at limit</th><th class="text-end">Numbers 24 h</th><th>First / last seen</th><th class="pe-3">Status</th></tr></thead>
            <tbody>
            @forelse($ips as $r)
                <tr>
                    <td class="ps-3 font-monospace"><a href="{{ route('admin.otp-security.index', ['ip' => $r->ip]) }}#log">{{ $r->ip }}</a></td>
                    <td class="text-end">{{ (int) $r->sent_24h }}</td><td class="text-end">{{ (int) $r->sent_7d }}</td>
                    <td class="text-end">{{ (int) $r->refused_7d }}</td><td class="text-end">{{ (int) $r->daily_days }}</td><td class="text-end">{{ (int) $r->mobiles_24h }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->first_seen)->format('d M') }} – {{ \Carbon\Carbon::parse($r->last_seen)->format('d M, h:i A') }}</td>
                    <td class="pe-3">@if($r->blocked)<span class="badge bg-dark">Blocked</span>@elseif($r->flags)<span class="badge bg-warning text-dark">Suspicious</span>@else<span class="text-muted">—</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No OTP requests yet.</td></tr>
            @endforelse
            </tbody>
        </table></div></div>
    </div>

    {{-- one IP's requests --}}
    @if($log !== null)
        <div class="card border-0 shadow-sm mb-4" id="log">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>Requests from <span class="font-monospace">{{ $ip }}</span> <span class="text-muted fw-normal small">(latest 200)</span></span>
                <a href="{{ route('admin.otp-security.index') }}" class="btn btn-sm btn-light border">Close</a>
            </div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-sm align-middle mb-0 small">
                <thead class="bg-light text-muted"><tr><th class="ps-3">When</th><th>Number</th><th>Result</th><th>Page</th><th class="pe-3">Browser</th></tr></thead>
                <tbody>
                @forelse($log as $l)
                    <tr>
                        <td class="ps-3">{{ \Carbon\Carbon::parse($l->created_at)->format('d M Y, h:i:s A') }}</td>
                        <td class="font-monospace">{{ $l->mobile ? '+91 ' . $l->mobile : '—' }}</td>
                        <td><span class="badge {{ $out[$l->outcome][1] ?? 'bg-secondary' }}">{{ $out[$l->outcome][0] ?? $l->outcome }}</span></td>
                        <td class="text-truncate" style="max-width: 220px" title="{{ $l->page }}">{{ $l->page ? preg_replace('~^https?://[^/]+~', '', $l->page) ?: '/' : '—' }}</td>
                        <td class="pe-3 text-truncate" style="max-width: 260px" title="{{ $l->user_agent }}">{{ $l->user_agent ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No requests from this IP.</td></tr>
                @endforelse
                </tbody>
            </table></div></div>
        </div>
    @endif
</div>
@endsection
