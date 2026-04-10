<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Department;
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
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Department::get();
            }else{
                $data = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                    return $name;
                })
                ->addColumn('created_at', function ($row) {
                    $created_at = '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                    return $created_at;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    if (auth()->user()->can('department-add') || auth()->user()->can('department-edit')) {
                        $btn .= '<a href="' . route('admin.department.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-edit text-success"></i></a>';
                        $btn .= ' | ';
                    }
                    if (auth()->user()->can('department-delete')) {
                        $btn .= '<form method="POST" action="' . route('admin.department.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';
                    }
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['name', 'created_at', 'action'])
                ->make(true);
        }
        return view('hr.department.index');
    }

    public function create()
    {
        return view('hr.department.create');
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
            $department = Department::create([
                'name' => $request->name,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.department.index'))->with('success', 'Department added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $departmentId = decrypt($id);
        $department = Department::find($departmentId);
        if ($department) {
            return view('hr.department.edit', compact('department'));
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
            $department = Department::findOrFail($departmentId);
            $department->update([
                'name' => $request->name,
                'is_parent' => $request->parent == 'self_parent' ? 'yes' : 'no',
                'parent_id' => $request->parent == 'self_parent' ? null : $request->parent,
            ]);
            return redirect(route('admin.department.index'))->with('success', 'Department updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $departmentId = decrypt($id);
        $delete = Department::find($departmentId);
        if ($delete) {
            $delete->delete();
            return redirect()->back()->with('success', 'Department deleted successfully');
        }
        return redirect()->back()->with('error', 'Department cannot delete');
    }
}
