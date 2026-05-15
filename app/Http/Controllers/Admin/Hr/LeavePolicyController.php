<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeavePolicy;
use App\Models\HrDepartment as Department;
use Yajra\DataTables\DataTables;
use App\Models\Designation;
use App\Models\LeaveSetting;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class LeavePolicyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            if ($user->is_admin && !isset($user->organization_id)) {
                $data = LeavePolicy::orderBy('created_at', 'desc')->get();
            } else {
                $data = LeavePolicy::where('organization_id', $user->organization_id)->orderBy('created_at', 'desc')->get();
            }

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('department', function ($row) {
                    $departmentIds = explode(',', $row->department_ids);
                    $departmentNames = Department::whereIn('id', $departmentIds)->pluck('name')->toArray();
                    return '<p class="text-sm font-weight-bold mb-0">' . implode(', ', $departmentNames) . '</p>';
                })
                ->addColumn('designation', function ($row) {
                    $designationIds = explode(',', $row->designation_ids);
                    $designationNames = Designation::whereIn('id', $designationIds)->pluck('name')->toArray();
                    return '<p class="text-sm font-weight-bold mb-0">' . implode(', ', $designationNames) . '</p>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.hr.leave-policies.edit', encrypt($row->id)) . '" class="btn btn-sm"><i class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';
                    $btn .= '<form method="POST" action="' . route('admin.hr.leave-policies.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i class="fa fa-trash text-danger"></i></button>
                        </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'department', 'designation', 'action'])
                ->make(true);
        }
        return view('admin.hr.leave_policies.index');
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->is_admin && !isset($user->organization_id)) {
            $department = Department::all();
            $designation = Designation::all();
        } else {
            $department = Department::where('organization_id', $user->organization_id)->get();
            $designation = Designation::where('organization_id', $user->organization_id)->get();
        }

        return view('admin.hr.leave_policies.create', compact('department', 'designation'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'policy' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $departmentIds = null;
            $designationIDs = null;
            $staffIDs = null;
            if($request->department_id){
                $departmentIds = implode(',', $request->department_id);
            }
            if($request->designation_id){
                $designationIDs = implode(',', $request->designation_id);
            }
            if($request->working_days){
                $staffIDs = implode(',', $request->working_days);
            }

            LeavePolicy::create([
                'name' => $request->name,
                'policy' => $request->policy,
                'department_ids' => $departmentIds,
                'designation_ids' => $designationIDs,
                'staff_ids' => $staffIDs,
                'organization_id' => auth()->user()->organization_id,
            ]);
            return redirect(route('admin.hr.leave-policies.index'))->with('success', 'Leave Policy created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $lType = decrypt($id);
        try {
            $lSetting = LeavePolicy::find($lType);
            $user = auth()->user();
            if ($user->is_admin && !isset($user->organization_id)) {
                $department = Department::all();
                $designation = Designation::all();
            } else {
                $department = Department::where('organization_id', $user->organization_id)->get();
                $designation = Designation::where('organization_id', $user->organization_id)->get();
            }
            $users = Admin::whereIn('department_id', explode(',', $lSetting->department_ids))
                ->whereIn('designation_id', explode(',', $lSetting->designation_ids))
                ->get();
            return view('admin.hr.leave_policies.edit', compact('lSetting', 'department', 'designation','users'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'department' => 'required',
            'designation' => 'required',
            'working_days' => 'required',
            'policy' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $leaveTypeId = decrypt($id);
        try {
            $exLeaveType = LeavePolicy::findOrFail($leaveTypeId);

            $departmentIds = null;
            $designationIDs = null;
            $staffIDs = null;
            if($request->department){
                $departmentIds = implode(',', $request->department);
            }
            if($request->designation){
                $designationIDs = implode(',', $request->designation);
            }
            if($request->working_days){
                $staffIDs = implode(',', $request->working_days);
            }
            $exLeaveType->update([
                'name' => $request->name,
                'policy' => $request->policy,
                'department_ids' => $departmentIds,
                'designation_ids' => $designationIDs,
                'staff_ids' => $staffIDs,
            ]);
            return redirect(route('admin.hr.leave-policies.index'))->with('success', 'Leave Policy updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $hID = decrypt($id);
        try {
            $policy = LeavePolicy::findOrFail($hID);
            $policy->delete();
            return redirect(route('admin.hr.leave-policies.index'))->with('success', 'Leave Policy deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }
}
