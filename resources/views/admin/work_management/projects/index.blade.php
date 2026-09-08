@extends('admin.layouts.master')

@section('title', 'All Projects')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-folder-open text-primary me-2"></i> All Projects</h5>
                <small class="text-muted">Manage cross-functional projects, timelines, health statuses & member allocations</small>
            </div>
            <a href="{{ route('admin.work_management.projects.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-1"></i> New Project
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Bar -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Department</label>
                    <select id="filter_department" class="form-select form-select-sm rounded-3">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Team</label>
                    <select id="filter_team" class="form-select form-select-sm rounded-3">
                        <option value="">All Teams</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Health Status</label>
                    <select id="filter_health" class="form-select form-select-sm rounded-3">
                        <option value="">All Health Statuses</option>
                        <option value="on_track">On Track</option>
                        <option value="at_risk">At Risk</option>
                        <option value="delayed">Delayed</option>
                        <option value="on_hold">On Hold</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Status</label>
                    <select id="filter_status" class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="planning">Planning</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="projectsTable" width="100%">
                    <thead class="bg-light small">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Project</th>
                            <th>Department / Team</th>
                            <th>Project Owner</th>
                            <th>Progress</th>
                            <th>Health</th>
                            <th>Timeline</th>
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
        var table = $('#projectsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.work_management.projects.index') }}",
                data: function(d) {
                    d.department_id = $('#filter_department').val();
                    d.team_id = $('#filter_team').val();
                    d.health_status = $('#filter_health').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code_title', name: 'title' },
                { data: 'team_dept', name: 'department.name' },
                { data: 'owner', name: 'owner.name' },
                { data: 'progress_bar', name: 'progress' },
                { data: 'health', name: 'health_status' },
                { data: 'timeline', name: 'due_date' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ]
        });

        $('#filter_department, #filter_team, #filter_health, #filter_status').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush
