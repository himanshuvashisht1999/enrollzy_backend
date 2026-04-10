<?php

namespace App\Http\Controllers\Project;

use Exception;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Project;
use App\Models\LeadSource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProjectCategory;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Project::get();
            }else{
                $data = Project::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0">' . Str::limit($row->title, 20, '...') . '</p>';
                    return $name;
                })
                ->addColumn('lead_source', function ($row) {
                    $lead_source = '<p class="text-sm mb-0">' . $row->lead_source->name . '</p>';
                    return $lead_source;
                })
                ->addColumn('client', function ($row) {
                    if ($row->client) {
                        return '<p class="text-sm mb-0">' . $row->client->name . '</p>';
                    } else {
                        return '<p class="text-sm mb-0">Personal</p>'; // or return an empty string
                    }
                })
                ->addColumn('project_category', function ($row) {
                    $project_category = '<p class="text-sm mb-0">' . $row->project_category->name . '</p>';
                    return $project_category;
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('created_at', function ($row) {
                    $created_at = '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                    return $created_at;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';

                    $btn .= '<a href="' . route('admin.projects.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';

                    $btn .= '<form method="POST" action="' . route('admin.projects.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns([
                    'name',
                    'lead_source',
                    'client',
                    'project_category',
                    'status',
                    'created_at',
                    'action'
                ])
                ->make(true);
        }

        return view('project.index');
    }

    public function create()
    {
        
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $leadSource = LeadSource::get();
            $projectCategory = ProjectCategory::get();
            $client = Client::get();
            $staff = Admin::where('role', 'staff')->get();
        }else{
            $leadSource = LeadSource::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $projectCategory = ProjectCategory::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $client = Client::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $staff = Admin::where('organization_id', Auth::guard('admin')->user()->organization_id)->where('role', 'staff')->get();
        }
        return view('project.create', compact('leadSource', 'projectCategory', 'client', 'staff'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category_id' => 'required',
            'price' => 'nullable',
            'start_date' => 'required|date', // Make sure it's a valid date
            'due_date' => 'nullable|date|after_or_equal:start_date', // Ensure due_date is after or equal to start_date
            'lead_source_id' => 'required',
            'client_id' => 'nullable',
            'status' => 'required',
            'employee_ids' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $insertProject = [
            'title' => $request->title,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'lead_source_id' => $request->lead_source_id,
            'client_id' => $request->client_id,
            'status' => $request->status,
            'employee_ids' =>   implode(',', $request->employee_ids),
            'description' => $request->description,
            'staff_id' => Auth::guard('admin')->id(),
            'organization_id' => Auth::guard('admin')->user()->organization_id,
        ];
        try {
            Project::create($insertProject);
            return redirect(route('admin.projects.index'))->with('success', 'Project added successfully');
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
        $pId = decrypt($id);
        $project = Project::find($pId);
        if ($project) {
            
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $leadSource = LeadSource::get();
            $projectCategory = ProjectCategory::get();
            $client = Client::get();
            $staff = Admin::where('role', 'staff')->get();
        }else{
            $leadSource = LeadSource::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $projectCategory = ProjectCategory::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $client = Client::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $staff = Admin::where('organization_id', Auth::guard('admin')->user()->organization_id)->where('role', 'staff')->get();
        }
            return view('project.edit', compact('project', 'leadSource', 'projectCategory', 'client', 'staff'));
        }
        return redirect()->back()->with('error', 'Project not found,');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category_id' => 'required',
            'price' => 'nullable',
            'start_date' => 'required|date', // Make sure it's a valid date
            'due_date' => 'nullable|date|after_or_equal:start_date', // Ensure due_date is after or equal to start_date
            'lead_source_id' => 'required',
            'client_id' => 'nullable',
            'status' => 'required',
            'employee_ids' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            $projectId = decrypt($id);
            $project = Project::findOrFail($projectId);
            $updateData = [
                'title' => $request->title,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'lead_source_id' => $request->lead_source_id,
                'client_id' => $request->client_id,
                'status' => $request->status,
                'employee_ids' =>   implode(',', $request->employee_ids),
                'description' => $request->description,
                'staff_id' => Auth::guard('admin')->id(),
            ];
            if ($project->update($updateData)) {
                return redirect(route('admin.projects.index'))->with('success', 'Project updated successfully');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $pId = decrypt($id);
        $delete = Project::find($pId);
        if ($delete) {
            $delete->delete();
            return redirect()->back()->with('success', 'Project deleted successfully');
        }
        return redirect()->back()->with('error', 'Project cannot delete');
    }
}
