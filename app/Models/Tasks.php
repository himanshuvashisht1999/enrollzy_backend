<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tasks extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at', 'start_date', 'due_date', 'completed_at', 'verified_at', 'closed_at'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function milestone_assigned()
    {
        return $this->belongsTo(Milestone::class, 'milestone');
    }

    public function parentTask()
    {
        return $this->belongsTo(Tasks::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Tasks::class, 'parent_task_id')->orderBy('id', 'asc');
    }

    public function allSubtasks()
    {
        return $this->subtasks()->with('allSubtasks');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function assigned_by()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function assignees()
    {
        return $this->hasMany(TaskAssignee::class, 'task_id');
    }

    public function activeAssignees()
    {
        return $this->hasMany(TaskAssignee::class, 'task_id')->where('status', 'active');
    }

    public function activePrimaryAssignee()
    {
        return $this->hasOne(TaskAssignee::class, 'task_id')->where('status', 'active')->where('is_primary', true);
    }

    public function delegations()
    {
        return $this->hasMany(TaskDelegation::class, 'task_id')->orderBy('id', 'desc');
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependentTasks()
    {
        return $this->belongsToMany(Tasks::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
    }

    public function checklists()
    {
        return $this->hasMany(TaskChecklist::class, 'task_id')->orderBy('sort_order', 'asc');
    }

    public function timeEntries()
    {
        return $this->hasMany(TaskTimeEntry::class, 'task_id')->orderBy('id', 'desc');
    }

    public function recurrence()
    {
        return $this->hasOne(TaskRecurrence::class, 'task_id');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class, 'task_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(TaskActivityLog::class, 'task_id')->orderBy('id', 'desc');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id')->orderBy('id', 'asc');
    }

    public function isSubtask()
    {
        return !empty($this->parent_task_id);
    }

    public function hasSubtasks()
    {
        return $this->subtasks()->count() > 0;
    }

    /**
     * Strict single-assignee execution guard:
     * Returns true if user is the active primary assignee, task creator, or manager/admin
     */
    public function canUserPerformAction($userId, $user = null)
    {
        if (!$user) {
            $user = Admin::find($userId);
        }
        if (!$user) return false;

        // If it's a subtask: Strict single-assignee custody
        // Only the current active primary assignee has custody; previous assignees are revoked.
        if ($this->isSubtask()) {
            $primary = $this->activePrimaryAssignee;
            if ($primary && $primary->user_id == $userId) return true;
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) return true;
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) return true;
        if (!empty($user->is_admin)) return true;

        // For parent tasks, check active assignees or legacy assigned_to
        $activeUserIds = $this->activeAssignees()->where('assignee_type', 'internal_user')->pluck('user_id')->toArray();
        if (in_array($userId, $activeUserIds)) return true;

        if ($this->assigned_to) {
            $legacyIds = explode(',', (string)$this->assigned_to);
            if (in_array($userId, $legacyIds)) return true;
        }

        if ($this->created_by == $userId || $this->staff_id == $userId) return true;

        return false;
    }

    public function getStatusBadge()
    {
        $status = strtolower($this->status);
        $badges = [
            'backlog' => '<span class="badge bg-secondary">Backlog</span>',
            'not_started' => '<span class="badge bg-secondary">Not Started</span>',
            'assigned' => '<span class="badge bg-info text-dark">Assigned</span>',
            'accepted' => '<span class="badge bg-primary">Accepted</span>',
            'in_progress' => '<span class="badge bg-primary">In Progress</span>',
            'under_review' => '<span class="badge bg-warning text-dark">Under Review</span>',
            'changes_requested' => '<span class="badge bg-danger">Changes Requested</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'verified' => '<span class="badge bg-success">Verified</span>',
            'closed' => '<span class="badge bg-dark">Closed</span>',
            'on_hold' => '<span class="badge bg-warning text-dark">On Hold</span>',
        ];
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    public function getPriorityBadge()
    {
        $priority = strtolower($this->priority ?? 'medium');
        $badges = [
            'low' => '<span class="badge bg-soft-success text-success border border-success">Low</span>',
            'medium' => '<span class="badge bg-soft-info text-info border border-info">Medium</span>',
            'high' => '<span class="badge bg-soft-warning text-warning border border-warning">High</span>',
            'urgent' => '<span class="badge bg-soft-danger text-danger border border-danger">Urgent</span>',
            'critical' => '<span class="badge bg-danger text-white">Critical</span>',
        ];
        return $badges[$priority] ?? '<span class="badge bg-secondary">' . ucfirst($priority) . '</span>';
    }
}
