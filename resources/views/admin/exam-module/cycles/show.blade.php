@extends('admin.layouts.master')

@section('title', $cycle->exam->name . ' ' . $cycle->title)

@php
    $badge = fn ($s) => match ($s) {
        'open', 'declared' => 'bg-success-subtle text-success',
        'upcoming', 'expected' => 'bg-info-subtle text-info',
        'postponed', 'cancelled', 'withheld' => 'bg-danger-subtle text-danger',
        default => 'bg-secondary-subtle text-secondary',
    };
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold">{{ $cycle->exam->name }} · {{ $cycle->title }}</h4>
        <span class="badge {{ $badge($cycle->cycle_status) }}">{{ ucfirst($cycle->cycle_status) }}</span>
        @if($cycle->is_current)<span class="badge bg-primary">Current</span>@endif
        <span class="badge {{ $cycle->is_public ? 'bg-success' : 'bg-secondary' }}">{{ $cycle->is_public ? 'Public' : 'Admin only' }}</span>
        @if($cycle->status_note)<span class="text-muted small ms-2">{{ $cycle->status_note }}</span>@endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.exam-module.exams.show', $cycle->exam) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#copyModal"><i class="fas fa-copy me-1"></i> Copy Cycle</button>
        <a href="{{ route('admin.exam-module.cycles.edit', $cycle) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i> Edit Cycle</a>
    </div>
</div>

@unless($cycle->is_public)
{{-- an admin-only cycle sends nothing to the website: no dates, eligibility, pattern, papers or extra tabs --}}
<div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><i class="fas fa-eye-slash me-1"></i> <strong>Not on the website.</strong> This cycle is admin only, so the exam page shows none of its dates, eligibility, pattern, papers or other tabs.
        @if($cycle->exam->visibility !== 'public')<br><span class="small">The exam itself is a draft too: publish it on the exam page.</span>@endif</div>
    @can('exm-exams-publish')
    <form action="{{ route('admin.exam-module.cycles.public', $cycle) }}" method="POST" data-confirm="Show this cycle's dates and details on the website?">@csrf @method('PATCH')
        <button class="btn btn-sm btn-success"><i class="fas fa-globe me-1"></i> Show on website</button></form>
    @endcan
</div>
@endunless

{{-- Calculated student-facing status --}}
@php
    $usesStages = $cycle->usesStages();
    $statusCols = ['registration' => 'Registration', 'admit-card' => 'Admit card', 'exam-date' => 'Exam date', 'result' => 'Result'];
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Status shown to students (calculated)</h6>
    @if($usesStages)
        <div class="table-responsive"><table class="table table-sm align-middle mb-1">
            <thead class="bg-light"><tr><th>Stage</th>@foreach($statusCols as $name)<th>{{ $name }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach($cycle->stages as $stage)
                <tr>
                    <td class="fw-semibold">{{ $stage->name }}@if($stage->is_qualifying) <span class="badge bg-light text-dark border">Qualifying</span>@endif</td>
                    @foreach($statusCols as $slug => $name)
                        @php $st = $cycle->statusForType($slug, $stage->id); @endphp
                        <td>@if($st['state'] !== 'hidden')<span class="badge {{ $badge($st['state']) }}">{{ $st['label'] }}</span>@endif</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <div class="small text-muted">Each stage shows its own events plus events marked "All stages (common)".</div>
    @else
        <div class="d-flex flex-wrap gap-4">
            @foreach($statusCols as $slug => $name)
                @php $st = $cycle->statusForType($slug); @endphp
                @continue($st['state'] === 'hidden')
                <div><div class="small text-muted">{{ $name }}</div><span class="badge {{ $badge($st['state']) }}">{{ $st['label'] }}</span></div>
            @endforeach
        </div>
    @endif
</div></div>
{{-- Stages --}}
@if($cycle->exam->has_stages)
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Stages <span class="text-muted fw-normal small">(Prelims / Mains / Interview)</span></h6>
    @unless($cycle->stages->count())<div class="alert alert-warning small py-2">Add at least one stage so events can be tagged to it.</div>@endunless
    @foreach($cycle->stages as $stage)
        <form action="{{ route('admin.exam-module.stages.update', $stage) }}" method="POST" class="row g-2 mb-2 align-items-center">
            @csrf @method('PUT')
            <div class="col-md-5"><input type="text" name="name" value="{{ $stage->name }}" class="form-control form-control-sm" required></div>
            <div class="col-md-2"><input type="number" name="sort_order" value="{{ $stage->sort_order }}" class="form-control form-control-sm" title="Order"></div>
            <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_qualifying" value="1" @checked($stage->is_qualifying) id="q{{ $stage->id }}"><label class="form-check-label small" for="q{{ $stage->id }}">Qualifying only</label></div></div>
            <div class="col-md-2 text-end">
                <button class="btn btn-sm btn-light border"><i class="fas fa-save text-primary"></i></button>
                <button class="btn btn-sm btn-light border text-danger" form="delstage{{ $stage->id }}" data-confirm="Delete stage?"><i class="fas fa-trash-alt"></i></button>
            </div>
        </form>
        <form id="delstage{{ $stage->id }}" action="{{ route('admin.exam-module.stages.destroy', $stage) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
    @endforeach
    <form action="{{ route('admin.exam-module.stages.store', $cycle) }}" method="POST" class="row g-2 mt-1 align-items-center">
        @csrf
        <div class="col-md-5"><input type="text" name="name" class="form-control form-control-sm" placeholder="New stage name" required></div>
        <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_qualifying" value="1" id="qnew"><label class="form-check-label small" for="qnew">Qualifying only</label></div></div>
        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary">Add stage</button></div>
    </form>
</div></div>
@else
<div class="alert alert-light border small">This exam has no stages (see "Does this exam have stages?" on the exam). Events apply to the whole cycle.</div>
@endif

{{-- Events --}}
<div class="card border-0 shadow-sm rounded-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
        <h6 class="fw-bold mb-0">Important dates & events</h6>
        <div class="d-flex gap-2">
            @if($cycle->events->count() > 1)
            <form action="{{ route('admin.exam-module.events.order', $cycle) }}" method="POST" data-confirm="Put the events in date order? Undated events go to the end.">@csrf<input type="hidden" name="by" value="date">
                <button class="btn btn-sm btn-light border"><i class="fas fa-sort-amount-down me-1"></i> Sort by date</button></form>
            @endif
            <button class="btn btn-sm btn-primary" onclick="openEventModal()"><i class="fas fa-plus me-1"></i> Add Event</button>
        </div>
    </div>
    <p class="small text-muted mb-3">The website shows the events in this order. Drag <i class="fas fa-grip-vertical"></i> to change it; it saves at once.</p>
    <div class="table-responsive">
    <table class="table align-middle">
        <thead class="bg-light"><tr><th style="width:34px"></th><th>Event</th><th>Date</th><th>Status</th><th>Verified</th><th class="text-end">Actions</th></tr></thead>
        <tbody id="eventRows">
        @forelse($cycle->events as $ev)
            <tr data-id="{{ $ev->id }}">
                <td class="ev-grip text-muted" title="Drag to reorder" style="cursor:grab"><i class="fas fa-grip-vertical"></i></td>
                <td>
                    <div class="fw-semibold">{{ $ev->type->name }}@if($usesStages && $ev->stageLabel()) <span class="text-muted">· {{ $ev->stageLabel() }}</span>@endif@if($ev->label) <span class="text-muted">· {{ $ev->label }}</span>@endif</div>
                    @if($usesStages)
                        @if($ev->stageLabel())<span class="badge bg-primary-subtle text-primary">{{ $ev->stageLabel() }}</span>
                        @else<span class="badge bg-danger-subtle text-danger">Stage missing</span>@endif
                    @endif
                    @php $link = \App\Models\ExamModule\CycleEvent::safeUrl($ev->link_url); $src = \App\Models\ExamModule\CycleEvent::safeUrl($ev->source_url); @endphp
                    <div class="small">
                        @if($link)<a href="{{ $link }}" target="_blank" rel="noopener noreferrer" title="{{ $link }}">Link: {{ parse_url($link, PHP_URL_HOST) }}</a>@endif
                        @if($src) <a href="{{ $src }}" target="_blank" rel="noopener noreferrer" title="{{ $src }}" class="ms-2">Source: {{ parse_url($src, PHP_URL_HOST) }}</a>@endif
                    </div>
                </td>
                <td class="small">{{ $ev->dateText() }}@if($ev->note)<div class="text-muted">{{ $ev->note }}</div>@endif</td>
                @php $d = $ev->displayStatus(); @endphp
                <td><span class="badge {{ $badge($d['state']) }}">{{ $d['label'] }}</span></td>
                <td class="small">
                    @if($ev->last_verified_at){{ $ev->last_verified_at->diffForHumans() }}@else <span class="text-danger">Not verified</span>@endif
                </td>
                <td class="text-end text-nowrap">
                    @can('exm-exams-verify')
                    <form action="{{ route('admin.exam-module.events.verify', $ev) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border" title="Mark as verified"><i class="fas fa-check text-success"></i></button></form>
                    @endcan
                    @php
                        $payload = $ev->only(['id', 'event_type_id', 'stage_id', 'label', 'date_precision', 'manual_status', 'on_demand_text', 'link_url', 'note', 'source_url']);
                        if ($ev->applies_to_all_stages) $payload['stage_id'] = 'all';
                        foreach (['start_at', 'end_at', 'extended_end_at'] as $k) {
                            $payload[$k] = $ev->$k?->format('Y-m-d\TH:i');
                        }
                    @endphp
                    <button class="btn btn-sm btn-light border" data-event="{{ json_encode($payload) }}" onclick="openEventModal(JSON.parse(this.dataset.event))"><i class="fas fa-edit text-primary"></i></button>
                    <form action="{{ route('admin.exam-module.events.destroy', $ev) }}" method="POST" class="d-inline" data-confirm="Delete this event?">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No events yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="small text-muted">Times are shown in {{ $cycle->timezone }}.</div>
</div></div>

@include('admin.exam-module.cycles._content')

{{-- Event modal --}}
<div class="modal fade" id="eventModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="eventForm" method="POST" action="{{ route('admin.exam-module.events.store', $cycle) }}">
        @csrf <span id="eventMethod"></span>
        <input type="hidden" name="_dialog" value="event"><input type="hidden" name="_dialog_id" id="f_dialog_id">
        <div class="modal-header"><h5 class="modal-title" id="eventTitle">Add Event</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="alert alert-danger small d-none dialog-errors"></div><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold d-flex justify-content-between">Event Type *
                    @can('exm-event-types-manage')<a href="#" class="fw-normal small" onclick="$('#newTypeBox').toggleClass('d-none'); $('#nt_name').trigger('focus'); return false;"><i class="fas fa-plus"></i> New type</a>@endcan</label>
                <select name="event_type_id" id="f_event_type_id" class="form-select custom-select2" required>
                    @foreach($eventTypes as $t)<option value="{{ $t->id }}" data-manual="{{ $t->status_source === 'manual' ? 1 : 0 }}">{{ $t->name }}{{ $t->status_source === 'manual' ? ' (manual status)' : '' }}</option>@endforeach
                </select></div>
            @can('exm-event-types-manage')
            {{-- a kind of date the list does not have yet: added to the Event Types master and picked here --}}
            <div class="col-12 d-none" id="newTypeBox"><div class="border rounded-3 p-3 bg-light">
                <div class="fw-bold small mb-2">New event type <span class="fw-normal text-muted">(saved to <a href="{{ route('admin.exam-module.event-types.index') }}" target="_blank">Event Types</a>, so every exam can use it)</span></div>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4"><label class="form-label small mb-1">Name</label><input type="text" id="nt_name" class="form-control form-control-sm" placeholder="e.g. Exam city slip, Re-exam"></div>
                    <div class="col-md-3"><label class="form-label small mb-1">Status</label><select id="nt_source" class="form-select form-select-sm"><option value="dates">From dates</option><option value="manual">Set by hand (results)</option></select></div>
                    <div class="col-md-3"><label class="form-label small mb-1">Shows under</label><select id="nt_group" class="form-select form-select-sm">@foreach(\App\Models\ExamModule\EventType::GROUPS as $k => $v)@continue($k === 'counselling')<option value="{{ $k }}" @selected($k === 'other')>{{ $v }}</option>@endforeach</select></div>
                    <div class="col-md-2"><button type="button" class="btn btn-sm btn-primary w-100" onclick="addEventType()">Add type</button></div>
                </div>
                <div class="small text-danger mt-1" id="nt_err"></div>
            </div></div>
            @endcan
            <div class="col-md-6"><label class="form-label fw-bold">Label</label><input type="text" name="label" id="f_label" class="form-control" placeholder="Phase 2, Shift 1"></div>
            @if($usesStages)
            <div class="col-md-6"><label class="form-label fw-bold">Stage <span class="text-danger">*</span></label>
                <select name="stage_id" id="f_stage_id" class="form-select custom-select2" required><option value="">Select stage</option><option value="all">All stages (common)</option>@foreach($cycle->stages as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            @endif
            <div class="col-md-6"><label class="form-label fw-bold">Date Precision</label>
                <select name="date_precision" id="f_date_precision" class="form-select custom-select2">@foreach(\App\Models\ExamModule\CycleEvent::PRECISIONS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Start</label><input type="datetime-local" name="start_at" id="f_start_at" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">End</label><input type="datetime-local" name="end_at" id="f_end_at" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Extended End</label><input type="datetime-local" name="extended_end_at" id="f_extended_end_at" class="form-control"></div>
            <div class="col-md-6" id="manualBox"><label class="form-label fw-bold">Status (manual)</label>
                <select name="manual_status" id="f_manual_status" class="form-select custom-select2">@foreach(\App\Models\ExamModule\CycleEvent::MANUAL_STATUSES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-bold">On-demand text</label><input type="text" name="on_demand_text" id="f_on_demand_text" class="form-control" placeholder="Scores within 13 days of test"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Link URL</label><input type="url" name="link_url" id="f_link_url" class="form-control" placeholder="Apply / admit card / result link"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Source URL <span class="text-muted small">(required with a date)</span></label><input type="url" name="source_url" id="f_source_url" class="form-control" placeholder="Official notice"></div>
            <div class="col-12"><label class="form-label fw-bold">Note</label><input type="text" name="note" id="f_note" class="form-control" placeholder="Late fee Rs 500"></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Event</button></div>
    </form>
</div></div></div>

{{-- Copy modal --}}
<div class="modal fade" id="copyModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('admin.exam-module.cycles.copy', $cycle) }}">
        @csrf <input type="hidden" name="_dialog" value="copy">
        @php
            $copyFailed = old('_dialog') === 'copy';
            // suggest the first year after this one that has no cycle yet (without a session label)
            $taken = \App\Models\ExamModule\Cycle::where('exam_id', $cycle->exam_id)->where(fn ($q) => $q->whereNull('session_label')->orWhere('session_label', ''))->pluck('year')->all();
            for ($nextYear = $cycle->year + 1; in_array($nextYear, $taken); $nextYear++);
        @endphp
        @if($copyFailed && $errors->any())<div class="alert alert-danger small m-3 mb-0">{!! implode('<br>', array_map('e', $errors->all())) !!}</div>@endif
        <div class="modal-header"><h5 class="modal-title">Copy Cycle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="small text-muted">Copies stages and the event list into a new cycle, without dates, links or verification.</p>
            <div class="mb-3"><label class="form-label fw-bold">New year</label><input type="number" name="year" class="form-control" value="{{ $copyFailed ? old('year') : $nextYear }}" required></div>
            <div><label class="form-label fw-bold">Session label</label><input type="text" name="session_label" class="form-control" value="{{ $copyFailed ? old('session_label') : '' }}"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Copy</button></div>
    </form>
</div></div></div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
    // drag events into the order the website shows; each drop saves the whole order
    $(function () {
        const rows = document.getElementById('eventRows');
        if (!window.Sortable || !rows || rows.querySelectorAll('tr[data-id]').length < 2) return;
        Sortable.create(rows, {
            handle: '.ev-grip', animation: 150, draggable: 'tr[data-id]',
            onEnd: function (e) {
                if (e.oldIndex === e.newIndex) return;
                $.ajax({
                    url: @json(route('admin.exam-module.events.order', $cycle)), method: 'POST', dataType: 'json',
                    data: {_token: @json(csrf_token()), ids: [...rows.querySelectorAll('tr[data-id]')].map(r => r.dataset.id)},
                }).done(() => window.toastr && toastr.success('Order saved.'))
                  .fail(x => { (window.toastr ? toastr.error : alert)((x.responseJSON && x.responseJSON.message) || 'Could not save the order. Reload and try again.'); });
            },
        });
    });

    @can('exm-event-types-manage')
    function addEventType() {
        const name = $('#nt_name').val().trim();
        $('#nt_err').text('');
        if (!name) { $('#nt_err').text('Enter a name.'); return; }
        $.ajax({url: @json(route('admin.exam-module.event-types.store')), method: 'POST', dataType: 'json',
            data: {_token: @json(csrf_token()), name: name, status_source: $('#nt_source').val(), applies_to: 'exam', date_group: $('#nt_group').val()}})
            .done(t => {
                $('#f_event_type_id').append($('<option>', {value: t.id, text: t.name + (t.manual ? ' (manual status)' : '')}).attr('data-manual', t.manual ? 1 : 0)).val(String(t.id)).trigger('change');
                $('#nt_name').val(''); $('#newTypeBox').addClass('d-none');
                window.toastr && toastr.success('Event type "' + t.name + '" added and selected.');
            })
            .fail(x => $('#nt_err').text(Object.values((x.responseJSON && x.responseJSON.errors) || {}).flat().join(' ') || 'Could not add the type.'));
    }
    @endcan

    const storeUrl = @json(route('admin.exam-module.events.store', $cycle));
    const updateUrl = @json(url('admin/exam-module/events'));
    const fields = ['event_type_id','stage_id','label','date_precision','manual_status','on_demand_text','link_url','note','source_url','start_at','end_at','extended_end_at'];

    function syncManual() {
        $('#manualBox').toggle($('#f_event_type_id option:selected').data('manual') == 1);
    }
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
    // A validation error reopens only the dialog that was submitted, with what was typed.
    @if($errors->any() && old('_dialog') === 'event')
    $(function () {
        openEventModal(Object.assign({!! json_encode(collect(old())->only(['event_type_id','stage_id','label','date_precision','manual_status','on_demand_text','link_url','note','source_url','start_at','end_at','extended_end_at']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}, {id: @json(old('_dialog_id'))}));
        $('#eventForm .dialog-errors').removeClass('d-none').html(@json($errors->all()).map(e => $('<div>').text(e).html()).join('<br>'));
    });
    @elseif($errors->any() && old('_dialog') === 'copy')
    $(function () { new bootstrap.Modal(document.getElementById('copyModal')).show(); });
    @endif
</script>
@endpush
