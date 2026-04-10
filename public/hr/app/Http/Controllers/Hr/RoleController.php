<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use FontLib\Table\Type\name;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:role-list', ['only' => ['store']]);
        // $this->middleware('permission:role-create', ['only' => ['store']]);
        // $this->middleware('permission:role-edit', ['only' => ['update']]);
        // $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            if(auth()->user()->role == "superadmin"){
                $data = Role::orderBy('id', 'desc')->get();
            }else{
                $data = Role::where('role_for', '!=', 'superadmin')
                ->orderBy('id', 'desc')
                ->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0 text-capitalize">' . $row->name . '</p>';
                    return $name;
                })
                ->addColumn('role_for', function ($row) {
                    $role_for = '<p class="text-sm">' . $row->role_for . '</p>';
                    return $role_for;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (auth()->user()->can('roles-read') || auth()->user()->can('roles-edit')) {
                        $btn = '<div class="d-flex">
                    <a href="' . route('admin.roles.edit', $row->id) . '" class="btn btn-primary btn-sm">edit </a>
                    </div>';
                    }
                    return $btn;
                })
                ->rawColumns(['name', 'role_for', 'created_at', 'action'])
                ->make(true);
        }

        return view('hr.roles.index');
    }

    public function create()
    {
        return view('hr.roles.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
        ]);
        $roleFor = $request->name = str_replace(' ', '', strtolower($request->name));

        Role::create([
            'name' => $request->input('name'),
            'role_for' => $roleFor,
            'guard_name' => $guardName ?? null
        ]);
        return redirect(route('admin.roles.index'))->with('success', 'Role created successfully');
    }

    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get();
        return view('hr.roles.show', compact('role', 'rolePermissions'));
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $permission = Permission::where('guard_name', $role->guard_name)->get();
        $rolePermissions = DB::table("role_has_permissions")
            ->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

            if(auth()->user()->role == "superadmin"){
                $users = Admin::orderBy('id', 'desc')->where('role', $role->role_for)->get();
                $department = Department::get();
                $designation = Designation::get();
                $roles= Role::get();
            }else{
                $users = Admin::whereNot('role', 'superadmin')->where('role', $role->role_for)->orderBy('id', 'desc')->get();
                $department = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
                $designation = Designation::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
                
                $roles = Role::where('role_for', '!=', 'superadmin')
                ->orderBy('id', 'desc')
                ->get();
            }
        return view('hr.roles.edit', compact('role', 'permission', 'rolePermissions','users','roles','department','designation'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');
        
        $role->save();
        $query = Admin::query(); // Start with the query builder for Admin model
    
        // First condition: Only department_id is provided
        if ($request->has('department_id') && !$request->has('designation_id') && !$request->has('working_days')) {
            $query->where('department_id', $request->department_id);
        }
        // Second condition: department_id and designation_id are provided
        elseif ($request->has('department_id') && $request->has('designation_id') && !$request->has('working_days')) {
            $query->where('department_id', $request->department_id)
                ->where('designation_id', $request->designation_id);
        }
        // Third condition: department_id, designation_id, and user_ids are provided
        elseif ($request->has('department_id') && $request->has('designation_id') && $request->has('working_days')) {
            $query->where('department_id', $request->department_id)->get()
            ->where('designation_id', $request->designation_id)
            ->whereIn('id', $request->working_days);
        }
        if($request->has('department_id') || $request->has('designation_id') || $request->has('working_days'))
        {
        $users = $query->whereNot('role','admin')->get(); 
        foreach ($users as $user) {
            // Detach all existing roles
            $user->roles()->detach();
            
            // Assign the new role to the user
            $user->assignRole($request->name);
            $user->role = $request->name;
            $user->save();
            
            // Assign specific permissions to users individually
            $permissions = isset($request->permissions[$user->id]) ? $request->permissions[$user->id] : $request->permission;
    
            // Sync permissions for this specific user
            $permissionUpdate = array_map('intval', $permissions);
            $user->syncPermissions($permissionUpdate);
        }
    }

        $permissionUpdate = array_map('intval', $request->permission);
        $role->syncPermissions($permissionUpdate);
        return redirect(route('admin.roles.index'))->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        Role::find($id)->delete();
        return redirect(route('admin.roles.index'))->with('success', 'Role deleted successfully');
    }

    public function getDesignations($department_id)
    {
        $designation_ids_array = explode(',', $department_id); 
        $designations = Designation::whereIn('department_id', $designation_ids_array)->get();
        
        // Return designations as JSON
        return response()->json([
            'designations' => $designations
        ]);
    }

    // Get users based on designation ID
    public function getUsers($designation_id)
        {
            $designation_ids_array = explode(',', $designation_id); 
            $users = Admin::whereIn('designation_id', $designation_ids_array)->whereNot('role', 'admin')->get();
        // Return users as JSON
        return response()->json([
            'users' => $users
        ]);
    }
}
