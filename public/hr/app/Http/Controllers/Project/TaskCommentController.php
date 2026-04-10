<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TaskComment;
use App\Models\Task;
use Auth;

class TaskCommentController extends Controller
{
    // Store comment for a task
    public function store(Request $request, $taskId)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $filePathsString = '';
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
            
            }



        // Store the comment
        TaskComment::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'documents' => $filePathsString,
        ]);
        return redirect()->back()->with('success', 'Tasks added successfully');
    }
}
