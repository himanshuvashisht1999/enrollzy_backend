<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\Project;
use App\Models\Milestone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        return $this->belongsTo(Admin::class, 'staff_id');
    }

    public function assigned_to()
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function mileStoneAssigned()
    {
        return $this->belongsTo(Milestone::class, 'milestone');
    }
}
