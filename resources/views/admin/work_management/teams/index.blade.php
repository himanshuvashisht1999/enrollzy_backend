@extends('admin.layouts.master')

@section('title', 'Internal Teams & Hierarchy')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-users text-primary me-2"></i> Internal Teams & Hierarchy</h5>
                <small class="text-muted">Manage departmental & cross-functional teams, parent-child structures, and multi-team staff assignments</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#hierarchyTreeSection">
                    <i class="fas fa-sitemap me-1"></i> Toggle Hierarchy Tree
                </button>
                <a href="{{ route('admin.work_management.teams.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-1"></i> New Team
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Collapsible Hierarchy Tree -->
            <div class="collapse mb-4" id="hierarchyTreeSection">
                <div class="card bg-light border-0 rounded-4 p-3">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-network-wired text-primary me-2"></i> Organizational Team Structure (Parent $\rightarrow$ Child Teams)</h6>
                    <div class="row g-3">
                        @forelse($parentTeams as $pTeam)
                            <div class="col-md-6 col-lg-4">
                                <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-light text-primary border me-1">{{ $pTeam->code }}</span>
                                            <a href="{{ route('admin.work_management.teams.show', encrypt($pTeam->id)) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $pTeam->name }}
                                            </a>
                                        </div>
                                        <span class="badge bg-soft-primary text-primary rounded-pill">{{ $pTeam->members->count() }} members</span>
                                    </div>
                                    <small class="text-muted d-block mb-2">
                                        Leader: <strong>{{ $pTeam->leader->name ?? 'No Leader' }}</strong>
                                    </small>

                                    @if($pTeam->children->isNotEmpty())
                                        <div class="ps-3 border-start border-2 border-primary mt-2">
                                            <small class="text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">Sub-Teams:</small>
                                            <ul class="list-unstyled mb-0 mt-1">
                                                @foreach($pTeam->children as $cTeam)
                                                    <li class="mb-1 small">
                                                        <a href="{{ route('admin.work_management.teams.show', encrypt($cTeam->id)) }}" class="text-dark text-decoration-none">
                                                            <i class="fas fa-level-down-alt text-muted me-1"></i> {{ $cTeam->name }}
                                                        </a>
                                                        <span class="text-muted">({{ $cTeam->members->count() }} staff)</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small">No teams defined yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Department</label>
                    <select id="filter_department" class="form-select form-select-sm rounded-3">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status</label>
                    <select id="filter_status" class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="teamsTable" width="100%">
                    <thead class="bg-light small">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Team</th>
                            <th>Parent Team</th>
                            <th>Department</th>
                            <th>Team Leader</th>
                            <th>Members</th>
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
        var table = $('#teamsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.work_management.teams.index') }}",
                data: function(d) {
                    d.department_id = $('#filter_department').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name_code', name: 'name' },
                { data: 'parent_team', name: 'parent_id' },
                { data: 'department', name: 'department_id' },
                { data: 'leader', name: 'team_leader_id' },
                { data: 'members_count', name: 'members_count', orderable: false, searchable: false },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[1, 'asc']]
        });

        $('#filter_department, #filter_status').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush
