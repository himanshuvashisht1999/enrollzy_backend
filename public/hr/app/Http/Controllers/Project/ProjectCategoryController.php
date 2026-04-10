<?php

namespace App\Http\Controllers\Project;

use Illuminate\Http\Request;
use App\Models\ProjectCategory;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Validator;

class ProjectCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = ProjectCategory::get();
            }else{
                $data = ProjectCategory::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
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

                    $btn .= '<a href="' . route('admin.projectCategory.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';

                    $btn .= '<form method="POST" action="' . route('admin.projectCategory.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['name', 'created_at', 'action'])
                ->make(true);
        }

        return view('project.projectCategory.index');
    }

    public function create()
    {
        return view('project.projectCategory.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $pCategory = [
            'name' => $request->name,
            'description' => $request->description,
            'organization_id' => Auth::guard('admin')->user()->organization_id,
        ];
        try {
            ProjectCategory::create($pCategory);
            return redirect(route('admin.projectCategory.index'))->with('success', 'Project Category added successfully');
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
        $pCatId = decrypt($id);
        $projectCat = ProjectCategory::find($pCatId);
        if ($projectCat) {
            return view('project.projectCategory.edit', compact('projectCat'));
        }
        return redirect()->back()->with('error', 'Project Category not found,');
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
            $leadSourceId = decrypt($id);
            $lSource = ProjectCategory::findOrFail($leadSourceId);
            $updateData = $request->only([
                'name',
            ]);
            if ($lSource->update($updateData)) {
                return redirect(route('admin.projectCategory.index'))->with('success', 'Project Category updated successfully');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $pCatId = decrypt($id);
        $delete = ProjectCategory::find($pCatId);
        if ($delete) {
            $delete->delete();
            return redirect()->back()->with('success', 'Project Category deleted successfully');
        }
        return redirect()->back()->with('error', 'Project Category cannot delete');
    }
}
