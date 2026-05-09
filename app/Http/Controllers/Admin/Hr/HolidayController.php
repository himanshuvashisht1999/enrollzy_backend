<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\HrDepartment as Department;
use Yajra\DataTables\DataTables;
use App\Models\Designation;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            if ($user->is_admin && !isset($user->organization_id)) {
                $data = Holiday::get();
            } else {
                $data = Holiday::where('organization_id', $user->organization_id)->get();
            }

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('date', function ($row) {
                    return '<p class="text-sm">' . date('d M, Y ', strtotime($row->date)) . '</p>';
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
                    $btn .= '<a href="' . route('admin.hr.holidays.edit', encrypt($row->id)) . '" class="btn btn-sm"><i class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';
                    $btn .= '<form method="POST" action="' . route('admin.hr.holidays.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i class="fa fa-trash text-danger"></i></button>
                        </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'date', 'department', 'designation', 'action'])
                ->make(true);
        }
        return view('admin.hr.holidays.index');
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->is_admin && !isset($user->organization_id)) {
            $department  = Department::all();
            $designation = Designation::all();
        } else {
            $department  = Department::where('organization_id', $user->organization_id)->get();
            $designation = Designation::where('organization_id', $user->organization_id)->get();
        }
        return view('admin.hr.holidays.create', compact('department', 'designation'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date' => 'required|date|after:' . now()->toDateString(),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
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
        try {
            Holiday::create([
                'name' => $request->name,
                'date' => $request->date,
                'department_ids' => $departmentIds,
                'designation_ids' => $designationIDs,
                'staff_ids' => $staffIDs,
                'description' => $request->description,
                'organization_id' => auth()->user()->organization_id,
            ]);
            return redirect(route('admin.hr.holidays.index'))->with('success', 'Holiday added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $hID = decrypt($id);
        try {
            $holiday = Holiday::findOrFail($hID);
            $user = auth()->user();
            if ($user->is_admin && !isset($user->organization_id)) {
                $department = Department::all();
                $designation = Designation::all();
            } else {
                $department = Department::where('organization_id', $user->organization_id)->get();
                $designation = Designation::where('organization_id', $user->organization_id)->get();
            }
            $users = Admin::whereIn('department_id', explode(',', $holiday->department_ids))
                ->whereIn('designation_id', explode(',', $holiday->designation_ids))
                ->get();
            return view('admin.hr.holidays.edit', compact('holiday','department','designation','users'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $hID = decrypt($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            $holiday = Holiday::findOrFail($hID);

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
            $holiday->update([
                'name' => $request->name,
                'date' => $request->date,
                'department_ids' => $departmentIds,
                'designation_ids' => $designationIDs,
                'staff_ids' => $staffIDs,
                'description' => $request->description
            ]);
            return redirect(route('admin.hr.holidays.index'))->with('success', 'Holiday updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $hID = decrypt($id);
        try {
            $holiday = Holiday::findOrFail($hID);
            $holiday->delete();
            return redirect(route('admin.hr.holidays.index'))->with('success', 'Holiday deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }
}
