<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\HrDepartment;
use App\Models\Designation;
use App\Models\Organization;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Role;
use App\Models\StaffType;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $query = Admin::with(['department', 'designation']);

            if (!$user->is_admin || isset($user->organization_id)) {
                $query->where('organization_id', $user->organization_id);
            }

            if ($request->filled('role')) {
                $role = $request->role;
                if($role == 'organisation'){
                    $query->whereNotNull('organization_id');
                }else{
                    $query->where('role', $role);
                }
            }

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            $data = $query->get();
            
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm fw-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('roles', function ($row) {
                    $user = auth()->user();
                    if($user->is_admin && !isset($user->organization_id)){
                        $roles = Role::pluck('name', 'id')->all();
                    }else{
                        $roles = Role::whereNot('name', 'superadmin')->pluck('name', 'id')->all();
                    }
                    $selectedRole = $row->roles->pluck('name')->first();
                    $select = '<select class="form-select form-select-sm text-capitalize role-change" data-user-id="' . $row->id . '">';
                    $select .= '<option value="">Select Role</option>';
                    foreach ($roles as $roleId => $roleName) {
                        $selected = ($selectedRole == $roleName) ? 'selected disabled' : '';
                        $select .= '<option value="' . $roleId . '" ' . $selected . '>' . $roleName . '</option>';
                    }
                    $select .= '</select>';
                    return $select;
                })
                ->addColumn('designation', function ($row) {
                    return '<p class="text-sm">' . ($row->designation->name ?? 'Not Assigned') . '</p>';
                })
                ->addColumn('department', function ($row) {
                    return '<p class="text-sm">' . ($row->department->name ?? 'Not Assigned') . '</p>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('created_at', function ($row) {
                    return '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.hr.staff.edit', encrypt($row->id)) . '" class="btn btn-sm btn-light rounded-circle me-1"><i class="fa fa-pen text-primary"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'roles', 'department', 'designation', 'status', 'created_at', 'action'])
                ->make(true);
        }
        return view('admin.hr.staff.index');
    }

    public function create()
    {
        $user = auth()->user();
        if($user->is_admin && !isset($user->organization_id)){
            $department = HrDepartment::get();
            $designation = Designation::get();
            $Organization = Organization::get();
            $roles = Role::where('guard_name', 'admin')->get();
            $managers = Admin::where('status', 1)->get();
        }else{
            $department = HrDepartment::where('organization_id', $user->organization_id)->get();
            $designation = Designation::where('organization_id', $user->organization_id)->get();
            $Organization = Organization::where('id', $user->organization_id)->get();
            $roles = Role::where('guard_name', 'admin')->where('name', '!=', 'superadmin')->get();
            $managers = Admin::where('organization_id', $user->organization_id)->where('status', 1)->get();
        }
        $staffTypes = StaffType::where('status', 1)->get();
        return view('admin.hr.staff.create', compact('department', 'designation', 'Organization', 'roles', 'staffTypes', 'managers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:admin,username',
            'name' => 'required',
            'shift_hours' => 'required',
            'email' => 'required|unique:admin,email',
            'phone' => 'required|unique:admin,phone',
            'working_days' => 'required',
            'password' => 'required|min:8|confirmed',
            'dob' => 'required|date',
            'joining_date' => 'required|date',
            'status' => 'required',
            'department_id' => 'required',
            'designation_id' => 'required',
            'marital_status' => 'required',
            'employment_type' => 'required',
            'rolename' => 'required',
            'salary' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $fileName = time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
            $destinationPath = public_path('staff_images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $fileName);
            $profileImagePath = 'staff_images/' . $fileName;
        }

        try {
            $staff = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'joining_date' => $request->joining_date,
                'working_days' => implode(',', $request->working_days ?? []),
                'salary' => $request->salary,
                'gender' => $request->gender,
                'password' => Hash::make($request->password),
                'dob' => $request->dob,
                'status' => $request->status,
                'profile_image' => $profileImagePath,
                'address' => $request->address,
                'about' => $request->about,
                'role' => $request->rolename,
                'username' => $request->username,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'shift_hours' => $request->shift_hours,
                'pay_based' => $request->pay_based,
                'marital_status' => $request->marital_status,
                'probation_end_date' => $request->probation_end_date,
                'employment_type' => $request->employment_type,
                'staff_type_id' => $request->staff_type_id,
                'manager_id' => $request->manager_id,
                'organization_id' => auth()->user()->organization_id,
            ]);

            $staff->assignRole($request->rolename);
            return redirect(route('admin.hr.staff.create'))->with('success', 'Staff Added Successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $staffId = decrypt($id);
        $staff = Admin::find($staffId);
        $user = auth()->user();
        
        if($user->is_admin && !isset($user->organization_id)){
            $department = HrDepartment::get();
            $designation = Designation::get();
            $roles = Role::where('guard_name', 'admin')->get();
            $managers = Admin::where('status', 1)->where('id', '!=', $staffId)->get();
        }else{
            $department = HrDepartment::where('organization_id', $user->organization_id)->get();
            $designation = Designation::where('organization_id', $user->organization_id)->get();
            $roles = Role::where('guard_name', 'admin')->where('name', '!=', 'superadmin')->get();
            $managers = Admin::where('organization_id', $user->organization_id)->where('status', 1)->where('id', '!=', $staffId)->get();
        }
        $staffTypes = StaffType::where('status', 1)->get();
        return view('admin.hr.staff.edit', compact('staff', 'department', 'designation', 'roles', 'staffTypes', 'managers'));
    }

    public function update(Request $request, $id)
    {
        $staffId = decrypt($id);
        $staff = Admin::findOrFail($staffId);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|string|unique:admin,username,' . $staffId,
            'email' => 'required|unique:admin,email,' . $staffId,
            'phone' => 'required|unique:admin,phone,' . $staffId,
            'status' => 'required',
            'department_id' => 'required',
            'designation_id' => 'required',
            'salary' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        $profileImagePath = $staff->profile_image;
        if ($request->hasFile('profile_image')) {
            if ($staff->profile_image && file_exists(public_path($staff->profile_image))) {
                @unlink(public_path($staff->profile_image));
            }
            $file = $request->file('profile_image');
            $fileName = time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
            $file->move(public_path('staff_images'), $fileName);
            $profileImagePath = 'staff_images/' . $fileName;
        }

        try {
            $updateData = [
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'joining_date' => $request->joining_date,
                'working_days' => implode(',', $request->working_days ?? []),
                'salary' => $request->salary,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'status' => $request->status,
                'address' => $request->address,
                'about' => $request->about,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'shift_hours' => $request->shift_hours,
                'pay_based' => $request->pay_based,
                'marital_status' => $request->marital_status,
                'probation_end_date' => $request->probation_end_date,
                'employment_type' => $request->employment_type,
                'staff_type_id' => $request->staff_type_id,
                'manager_id' => $request->manager_id,
                'profile_image' => $profileImagePath,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $staff->update($updateData);

            if ($request->filled('rolename')) {
                $staff->syncRoles([$request->rolename]);
                $staff->syncPermissions([]);
                $staff->role = $request->rolename;
                $staff->save();
            }

            return redirect(route('admin.hr.staff.index'))->with('success', 'Staff updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function changeStaffRole(Request $request)
    {
        $user = Admin::find($request->staff_id);
        $role = Role::find($request->role_id);
        if (!$user || !$role) {
            return response()->json(['status' => 0, 'message' => 'Staff or Role not found']);
        }
        try {
            $user->syncRoles([$role->name]);
            $user->syncPermissions([]);
            $user->role = $role->name;
            $user->save();
            return response()->json(['status' => 1, 'message' => $role->name . ' Role Assigned to ' . $user->name]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }
}
