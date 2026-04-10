<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Designation::get();
            }else{
                $data = Designation::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                    return $name;
                })
                ->addColumn('department', function ($row) {
                    $department = '<p class="text-sm">' . $row->department->name . '</p>';
                    return $department;
                })
                ->addColumn('created_at', function ($row) {
                    $created_at = '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                    return $created_at;
                })
                ->addColumn('action', function ($row) {

                    $btn = '<div class="d-flex">';
                    if (auth()->user()->can('designation-edit') || auth()->user()->can('designation-read')) {
                        $btn .= '<a href="' . route('admin.designation.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                            class="fa fa-edit text-success"></i></a>';
                        $btn .= ' | ';
                    }
                    if (auth()->user()->can('designation-delete')) {
                        $btn .= '<form method="POST" action="' . route('admin.designation.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'department', 'created_at', 'action'])
                ->make(true);
        }
        return view('hr.designation.index');
    }

    public function create()
    {

        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $department = Department::get();
        }else{
            $department = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }
        return view('hr.designation.create', compact('department'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:department,id',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $createDesignation = [
            'department_id' => $request->department_id,
            'name' => $request->name,
            'organization_id' => Auth::guard('admin')->user()->organization_id,
        ];
        try {
            $designation = Designation::create($createDesignation);
            return redirect(route('admin.designation.index'))->with('success', 'Designation added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $designationId = decrypt($id);
        $designation = Designation::find($designationId);

        if ($designation) {
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $department = Department::get();
            }else{
                $department = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            
            return view('hr.designation.edit', compact('designation', 'department'));
        }
        return redirect()->back()->with('error', 'Designation not found,');
    }

    public function update(Request $request, $id)
    {
        $designationId = decrypt($id);
        $validator = Validator::make($request->all(), [
            'department_id' => 'required',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $designationData = $request->only([
            'name',
            'department_id',
        ]);
        try {
            $designation = Designation::findOrFail($designationId);
            if ($designation->update($designationData)) {
                return redirect(route('admin.designation.index'))->with('success', 'Designation updated successfully');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $designationId = decrypt($id);
        $delete = Designation::find($designationId);
        if ($delete) {
            $delete->delete();
            return redirect()->back()->with('success', 'Designation deleted successfully');
        }
        return redirect()->back()->with('error', 'Designation cannot delete');
    }
}
