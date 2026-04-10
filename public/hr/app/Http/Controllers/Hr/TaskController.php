<?php

namespace App\Http\Controllers\Project;

use App\Models\Project;
use App\Models\Milestone;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Support\Facades\Storage;
use App\Models\TaskComment;
use App\Models\Tasks;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $auth = Auth::guard('admin')->user();
        if ($request->ajax()) {
            if ($auth->role == 'superadmin') {
                $data = Tasks::get();
            } elseif($auth->role == 'admin') {
                $data = Tasks::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            } else {
                $data = Tasks::where('staff_id', $auth->id)->orWhere('assigned_to', $auth->id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('title', function ($row) {
                    $title = '<p class="text-sm font-weight-bold mb-0">' . $row->title . '</p>';
                    return $title;
                })
                ->addColumn('project', function ($row) {
                    $project = '<p class="text-sm mb-0">' . $row->project->title . '</p>';
                    return $project;
                })
                ->addColumn('id_recursive_task', function ($row) {
                    $id_recursive_task = '<p class="text-sm mb-0">' . $row->id_recursive_task . '</p>';
                    return $id_recursive_task;
                })
                ->addColumn('milestone', function ($row) {
                    if ($row->mileStoneAssigned) {
                        return '<p class="text-sm mb-0">' . $row->mileStoneAssigned->title . '</p>';
                    } else {
                        return '<p class="text-sm mb-0">No Milestone Assigned</p>'; // or return an empty string
                    }
                })
                ->addColumn('assigned_by', function ($row) {
                    return '<p class="text-sm mb-0">' . $row->assigned_by->name . '</p>'; // or return an empty string
                })
                ->addColumn('staff', function ($row) {
                    if ($row->assigned_to) {
                        // Assuming assigned_to is a comma-separated string of staff IDs
                        $staffIds = explode(',', $row->assigned_to);
                        $staffNames = [];
                        foreach ($staffIds as $staffId) {
                            // Assuming you have a method to get staff details by ID
                            $staffMember = Admin::find($staffId); // Adjust this line based on how you retrieve staff members
                            if ($staffMember) {
                                $staffNames[] = $staffMember->name;
                            }
                        }
                        return '<p class="text-sm mb-0">' . implode(', ', $staffNames) . '</p>';
                    } else {
                        return '<p class="text-sm mb-0">No Staff Assigned</p>'; // or return an empty string
                    }
                })
                ->addColumn('priority', function ($row) {
                    return GetStatusBadge($row->priority);
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
                    $btn .= '<a href="' . route('admin.task.show', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-eye text-success"></i></a>';
                    $btn .= ' | ';
                    $btn .= '<a href="' . route('admin.task.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';
                    $btn .= '<form method="POST" action="' . route('admin.task.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns([
                    'title',
                    'project',
                    'id_recursive_task',
                    'milestone',
                    'assigned_by',
                    'staff',
                    'priority',
                    'status',
                    'created_at',
                    'action'
                ])
                ->make(true);
        }

        return view('project.tasks.index');
    }

    public function create()
    {
        $loginUser = Auth::guard('admin')->user();
        if ($loginUser->role == 'superadmin') {
            $project = Project::get();
        }elseif($loginUser->role == 'admin') {
            $project = Project::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        } else {
            $project = Project::whereRaw('FIND_IN_SET(?, employee_ids)', [$loginUser->id])->get();
        }
        return view('project.tasks.create', compact('project'));
    }

    public function getProjectStaffMilestone(Request $request)
    {
        $projectID = $request->project_id;
        try {
            $project = Project::findOrFail($projectID);
            $staffIds = !empty($project->employee_ids) ? explode(',', $project->employee_ids) : [];
            $staff = Admin::whereIn('id', $staffIds)->get();
            $milestone = Milestone::where('project_id', $projectID)->get();
            return response()->json(['status' => 1, 'staff' => $staff, 'milestone' => $milestone]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 0, 'message' => 'Project not found.' . $e->getMessage()]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'project_id' => 'required',
            'priority' => 'required',
            'start_date' => 'required|date', // Make sure it's a valid date
            'due_date' => 'nullable|date|after_or_equal:start_date', // Ensure due_date is after or equal to start_date
            'estimated_hours' => 'nullable',
            'assigned_to' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
            $RecursiveInterval = "";
            if($request->recursive_interval){
                $RecursiveInterval = $request->recursive_interval;
            }
            $RecursiveRepeat = "";
            if($request->recursive_repeat){
                $RecursiveRepeat = $request->recursive_repeat;
            }
            $RecursiveManualy ="";
            if($request->recursive_manualy){
                $RecursiveManualy = $request->recursive_manualy;
            }
        try {
            Tasks::create([
                'title' => $request->title,
                'project_id' => $request->project_id,
                'priority' => $request->priority,
                'id_recursive_task' => $request->recursive_task,
                'recursive_interval' => $RecursiveInterval,
                'recursive_repeat' => $RecursiveRepeat,
                'recursive_manualy' => $RecursiveManualy,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'estimated_hours' => $request->estimated_hours,
                'assigned_to' => implode(',', $request->assigned_to),
                'milestone' => $request->milestone,
                'status' => $request->status,
                'description' => $request->description,
                'staff_id' => Auth::guard('admin')->id(),
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.task.index'))->with('success', 'Tasks added successfully');
        } catch (Exception $e) {
            return $e;
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $taskID = decrypt($id);
        $task = Tasks::with('project')->find($taskID);
        $project = $task->project;
        if ($task) {
            $staffIds = !empty($task->project->employee_ids) ? explode(',', $task->project->employee_ids) : [];
            $staff = Admin::whereIn('id', $staffIds)->get();
            $milestone = Milestone::where('project_id', $task->project_id)->get();
            $taskcomments = TaskComment::where('task_id', $task->id)->orderBy('created_at', 'desc')->get();

            return view('project.tasks.edit', compact('milestone', 'staff', 'project', 'task'));
        }
        return redirect()->back()->with('error', 'Task not found,');
    }

    public function edit($id)
    {
        $taskID = decrypt($id);
        $task = Tasks::find($taskID);
        if ($task) {
            $project = Project::find($task->project_id);
            $staffIds = !empty($project->employee_ids) ? explode(',', $project->employee_ids) : [];
            $staff = Admin::whereIn('id', $staffIds)->get();
            $milestone = Milestone::where('project_id', $task->project_id)->get();
            $taskcomments = TaskComment::where('task_id', $task->id)->orderBy('created_at', 'desc')->get();
            return view('project.tasks.edit', compact('milestone', 'staff', 'project', 'task','taskcomments'));
        }
        return redirect()->back()->with('error', 'Task not found,');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'project_id' => 'required',
            'priority' => 'required',
            'start_date' => 'required|date', // Make sure it's a valid date
            'due_date' => 'nullable|date|after_or_equal:start_date', // Ensure due_date is after or equal to start_date
            'estimated_hours' => 'nullable',
            'assigned_to' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        $RecursiveInterval = NULL;
        if($request->recursive_interval){
            $RecursiveInterval = $request->recursive_interval;
        }
        $RecursiveRepeat = NULL;
        if($request->recursive_repeat){
            $RecursiveRepeat = $request->recursive_repeat;
        }
        $RecursiveManualy = NULL;
        if($request->manual_date){
            $RecursiveManualy = $request->manual_date;
        }
        try {
            $taskId = decrypt($id);
            $task = Tasks::findOrFail($taskId);
            $updateTask = [
                'title' => $request->title,
                'project_id' => $request->project_id,
                'priority' => $request->priority,
                'id_recursive_task' => $request->recursive_task,
                'recursive_interval' => $RecursiveInterval,
                'recursive_repeat' => $RecursiveRepeat,
                'recursive_manualy' => $RecursiveManualy,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'estimated_hours' => $request->estimated_hours,
                'assigned_to' => implode(',', $request->assigned_to),
                'milestone' => $request->milestone,
                'status' => $request->status,
                'description' => $request->description,
                'staff_id' => Auth::guard('admin')->id(),
            ];
            if ($task->update($updateTask)) {
                return redirect(route('admin.task.index'))->with('success', 'Task updated successfully');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $taskId = decrypt($id);
        try {
            $delete = Tasks::findOrFail($taskId);
            $delete->delete();
            return redirect()->back()->with('success', 'Task deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Task cannot delete.' . $e->getMessage());
        }
    }

    public function updateDocuments(Request $request, $id)
    {
        $staffId = decrypt($id);
        try {
            // Validate the request to ensure files are of the allowed types
            $request->validate([
                'files.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx|max:20480', // Max size: 20MB per file
            ]);
            // Find the staff by ID (assuming you have a model associated with this ID)
            $staff = Tasks::find($staffId); // Replace `YourModel` with the actual model
            if (!$staff) {
                return redirect()->back()->with('error', 'staff not found');
            }
            // Check if a file is uploaded
            $existingDocs = $staff->documents;
            $existingDocsArray = $existingDocs ? explode(',', $existingDocs) : [];
            // Initialize an array to store new file paths
            $filePaths = $existingDocsArray;

            // Check if files are uploaded
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $file) {
                    $fileName = time() . '-' . $file->getClientOriginalName();
                    // Convert file name to lowercase and remove spaces
                    $fileName = strtolower($fileName);
                    $fileName = preg_replace('/\s+/', '', $fileName);
            
                    // Define the public path where the files will be stored
                    $destinationPath = public_path('documents');
                    
                    // Ensure the directory exists
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
            
                    // Move the file to the public/documents folder
                    $file->move($destinationPath, $fileName);
            
                    // Append file path to array
                    $filePaths[] = 'documents/' . $fileName;
                }
            
                // Convert file paths array to a comma-separated string
                $filePathsString = implode(',', $filePaths);
            
                // Update the staff with the file paths
                $staff->documents = $filePathsString;
                $staff->save();
            
                return redirect()->back()->with('success', 'Document uploaded successfully');
            }
            
            else {
                return redirect()->back()->with('error', 'No file was uploaded');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while uploading the document' . $e->getMessage());
        }
    }
    public function deleteStaffDocument(Request $request, $file)
    {
        // Find the staff record associated with the document
        $staff = Tasks::find($request->staff_id);
        if (!$staff) {
            return redirect()->back()->with('error', 'Document not found.');
        }
        // Remove the file from storage
        if (Storage::exists('public/' . $file)) {
            Storage::delete('public/' . $file);
        }
        // Remove the file from the database field
        $documents = explode(',', $staff->documents);
        $documents = array_filter($documents, function ($item) use ($file) {
            return basename($item) !== $file;
        });
        $staff->documents = implode(',', $documents);
        $staff->save();
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
