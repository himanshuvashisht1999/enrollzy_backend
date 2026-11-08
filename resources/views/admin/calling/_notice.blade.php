{{-- One notice. $n (Notice), $nb (Notices), $me. --}}
@php
    $rc = $nb->recipients($n);
    $mine = in_array($me->id, $rc, true);
    $acked = $n->acks->firstWhere('staff_id', $me->id);
    $manage = $nb->canManage($me, $n);
@endphp
<div class="notice {{ $mine && $n->require_ack && !$acked ? 'unread' : '' }} {{ $n->pinned ? 'pinned' : '' }}">
    <div class="row sp"><b class="sm">{{ $n->pinned ? '📌 ' : '' }}{{ $n->title }}</b>
        <span class="row"><span class="pill pr-{{ $n->priority }}">{{ \App\Models\Notice::PRIORITIES[$n->priority] }}</span><span class="xs muted">{{ $n->created_at->format('j M, g:i A') }}</span></span></div>
    <div class="body">{{ $n->body }}</div>
    <div class="row sp"><span class="xs muted">From {{ $n->poster->name ?? 'Staff' }} → {{ $nb->audienceLabel($n) }}{{ $n->expires_on ? ' · until ' . $n->expires_on->format('j M') : '' }}</span>
        <span class="row">
            @if($manage && $n->require_ack)<button type="button" class="btn sm" data-receipts="{{ route('admin.calling.notices.receipts', $n) }}">👁 {{ $n->acks->whereIn('staff_id', $rc)->count() }}/{{ count($rc) }} acknowledged</button>@endif
            @if($mine && $n->require_ack)
                @if($acked)<span class="xs yes">✓ Acknowledged {{ $acked->acked_at->format('j M, g:i A') }}</span>
                @else<form method="POST" action="{{ route('admin.calling.notices.ack', $n) }}" class="m-0">@csrf<button class="btn sm pri">Acknowledge</button></form>@endif
            @endif
            @if($manage && !empty($withDelete))<form method="POST" action="{{ route('admin.calling.notices.destroy', $n) }}" class="m-0" data-confirm="Remove this notice?">@csrf @method('DELETE')<button class="btn sm ghost" title="Remove">🗑</button></form>@endif
        </span></div>
</div>
