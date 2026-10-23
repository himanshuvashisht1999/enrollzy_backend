@extends('admin.layouts.master')

@section('title', 'All Milestones')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-flag text-primary me-2"></i> All Milestones</h5>
                <small class="text-muted">Track major project stages, progress roadmaps, and target milestones</small>
            </div>
            <a href="{{ route('admin.work_management.milestones.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-1"></i> New Milestone
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Bar -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Filter by Project</label>
                    <select id="filter_project" class="form-select form-select-sm rounded-3">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Filter by Status</label>
                    <select id="filter_status" class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="delayed">Delayed</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="milestonesTable" width="100%">
                    <thead class="bg-light small">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Milestone</th>
                            <th>Project</th>
                            <th>Assigned Team</th>
                            <th>Owner</th>
                            <th>Progress</th>
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
        var table = $('#milestonesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.work_management.milestones.index') }}",
                data: function(d) {
                    d.project_id = $('#filter_project').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'title' },
                { data: 'project', name: 'project.title' },
                { data: 'assigned_team', name: 'team.name' },
                { data: 'owner', name: 'owner.name' },
                { data: 'progress_bar', name: 'progress' },
                { data: 'timeline', name: 'due_date' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ]
        });

        $('#filter_project, #filter_status').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush
