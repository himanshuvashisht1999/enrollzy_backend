<?php

namespace App\Http\Controllers\Hr;

use Exception;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Admin;
use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Holiday::get();
            }else{
                $data = Holiday::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
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
                    $btn .= '<a href="' . route('admin.holidays.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                            class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';
                    $btn .= '<form method="POST" action="' . route('admin.holidays.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'date', 'department', 'designation', 'action'])
                ->make(true);
        }
        return view('hr.holidays.index');
    }

    public function create()
    {

        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $department  = Department::all();
            $designation = Designation::all();
        }else{
            $department  = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $designation = Designation::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }
        return view('hr.holidays.create', compact('department', 'designation'));
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
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.holidays.index'))->with('success', 'Holiday added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong,' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $hID = decrypt($id);
        $department  = Department::all();
        $designation = Designation::all();
        try {
            $holiday = Holiday::findOrFail($hID);

        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $department = Department::all();
            $designation = Designation::all();
        }else{
            $department = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $designation = Designation::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }
            $users = Admin::whereIn('department_id', explode(',', $holiday->department_ids))
            ->whereIn('designation_id', explode(',', $holiday->designation_ids))
            ->get();
            return view('hr.holidays.edit', compact('holiday','department','designation','users'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
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
            return redirect(route('admin.holidays.index'))->with('success', 'Holiday updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $hID = decrypt($id);
        try {
            $holiday = Holiday::findOrFail($hID);
            $holiday->delete();
            return redirect(route('admin.holidays.index'))->with('success', 'Holiday deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }
}
