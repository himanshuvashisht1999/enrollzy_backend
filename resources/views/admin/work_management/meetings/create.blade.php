@extends('admin.layouts.master')

@section('title', 'Schedule Meeting')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="m-0 fw-bold text-dark">
                            <i class="fas fa-video text-primary me-2"></i> Schedule Project Meeting
                        </h5>
                        <small class="text-muted">Set up internal standups, client reviews, sprint planning sessions, and syncs</small>
                    </div>
                    <a href="{{ route('admin.work_management.meetings.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i> Back to Meetings
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.work_management.meetings.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Meeting Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Sprint 14 Planning & Review" required value="{{ old('title') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Meeting Type <span class="text-danger">*</span></label>
                                <select name="meeting_type" class="form-select rounded-3" required>
                                    <option value="sprint_planning" {{ old('meeting_type') == 'sprint_planning' ? 'selected' : '' }}>Sprint Planning</option>
                                    <option value="status_update" {{ old('meeting_type') == 'status_update' ? 'selected' : '' }}>Daily / Weekly Status Update</option>
                                    <option value="kickoff" {{ old('meeting_type') == 'kickoff' ? 'selected' : '' }}>Project Kickoff</option>
                                    <option value="review" {{ old('meeting_type') == 'review' ? 'selected' : '' }}>Milestone Review</option>
                                    <option value="retrospective" {{ old('meeting_type') == 'retrospective' ? 'selected' : '' }}>Sprint Retrospective</option>
                                    <option value="client_sync" {{ old('meeting_type') == 'client_sync' ? 'selected' : '' }}>Client Sync</option>
                                    <option value="other" {{ old('meeting_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Related Project</label>
                                <select name="project_id" class="form-select rounded-3">
                                    <option value="">General (No Specific Project)</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Meeting URL / Link</label>
                                <input type="url" name="meeting_url" class="form-control rounded-3" placeholder="https://meet.google.com/xyz-abc or Zoom link" value="{{ old('meeting_url') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Meeting Date <span class="text-danger">*</span></label>
                                <input type="date" name="meeting_date" class="form-control rounded-3" required value="{{ old('meeting_date', date('Y-m-d')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" class="form-control rounded-3" required value="{{ old('start_time', '10:00') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">End Time</label>
                                <input type="time" name="end_time" class="form-control rounded-3" value="{{ old('end_time', '11:00') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Select Participants (Staff)</label>
                                <select name="participant_ids[]" class="form-select rounded-3" multiple style="min-height: 120px;">
                                    @foreach($staff as $st)
                                        <option value="{{ $st->id }}" {{ (is_array(old('participant_ids')) && in_array($st->id, old('participant_ids'))) ? 'selected' : '' }}>
                                            {{ $st->name }} ({{ $st->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold down Ctrl (Windows) or Cmd (Mac) to select multiple participants.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Meeting Agenda</label>
                                <textarea name="agenda" class="form-control rounded-3" rows="4" placeholder="Topics to cover, discussion points, key decisions needed...">{{ old('agenda') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.work_management.meetings.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                <i class="fas fa-calendar-check me-1"></i> Schedule Meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
