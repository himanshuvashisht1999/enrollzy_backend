<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Project;
use App\Models\LeadSource;
use App\Models\ProjectCategory;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProjectsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = Project::with(['lead_source', 'client', 'project_category'])
                ->where('organization_id', $organization_id)
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="fw-bold mb-0">' . Str::limit($row->title, 30) . '</p>';
                })
                ->addColumn('lead_source', function ($row) {
                    return $row->lead_source->name ?? 'N/A';
                })
                ->addColumn('client', function ($row) {
                    return $row->client->name ?? '<span class="text-muted small">Personal</span>';
                })
                ->addColumn('project_category', function ($row) {
                    return $row->project_category->name ?? 'N/A';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.hr.projects.index.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.hr.projects.index.destroy', encrypt($row->id)) . '" class="ms-1 delete-form">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'client', 'status', 'action'])
                ->make(true);
        }

        return view('admin.hr.projects.project.index');
    }

    public function create()
    {
        $organization_id = auth()->user()->organization_id;
        $leadSource = LeadSource::where('organization_id', $organization_id)->get();
        $projectCategory = ProjectCategory::where('organization_id', $organization_id)->get();
        $client = Client::where('organization_id', $organization_id)->get();
        $staff = Admin::where('organization_id', $organization_id)->get();

        return view('admin.hr.projects.project.create', compact('leadSource', 'projectCategory', 'client', 'staff'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category_id' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'lead_source_id' => 'required',
            'status' => 'required',
            'employee_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            $data['employee_ids'] = implode(',', $request->employee_ids);
            $data['staff_id'] = auth()->id();
            $data['organization_id'] = auth()->user()->organization_id;

            Project::create($data);
            return redirect()->route('admin.hr.projects.index.index')->with('success', 'Project added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $organization_id = auth()->user()->organization_id;
        $project = Project::where('organization_id', $organization_id)->findOrFail($id);
        
        $leadSource = LeadSource::where('organization_id', $organization_id)->get();
        $projectCategory = ProjectCategory::where('organization_id', $organization_id)->get();
        $client = Client::where('organization_id', $organization_id)->get();
        $staff = Admin::where('organization_id', $organization_id)->get();

        return view('admin.hr.projects.project.edit', compact('project', 'leadSource', 'projectCategory', 'client', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $project = Project::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category_id' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'lead_source_id' => 'required',
            'status' => 'required',
            'employee_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', '_method']);
            $data['employee_ids'] = implode(',', $request->employee_ids);
            
            $project->update($data);
            return redirect()->route('admin.hr.projects.index.index')->with('success', 'Project updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $project = Project::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $project->delete();
        return redirect()->back()->with('success', 'Project deleted successfully');
    }
}
