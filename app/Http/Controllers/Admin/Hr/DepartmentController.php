<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrDepartment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            if($user->is_admin && !isset($user->organization_id)){
                $data = HrDepartment::get();
            }else{
                $data = HrDepartment::where('organization_id', $user->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm fw-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('created_at', function ($row) {
                    return '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.hr.departments.edit', encrypt($row->id)) . '" class="btn btn-sm btn-light rounded-circle me-1"><i class="fa fa-edit text-success"></i></a>';
                    
                    $btn .= '<form method="POST" action="' . route('admin.hr.departments.destroy', encrypt($row->id)) . '" class="m-0 p-0 d-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm btn-light rounded-circle confirm-button"><i class="fa fa-trash text-danger"></i></button>
                    </form>';
                    
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'created_at', 'action'])
                ->make(true);
        }
        return view('admin.hr.departments.index');
    }

    public function create()
    {
        return view('admin.hr.departments.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            HrDepartment::create([
                'name' => $request->name,
                'organization_id' => auth()->user()->organization_id,
            ]);
            return redirect(route('admin.hr.departments.create'))->with('success', 'Department added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $departmentId = decrypt($id);
        $department = HrDepartment::find($departmentId);
        if ($department) {
            return view('admin.hr.departments.edit', compact('department'));
        }
        return redirect()->back()->with('error', ' Department not found,');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            $departmentId = decrypt($id);
            $department = HrDepartment::findOrFail($departmentId);
            $department->update([
                'name' => $request->name,
                'is_parent' => $request->parent == 'self_parent' ? 'yes' : 'no',
                'parent_id' => $request->parent == 'self_parent' ? null : $request->parent,
            ]);
            return redirect(route('admin.hr.departments.index'))->with('success', 'Department updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $departmentId = decrypt($id);
        $delete = HrDepartment::find($departmentId);
        if ($delete) {
            $delete->delete();
            return redirect()->back()->with('success', 'Department deleted successfully');
        }
        return redirect()->back()->with('error', 'Department cannot delete');
    }
}
