{{-- Dates table + add/edit modal for a counselling cycle (round = null) or one round. Needs: $cycle, $round, $events, $eventTypes, $quotas, $storeUrl --}}
@php
    $badge = fn ($s) => match ($s) {
        'open', 'declared' => 'bg-success-subtle text-success',
        'upcoming', 'expected' => 'bg-info-subtle text-info',
        'postponed', 'cancelled', 'withheld' => 'bg-danger-subtle text-danger',
        default => 'bg-secondary-subtle text-secondary',
    };
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">{{ $round ? 'Round dates' : 'Cycle dates' }}</h6>
        @can('cns-counsellings-edit')<button class="btn btn-sm btn-primary" onclick="openEventModal()"><i class="fas fa-plus me-1"></i> Add Event</button>@endcan
    </div>
    <div class="table-responsive"><table class="table align-middle">
        <thead class="bg-light"><tr><th>Event</th><th>Date</th><th>Status</th><th>Verified</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($events as $ev)
            @php
                $d = $ev->displayStatus();
                $link = \App\Models\ExamModule\CycleEvent::safeUrl($ev->link_url);
                $src = \App\Models\ExamModule\CycleEvent::safeUrl($ev->source_url);
                $payload = $ev->only(['id', 'event_type_id', 'quota_id', 'label', 'date_precision', 'manual_status', 'on_demand_text', 'link_url', 'note', 'source_url']);
                foreach (['start_at', 'end_at', 'extended_end_at'] as $k) { $payload[$k] = $ev->$k?->format('Y-m-d\TH:i'); }
            @endphp
            <tr>
                <td>
                    <div class="fw-semibold">{{ $ev->type->name }}@if($ev->label) <span class="text-muted">· {{ $ev->label }}</span>@endif</div>
                    @if($ev->quota)<span class="badge bg-primary-subtle text-primary">{{ $ev->quota->name }}</span>@endif
                    <div class="small">
                        @if($link)<a href="{{ $link }}" target="_blank" rel="noopener noreferrer" title="{{ $link }}">Link: {{ parse_url($link, PHP_URL_HOST) }}</a>@endif
                        @if($src)<a href="{{ $src }}" target="_blank" rel="noopener noreferrer" title="{{ $src }}" class="ms-2">Source: {{ parse_url($src, PHP_URL_HOST) }}</a>@endif
                    </div>
                </td>
                <td class="small">{{ $ev->dateText() }}@if($ev->note)<div class="text-muted">{{ $ev->note }}</div>@endif</td>
                <td><span class="badge {{ $badge($d['state']) }}">{{ $d['label'] }}</span></td>
                <td class="small">@if($ev->last_verified_at){{ $ev->last_verified_at->diffForHumans() }}@else <span class="text-danger">Not verified</span>@endif</td>
                <td class="text-end text-nowrap">
                    @can('cns-counsellings-verify')
                    <form action="{{ route('admin.counselling.events.verify', $ev) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border" title="Mark as verified"><i class="fas fa-check text-success"></i></button></form>
                    @endcan
                    @can('cns-counsellings-edit')
                    <button class="btn btn-sm btn-light border" data-event="{{ json_encode($payload) }}" onclick="openEventModal(JSON.parse(this.dataset.event))"><i class="fas fa-edit text-primary"></i></button>
                    <form action="{{ route('admin.counselling.events.destroy', $ev) }}" method="POST" class="d-inline" data-confirm="Delete this event?">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No dates yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="small text-muted">Times are shown in {{ $cycle->timezone }}.</div>
</div></div>

<div class="modal fade" id="eventModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="eventForm" method="POST" action="{{ $storeUrl }}">
        @csrf <span id="eventMethod"></span>
        <input type="hidden" name="_dialog" value="event"><input type="hidden" name="_dialog_id" id="f_dialog_id">
        <div class="modal-header"><h5 class="modal-title" id="eventTitle">Add Event</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="alert alert-danger small d-none dialog-errors"></div><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">Event Type *</label>
                <select name="event_type_id" id="f_event_type_id" class="form-select custom-select2" required>
                    @foreach($eventTypes as $t)<option value="{{ $t->id }}" data-manual="{{ $t->status_source === 'manual' ? 1 : 0 }}">{{ $t->name }}{{ $t->status_source === 'manual' ? ' (manual status)' : '' }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Label</label><input type="text" name="label" id="f_label" class="form-control" placeholder="Phase 2, Deemed universities"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Quota <span class="text-muted small">(optional)</span></label>
                <select name="quota_id" id="f_quota_id" class="form-select custom-select2"><option value="">All quotas</option>@foreach($quotas as $q)<option value="{{ $q->id }}">{{ $q->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Date Precision</label>
                <select name="date_precision" id="f_date_precision" class="form-select custom-select2">@foreach(\App\Models\ExamModule\CycleEvent::PRECISIONS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Start</label><input type="datetime-local" name="start_at" id="f_start_at" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">End</label><input type="datetime-local" name="end_at" id="f_end_at" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Extended End</label><input type="datetime-local" name="extended_end_at" id="f_extended_end_at" class="form-control"></div>
            <div class="col-md-6" id="manualBox"><label class="form-label fw-bold">Status (manual)</label>
                <select name="manual_status" id="f_manual_status" class="form-select custom-select2">@foreach(\App\Models\ExamModule\CycleEvent::MANUAL_STATUSES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-bold">On-demand text</label><input type="text" name="on_demand_text" id="f_on_demand_text" class="form-control"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Link URL</label><input type="url" name="link_url" id="f_link_url" class="form-control" placeholder="Notice / portal link"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Source URL <span class="text-muted small">(required with a date)</span></label><input type="url" name="source_url" id="f_source_url" class="form-control" placeholder="Official notice"></div>
            <div class="col-12"><label class="form-label fw-bold">Note</label><input type="text" name="note" id="f_note" class="form-control"></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Event</button></div>
    </form>
</div></div></div>

@push('scripts')
<script>
    const storeUrl = @json($storeUrl);
    const updateUrl = @json(url('admin/counselling/events'));
    const fields = ['event_type_id','quota_id','label','date_precision','manual_status','on_demand_text','link_url','note','source_url','start_at','end_at','extended_end_at'];
    function syncManual() { $('#manualBox').toggle($('#f_event_type_id option:selected').data('manual') == 1); }
    function openEventModal(ev) {
        const form = document.getElementById('eventForm');
        form.reset();
        fields.forEach(f => { const el = $('#f_' + f); if (el.length) el.val(ev ? (ev[f] ?? '') : (f === 'date_precision' ? 'exact' : '')).trigger('change'); });
        const editing = !!(ev && ev.id);
        form.action = editing ? updateUrl + '/' + ev.id : storeUrl;
        $('#eventMethod').html(editing ? '<input type="hidden" name="_method" value="PUT">' : '');
        $('#f_dialog_id').val(editing ? ev.id : '');
        $('#eventTitle').text(editing ? 'Edit Event' : 'Add Event');
        $(form).find('.dialog-errors').addClass('d-none').empty();
        syncManual();
        new bootstrap.Modal(document.getElementById('eventModal')).show();
    }
    $(function () { $('#f_event_type_id').on('change', syncManual); });
    // A validation error reopens THIS dialog only when it was the one submitted, with what was typed.
    @if($errors->any() && old('_dialog') === 'event')
    $(function () {
        openEventModal(Object.assign({!! json_encode(collect(old())->only(['event_type_id','quota_id','label','date_precision','manual_status','on_demand_text','link_url','note','source_url','start_at','end_at','extended_end_at']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}, {id: @json(old('_dialog_id'))}));
        $('#eventForm .dialog-errors').removeClass('d-none').html(@json($errors->all()).map(e => $('<div>').text(e).html()).join('<br>'));
    });
    @endif
</script>
@endpush
