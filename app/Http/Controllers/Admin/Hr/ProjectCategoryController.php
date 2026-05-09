<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProjectCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = ProjectCategory::where('organization_id', $organization_id)->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.hr.projects.project-categories.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.hr.projects.project-categories.destroy', encrypt($row->id)) . '" class="ms-1 delete-form">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.hr.projects.category.index');
    }

    public function create()
    {
        return view('admin.hr.projects.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            ProjectCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'organization_id' => auth()->user()->organization_id,
            ]);
            return redirect()->route('admin.hr.projects.project-categories.index')->with('success', 'Project Category added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $category = ProjectCategory::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return view('admin.hr.projects.category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $category = ProjectCategory::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);
            return redirect()->route('admin.hr.projects.project-categories.index')->with('success', 'Project Category updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $category = ProjectCategory::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $category->delete();
        return redirect()->back()->with('success', 'Project Category deleted successfully');
    }
}
