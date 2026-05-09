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
    protected $dates = ['deleted_at'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function assigned_by()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    public function milestone_assigned()
    {
        return $this->belongsTo(Milestone::class, 'milestone');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id');
    }
}
