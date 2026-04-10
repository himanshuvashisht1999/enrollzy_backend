<?php

namespace App\Http\Controllers\Hr;

use Exception;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveSetting;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class LeaveSettingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

                if(Auth::guard('admin')->user()->role === 'superadmin'){
                    $data = LeaveSetting::orderBy('created_at', 'desc')->get();
                }else{
                    $data = LeaveSetting::where('organization_id', Auth::guard('admin')->user()->organization_id)->orderBy('created_at', 'desc')->get();
                }

            
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('allotment_type', function ($row) {
                    return '<p class="text-sm font-weight-bold mb-0">' . $row->allotment_type . '</p>';
                })
                ->addColumn('yearly_leave', function ($row) {
                    return '<p class="text-sm font-weight-bold mb-0">' . $row->yearly_leave . '</p>';
                })
                ->addColumn('monthly_leave', function ($row) {
                    return '<p class="text-sm font-weight-bold mb-0">' . $row->monthly_leave . '</p>';
                })
                ->addColumn('pay_status', function ($row) {
                    return '<p class="text-sm font-weight-bold mb-0">' . $row->pay_status . '</p>';
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
                    $btn .= '<a href="' . route('admin.leaveSetting.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                            class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';
                    $btn .= '<form method="POST" action="' . route('admin.leaveSetting.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'allotment_type', 'yearly_leave', 'monthly_leave', 'pay_status', 'department', 'designation', 'action'])
                ->make(true);
        }
        return view('hr.leavesSetting.index');
    }

    public function create()
    {

        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $department = Department::all();
            $designation = Designation::all();
        }else{
            $department = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $designation = Designation::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }
        return view('hr.leavesSetting.create', compact('department', 'designation'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'allotment_type' => 'required',
            'yearly_leave' => 'required|numeric',
            'monthly_leave' => 'required|numeric',
            'pay_status' => 'required',
            'effective_after' => 'required|numeric',
            'unused_leave' => 'required',
            'over_utilization' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'department' => 'required',
            'designation' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        if ($request->allotment_type === 'yearly') {
            if ($request->monthly_leave > ($request->yearly_leave / 12)) {
                return redirect()->back()->with('error', 'Monthly leave cannot be more than yearly leave divided by 12')->withInput();
            }
        } elseif ($request->allotment_type === 'monthly') {
            if ($request->monthly_leave > 4) {
                return redirect()->back()->with('monthly_leave', 'Monthly leave cannot be more than 4')->withInput();
            }
        }
        try {
            LeaveSetting::create([
                'name' => $request->name,
                'allotment_type' => $request->allotment_type,
                'yearly_leave' => $request->yearly_leave,
                'monthly_leave' => $request->monthly_leave,
                'penalty' => $request->monthly_penalty,
                'pay_status' => $request->pay_status,
                'effective_after' => $request->effective_after,
                'unused_leave' => $request->unused_leave,
                'over_utilization' => $request->over_utilization,
                'allow_in_probation' => $request->allow_in_probation,
                'allow_in_noticePeroid' => $request->allow_in_noticePeroid,
                'gender' => implode(',', $request->gender),
                'marital_status' => implode(',', $request->marital_status),
                'department_ids' => implode(',', $request->department),
                'designation_ids' => implode(',', $request->designation),
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.leaveSetting.index'))->with('success', 'Leave Type created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $lType = decrypt($id);
        try {
            $lSetting = LeaveSetting::find($lType);
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $department = Department::all();
                $designation = Designation::all();
            }else{
                $department = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
                $designation = Designation::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return view('hr.leavesSetting.edit', compact('lSetting', 'department', 'designation'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'yearly_leave' => 'required|numeric',
            'monthly_leave' => 'required|numeric',
            'pay_status' => 'required',
            'effective_after' => 'required|numeric',
            'over_utilization' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'department' => 'required',
            'designation' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        if ($request->allotment_type === 'yearly') {
            if ($request->monthly_leave > ($request->yearly_leave / 12)) {
                return redirect()->back()->with('error', 'Monthly leave cannot be more than yearly leave divided by 12')->withInput();
            }
        } elseif ($request->allotment_type === 'monthly') {
            if ($request->monthly_leave > 4) {
                return redirect()->back()->with('monthly_leave', 'Monthly leave cannot be more than 4')->withInput();
            }
        }
        $leaveTypeId = decrypt($id);
        try {
            $exLeaveType = LeaveSetting::findOrFail($leaveTypeId);

            $exLeaveType->update([
                'name' => $request->name,
                'yearly_leave' => $request->yearly_leave,
                'monthly_leave' => $request->monthly_leave,
                'penalty' => $request->monthly_penalty,
                'pay_status' => $request->pay_status,
                'effective_after' => $request->effective_after,
                'over_utilization' => $request->over_utilization,
                'allow_in_probation' => $request->allow_in_probation,
                'allow_in_noticePeroid' => $request->allow_in_noticePeroid,
                'gender' => implode(',', $request->gender),
                'marital_status' => implode(',', $request->marital_status),
                'department_ids' => implode(',', $request->department),
                'designation_ids' => implode(',', $request->designation),
            ]);
            return redirect(route('admin.leaveSetting.index'))->with('success', 'Leave Type updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }


    public function destroy($id)
    {
        $hID = decrypt($id);
        try {
            $holiday = LeaveSetting::findOrFail($hID);
            $holiday->delete();
            return redirect(route('admin.leaveSetting.index'))->with('success', 'Leave Setting deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }
}
