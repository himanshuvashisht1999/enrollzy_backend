<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class MilestoneController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = Milestone::with('project')->where('organization_id', $organization_id)->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="fw-bold mb-0">' . $row->title . '</p>';
                })
                ->addColumn('project', function ($row) {
                    return $row->project->title ?? 'N/A';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.hr.projects.milestones.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.hr.projects.milestones.destroy', encrypt($row->id)) . '" class="ms-1 delete-form">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'status', 'action'])
                ->make(true);
        }

        return view('admin.hr.projects.milestone.index');
    }

    public function create()
    {
        $project = Project::where('organization_id', auth()->user()->organization_id)->get();
        return view('admin.hr.projects.milestone.create', compact('project'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'status' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            $data['organization_id'] = auth()->user()->organization_id;
            
            Milestone::create($data);
            return redirect()->route('admin.hr.projects.milestones.index')->with('success', 'Milestone added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $milestone = Milestone::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $project = Project::where('organization_id', auth()->user()->organization_id)->get();
        return view('admin.hr.projects.milestone.edit', compact('milestone', 'project'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $milestone = Milestone::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'status' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', '_method']);
            $milestone->update($data);
            return redirect()->route('admin.hr.projects.milestones.index')->with('success', 'Milestone updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $milestone = Milestone::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $milestone->delete();
        return redirect()->back()->with('success', 'Milestone deleted successfully');
    }
}
