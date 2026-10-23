@extends('admin.layouts.master')

@section('title', 'Meeting: ' . $meeting->title)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.work_management.dashboard.overview') }}">Work Management</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.work_management.meetings.index') }}">Meetings</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $meeting->title }}</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <button class="btn btn-warning text-dark btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#decisionModal">
                <i class="fas fa-check-circle me-1"></i> Convert Decision to Task
            </button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-soft-info text-info text-capitalize">{{ $meeting->meeting_type }}</span>
                        {!! GetStatusBadge($meeting->status) !!}
                    </div>

                    <h3 class="fw-bold text-dark mb-3">{{ $meeting->title }}</h3>

                    <div class="p-3 bg-light rounded-3 mb-4 d-flex flex-wrap gap-4 text-muted small">
                        <div>
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Date: 
                            <strong class="text-dark">{{ date('l, M d, Y', strtotime($meeting->meeting_date)) }}</strong>
                        </div>
                        <div>
                            <i class="fas fa-clock text-primary me-1"></i> Time: 
                            <strong class="text-dark">{{ date('h:i A', strtotime($meeting->start_time)) }} {{ $meeting->end_time ? '- ' . date('h:i A', strtotime($meeting->end_time)) : '' }}</strong>
                        </div>
                        <div>
                            <i class="fas fa-user text-primary me-1"></i> Organizer: 
                            <strong class="text-dark">{{ $meeting->creator->name ?? 'Staff' }}</strong>
                        </div>
                        @if($meeting->project)
                            <div>
                                <i class="fas fa-folder text-primary me-1"></i> Project: 
                                <a href="{{ route('admin.work_management.projects.show', encrypt($meeting->project->id)) }}" class="text-decoration-none fw-bold">
                                    {{ $meeting->project->title }}
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($meeting->meeting_url)
                        <div class="mb-4">
                            <a href="{{ $meeting->meeting_url }}" target="_blank" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fas fa-video me-2"></i> Join Video Call
                            </a>
                        </div>
                    @endif

                    <h5 class="fw-bold text-dark mb-2">Agenda & Discussion Points</h5>
                    <div class="p-3 border rounded-3 bg-white mb-4">
                        <p class="text-dark mb-0" style="white-space: pre-line;">{{ $meeting->agenda ?: 'No formal agenda specified for this meeting.' }}</p>
                    </div>

                    @if($meeting->minutes)
                        <h5 class="fw-bold text-dark mb-2">Meeting Minutes & Summary</h5>
                        <div class="p-3 border rounded-3 bg-white">
                            <p class="text-dark mb-0" style="white-space: pre-line;">{{ $meeting->minutes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark m-0"><i class="fas fa-users text-primary me-2"></i> Participants ({{ $meeting->participants->count() }})</h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush">
                        @forelse($meeting->participants as $part)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 30px; height: 30px;">
                                        {{ strtoupper(substr($part->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold small text-dark">{{ $part->user->name ?? 'User' }}</div>
                                        <small class="text-muted">{{ $part->user->email ?? '' }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-secondary border small text-capitalize">{{ $part->response }}</span>
                            </li>
                        @empty
                            <p class="text-muted small text-center py-3 mb-0">No participants registered.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Convert Decision to Task -->
<div class="modal fade" id="decisionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-check-circle text-warning me-2"></i> Convert Decision to Actionable Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.work_management.meetings.convert_decision_to_task') }}" method="POST">
                    @csrf
                    <input type="hidden" name="meeting_id" value="{{ $meeting->id }}">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" required placeholder="e.g. Implement client review changes">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign Custody To (Staff)</label>
                        <select name="assigned_to" class="form-select rounded-3">
                            <option value="">Select Staff Member</option>
                            @foreach($allStaff as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Priority</label>
                            <select name="priority" class="form-select rounded-3">
                                <option value="urgent">Urgent</option>
                                <option value="high">High</option>
                                <option value="medium" selected>Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Due Date</label>
                            <input type="date" name="due_date" class="form-control rounded-3">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Decision Details / Acceptance</label>
                        <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Context from the meeting discussion..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning text-dark w-100 rounded-pill fw-bold">Create Task from Decision</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
