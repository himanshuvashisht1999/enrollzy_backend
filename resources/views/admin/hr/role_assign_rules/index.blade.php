@extends("admin.layouts.master")

@section("title", "Role Assignment Rules")

@section("content")
<div class="container-fluid py-4">

    <!-- Hierarchy Guide Alert -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary">
                    <i class="fas fa-sitemap fa-2x"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1">Lead Delegation & Role Assignment Hierarchy</h5>
                    <p class="text-muted small mb-2">Configure which roles have authority to distribute leads to other roles across the CRM.</p>
                    <div class="d-flex flex-wrap gap-2 small">
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill">
                            <i class="fas fa-crown text-warning me-1"></i> <strong>Superadmin / Admin:</strong> Assigns from Global Unassigned Database Pool
                        </span>
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill">
                            <i class="fas fa-user-tie text-primary me-1"></i> <strong>Manager:</strong> Delegates from leads assigned to them to their reporting team
                        </span>
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill">
                            <i class="fas fa-headset text-success me-1"></i> <strong>Telecaller:</strong> Receives assigned queue for calling
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Rules Matrix Table -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark">Role Assignment Permission Matrix</h5>
                <small class="text-muted">Check the roles that each parent role is allowed to assign leads to.</small>
            </div>
            @if(session('success'))
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                </span>
            @endif
        </div>
        <div class="card-body p-4 pt-0">
            <form method="POST" action="{{ route("admin.hr.role-assign-rules.store") }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 220px;">Parent Role (Assigner)</th>
                                <th>Allowed Target Roles (Who can receive leads)</th>
                                <th class="text-end" style="width: 140px;">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                @php
                                    $allowedRoles = \App\Models\RoleAssignRule::where("role_id", $role->id)->pluck("can_assign_to_role_id")->toArray();
                                @endphp
                                <tr id="role-row-{{ $role->id }}">
                                    <td class="align-middle">
                                        <div class="fw-bold text-dark fs-6">{{ ucfirst($role->name) }}</div>
                                        <small class="text-muted">
                                            @if(in_array(strtolower($role->name), ['superadmin', 'admin']))
                                                <span class="text-primary fw-semibold">Global Pool Access</span>
                                            @elseif(str_contains(strtolower($role->name), 'manager'))
                                                <span class="text-info fw-semibold">Assigned Pool Delegation</span>
                                            @else
                                                <span>Operational Role</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2.5 py-1">
                                            @foreach($roles as $childRole)
                                                <div class="form-check me-2 mb-1">
                                                    <input class="form-check-input role-chk-{{ $role->id }}" type="checkbox" name="rules[{{ $role->id }}][]" value="{{ $childRole->id }}" id="role_{{ $role->id }}_{{ $childRole->id }}" {{ in_array($childRole->id, $allowedRoles) ? "checked" : "" }}>
                                                    <label class="form-check-label small fw-medium" for="role_{{ $role->id }}_{{ $childRole->id }}">
                                                        {{ ucfirst($childRole->name) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-end align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-sm select-all-btn" data-role-id="{{ $role->id }}" title="Select All Roles">
                                                All
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm clear-all-btn" data-role-id="{{ $role->id }}" title="Clear All Roles">
                                                None
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-save me-1.5"></i> Save Assignment Rules
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function() {
    $('.select-all-btn').on('click', function() {
        let roleId = $(this).data('role-id');
        $('.role-chk-' + roleId).prop('checked', true);
    });

    $('.clear-all-btn').on('click', function() {
        let roleId = $(this).data('role-id');
        $('.role-chk-' + roleId).prop('checked', false);
    });
});
</script>
@endpush

