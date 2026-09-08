@extends('admin.layouts.master')

@section('title', 'Project Meetings & Syncs')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-video text-primary me-2"></i> Project Meetings & Syncs</h5>
                <small class="text-muted">Schedule sprint reviews, kickoff calls, decision logs, and convert meeting items into tasks</small>
            </div>
            <a href="{{ route('admin.work_management.meetings.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-1"></i> Schedule Meeting
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Bar -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Project</label>
                    <select id="filter_project" class="form-select form-select-sm rounded-3">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="meetingsTable" width="100%">
                    <thead class="bg-light small">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Meeting</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Participants</th>
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
        var table = $('#meetingsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.work_management.meetings.index') }}",
                data: function(d) {
                    d.project_id = $('#filter_project').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title_meeting', name: 'title' },
                { data: 'datetime', name: 'meeting_date' },
                { data: 'meeting_type_badge', name: 'meeting_type' },
                { data: 'participants_count', name: 'participants_count', orderable: false, searchable: false },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[2, 'desc']]
        });

        $('#filter_project').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush
