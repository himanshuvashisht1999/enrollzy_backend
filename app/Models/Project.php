<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at', 'start_date', 'due_date', 'target_end_date', 'actual_end_date'];

    public function project_category()
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function lead_source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function owner()
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'project_id')->orderBy('sequence', 'asc');
    }

    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'project_id');
    }

    public function rootTasks()
    {
        return $this->hasMany(Tasks::class, 'project_id')->whereNull('parent_task_id');
    }

    public function members()
    {
        return $this->belongsToMany(Admin::class, 'project_members', 'project_id', 'user_id')
            ->withPivot(['id', 'role', 'access_level', 'joined_at', 'status'])
            ->withTimestamps();
    }

    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function externalMembers()
    {
        return $this->hasMany(ProjectExternalMember::class, 'project_id');
    }

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class, 'project_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(TaskActivityLog::class, 'project_id')->orderBy('id', 'desc');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'project_id');
    }

    public function calculateProgress()
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks == 0) return 0;
        $completedTasks = $this->tasks()->whereIn('status', ['completed', 'verified', 'closed'])->count();
        $progress = round(($completedTasks / $totalTasks) * 100);
        $this->progress_percentage = $progress;
        
        // Compute health status
        $overdueCount = $this->tasks()->whereNotIn('status', ['completed', 'verified', 'closed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', date('Y-m-d'))
            ->count();
            
        if ($this->status == 'on_hold') {
            $this->health_status = 'on_hold';
        } elseif ($overdueCount > 3) {
            $this->health_status = 'delayed';
        } elseif ($overdueCount > 0) {
            $this->health_status = 'at_risk';
        } else {
            $this->health_status = 'on_track';
        }
        
        $this->save();
        return $progress;
    }

    public function getHealthBadge()
    {
        $status = strtolower($this->health_status ?: 'on_track');
        $badges = [
            'on_track' => '<span class="badge bg-success">On Track</span>',
            'at_risk' => '<span class="badge bg-warning text-dark">At Risk</span>',
            'delayed' => '<span class="badge bg-danger">Delayed</span>',
            'on_hold' => '<span class="badge bg-secondary">On Hold</span>',
            'completed' => '<span class="badge bg-primary">Completed</span>',
        ];
        return $badges[$status] ?? '<span class="badge bg-light text-dark border">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
    }

    public function getProgressProgressBar()
    {
        $pct = $this->progress_percentage ?: 0;
        $color = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-primary' : 'bg-info');
        return '<div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height: 6px;">
                        <div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $pct . '%;"></div>
                    </div>
                    <span class="small fw-bold text-muted">' . $pct . '%</span>
                </div>';
    }
}
