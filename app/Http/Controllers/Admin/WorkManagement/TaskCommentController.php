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

        $docPath = null;
        if ($request->hasFile('documents')) {
            $file = $request->file('documents');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('task_comment_docs', $fileName, 'public');
            $docPath = 'storage/' . $path;
        }

        $comment = TaskComment::create([
            'task_id' => $request->task_id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'documents' => $docPath,
            'parent_comment_id' => $request->parent_comment_id ?? null,
        ]);

        $task = Tasks::find($request->task_id);
        if ($task) {
            TaskActivityLog::log(
                'comment_added',
                "Comment posted on task by " . auth()->user()->name,
                $task->id,
                $task->project_id,
                $task->milestone
            );
        }

        return redirect()->back()->with('success', 'Comment posted successfully');
    }
}
