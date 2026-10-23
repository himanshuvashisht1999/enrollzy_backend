<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignee extends Model
{
    use HasFactory;

    protected $table = 'task_assignees';
    protected $guarded = ['id'];
    protected $dates = ['assigned_at', 'due_date'];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function externalContact()
    {
        return $this->belongsTo(ExternalContact::class, 'external_contact_id');
    }

    public function externalTeam()
    {
        return $this->belongsTo(ExternalTeam::class, 'external_team_id');
    }

    public function assigner()
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }

    public function getDisplayNameAttribute()
    {
        if ($this->assignee_type === 'internal_user') {
            return $this->user->name ?? 'Unassigned';
        } elseif ($this->assignee_type === 'internal_team') {
            return ($this->team->name ?? 'Team') . ' (Team)';
        } elseif ($this->assignee_type === 'external_contact') {
            return ($this->externalContact->name ?? 'External') . ' (Partner)';
        } elseif ($this->assignee_type === 'external_team') {
            return ($this->externalTeam->name ?? 'External Team') . ' (Agency)';
        }
        return 'N/A';
    }
}
