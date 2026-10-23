@extends('admin.layouts.master')

@section('title', 'Edit Team: ' . $team->name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="m-0 fw-bold text-dark">
                            <i class="fas fa-edit text-primary me-2"></i> Edit Team: {{ $team->name }}
                        </h5>
                        <small class="text-muted">Update hierarchy, department, leadership, and operational details</small>
                    </div>
                    <a href="{{ route('admin.work_management.teams.show', encrypt($team->id)) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i> Back to Team Hub
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.work_management.teams.update', encrypt($team->id)) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Team Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $team->name) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Team Code</label>
                                <input type="text" name="code" class="form-control rounded-3" value="{{ old('code', $team->code) }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parent Team</label>
                                <select name="parent_id" class="form-select rounded-3">
                                    <option value="">None (Top-Level Parent Team)</option>
                                    @foreach($parentTeams as $pTeam)
                                        <option value="{{ $pTeam->id }}" {{ old('parent_id', $team->parent_id) == $pTeam->id ? 'selected' : '' }}>
                                            {{ $pTeam->name }} ({{ $pTeam->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Department</label>
                                <select name="department_id" class="form-select rounded-3">
                                    <option value="">Cross-Departmental / General</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $team->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Team Leader</label>
                                <select name="team_leader_id" class="form-select rounded-3">
                                    <option value="">Select Team Leader</option>
                                    @foreach($staff as $st)
                                        <option value="{{ $st->id }}" {{ old('team_leader_id', $team->team_leader_id) == $st->id ? 'selected' : '' }}>
                                            {{ $st->name }} ({{ $st->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select rounded-3" required>
                                    <option value="active" {{ old('status', $team->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $team->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description & Objectives</label>
                                <textarea name="description" class="form-control rounded-3" rows="3">{{ old('description', $team->description) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.work_management.teams.show', encrypt($team->id)) }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Team
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
