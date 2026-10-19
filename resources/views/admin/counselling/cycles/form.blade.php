@extends('admin.layouts.master')

@php $isEdit = $cycle->exists; @endphp
@section('title', $isEdit ? 'Edit Cycle' : 'Add Cycle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ $isEdit ? 'Edit ' . $cycle->title : 'Add Cycle' }} <span class="text-muted fs-6">{{ $counselling->name }}</span></h4>
    <a href="{{ $isEdit ? route('admin.counselling.cycles.show', $cycle) : route('admin.counselling.counsellings.show', $counselling) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<form action="{{ $isEdit ? route('admin.counselling.cycles.update', $cycle) : route('admin.counselling.cycles.store', $counselling) }}" method="POST" enctype="multipart/form-data">
    @csrf @if($isEdit) @method('PUT') @endif
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><div class="row g-4">
        <div class="col-md-3"><label class="form-label fw-bold">Year <span class="text-danger">*</span></label>
            <input type="number" name="year" class="form-control" value="{{ old('year', $cycle->year) }}" required></div>
        <div class="col-md-5"><label class="form-label fw-bold">Session Label</label>
            <input type="text" name="session_label" class="form-control" value="{{ old('session_label', $cycle->session_label) }}" placeholder="Lateral entry, Second session"></div>
        <div class="col-md-4"><label class="form-label fw-bold">Timezone</label>
            <select name="timezone" class="form-select">@foreach(['Asia/Kolkata', 'UTC', 'America/New_York', 'Europe/London', 'Asia/Dubai', 'Asia/Singapore', 'Australia/Sydney'] as $tz)
                <option value="{{ $tz }}" @selected(old('timezone', $cycle->timezone) === $tz)>{{ $tz }}</option>@endforeach</select></div>

        <div class="col-md-4"><label class="form-label fw-bold">Cycle Status</label>
            <select name="cycle_status" class="form-select custom-select2">@foreach(\App\Models\Counselling\CounsellingCycle::STATUSES as $k => $v)
                <option value="{{ $k }}" @selected(old('cycle_status', $cycle->cycle_status) === $k)>{{ $v }}</option>@endforeach</select></div>
        <div class="col-md-8"><label class="form-label fw-bold">Status Note</label>
            <input type="text" name="status_note" class="form-control" value="{{ old('status_note', $cycle->status_note) }}" placeholder="Round 2 postponed by court order"></div>

        <div class="col-12"><label class="form-label fw-bold">Exam cycles feeding this year</label>
            <select name="exam_cycle_ids[]" class="form-select" multiple>
                @foreach($examCycles as $ec)<option value="{{ $ec->id }}" @selected(in_array($ec->id, array_map('intval', old('exam_cycle_ids', $selected))))>{{ $ec->exam->name }} {{ $ec->title }}</option>@endforeach
            </select>
            <div class="form-text">Only cycles of exams mapped to this counselling are listed. The current cycle of each is pre-selected for a new year.</div></div>

        <div class="col-md-6"><label class="form-label fw-bold">Information Bulletin (URL)</label>
            <input type="url" name="information_bulletin_url" class="form-control" value="{{ old('information_bulletin_url', $cycle->information_bulletin_url) }}" placeholder="https://"></div>
        <div class="col-md-6"><label class="form-label fw-bold">Information Bulletin (PDF)</label>
            <input type="file" name="information_bulletin_pdf" class="form-control" accept=".pdf">
            @if($cycle->information_bulletin_pdf)<a class="small" href="{{ asset('storage/' . $cycle->information_bulletin_pdf) }}" target="_blank" rel="noopener">Current file</a>@endif</div>

        <div class="col-md-6"><label class="form-label fw-bold">Willingness options</label>
            <select name="willingness_options[]" class="form-select" multiple>@foreach(\App\Models\Counselling\CounsellingCycle::WILLINGNESS as $k => $v)
                <option value="{{ $k }}" @selected(in_array($k, old('willingness_options', $cycle->willingness_options ?? [])))>{{ $v }}</option>@endforeach</select>
            <div class="form-text">Freeze, float, slide... A round can override this.</div></div>
        <div class="col-md-6"><label class="form-label fw-bold">Source URL</label>
            <input type="url" name="source_url" class="form-control" value="{{ old('source_url', $cycle->source_url) }}" placeholder="Official notice"></div>

        <div class="col-12"><label class="form-label fw-bold">Reservation policy</label>
            <textarea name="reservation_policy" rows="4" class="form-control">{{ old('reservation_policy', $cycle->reservation_policy) }}</textarea></div>
        <div class="col-12"><label class="form-label fw-bold">Seat allotment method</label>
            <textarea name="seat_allotment_method" rows="4" class="form-control">{{ old('seat_allotment_method', $cycle->seat_allotment_method) }}</textarea></div>

        <div class="col-12"><div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1" @checked(old('is_current', $cycle->is_current))>
            <label class="form-check-label" for="is_current">Current cycle (shown on the public page by default)</label></div></div>
    </div></div></div>
    <button class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Save Cycle</button>
</form>
@endsection
