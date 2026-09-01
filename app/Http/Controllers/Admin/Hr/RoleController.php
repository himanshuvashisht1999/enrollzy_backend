<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\HrDepartment;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            if($user->is_admin && !isset($user->organization_id)){
                $data = Role::orderBy('id', 'desc')->get();
            }else{
                $data = Role::where('role_for', '!=', 'superadmin')
                    ->orderBy('id', 'desc')
                    ->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm fw-bold mb-0 text-capitalize">' . $row->name . '</p>';
                })
                ->addColumn('role_for', function ($row) {
                    return '<p class="text-sm">' . $row->role_for . '</p>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.hr.roles.edit', $row->id) . '" class="btn btn-sm btn-light rounded-circle me-1"><i class="fa fa-edit text-success"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'role_for', 'action'])
                ->make(true);
        }
        return view('admin.hr.roles.index');
    }

    public function create()
    {
        return view('admin.hr.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);
        $name = str_replace(' ', '', strtolower($request->name));

        Role::create([
            'name' => $request->input('name'),
            'role_for' => $name,
            'guard_name' => 'admin' 
        ]);
        return redirect(route('admin.hr.roles.index'))->with('success', 'Role created successfully');
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $permission = Permission::where('guard_name', $role->guard_name)->get();
        $rolePermissions = DB::table("role_has_permissions")
            ->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        $userAuth = auth()->user();
        if($userAuth->is_admin && !isset($userAuth->organization_id)){
            $department = HrDepartment::get();
            $designation = Designation::get();
            $roles = Role::get();
        }else{
            $department = HrDepartment::where('organization_id', $userAuth->organization_id)->get();
            $designation = Designation::where('organization_id', $userAuth->organization_id)->get();
            $roles = Role::where('role_for', '!=', 'superadmin')->orderBy('id', 'desc')->get();
        }
        
        return view('admin.hr.roles.edit', compact('role', 'permission', 'rolePermissions', 'department', 'designation', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->input('name');
        $role->save();

        if($request->has('department_id') || $request->has('designation_id') || $request->has('working_days')) {
            $query = Admin::query();
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->filled('designation_id')) {
                $query->where('designation_id', $request->designation_id);
            }
            if ($request->filled('working_days')) {
                $query->whereIn('id', $request->working_days);
            }

            $users = $query->where('role', '!=', 'admin')->get(); 
            foreach ($users as $user) {
                $user->syncRoles([$role->name]);
                $user->role = $role->name;
                $user->save();
                
                $permissions = $request->permission;
                $permissionUpdate = array_map('intval', $permissions);
                $user->syncPermissions($permissionUpdate);
            }
        }

        $permissionUpdate = array_map('intval', $request->permission);
        $role->syncPermissions($permissionUpdate);
        
        // Clear cached permissions so changes take effect immediately
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect(route('admin.hr.roles.index'))->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        Role::find($id)->delete();
        return redirect(route('admin.hr.roles.index'))->with('success', 'Role deleted successfully');
    }
}
