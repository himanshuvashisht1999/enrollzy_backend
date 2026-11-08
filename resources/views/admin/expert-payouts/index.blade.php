@extends('admin.layouts.master')

@section('title', 'Expert Payouts')

@section('content')
@php
    $money = fn ($v) => '₹' . number_format((float) $v, 2);
    $pBadge = ['pending' => 'bg-warning text-dark', 'processing' => 'bg-info', 'paid' => 'bg-success'];
@endphp
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 text-dark fw-bold">Expert Payouts</h4>
            <div class="text-muted small">Earnings come from sessions that are <b>completed</b> and <b>paid</b>, after platform fee, GST and TDS.</div>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-calendar-check me-1"></i> Expert Bookings</a>
    </div>

    @if($errors->any())<div class="alert alert-danger">{!! implode('<br>', array_map('e', $errors->all())) !!}</div>@endif

    <div class="row g-3 mb-4">
        @foreach([['Due to experts', $totals['due'], 'danger', 'fa-hourglass-half'], ['Payouts in progress', $totals['in_progress'], 'info', 'fa-sync'], ['Paid out', $totals['paid_out'], 'success', 'fa-check-circle'], ['Platform fee + GST + TDS withheld', $totals['platform'], 'primary', 'fa-building']] as [$label, $v, $c, $i])
            <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small"><i class="fas {{ $i }} text-{{ $c }} me-1"></i>{{ $label }}</div>
                <div class="fs-4 fw-bold text-{{ $c }}">{{ $money($v) }}</div>
            </div></div></div>
        @endforeach
    </div>

    <!-- Per expert -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Earnings by expert</h6>
            <a class="small" href="{{ route('admin.expert-payouts.index', ['all' => 1]) }}">Show experts with no sessions too</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light small text-muted text-uppercase">
                    <tr><th class="ps-4">Expert</th><th class="text-end">Paid sessions</th><th class="text-end">Gross (student paid)</th><th class="text-end">Net earned</th><th class="text-end">Paid out</th><th class="text-end">In progress</th><th class="text-end">Due</th><th class="text-end pe-4"></th></tr>
                </thead>
                <tbody>
                    @forelse($summaries as $s)
                        <tr>
                            <td class="ps-4"><div class="fw-bold small">{{ $s['expert']->name }}</div><div class="text-muted" style="font-size:11px">{{ $s['expert']->email }}</div></td>
                            <td class="text-end">{{ $s['sessions'] }}</td>
                            <td class="text-end">{{ $money($s['gross']) }}</td>
                            <td class="text-end fw-semibold">{{ $money($s['net_earned']) }}</td>
                            <td class="text-end text-success">{{ $money($s['paid_out']) }}</td>
                            <td class="text-end text-info">{{ $money($s['in_progress']) }}</td>
                            <td class="text-end fw-bold {{ $s['due'] > 0 ? 'text-danger' : 'text-muted' }}">{{ $money($s['due']) }}</td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#payoutModal" data-expert="{{ $s['expert']->id }}" data-name="{{ $s['expert']->name }}" data-due="{{ $s['due'] }}" @disabled($s['due'] <= 0)>
                                    <i class="fas fa-plus me-1"></i> Record payout
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No expert has completed and paid sessions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payouts -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <form class="row g-2 align-items-center" method="GET">
                <div class="col-auto"><h6 class="mb-0 fw-bold me-3">Payouts</h6></div>
                <div class="col-md-3"><select name="expert_id" class="form-select form-select-sm"><option value="">All experts</option>@foreach($experts as $e)<option value="{{ $e->id }}" @selected(request('expert_id') == $e->id)>{{ $e->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">All statuses</option>@foreach(\App\Models\ExpertPayout::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select></div>
                <div class="col-auto"><button class="btn btn-sm btn-secondary">Filter</button></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light small text-muted text-uppercase">
                    <tr><th class="ps-4">Date</th><th>Expert</th><th class="text-end">Amount</th><th>Method</th><th>Period</th><th>Reference</th><th>Status</th><th class="text-end pe-4">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($payouts as $p)
                        <tr>
                            <td class="ps-4 small">{{ $p->created_at->format('d M Y') }}@if($p->paid_at)<div class="text-success" style="font-size:11px">Paid {{ $p->paid_at->format('d M Y') }}</div>@endif</td>
                            <td class="small fw-semibold">{{ $p->expert->name ?? 'Deleted expert' }}</td>
                            <td class="text-end fw-bold">{{ $money($p->amount) }}</td>
                            <td class="small">{{ \App\Models\ExpertPayout::METHODS[$p->payout_method] ?? $p->payout_method }}</td>
                            <td class="small">{{ $p->period_start ? $p->period_start->format('d M') . ' – ' . optional($p->period_end)->format('d M Y') : '–' }}</td>
                            <td class="small"><code>{{ $p->reference_id ?: '–' }}</code>@if($p->notes)<div class="text-muted" style="font-size:11px">{{ \Illuminate\Support\Str::limit($p->notes, 60) }}</div>@endif</td>
                            <td><span class="badge {{ $pBadge[$p->status] ?? 'bg-secondary' }}">{{ \App\Models\ExpertPayout::STATUSES[$p->status] ?? $p->status }}</span></td>
                            <td class="text-end pe-4 text-nowrap">
                                @if($p->status !== 'paid')
                                    <form action="{{ route('admin.expert-payouts.update', $p) }}" method="POST" class="d-inline-flex gap-1 align-items-center">
                                        @csrf @method('PUT')
                                        <select name="status" class="form-select form-select-sm" style="width:auto">@foreach(\App\Models\ExpertPayout::STATUSES as $k => $l)<option value="{{ $k }}" @selected($p->status === $k)>{{ $l }}</option>@endforeach</select>
                                        <input name="reference_id" class="form-control form-control-sm" style="width:130px" placeholder="Reference no." value="{{ $p->reference_id }}">
                                        <button class="btn btn-sm btn-outline-primary">Save</button>
                                    </form>
                                    <form action="{{ route('admin.expert-payouts.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payout?');">
                                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @else
                                    <span class="text-muted small">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No payouts recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">{{ $payouts->links() }}</div>
    </div>
</div>

<!-- Record payout -->
<div class="modal fade" id="payoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.expert-payouts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="expert_id" id="po_expert" value="{{ old('expert_id') }}">
                <div class="modal-header"><h5 class="modal-title fw-bold">Record payout <span class="text-muted fw-normal" id="po_name"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3">Due now: <b id="po_due">–</b></div>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label fw-semibold">Amount (₹)</label><input type="number" step="0.01" min="1" name="amount" id="po_amount" class="form-control" value="{{ old('amount') }}" required></div>
                        <div class="col-6"><label class="form-label fw-semibold">Method</label><select name="payout_method" class="form-select">@foreach(\App\Models\ExpertPayout::METHODS as $k => $l)<option value="{{ $k }}" @selected(old('payout_method') === $k)>{{ $l }}</option>@endforeach</select></div>
                        <div class="col-6"><label class="form-label fw-semibold">Period from</label><input type="date" name="period_start" class="form-control" value="{{ old('period_start') }}"></div>
                        <div class="col-6"><label class="form-label fw-semibold">Period to</label><input type="date" name="period_end" class="form-control" value="{{ old('period_end') }}"></div>
                        <div class="col-6"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select">@foreach(\App\Models\ExpertPayout::STATUSES as $k => $l)<option value="{{ $k }}" @selected(old('status', 'pending') === $k)>{{ $l }}</option>@endforeach</select></div>
                        <div class="col-6"><label class="form-label fw-semibold">Reference no.</label><input name="reference_id" class="form-control" placeholder="Needed when Paid" value="{{ old('reference_id') }}"></div>
                        <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea></div>
                    </div>
                </div>
                <div class="modal-footer bg-light"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary px-4">Save payout</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('payoutModal').addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget; if (!b) return;
    document.getElementById('po_expert').value = b.dataset.expert;
    document.getElementById('po_name').textContent = '· ' + b.dataset.name;
    document.getElementById('po_due').textContent = '₹' + Number(b.dataset.due).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    const amt = document.getElementById('po_amount'); amt.value = b.dataset.due; amt.max = b.dataset.due;
});
</script>
@endsection
