<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrDepartment;
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
            $user = auth()->user();
            if($user->is_admin && !isset($user->organization_id)){
                $data = Designation::with('department')->get();
            }else{
                $data = Designation::with('department')->where('organization_id', $user->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm fw-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('department', function ($row) {
                    return '<p class="text-sm">' . ($row->department->name ?? 'N/A') . '</p>';
                })
                ->addColumn('created_at', function ($row) {
                    return '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.hr.designations.edit', encrypt($row->id)) . '" class="btn btn-sm btn-light rounded-circle me-1"><i class="fa fa-edit text-success"></i></a>';
                    
                    $btn .= '<form method="POST" action="' . route('admin.hr.designations.destroy', encrypt($row->id)) . '" class="m-0 p-0 d-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm btn-light rounded-circle confirm-button"><i class="fa fa-trash text-danger"></i></button>
                    </form>';
                    
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'department', 'created_at', 'action'])
                ->make(true);
        }
        return view('admin.hr.designations.index');
    }

    public function create()
    {
        $user = auth()->user();
        if($user->is_admin && !isset($user->organization_id)){
            $department = HrDepartment::get();
        }else{
            $department = HrDepartment::where('organization_id', $user->organization_id)->get();
        }
        return view('admin.hr.designations.create', compact('department'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            Designation::create([
                'department_id' => $request->department_id,
                'name' => $request->name,
                'organization_id' => auth()->user()->organization_id,
            ]);
            return redirect(route('admin.hr.designations.create'))->with('success', 'Designation added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $designationId = decrypt($id);
        $designation = Designation::find($designationId);

        if ($designation) {
            $user = auth()->user();
            if($user->is_admin && !isset($user->organization_id)){
                $department = HrDepartment::get();
            }else{
                $department = HrDepartment::where('organization_id', $user->organization_id)->get();
            }
            
            return view('admin.hr.designations.edit', compact('designation', 'department'));
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
        try {
            $designation = Designation::findOrFail($designationId);
            $designation->update([
                'name' => $request->name,
                'department_id' => $request->department_id,
            ]);
            return redirect(route('admin.hr.designations.index'))->with('success', 'Designation updated successfully');
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
