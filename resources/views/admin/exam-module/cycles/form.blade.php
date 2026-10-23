@extends('admin.layouts.master')

@php $isEdit = $cycle->exists; @endphp
@section('title', $isEdit ? 'Edit Cycle' : 'Add Cycle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ $isEdit ? 'Edit ' . $cycle->title : 'Add Cycle' }} <span class="text-muted fs-6">{{ $exam->name }}</span></h4>
    <a href="{{ $isEdit ? route('admin.exam-module.cycles.show', $cycle) : route('admin.exam-module.exams.show', $exam) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<form action="{{ $isEdit ? route('admin.exam-module.cycles.update', $cycle) : route('admin.exam-module.cycles.store', $exam) }}" method="POST">
    @csrf @if($isEdit) @method('PUT') @endif
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><div class="row g-4">
        <div class="col-md-3">
            <label class="form-label fw-bold">Year <span class="text-danger">*</span></label>
            <input type="number" name="year" class="form-control" value="{{ old('year', $cycle->year) }}" required>
        </div>
        <div class="col-md-5">
            <label class="form-label fw-bold">Session Label</label>
            <input type="text" name="session_label" class="form-control" value="{{ old('session_label', $cycle->session_label) }}" placeholder="Session 1, Dec 2026 Semester">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold">Timezone</label>
            <select name="timezone" class="form-select">
                @foreach(['Asia/Kolkata', 'UTC', 'America/New_York', 'Europe/London', 'Asia/Dubai', 'Asia/Singapore', 'Australia/Sydney'] as $tz)
                    <option value="{{ $tz }}" @selected(old('timezone', $cycle->timezone) === $tz)>{{ $tz }}</option>
                @endforeach
            </select>
            <div class="form-text">Dates are entered in this zone and stored in UTC.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold">Registration Mode</label>
            <select name="registration_mode" class="form-select custom-select2">
                @foreach(\App\Models\ExamModule\Cycle::REGISTRATION_MODES as $k => $v)<option value="{{ $k }}" @selected(old('registration_mode', $cycle->registration_mode) === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold">Result Mode</label>
            <select name="result_mode" class="form-select custom-select2">
                @foreach(\App\Models\ExamModule\Cycle::RESULT_MODES as $k => $v)<option value="{{ $k }}" @selected(old('result_mode', $cycle->result_mode) === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold">Cycle Status</label>
            <select name="cycle_status" class="form-select custom-select2">
                @foreach(\App\Models\ExamModule\Cycle::STATUSES as $k => $v)<option value="{{ $k }}" @selected(old('cycle_status', $cycle->cycle_status) === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label fw-bold">Status Note</label>
            <input type="text" name="status_note" class="form-control" value="{{ old('status_note', $cycle->status_note) }}" placeholder="Postponed, new dates awaited">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold d-block">Current cycle</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1" @checked(old('is_current', $cycle->is_current))>
                <label class="form-check-label" for="is_current">Show on the public page by default</label>
            </div>
        </div>
    </div></div></div>
    <button class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Save Cycle</button>
</form>
@endsection
