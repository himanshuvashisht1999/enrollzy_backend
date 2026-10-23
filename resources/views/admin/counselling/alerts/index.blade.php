@extends('admin.layouts.master')

@section('title', 'Alerts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Alerts</h4>
        <p class="text-muted mb-0">Students who follow a counselling or exam get alerts when <strong>verified</strong> data changes. Each alert is created once.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.counsellings.index') }}" class="btn btn-light border">Counselling</a>
        @can('cns-counsellings-edit')
        <form action="{{ route('admin.counselling.alerts.retry') }}" method="POST">@csrf<button class="btn btn-outline-secondary">Retry failed</button></form>
        <form action="{{ route('admin.counselling.alerts.run') }}" method="POST">@csrf<button class="btn btn-primary"><i class="fas fa-play me-1"></i> Run now</button></form>
        @endcan
    </div>
</div>

<div class="alert alert-light border small">
    Runs automatically every 10 minutes <em>if</em> the server cron calls <code>php artisan schedule:run</code> every minute.
    WhatsApp: driver <strong>{{ $whatsapp['driver'] }}</strong> ({{ $whatsapp['configured'] ? 'ready' : 'not configured: WhatsApp deliveries are skipped, not faked' }}).
</div>

<div class="row g-3 mb-4">
    @foreach([['Subscribers', $stats['subscribers']], ['Email confirmed', $stats['email_confirmed']], ['WhatsApp opted in', $stats['whatsapp_opted_in']], ['Confirmed follows', $stats['follows']],
        ['Awaiting confirmation', $stats['awaiting_confirmation']], ['Queued', $stats['pending']], ['Failed', $stats['failed']]] as [$l, $n])
    <div class="col-6 col-md-3 col-lg"><div class="rounded-4 p-3 bg-light"><div class="small text-muted">{{ $l }}</div><div class="fs-4 fw-bold">{{ number_format($n) }}</div></div></div>
    @endforeach
</div>

@if($failures->count())
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold text-danger">Recent failures</h6>
    @foreach($failures as $f)
        <div class="border-top py-2 small"><strong>{{ ucfirst($f->channel) }}</strong> · {{ $f->alert->title }} <span class="text-danger">: {{ $f->error }}</span> <span class="text-muted">(attempt {{ $f->attempts }})</span></div>
    @endforeach
</div></div>
@endif

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">When</th><th>Alert</th><th>About</th><th>Kind</th><th>Sent</th><th>Queued</th><th>Failed</th><th>Skipped</th></tr></thead>
        <tbody>
        @forelse($alerts as $a)
            <tr>
                <td class="ps-4 small text-nowrap">{{ $a->created_at->format('d M Y, h:i A') }}</td>
                <td>{{ $a->title }}@if($a->source_url)<div class="small"><a href="{{ $a->source_url }}" target="_blank" rel="noopener">source</a></div>@endif</td>
                <td class="small">{{ ucfirst($a->target_type) }}: {{ $targets[$a->id] ?? '-' }}</td>
                <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $a->kind) }}</span></td>
                <td>{{ $a->sent_count }}</td><td>{{ $a->pending_count }}</td>
                <td class="{{ $a->failed_count ? 'text-danger fw-semibold' : '' }}">{{ $a->failed_count }}</td><td>{{ $a->skipped_count }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-5">No alerts yet. They appear once verified dates change for something that has followers.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $alerts->links() }}</div>
@endsection
