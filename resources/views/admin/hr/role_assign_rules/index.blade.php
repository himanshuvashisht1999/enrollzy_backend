@extends("admin.layouts.master")

@section("title", "Role Assignment Rules")

@section("content")
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Role Assignment Rules</h5>
            <small class="text-muted">Configure which roles can assign leads to which other roles.</small>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route("admin.hr.role-assign-rules.store") }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Parent Role (Who is assigning)</th>
                                <th>Can Assign Leads To...</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td class="align-middle fw-bold">{{ ucfirst($role->name) }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-3">
                                            @php
                                                $allowedRoles = \App\Models\RoleAssignRule::where("role_id", $role->id)->pluck("can_assign_to_role_id")->toArray();
                                            @endphp
                                            @foreach($roles as $childRole)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="rules[{{ $role->id }}][]" value="{{ $childRole->id }}" id="role_{{ $role->id }}_{{ $childRole->id }}" {{ in_array($childRole->id, $allowedRoles) ? "checked" : "" }}>
                                                    <label class="form-check-label" for="role_{{ $role->id }}_{{ $childRole->id }}">
                                                        {{ ucfirst($childRole->name) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4">Save Rules</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

