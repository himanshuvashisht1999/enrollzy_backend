@extends('admin.layouts.master')

@section('title', 'Commission Rules')

{{-- Commission Rules (CommissionController): the global default, a rate per expert category, and a rate per expert.
     The first that exists applies to a booking: booking override > expert > category > global. --}}
@php
    $fmt = fn ($type, $value) => $type === 'percentage' ? rtrim(rtrim(number_format((float) $value, 2), '0'), '.') . '%' : '₹' . rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    $money = fn ($v) => '₹' . number_format((float) $v, 2);
    $canEdit = auth()->user()?->can('commission-edit');
    $err = fn ($form) => $errors->any() && old('_form') === $form;
    // typed values come back only to the form they were typed in (all three forms share field names)
    $typed = fn ($form, $key, $default = null) => old('_form') === $form ? old($key, $default) : $default;
    $globalEx = $example($global->commission_type, $global->commission_value);
@endphp

@push('css')
<style>
    .cr-steps { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .cr-step { position: relative; background: #fff; border: 1px solid #e3e8f0; border-radius: 14px; padding: 14px 16px; }
    .cr-step .n { width: 26px; height: 26px; border-radius: 50%; display: inline-grid; place-items: center; font-weight: 700; font-size: 13px; background: #eaf1ff; color: #1f5fd8; margin-bottom: 6px; }
    .cr-step b { display: block; font-size: 14.5px; }
    .cr-step small { color: #6c7a95; }
    .cr-step:not(:last-child)::after { content: '›'; position: absolute; right: -11px; top: 50%; transform: translateY(-50%); font-size: 22px; color: #9aa8c0; z-index: 1; }
    @media (max-width: 991px) { .cr-steps { grid-template-columns: repeat(2, minmax(0, 1fr)); } .cr-step::after { display: none; } }
    .cr-rate { min-width: 210px; max-width: 240px; flex-wrap: nowrap; }
    .cr-rate .form-select { flex: 0 0 92px; }
    .cr-example { background: #f6f9ff; border: 1px dashed #c9d8f3; border-radius: 12px; padding: 10px 12px; font-size: 13.5px; }
    .cr-example b { color: #0f2147; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Commission Rules</h4>
        <p class="text-muted mb-0">What Enrollzy keeps from each expert booking. Changes apply to new bookings; past bookings keep the split they were made with.</p>
    </div>
</div>

{{-- which rate applies --}}
<div class="cr-steps mb-4">
    <div class="cr-step"><span class="n">1</span><b>Booking override</b><small>Set on one booking (Bookings page), for exceptions.</small></div>
    <div class="cr-step"><span class="n">2</span><b>Expert's own rate</b><small>{{ $expertRates->count() }} {{ Str::plural('expert', $expertRates->count()) }} with their own rate.</small></div>
    <div class="cr-step"><span class="n">3</span><b>Category rate</b><small>{{ $categoryRates->count() }} of {{ $categories->count() }} categories have one.</small></div>
    <div class="cr-step"><span class="n">4</span><b>Global default</b><small>{{ $fmt($global->commission_type, $global->commission_value) }} for everyone else.</small></div>
</div>
<p class="small text-muted mt-n2 mb-4"><i class="fas fa-info-circle me-1"></i> The first rate that exists, from left to right, is used. GST and TDS follow the global settings for every booking.</p>

<div class="row g-4 mb-4">
    {{-- global default --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <h6 class="fw-bold mb-1"><i class="fas fa-globe me-2 text-primary"></i>Global default</h6>
            <p class="small text-muted">Used when neither the expert nor their category has a rate.</p>
            @if($err('global'))<div class="alert alert-danger small py-2">{{ $errors->first() }}</div>@endif
            <form action="{{ route('admin.commission.global.update') }}" method="POST">
                @csrf <input type="hidden" name="_form" value="global">
                <label class="form-label fw-semibold small">Rate</label>
                <div class="input-group mb-3">
                    <select name="commission_type" class="form-select custom-select2" style="max-width: 130px" @disabled(!$canEdit)>
                        <option value="percentage" @selected($typed('global', 'commission_type', $global->commission_type) === 'percentage')>Percent %</option>
                        <option value="flat_fee" @selected($typed('global', 'commission_type', $global->commission_type) === 'flat_fee')>Flat ₹</option>
                    </select>
                    <input type="number" step="0.01" min="0" name="commission_value" class="form-control" value="{{ $typed('global', 'commission_value', (float) $global->commission_value) }}" required @disabled(!$canEdit)>
                </div>
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="gst_applicable" value="1" id="cr-gst" @checked($global->gst_applicable) @disabled(!$canEdit)>
                    <label class="form-check-label" for="cr-gst">Charge GST (18% on Enrollzy's fee)</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="tds_applicable" value="1" id="cr-tds" @checked($global->tds_applicable) @disabled(!$canEdit)>
                    <label class="form-check-label" for="cr-tds">Deduct TDS (10% of the expert's share)</label>
                </div>
                <div class="cr-example mb-3">On a <b>₹1,000</b> session: Enrollzy keeps <b>{{ $money($globalEx['platform_total_deduction']) }}</b>{{ $globalEx['gst_on_fee'] > 0 ? ' (incl. GST)' : '' }}, the expert gets <b>{{ $money($globalEx['net_expert_earning']) }}</b>{{ $globalEx['tds_deduction'] > 0 ? ' after TDS' : '' }}.</div>
                @if($canEdit)<button class="btn btn-primary w-100">Save global default</button>@endif
            </form>
        </div></div>
    </div>

    {{-- category rates --}}
    <div class="col-lg-8" id="categories">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <h6 class="fw-bold mb-1"><i class="fas fa-layer-group me-2 text-primary"></i>Category rates</h6>
            <p class="small text-muted">For every expert in a category. A category without a rate uses the global default.</p>
            @if($err('category'))<div class="alert alert-danger small py-2">{{ $errors->first() }}</div>@endif
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Category</th><th class="text-center">Experts</th><th>Rate</th><th>Now</th>@if($canEdit)<th class="text-end">Actions</th>@endif</tr></thead>
                    <tbody>
                    @forelse($categories as $cat)
                        @php $rate = $categoryRates[$cat->id] ?? null; @endphp
                        <tr>
                            <td class="fw-semibold">{{ $cat->name }}</td>
                            <td class="text-center">{{ $cat->experts_count }}</td>
                            <td>
                                <form id="cat-{{ $cat->id }}" action="{{ route('admin.commission.category.update') }}" method="POST">@csrf
                                    <input type="hidden" name="_form" value="category"><input type="hidden" name="expert_category_id" value="{{ $cat->id }}">
                                    <div class="input-group input-group-sm cr-rate">
                                        <select name="commission_type" class="form-select custom-select2" @disabled(!$canEdit)>
                                            <option value="percentage" @selected(($rate->commission_type ?? $global->commission_type) === 'percentage')>%</option>
                                            <option value="flat_fee" @selected(($rate->commission_type ?? $global->commission_type) === 'flat_fee')>₹ flat</option>
                                        </select>
                                        <input type="number" step="0.01" min="0" name="commission_value" class="form-control" value="{{ $rate ? (float) $rate->commission_value : '' }}" placeholder="{{ (float) $global->commission_value }} (global)" required @disabled(!$canEdit)>
                                    </div>
                                </form>
                            </td>
                            <td>@if($rate)<span class="badge bg-primary-subtle text-primary">Own rate · {{ $fmt($rate->commission_type, $rate->commission_value) }}</span>@else<span class="badge bg-light text-dark border">Global · {{ $fmt($global->commission_type, $global->commission_value) }}</span>@endif</td>
                            @if($canEdit)
                            <td class="text-end text-nowrap">
                                <button form="cat-{{ $cat->id }}" class="btn btn-sm btn-outline-primary" title="Save"><i class="fas fa-save"></i></button>
                                @if($rate)
                                    <form action="{{ route('admin.commission.category.destroy', $cat->id) }}" method="POST" class="d-inline" data-confirm="Remove the rate for {{ $cat->name }}? Its experts will use the global default ({{ $fmt($global->commission_type, $global->commission_value) }}).">
                                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary" title="Use the global default"><i class="fas fa-undo"></i></button></form>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No active expert categories.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>

{{-- expert rates --}}
<div class="card border-0 shadow-sm rounded-4 mb-4" id="experts"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1"><i class="fas fa-user-tie me-2 text-primary"></i>Expert rates</h6>
            <p class="small text-muted mb-0">A rate for one expert, ahead of their category and the global default. Removing it puts them back on those.</p>
        </div>
        <form method="GET" action="{{ route('admin.commission.index') }}#experts" class="d-flex gap-2">
            <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Find an expert" style="width: 200px">
            <button class="btn btn-sm btn-light border"><i class="fas fa-search"></i></button>
        </form>
    </div>

    @if($canEdit)
        @if($err('expert'))<div class="alert alert-danger small py-2">{{ $errors->first() }}</div>@endif
        <form action="{{ route('admin.commission.expert.store') }}" method="POST" class="row g-2 align-items-end p-3 mb-3 rounded-3 bg-light" id="cr-expert-form">
            @csrf <input type="hidden" name="_form" value="expert">
            <div class="col-lg-4">
                <label class="form-label small fw-semibold mb-1">Expert</label>
                <select name="expert_id" class="form-select custom-select2" id="cr-expert" required>
                    <option value="">Choose an expert</option>
                    @foreach($experts as $ex)
                        @php [$src, $p] = $fallback($ex->expert_category_id); @endphp
                        <option value="{{ $ex->id }}" data-now="{{ $expertRates->firstWhere('expert_id', $ex->id) ? 'Has own rate' : ($src === 'category' ? 'Category' : 'Global') . ' ' . $fmt($p->commission_type, $p->commission_value) }}" @selected((int) $typed('expert', 'expert_id') === $ex->id)>{{ $ex->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label small fw-semibold mb-1">Rate</label>
                <div class="input-group">
                    <select name="commission_type" class="form-select custom-select2" id="cr-type" style="max-width: 105px">
                        <option value="percentage" @selected($typed('expert', 'commission_type', 'percentage') === 'percentage')>%</option>
                        <option value="flat_fee" @selected($typed('expert', 'commission_type') === 'flat_fee')>₹ flat</option>
                    </select>
                    <input type="number" step="0.01" min="0" name="commission_value" class="form-control" id="cr-value" value="{{ $typed('expert', 'commission_value') }}" placeholder="e.g. 12" required>
                </div>
            </div>
            <div class="col-lg-3">
                <label class="form-label small fw-semibold mb-1">Reason <span class="text-muted fw-normal">(optional)</span></label>
                <input type="text" name="reason" class="form-control" id="cr-reason" maxlength="255" value="{{ $typed('expert', 'reason') }}" placeholder="e.g. Launch offer till March">
            </div>
            <div class="col-lg-2"><button class="btn btn-primary w-100" id="cr-save">Save rate</button></div>
            <div class="col-12"><small class="text-muted" id="cr-now"></small></div>
        </form>
    @endif

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Expert</th><th>Own rate</th><th>Without it</th><th>On ₹1,000 the expert gets</th><th>Reason</th><th>Since</th>@if($canEdit)<th class="text-end">Actions</th>@endif</tr></thead>
            <tbody>
            @forelse($expertRates as $r)
                @php
                    [$src, $p] = $fallback($r->expert->expert_category_id);
                    $ex = $example($r->commission_type, $r->commission_value);
                @endphp
                <tr>
                    <td><a href="{{ route('experts.edit', $r->expert_id) }}" class="fw-semibold text-decoration-none">{{ $r->expert->name }}</a>
                        <div class="small text-muted">{{ $r->expert->expertCategory->name ?? 'No category' }}@if($r->expert->profile_visibility === 'Private') · <span class="text-warning">Private</span>@endif</div></td>
                    <td><span class="badge bg-success-subtle text-success fs-6 fw-semibold">{{ $fmt($r->commission_type, $r->commission_value) }}</span></td>
                    <td class="small text-muted">{{ $src === 'category' ? 'Category' : 'Global' }} · {{ $fmt($p->commission_type, $p->commission_value) }}</td>
                    <td><b>{{ $money($ex['net_expert_earning']) }}</b> <span class="small text-muted">· Enrollzy {{ $money($ex['platform_total_deduction']) }}</span></td>
                    <td class="small">{{ $r->reason ?: '—' }}</td>
                    <td class="small text-muted text-nowrap">{{ $r->created_at?->format('j M Y') }}</td>
                    @if($canEdit)
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-primary" title="Change" data-cr-edit="{{ json_encode(['id' => $r->expert_id, 'type' => $r->commission_type, 'value' => (float) $r->commission_value, 'reason' => $r->reason]) }}"><i class="fas fa-pen"></i></button>
                        <form action="{{ route('admin.commission.expert.destroy', $r->expert_id) }}" method="POST" class="d-inline" data-confirm="Remove {{ $r->expert->name }}'s own rate? They will pay the {{ $src }} rate ({{ $fmt($p->commission_type, $p->commission_value) }}).">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Remove (use category / global)"><i class="fas fa-times"></i></button></form>
                    </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">{{ $q ? 'No expert named "' . $q . '" has their own rate.' : 'No expert has their own rate yet. Every expert pays their category rate or the global default.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection

@push('scripts')
<script>
    (function () {
        var sel = document.getElementById('cr-expert'); if (!sel) return;
        var now = document.getElementById('cr-now');
        // what the chosen expert pays today
        function hint() { var o = sel.options[sel.selectedIndex]; now.textContent = o && o.value ? 'Now: ' + o.getAttribute('data-now') + '.' : ''; }
        if (window.jQuery && jQuery.fn.select2) jQuery(sel).select2({ width: '100%', placeholder: 'Choose an expert' }).on('change', hint);
        sel.addEventListener('change', hint); hint();
        // "change" on a row fills the form above with that expert's rate
        document.addEventListener('click', function (e) {
            var b = e.target.closest('[data-cr-edit]'); if (!b) return;
            var d = JSON.parse(b.getAttribute('data-cr-edit'));
            if (window.jQuery && jQuery.fn.select2) jQuery(sel).val(String(d.id)).trigger('change'); else { sel.value = d.id; hint(); }
            document.getElementById('cr-type').value = d.type;
            document.getElementById('cr-value').value = d.value;
            document.getElementById('cr-reason').value = d.reason || '';
            document.getElementById('cr-expert-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('cr-value').focus();
        });
    })();
</script>
@endpush
