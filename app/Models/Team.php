<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teams';
    protected $guarded = ['id'];

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function parent()
    {
        return $this->belongsTo(Team::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Team::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function leader()
    {
        return $this->belongsTo(Admin::class, 'team_leader_id');
    }

    public function members()
    {
        return $this->belongsToMany(Admin::class, 'team_members', 'team_id', 'user_id')
            ->withPivot(['id', 'role', 'is_team_leader', 'joined_at', 'status'])
            ->withTimestamps();
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class, 'team_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'team_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'team_id');
    }

    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'team_id');
    }

    public function getActiveMembersCountAttribute()
    {
        return $this->members()->wherePivot('status', 'active')->count();
    }

    public function getAllDescendantIds()
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        return $ids;
    }
}
