<?php

namespace App\Http\Controllers\Project;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MilestoneController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Milestone::get();
            }else{
                $data = Milestone::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0">' . $row->title . '</p>';
                    return $name;
                })
                ->addColumn('project', function ($row) {
                    $project = '<p class="text-sm mb-0">' . $row->project->title . '</p>';
                    return $project;
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

                    $btn .= '<a href="' . route('admin.milestones.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';

                    $btn .= '<form method="POST" action="' . route('admin.milestones.destroy', encrypt($row->id)) . '" class="m-0 p-0">
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
                    'project',
                    'status',
                    'created_at',
                    'action'
                ])
                ->make(true);
        }

        return view('project.milestones.index');
    }

    public function create()
    {

        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $project = Project::get();
        }else{
            $project = Project::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }
        return view('project.milestones.create', compact('project'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'project_id' => 'required',
            'status' => 'required',
            'start_date' => 'required|date', // Make sure it's a valid date
            'due_date' => 'nullable|date|after_or_equal:start_date', // Ensure due_date is after or equal to start_date
            'price' => 'nullable',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            Milestone::create([
                'title' => $request->title,
                'project_id' => $request->project_id,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'price' => $request->price,
                'description' => $request->description,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.milestones.index'))->with('success', 'Milestone added successfully');
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
        $milestoneID = decrypt($id);
        $milestone = Milestone::find($milestoneID);
        if ($milestone) {

            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $project = Project::get();
            }else{
                $project = Project::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return view('project.milestones.edit', compact('milestone', 'project'));
        }
        return redirect()->back()->with('error', 'Milestone not found,');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'project_id' => 'required',
            'status' => 'required',
            'start_date' => 'required|date', // Make sure it's a valid date
            'due_date' => 'nullable|date|after_or_equal:start_date', // Ensure due_date is after or equal to start_date
            'price' => 'nullable',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            $mId = decrypt($id);
            $milestone = Milestone::findOrFail($mId);
            $updateMilestone = [
                'title' => $request->title,
                'project_id' => $request->project_id,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'price' => $request->price,
                'description' => $request->description,
            ];
            if ($milestone->update($updateMilestone)) {
                return redirect(route('admin.milestones.index'))->with('success', 'Milestone updated successfully');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $mId = decrypt($id);
        $delete = Milestone::find($mId);
        if ($delete) {
            $delete->delete();
            return redirect()->back()->with('success', 'Milestone deleted successfully');
        }
        return redirect()->back()->with('error', 'Milestone cannot delete');
    }
}
