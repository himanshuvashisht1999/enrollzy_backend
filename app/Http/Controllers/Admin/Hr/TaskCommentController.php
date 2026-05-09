<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'comment' => 'required|string',
        ]);

        try {
            $data = [
                'task_id' => $request->task_id,
                'comment' => $request->comment,
                'user_id' => auth()->id(),
            ];

            if ($request->hasFile('documents')) {
                $file = $request->file('documents');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('assets/task_docs'), $filename);
                $data['documents'] = $filename;
            }

            TaskComment::create($data);
            return redirect()->back()->with('success', 'Comment added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
