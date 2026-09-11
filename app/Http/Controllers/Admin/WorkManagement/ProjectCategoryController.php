<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use App\Models\Project;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProjectCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $query = ProjectCategory::withCount('projects');

            if ($user->organization_id) {
                $query->where(function ($q) use ($user) {
                    $q->where('organization_id', $user->organization_id)
                      ->orWhereNull('organization_id');
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<div class="d-flex align-items-center gap-2">' .
                           '<div class="avatar-xs rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">' .
                           substr($row->name, 0, 1) .
                           '</div>' .
                           '<div>' .
                           '<span class="fw-bold text-dark">' . e($row->name) . '</span>' .
                           '</div>' .
                           '</div>';
                })
                ->addColumn('description', function ($row) {
                    return '<span class="text-muted">' . ($row->description ? e(\Illuminate\Support\Str::limit($row->description, 60)) : '<em class="text-secondary small">No description provided</em>') . '</span>';
                })
                ->addColumn('projects_count', function ($row) {
                    return '<span class="badge bg-soft-info text-info px-3 py-2 rounded-pill"><i class="fas fa-folder-open me-1"></i> ' . $row->projects_count . ' projects</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-soft-primary edit-category-btn" ' .
                            'data-id="' . encrypt($row->id) . '" ' .
                            'data-name="' . e($row->name) . '" ' .
                            'data-description="' . e($row->description ?? '') . '" ' .
                            'title="Edit Category"><i class="fas fa-edit"></i></button>';
                    $btn .= '<form method="POST" action="' . route('admin.work_management.categories.destroy', encrypt($row->id)) . '" class="ms-1 delete-form" onsubmit="return confirm(\'Are you sure you want to delete this category?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'description', 'projects_count', 'action'])
                ->make(true);
        }

        return view('admin.work_management.categories.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $category = ProjectCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'organization_id' => auth()->user()->organization_id ?? null,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project Category created successfully',
                    'category' => $category,
                ]);
            }

            return redirect()->route('admin.work_management.categories.index')->with('success', 'Project Category created successfully');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function quickStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $category = ProjectCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'organization_id' => auth()->user()->organization_id ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $category = ProjectCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('admin.work_management.categories.index')->with('success', 'Project Category updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $category = ProjectCategory::findOrFail($id);

        if ($category->projects()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete this category because it is currently assigned to ' . $category->projects()->count() . ' project(s).');
        }

        $category->delete();
        return redirect()->back()->with('success', 'Project Category deleted successfully');
    }
}
