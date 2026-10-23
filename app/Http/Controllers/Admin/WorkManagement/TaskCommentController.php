<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\TaskComment;
use App\Models\Tasks;
use App\Models\TaskActivityLog;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'comment' => 'required|string',
        ]);

        $uploadedPaths = [];
        $destinationPath = public_path('uploads/tasks/comments');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        // Collect all uploaded photos/documents
        $allFiles = [];
        if ($request->hasFile('photos')) {
            $photoFiles = $request->file('photos');
            $allFiles = array_merge($allFiles, is_array($photoFiles) ? $photoFiles : [$photoFiles]);
        }
        if ($request->hasFile('documents')) {
            $docFiles = $request->file('documents');
            $allFiles = array_merge($allFiles, is_array($docFiles) ? $docFiles : [$docFiles]);
        }
        if ($request->hasFile('attachments')) {
            $attFiles = $request->file('attachments');
            $allFiles = array_merge($allFiles, is_array($attFiles) ? $attFiles : [$attFiles]);
        }

        foreach ($allFiles as $file) {
            if ($file && $file->isValid()) {
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
                $file->move($destinationPath, $safeName);
                $uploadedPaths[] = 'uploads/tasks/comments/' . $safeName;
            }
        }

        $docsValue = null;
        if (count($uploadedPaths) === 1) {
            $docsValue = $uploadedPaths[0];
        } elseif (count($uploadedPaths) > 1) {
            $docsValue = json_encode($uploadedPaths);
        }

        $comment = TaskComment::create([
            'task_id' => $request->task_id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'documents' => $docsValue,
            'parent_comment_id' => $request->parent_comment_id ?? null,
        ]);

        $task = Tasks::find($request->task_id);
        if ($task) {
            $fileNote = count($uploadedPaths) > 0 ? " with " . count($uploadedPaths) . " photo(s)/file(s)" : "";
            TaskActivityLog::log(
                'comment_added',
                "Update/Comment posted on task{$fileNote} by " . auth()->user()->name,
                $task->id,
                $task->project_id,
                $task->milestone
            );
        }

        return redirect()->back()->with('success', 'Update / Comment posted successfully');
    }
}
