@extends('admin.layouts.master')

@section('title', 'All Tasks & Subtasks')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-tasks text-primary me-2"></i> All Tasks & Subtasks</h5>
                <small class="text-muted">Track work execution, single-assignee custody, dependencies and task progress</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.work_management.tasks.kanban') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-columns me-1"></i> Kanban Board
                </a>
                <a href="{{ route('admin.work_management.tasks.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-1"></i> New Task
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Bar -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-4">
                <input type="hidden" id="filter_overdue" value="{{ request('overdue', '') }}">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Project</label>
                    <select id="filter_project" class="form-select form-select-sm rounded-3">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ (isset($selectedProjectId) && $selectedProjectId == $p->id) ? 'selected' : '' }}>{{ $p->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Team</label>
                    <select id="filter_team" class="form-select form-select-sm rounded-3">
                        <option value="">All Teams</option>
                        @foreach($teams as $t)
                            <option value="{{ $t->id }}" {{ request('team_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Assignee</label>
                    <select id="filter_assignee" class="form-select form-select-sm rounded-3">
                        <option value="">All Staff</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}" {{ request('assigned_to') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select id="filter_status" class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="backlog" {{ request('status') == 'backlog' ? 'selected' : '' }}>Backlog</option>
                        <option value="not_started" {{ request('status') == 'not_started' ? 'selected' : '' }}>Not Started</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Priority</label>
                    <select id="filter_priority" class="form-select form-select-sm rounded-3">
                        <option value="">All Priorities</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select id="filter_is_subtask" class="form-select form-select-sm rounded-3">
                        <option value="">All (Tasks & Subtasks)</option>
                        <option value="0" {{ request('is_subtask') === '0' ? 'selected' : '' }}>Parent Tasks Only</option>
                        <option value="1" {{ request('is_subtask') === '1' ? 'selected' : '' }}>Subtasks Only</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tasksTable" width="100%">
                    <thead class="bg-light small">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Task / Subtask</th>
                            <th>Active Assignee</th>
                            <th>Team</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#tasksTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.work_management.tasks.index') }}",
                data: function(d) {
                    d.project_id = $('#filter_project').val();
                    d.team_id = $('#filter_team').val();
                    d.assigned_to = $('#filter_assignee').val();
                    d.status = $('#filter_status').val();
                    d.priority = $('#filter_priority').val();
                    d.is_subtask = $('#filter_is_subtask').val();
                    d.overdue = $('#filter_overdue').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title_code', name: 'title' },
                { data: 'assignee', name: 'assignee', orderable: false, searchable: false },
                { data: 'team_badge', name: 'team_id' },
                { data: 'priority_badge', name: 'priority' },
                { data: 'due_date_formatted', name: 'due_date' },
                { data: 'status_badge', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[1, 'asc']]
        });

        $('#filter_project, #filter_team, #filter_assignee, #filter_priority, #filter_is_subtask').on('change', function() {
            table.draw();
        });

        $('#filter_status').on('change', function() {
            $('#filter_overdue').val('');
            table.draw();
        });
    });
</script>
@endpush
