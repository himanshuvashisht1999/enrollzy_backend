<?php

namespace App\Services\WorkManagement;

use App\Models\Tasks;
use App\Models\TaskAssignee;
use App\Models\TaskDelegation;
use App\Models\TaskActivityLog;
use App\Models\Admin;
use App\Models\Team;
use App\Models\ExternalContact;
use App\Models\ExternalTeam;
use Exception;

class WorkAssignmentService
{
    /**
     * Assign work to internal user, team, external contact or external agency
     */
    public function assign($taskId, $assigneeType, $assigneeId, $role = 'Assignee', $isPrimary = true, $dueDate = null, $assignedBy = null)
    {
        $task = Tasks::findOrFail($taskId);
        $assignedBy = $assignedBy ?: (auth()->check() ? auth()->id() : null);

        $data = [
            'task_id' => $task->id,
            'assignee_type' => $assigneeType,
            'role' => $role,
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
            'due_date' => $dueDate ?: $task->due_date,
            'status' => 'active',
            'is_primary' => $isPrimary,
        ];

        if ($assigneeType === 'internal_user') {
            $data['user_id'] = $assigneeId;
        } elseif ($assigneeType === 'internal_team') {
            $data['team_id'] = $assigneeId;
        } elseif ($assigneeType === 'external_contact') {
            $data['external_contact_id'] = $assigneeId;
        } elseif ($assigneeType === 'external_team') {
            $data['external_team_id'] = $assigneeId;
        }

        // If subtask and is_primary, mark existing active assignees as transferred
        if ($task->isSubtask() && $isPrimary) {
            TaskAssignee::where('task_id', $task->id)
                ->where('status', 'active')
                ->update(['status' => 'transferred']);
        }

        $assignee = TaskAssignee::create($data);

        // Also update task table assigned_to and team_id for backward compatibility
        if ($assigneeType === 'internal_user') {
            $task->assigned_to = $assigneeId;
        } elseif ($assigneeType === 'internal_team') {
            $task->team_id = $assigneeId;
        }
        $task->save();

        // Log activity
        $targetName = $assignee->display_name;
        TaskActivityLog::log(
            'assigned',
            "Task '{$task->title}' assigned to {$targetName} as {$role}",
            $task->id,
            $task->project_id,
            $task->milestone
        );

        return $assignee;
    }

    /**
     * Strict single-assignee reassignment for Subtasks (or parent tasks)
     * Automatically locks the previous staff member and grants active custody to new staff
     */
    public function reassignSubtask($subtaskId, $newUserId, $newTeamId = null, $reason = null)
    {
        $task = Tasks::findOrFail($subtaskId);
        $currentUser = auth()->user();
        $assignedBy = $currentUser ? $currentUser->id : null;

        // Find previous active primary assignee
        $prevAssignee = TaskAssignee::where('task_id', $task->id)
            ->where('status', 'active')
            ->where('is_primary', true)
            ->first();

        $prevName = $prevAssignee ? $prevAssignee->display_name : ($task->assigned_to_user->name ?? 'Unassigned');

        // Mark all existing active assignees as transferred
        TaskAssignee::where('task_id', $task->id)
            ->where('status', 'active')
            ->update(['status' => 'transferred']);

        // Create new active primary assignee
        $newAssignee = TaskAssignee::create([
            'task_id' => $task->id,
            'assignee_type' => 'internal_user',
            'user_id' => $newUserId,
            'team_id' => $newTeamId ?: $task->team_id,
            'role' => 'Assignee',
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
            'due_date' => $task->due_date,
            'status' => 'active',
            'is_primary' => true,
        ]);

        $task->assigned_to = $newUserId;
        if ($newTeamId) {
            $task->team_id = $newTeamId;
        }
        $task->save();

        $newStaff = Admin::find($newUserId);
        $newName = $newStaff ? $newStaff->name : "Staff #{$newUserId}";

        $desc = "Reassigned subtask from {$prevName} to {$newName}.";
        if ($reason) {
            $desc .= " Reason: {$reason}";
        }

        TaskActivityLog::log(
            'reassigned',
            $desc,
            $task->id,
            $task->project_id,
            $task->milestone,
            $prevName,
            $newName,
            ['reason' => $reason, 'previous_user_id' => $prevAssignee ? $prevAssignee->user_id : null, 'new_user_id' => $newUserId]
        );

        return $newAssignee;
    }

    /**
     * Delegate task down or across teams preserving delegation chain
     */
    public function delegate($taskId, $toType, $toId, $remarks = null)
    {
        $task = Tasks::findOrFail($taskId);
        $currentUser = auth()->user();
        $fromUserId = $currentUser ? $currentUser->id : null;

        $delegationData = [
            'task_id' => $task->id,
            'from_user_id' => $fromUserId,
            'to_type' => $toType,
            'delegated_at' => now(),
            'remarks' => $remarks,
            'status' => 'active',
        ];

        if ($toType === 'internal_user') {
            $delegationData['to_user_id'] = $toId;
        } elseif ($toType === 'internal_team') {
            $delegationData['to_team_id'] = $toId;
        } elseif ($toType === 'external_contact') {
            $delegationData['external_contact_id'] = $toId;
        } elseif ($toType === 'external_team') {
            $delegationData['external_team_id'] = $toId;
        }

        $delegation = TaskDelegation::create($delegationData);

        // Also create a secondary active assignee record
        $this->assign($task->id, $toType, $toId, 'Delegated Assignee', false, $task->due_date, $fromUserId);

        TaskActivityLog::log(
            'delegated',
            "Task delegated to {$toType} #{$toId} by " . ($currentUser ? $currentUser->name : 'User') . ($remarks ? ". Note: {$remarks}" : ""),
            $task->id,
            $task->project_id,
            $task->milestone
        );

        return $delegation;
    }
}
