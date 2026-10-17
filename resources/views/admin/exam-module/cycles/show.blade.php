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

{{-- Calculated student-facing status --}}
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Status shown to students (calculated)</h6>
    <div class="d-flex flex-wrap gap-4">
        @foreach(['registration' => 'Registration', 'admit-card' => 'Admit card', 'exam-date' => 'Exam date', 'result' => 'Result'] as $slug => $name)
            @php $st = $cycle->statusForType($slug); @endphp
            @continue($st['state'] === 'hidden')
            <div><div class="small text-muted">{{ $name }}</div><span class="badge {{ $badge($st['state']) }}">{{ $st['label'] }}</span></div>
        @endforeach
    </div>
</div></div>

{{-- Stages --}}
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Stages <span class="text-muted fw-normal small">(optional: Prelims / Mains / Interview)</span></h6>
    @foreach($cycle->stages as $stage)
        <form action="{{ route('admin.exam-module.stages.update', $stage) }}" method="POST" class="row g-2 mb-2 align-items-center">
            @csrf @method('PUT')
            <div class="col-md-5"><input type="text" name="name" value="{{ $stage->name }}" class="form-control form-control-sm" required></div>
            <div class="col-md-2"><input type="number" name="sort_order" value="{{ $stage->sort_order }}" class="form-control form-control-sm" title="Order"></div>
            <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_qualifying" value="1" @checked($stage->is_qualifying) id="q{{ $stage->id }}"><label class="form-check-label small" for="q{{ $stage->id }}">Qualifying only</label></div></div>
            <div class="col-md-2 text-end">
                <button class="btn btn-sm btn-light border"><i class="fas fa-save text-primary"></i></button>
                <button class="btn btn-sm btn-light border text-danger" form="delstage{{ $stage->id }}" onclick="return confirm('Delete stage?')"><i class="fas fa-trash-alt"></i></button>
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

{{-- Events --}}
<div class="card border-0 shadow-sm rounded-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Important dates & events</h6>
        <button class="btn btn-sm btn-primary" onclick="openEventModal()"><i class="fas fa-plus me-1"></i> Add Event</button>
    </div>
    <div class="table-responsive">
    <table class="table align-middle">
        <thead class="bg-light"><tr><th>Event</th><th>Date</th><th>Status</th><th>Verified</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($cycle->events as $ev)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $ev->type->name }}@if($ev->label) <span class="text-muted">· {{ $ev->label }}</span>@endif</div>
                    @if($ev->stage)<div class="small text-muted">{{ $ev->stage->name }}</div>@endif
                    @if($ev->link_url)<a href="{{ $ev->link_url }}" target="_blank" rel="noopener" class="small">Link</a>@endif
                    @if($ev->source_url) <a href="{{ $ev->source_url }}" target="_blank" rel="noopener" class="small">Source</a>@endif
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
                        foreach (['start_at', 'end_at', 'extended_end_at'] as $k) {
                            $payload[$k] = $ev->$k?->format('Y-m-d\TH:i');
                        }
                    @endphp
                    <button class="btn btn-sm btn-light border" data-event="{{ json_encode($payload) }}" onclick="openEventModal(JSON.parse(this.dataset.event))"><i class="fas fa-edit text-primary"></i></button>
                    <form action="{{ route('admin.exam-module.events.destroy', $ev) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this event?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No events yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="small text-muted">Times are shown in {{ $cycle->timezone }}.</div>
</div></div>

{{-- Event modal --}}
<div class="modal fade" id="eventModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="eventForm" method="POST" action="{{ route('admin.exam-module.events.store', $cycle) }}">
        @csrf <span id="eventMethod"></span>
        <div class="modal-header"><h5 class="modal-title" id="eventTitle">Add Event</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">Event Type *</label>
                <select name="event_type_id" id="f_event_type_id" class="form-select custom-select2" required>
                    @foreach($eventTypes as $t)<option value="{{ $t->id }}" data-manual="{{ $t->status_source === 'manual' ? 1 : 0 }}">{{ $t->name }}{{ $t->status_source === 'manual' ? ' (manual status)' : '' }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Label</label><input type="text" name="label" id="f_label" class="form-control" placeholder="Phase 2, Shift 1"></div>
            @if($cycle->stages->count())
            <div class="col-md-6"><label class="form-label fw-bold">Stage</label>
                <select name="stage_id" id="f_stage_id" class="form-select custom-select2"><option value="">None</option>@foreach($cycle->stages as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
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
        @csrf
        <div class="modal-header"><h5 class="modal-title">Copy Cycle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="small text-muted">Copies stages and the event list into a new cycle, without dates, links or verification.</p>
            <div class="mb-3"><label class="form-label fw-bold">New year</label><input type="number" name="year" class="form-control" value="{{ $cycle->year + 1 }}" required></div>
            <div><label class="form-label fw-bold">Session label</label><input type="text" name="session_label" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Copy</button></div>
    </form>
</div></div></div>
@endsection

@push('scripts')
<script>
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
        form.action = ev ? updateUrl + '/' + ev.id : storeUrl;
        $('#eventMethod').html(ev ? '<input type="hidden" name="_method" value="PUT">' : '');
        $('#eventTitle').text(ev ? 'Edit Event' : 'Add Event');
        syncManual();
        new bootstrap.Modal(document.getElementById('eventModal')).show();
    }
    $(function () { $('#f_event_type_id').on('change', syncManual); });
    @if($errors->any()) $(function () { new bootstrap.Modal(document.getElementById('eventModal')).show(); }); @endif
</script>
@endpush
