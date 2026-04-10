<?php

namespace App\Http\Controllers\Hr;

use Carbon\Carbon;
use App\Models\Breaks;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;

class ClockController extends Controller
{
    public function checkInAttendance(Request $request)
    {
        $validator = Validator($request->all(), [
            'work_from' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }


        $authuser = Auth::user()->id;
        $arFormData = DB::table('tasks')->where('assigned_to', $authuser)->where('status', '!=', 'completed')->get();

        $hasRecursiveTask = false;
        // Iterate over the records
        foreach ($arFormData as $task) {
            // Check if the task has id_recursive_task as 'yes'
            if ($task->id_recursive_task === 'yes') {
                // Update the task's status to 'pending'
                DB::table('tasks')
                    ->where('id', $task->id)  // Make sure to update the specific task
                    ->update(['status' => 'pending']);
            }
        }

        $check_in = [
            'staff_id' => Auth::guard('admin')->id(),
            'work_from' => $request->work_from,
            'date' => date('Y-m-d'),
            'check_in' => date('Y-m-d H:i:s'),
            'start_comment' => $request->comment,
            'status' => 'present', // ENUM('absent','present','leave')
        ];
        try {
            $result = Attendance::create($check_in);
            return response()->json(['status' => 1, 'message' => 'Checked IN successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'something went wrong,' . $e->getMessage()]);
        }
    }

    public function startBreakTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'break_for' => 'required',
            'attendance_id' => 'required|exists:attendance,id', // Ensure attendance_id is valid
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $data = [
            'staff_id' => Auth::guard('admin')->id(),
            'attendance_id' => $request->attendance_id,
            'start' => date('Y-m-d H:i:s'), // Use Carbon's now() for current timestamp
            'type' => $request->break_for,
        ];
        try {
            $result = Breaks::create($data);
            return response()->json([
                'status' => 1,
                'message' => 'Lunch break started successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

    public function endLunchBreak(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendance_id' => 'required|exists:attendance,id', // Ensure attendance_id is valid
            'break_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $break = Breaks::find($request->break_id);
        if (!$break) {
            return response()->json(['status' => 0, 'message' => 'Invalid activity, break not found.']);
        }
        $startTime = Carbon::parse($break->start); // Parse the break start time
        $now = Carbon::now(); // Get the current time
        $duration = $now->diff($startTime); // Calculate the difference
        $durationInMinutes = $duration->h * 60 + $duration->i; // Convert duration to minutes
        $endLunch = [
            'attendance_id' => $request->attendance_id,
            'end' => date('Y-m-d H:i:s'), // Use Carbon's now() for current timestamp
            'lunch_was' => $request->lunch_was,
            'reason' => $request->reason,
            'duration' => $durationInMinutes,
        ];
        try {
            $result = $break->update($endLunch);
            return response()->json(['status' => 1, 'message' => 'Break ended successfully, back to work now.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    public function checkOutAttendance(Request $request)
    {
        $validator = Validator($request->all(), [
            'attendance_id' => 'required|exists:attendance,id', // Ensure attendance_id is valid
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $authuser = Auth::user()->id;
        $arFormData = DB::table('tasks')->where('assigned_to', $authuser)->where('status', '!=', 'completed')->get();

        $hasRecursiveTask = false;
        $taskCommentUpdate = false;
        // Iterate over the records
        
        if ($arFormData->isNotEmpty()) {
            foreach ($arFormData as $task) {
                // Check if the task has id_recursive_task as 'yes'
                if ($task->id_recursive_task === 'yes' && 'status' != 'completed') {
                    $hasRecursiveTask = true;
                    break; 
                }else{
                    $taskComment = DB::table('task_comments')
                        ->whereDate('created_at', today())
                        ->orderBy('created_at', 'asc')
                        ->first();

                    if (!$taskComment) {
                        $taskCommentUpdate = true;
                    }
                    
                }
            }
        }
        if ($hasRecursiveTask) {
            return response()->json(['status' => 0, 'message' => 'Wait, first complete Daily Task and Then Logout.']);
        } 
        if ($taskCommentUpdate) {
            return response()->json(['status' => 0, 'message' => 'Wait, first Add Comment to your Ongoing Task and Then Logout.']);
        } 





        $attend = Attendance::find($request->attendance_id);
        if (!$attend) {
            return response()->json(['status' => 0, 'message' => 'Invalid activity, attendance not found.']);
        }

        $openBreak = Breaks::where('attendance_id', $attend->id)
            ->where('staff_id', Auth::guard('admin')->id())
            ->latest('created_at') // Order by latest break entry
            ->first();
        if ($openBreak) {
            // Now check if duration or end is null
            if ($openBreak->duration === null || $openBreak->end === null) {
                return response()->json(['status' => 0, 'message' => 'Wait, first complete your break before making logout.']);
            }
        }
        $startTime = Carbon::parse($attend->check_in); // Parse the break start time
        $now = Carbon::now(); // Get the current time
        $duration = $now->diff($startTime); // Calculate the difference
        $durationInMinutes = $duration->h * 60 + $duration->i; // Convert duration to minutes
        $endDay = [
            'check_out' => date('Y-m-d H:i:s'),
            'end_comment' => $request->comment,
            'duration' => $durationInMinutes,
        ];
        try {
            $result = $attend->update($endDay);
            return response()->json(['status' => 1, 'message' => 'Good Bye Dear, will meet soon']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'something went wrong,' . $e->getMessage()]);
        }
    }
}
