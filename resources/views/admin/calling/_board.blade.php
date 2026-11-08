{{-- Notice board card for the dashboards: the 4 latest live notices. --}}
<div class="card">
    <div class="hd"><h3>📌 Notice board</h3><span class="muted sm">{{ $unacked->count() }} to acknowledge</span><div class="grow"></div><a href="{{ route('admin.calling.notices.index') }}" class="sm">View all</a></div>
    <div class="bd">
        @forelse($board as $n)@include('admin.calling._notice', ['n' => $n, 'nb' => $notices])@empty<div class="empty">No notices</div>@endforelse
    </div>
</div>
