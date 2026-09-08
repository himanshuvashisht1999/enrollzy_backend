<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\HrDepartment;
use App\Models\Designation;
use App\Models\Organization;
use App\Models\StaffType;
use App\Models\Team;
use App\Models\Project;
use App\Models\Tasks;
use App\Models\TaskAssignee;
use App\Models\TaskTimeEntry;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasRoles;

    protected $guard_name = 'admin';

    protected $table = 'admin';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'staff_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function staffType()
    {
        return $this->belongsTo(StaffType::class, 'staff_type_id');
    }

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    // Work Management Relations
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')
            ->withPivot(['id', 'role', 'is_team_leader', 'joined_at', 'status'])
            ->withTimestamps();
    }

    public function leadingTeams()
    {
        return $this->hasMany(Team::class, 'team_leader_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members', 'user_id', 'project_id')
            ->withPivot(['role', 'access_level', 'joined_at', 'status'])
            ->withTimestamps();
    }

    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'staff_id');
    }

    public function taskAssigneeRecords()
    {
        return $this->hasMany(TaskAssignee::class, 'user_id');
    }

    public function timeEntries()
    {
        return $this->hasMany(TaskTimeEntry::class, 'user_id');
    }
}
