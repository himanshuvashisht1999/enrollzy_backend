@extends('admin.layouts.master')

@section('title', 'Role Assignment Rules')

@push('css')
<style>
    .role-matrix-card {
        transition: all 0.2s ease;
        border: 1px solid #eef2f6;
    }
    .role-matrix-card:hover {
        border-color: #cbd5e1;
    }
    .target-role-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
        margin-bottom: 6px;
        margin-right: 6px;
    }
    .target-role-pill:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
    }
    .target-role-pill input:checked + span {
        font-weight: 600;
        color: #1e40af;
    }
    .target-role-pill.checked {
        background-color: #eff6ff;
        border-color: #bfdbfe;
        color: #1e40af;
    }
    .target-role-pill input {
        cursor: pointer;
    }
    .role-badge-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">

    <!-- Header Information Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary">
                        <i class="fas fa-sitemap fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Role Assignment Permission Matrix</h4>
                        <p class="text-muted small mb-0">Configure which parent roles have permission to assign or delegate leads to staff members in other roles.</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" id="globalSelectAll">
                        <i class="fas fa-check-double me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" id="globalClearAll">
                        <i class="fas fa-times me-1"></i> Clear All
                    </button>
                    <button type="button" onclick="document.getElementById('rulesForm').submit();" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> Save Rules
                    </button>
                </div>
            </div>

            <hr class="my-3 text-muted opacity-25">

            <!-- Hierarchy Legend -->
            <div class="row g-2 pt-1">
                <div class="col-md-4">
                    <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center gap-2 small">
                        <span class="badge bg-warning bg-opacity-20 text-warning-emphasis p-1.5 rounded-2">
                            <i class="fas fa-crown"></i>
                        </span>
                        <div>
                            <strong>Superadmin & Admin:</strong>
                            <div class="text-muted" style="font-size: 0.78rem;">Assigns directly from Global CRM pool</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center gap-2 small">
                        <span class="badge bg-primary bg-opacity-20 text-primary p-1.5 rounded-2">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <div>
                            <strong>Managers:</strong>
                            <div class="text-muted" style="font-size: 0.78rem;">Delegates from their assigned lead quota</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center gap-2 small">
                        <span class="badge bg-success bg-opacity-20 text-success p-1.5 rounded-2">
                            <i class="fas fa-headset"></i>
                        </span>
                        <div>
                            <strong>Telecallers & Counselors:</strong>
                            <div class="text-muted" style="font-size: 0.78rem;">Work on their assigned calling queues</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Rules Form Matrix -->
    <form method="POST" action="{{ route('admin.hr.role-assign-rules.store') }}" id="rulesForm">
        @csrf

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th style="width: 260px; padding: 1rem 1.25rem;">Assigner Role</th>
                            <th style="padding: 1rem 1.25rem;">Allowed Target Roles (Who can receive leads)</th>
                            <th class="text-end" style="width: 160px; padding: 1rem 1.25rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($roles as $role)
                            @php
                                $allowedRoles = \App\Models\RoleAssignRule::where('role_id', $role->id)->pluck('can_assign_to_role_id')->toArray();
                                $isTop = in_array(strtolower($role->name), ['superadmin', 'admin']);
                                $isManager = str_contains(strtolower($role->name), 'manager');
                            @endphp
                            <tr id="role-row-{{ $role->id }}" class="border-bottom">
                                <!-- Assigner Role Info -->
                                <td style="padding: 1.25rem;" class="align-top">
                                    <div class="d-flex align-items-center gap-2.5 mb-1">
                                        <div class="role-badge-icon {{ $isTop ? 'bg-warning bg-opacity-15 text-warning-emphasis' : ($isManager ? 'bg-primary bg-opacity-15 text-primary' : 'bg-secondary bg-opacity-15 text-secondary') }}">
                                            <i class="fas {{ $isTop ? 'fa-crown' : ($isManager ? 'fa-user-tie' : 'fa-user') }}"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0 fs-6">{{ ucfirst($role->name) }}</h6>
                                            <span class="badge {{ $isTop ? 'bg-warning bg-opacity-10 text-dark border border-warning' : ($isManager ? 'bg-primary bg-opacity-10 text-primary border border-primary' : 'bg-light text-muted border') }} rounded-pill" style="font-size: 0.7rem;">
                                                {{ $isTop ? 'Global Pool' : ($isManager ? 'Team Manager' : 'Operational') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        <span id="counter-{{ $role->id }}" class="fw-bold text-primary">{{ count($allowedRoles) }}</span> of {{ count($roles) }} roles allowed
                                    </div>
                                </td>

                                <!-- Target Roles Checkbox Pills -->
                                <td style="padding: 1.25rem;">
                                    <div class="d-flex flex-wrap">
                                        @foreach($roles as $childRole)
                                            @php
                                                $isChecked = in_array($childRole->id, $allowedRoles);
                                            @endphp
                                            <label class="target-role-pill {{ $isChecked ? 'checked' : '' }}" for="role_{{ $role->id }}_{{ $childRole->id }}">
                                                <input class="form-check-input me-2 role-chk role-chk-{{ $role->id }}" 
                                                       type="checkbox" 
                                                       name="rules[{{ $role->id }}][]" 
                                                       value="{{ $childRole->id }}" 
                                                       id="role_{{ $role->id }}_{{ $childRole->id }}" 
                                                       data-parent-id="{{ $role->id }}"
                                                       {{ $isChecked ? 'checked' : '' }}>
                                                <span class="small">{{ ucfirst($childRole->name) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- Quick Row Actions -->
                                <td style="padding: 1.25rem;" class="text-end align-top">
                                    <div class="btn-group btn-group-sm rounded-pill border overflow-hidden">
                                        <button type="button" class="btn btn-light btn-sm select-all-btn px-2.5" data-role-id="{{ $role->id }}" title="Select All Roles">
                                            <i class="fas fa-check text-success me-1"></i> All
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm clear-all-btn px-2.5" data-role-id="{{ $role->id }}" title="Clear All Roles">
                                            <i class="fas fa-times text-danger me-1"></i> None
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Submit Bar -->
            <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center border-top">
                <span class="text-muted small">
                    <i class="fas fa-shield-alt text-primary me-1"></i> Changes take effect immediately on the CRM Lead Assigner.
                </span>
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="fas fa-save me-1.5"></i> Save Assignment Rules
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    function updateCounter(roleId) {
        let count = $('.role-chk-' + roleId + ':checked').length;
        $('#counter-' + roleId).text(count);
    }

    // Toggle pill highlight on change
    $('.role-chk').on('change', function() {
        let parentId = $(this).data('parent-id');
        if ($(this).is(':checked')) {
            $(this).closest('.target-role-pill').addClass('checked');
        } else {
            $(this).closest('.target-role-pill').removeClass('checked');
        }
        updateCounter(parentId);
    });

    // Row Select All
    $('.select-all-btn').on('click', function() {
        let roleId = $(this).data('role-id');
        let $chks = $('.role-chk-' + roleId);
        $chks.prop('checked', true);
        $chks.closest('.target-role-pill').addClass('checked');
        updateCounter(roleId);
    });

    // Row Clear All
    $('.clear-all-btn').on('click', function() {
        let roleId = $(this).data('role-id');
        let $chks = $('.role-chk-' + roleId);
        $chks.prop('checked', false);
        $chks.closest('.target-role-pill').removeClass('checked');
        updateCounter(roleId);
    });

    // Global Select All
    $('#globalSelectAll').on('click', function() {
        $('.role-chk').prop('checked', true);
        $('.target-role-pill').addClass('checked');
        @foreach($roles as $role)
            updateCounter({{ $role->id }});
        @endforeach
    });

    // Global Clear All
    $('#globalClearAll').on('click', function() {
        $('.role-chk').prop('checked', false);
        $('.target-role-pill').removeClass('checked');
        @foreach($roles as $role)
            updateCounter({{ $role->id }});
        @endforeach
    });
});
</script>
@endpush
