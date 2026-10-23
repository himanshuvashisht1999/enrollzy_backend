<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskActivityLog extends Model
{
    use HasFactory;

    protected $table = 'task_activity_logs';
    protected $guarded = ['id'];
    protected $casts = [
        'extra_data' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class, 'milestone_id');
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function actor()
    {
        return $this->belongsTo(Admin::class, 'performed_by');
    }

    /**
     * Helper to quickly log an activity across the system
     */
    public static function log($actionType, $description, $taskId = null, $projectId = null, $milestoneId = null, $fromState = null, $toState = null, $extraData = null)
    {
        $user = auth()->user();
        return self::create([
            'organization_id' => $user->organization_id ?? null,
            'project_id' => $projectId,
            'milestone_id' => $milestoneId,
            'task_id' => $taskId,
            'action_type' => $actionType,
            'performed_by' => $user ? $user->id : null,
            'performed_by_name' => $user ? $user->name : 'System',
            'from_state' => $fromState,
            'to_state' => $toState,
            'description' => $description,
            'extra_data' => $extraData,
        ]);
    }
}
