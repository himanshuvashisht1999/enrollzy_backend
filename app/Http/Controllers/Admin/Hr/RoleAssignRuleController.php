<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoleAssignRule;
use Spatie\Permission\Models\Role;

class RoleAssignRuleController extends Controller
{
    public function index()
    {
        $roles = Role::where("guard_name", "admin")->get();
        return view("admin.hr.role_assign_rules.index", compact("roles"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "rules" => "array",
            "rules.*" => "array",
        ]);

        RoleAssignRule::truncate();

        if ($request->has("rules")) {
            foreach ($request->rules as $role_id => $assignable_roles) {
                foreach ($assignable_roles as $can_assign_to) {
                    RoleAssignRule::create([
                        "role_id" => $role_id,
                        "can_assign_to_role_id" => $can_assign_to
                    ]);
                }
            }
        }

        return redirect()->back()->with("success", "Role Assignment Rules updated successfully.");
    }
}
