<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\ProjectType;
use App\Models\Project;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProjectTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $query = ProjectType::withCount('projects');

            if ($user->organization_id) {
                $query->where(function ($q) use ($user) {
                    $q->where('organization_id', $user->organization_id)
                      ->orWhereNull('organization_id');
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $badgeClass = $row->getBadgeClass();
                    $code = $row->code ? '<span class="badge bg-light text-secondary border me-2">' . e($row->code) . '</span>' : '';
                    return '<div class="d-flex align-items-center gap-2">' .
                           '<span class="badge ' . $badgeClass . ' px-3 py-2 fs-6 rounded-3">' . e($row->name) . '</span>' .
                           $code .
                           '</div>';
                })
                ->addColumn('color', function ($row) {
                    $colorName = ucfirst($row->color ?? 'primary');
                    return '<span class="badge bg-soft-' . ($row->color ?? 'primary') . ' text-' . ($row->color ?? 'primary') . ' border border-' . ($row->color ?? 'primary') . ' px-2 py-1"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> ' . $colorName . '</span>';
                })
                ->addColumn('description', function ($row) {
                    return '<span class="text-muted">' . ($row->description ? e(\Illuminate\Support\Str::limit($row->description, 60)) : '<em class="text-secondary small">No description provided</em>') . '</span>';
                })
                ->addColumn('projects_count', function ($row) {
                    return '<span class="badge bg-soft-info text-info px-3 py-2 rounded-pill"><i class="fas fa-folder-open me-1"></i> ' . $row->projects_count . ' projects</span>';
                })
                ->addColumn('status', function ($row) {
                    return $row->status === 'active'
                        ? '<span class="badge bg-soft-success text-success border border-success"><i class="fas fa-check-circle me-1"></i> Active</span>'
                        : '<span class="badge bg-soft-secondary text-secondary border"><i class="fas fa-pause-circle me-1"></i> Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-soft-primary edit-type-btn" ' .
                            'data-id="' . encrypt($row->id) . '" ' .
                            'data-name="' . e($row->name) . '" ' .
                            'data-code="' . e($row->code ?? '') . '" ' .
                            'data-color="' . e($row->color ?? 'primary') . '" ' .
                            'data-status="' . e($row->status ?? 'active') . '" ' .
                            'data-description="' . e($row->description ?? '') . '" ' .
                            'title="Edit Project Type"><i class="fas fa-edit"></i></button>';
                    $btn .= '<form method="POST" action="' . route('admin.work_management.types.destroy', encrypt($row->id)) . '" class="ms-1 delete-form" onsubmit="return confirm(\'Are you sure you want to delete this project type?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'color', 'description', 'projects_count', 'status', 'action'])
                ->make(true);
        }

        return view('admin.work_management.types.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $type = ProjectType::create([
                'name' => $request->name,
                'code' => $request->code ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->name), 0, 4)),
                'color' => $request->color ?: 'primary',
                'status' => $request->status ?: 'active',
                'description' => $request->description,
                'organization_id' => auth()->user()->organization_id ?? null,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project Type created successfully',
                    'type' => $type,
                ]);
            }

            return redirect()->route('admin.work_management.types.index')->with('success', 'Project Type created successfully');
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
            'code' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $type = ProjectType::create([
                'name' => $request->name,
                'code' => $request->code ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->name), 0, 4)),
                'color' => $request->color ?: 'primary',
                'status' => 'active',
                'description' => $request->description,
                'organization_id' => auth()->user()->organization_id ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Project Type created successfully',
                'type' => $type,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $type = ProjectType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $type->update([
                'name' => $request->name,
                'code' => $request->code ?: $type->code,
                'color' => $request->color ?: 'primary',
                'status' => $request->status ?: 'active',
                'description' => $request->description,
            ]);

            return redirect()->route('admin.work_management.types.index')->with('success', 'Project Type updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $type = ProjectType::findOrFail($id);

        if ($type->projects()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete this project type because it is currently assigned to ' . $type->projects()->count() . ' project(s).');
        }

        $type->delete();
        return redirect()->back()->with('success', 'Project Type deleted successfully');
    }
}
