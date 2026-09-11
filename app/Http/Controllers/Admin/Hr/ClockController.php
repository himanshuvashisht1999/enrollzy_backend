<?php

namespace App\Http\Controllers\Admin\Hr;

use Carbon\Carbon;
use App\Models\Breaks;
use App\Models\Attendance;
use App\Models\Tasks;
use App\Models\TaskComment;
use App\Services\WorkManagement\WorkHierarchyService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClockController extends Controller
{
    public function checkInAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_from' => 'required',
            'image_data' => 'nullable', // Camera capture
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $authuser = Auth::id();
        
        // Custom logic for recursive tasks
        $tasks = Tasks::where('assigned_to', $authuser)->whereNotIn('status', ['completed', 'verified', 'closed', 'archived'])->get();
        foreach ($tasks as $task) {
            if (isset($task->id_recursive_task) && $task->id_recursive_task === 'yes') {
                $task->update(['status' => 'not_started']);
            }
        }

        $check_in_image = null;
        if ($request->image_data) {
            $image = $request->image_data;
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'punch_in_' . time() . '_' . Auth::id() . '.jpg';
            Storage::disk('public')->put('attendance/' . $imageName, base64_decode($image));
            $check_in_image = 'attendance/' . $imageName;
        }

        $check_in = [
            'staff_id' => Auth::id(),
            'work_from' => $request->work_from,
            'date' => date('Y-m-d'),
            'check_in' => date('Y-m-d H:i:s'),
            'check_in_image' => $check_in_image,
            'start_comment' => $request->comment,
            'status' => 'present',
        ];

        try {
            $result = Attendance::create($check_in);
            return response()->json(['status' => 1, 'message' => 'Checked IN successfully', 'attendance_id' => $result->id]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function startBreakTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'break_for' => 'required',
            'attendance_id' => 'required|exists:attendance,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $data = [
            'staff_id' => Auth::id(),
            'attendance_id' => $request->attendance_id,
            'start' => date('Y-m-d H:i:s'),
            'type' => $request->break_for,
        ];

        try {
            $result = Breaks::create($data);
            return response()->json(['status' => 1, 'message' => 'Break started successfully', 'break_id' => $result->id]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function endLunchBreak(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendance_id' => 'required|exists:attendance,id',
            'break_id' => 'required|exists:breaks,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $break = Breaks::find($request->break_id);
        $startTime = Carbon::parse($break->start);
        $now = Carbon::now();
        $durationInMinutes = abs($now->diffInMinutes($startTime));

        try {
            $break->update([
                'end' => date('Y-m-d H:i:s'),
                'lunch_was' => $request->lunch_was,
                'reason' => $request->reason,
                'duration' => $durationInMinutes,
            ]);
            return response()->json(['status' => 1, 'message' => 'Break ended successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function checkOutAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendance_id' => 'required|exists:attendance,id',
            'image_data' => 'nullable', // Camera capture
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $user = Auth::user();
        $authuser = $user->id;
        $attend = Attendance::find($request->attendance_id);
        
        // Enforce daily task update/comment requirement for staff members (SuperAdmin / Admin is exempt)
        if (!WorkHierarchyService::isSuperAdmin($user)) {
            // Find all pending tasks & subtasks assigned to this staff member (direct assignee or in activeAssignees)
            $pendingTasks = Tasks::where(function ($q) use ($authuser) {
                    $q->where('assigned_to', $authuser)
                      ->orWhereHas('activeAssignees', function ($aq) use ($authuser) {
                          $aq->where('user_id', $authuser);
                      });
                })
                ->whereNotIn('status', ['completed', 'verified', 'closed', 'archived'])
                ->get();

            if ($pendingTasks->isNotEmpty()) {
                // Check for daily recursive tasks
                $hasRecursiveTask = $pendingTasks->contains(function ($task) {
                    return isset($task->id_recursive_task) && $task->id_recursive_task === 'yes';
                });

                if ($hasRecursiveTask) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Wait, first complete your Daily Task and Then Logout.'
                    ]);
                }

                // Check if user has posted at least one update/comment on their pending assigned tasks today
                $pendingTaskIds = $pendingTasks->pluck('id')->toArray();
                $hasCommentedToday = TaskComment::where('user_id', $authuser)
                    ->whereIn('task_id', $pendingTaskIds)
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if (!$hasCommentedToday) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Wait, please add at least one update or comment to your ongoing assigned task(s) for today before punching out.'
                    ]);
                }
            }
        }

        $openBreak = Breaks::where('attendance_id', $attend->id)->whereNull('end')->first();
        if ($openBreak) {
            return response()->json(['status' => 0, 'message' => 'Wait, first complete your break before making logout.']);
        }

        $check_out_image = null;
        if ($request->image_data) {
            $image = $request->image_data;
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'punch_out_' . time() . '_' . $authuser . '.jpg';
            Storage::disk('public')->put('attendance/' . $imageName, base64_decode($image));
            $check_out_image = 'attendance/' . $imageName;
        }

        $startTime = Carbon::parse($attend->check_in);
        $now = Carbon::now();
        $durationInMinutes = abs($now->diffInMinutes($startTime));

        try {
            $attend->update([
                'check_out' => date('Y-m-d H:i:s'),
                'check_out_image' => $check_out_image,
                'end_comment' => $request->comment,
                'duration' => $durationInMinutes,
            ]);
            return response()->json(['status' => 1, 'message' => 'Good Bye Dear, will meet soon']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
