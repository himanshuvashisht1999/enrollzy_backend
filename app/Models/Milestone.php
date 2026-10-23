<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'milestones';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at', 'start_date', 'due_date'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
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
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function externalOrganization()
    {
        return $this->belongsTo(ExternalOrganization::class, 'external_organization_id');
    }

    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'milestone');
    }

    public function calculateProgress()
    {
        $total = $this->tasks()->count();
        if ($total == 0) return 0;
        $completed = $this->tasks()->whereIn('status', ['completed', 'verified', 'closed'])->count();
        $progress = round(($completed / $total) * 100);
        $this->progress_percentage = $progress;
        if ($progress == 100) {
            $this->status = 'completed';
        } elseif ($progress > 0 && $this->status == 'pending') {
            $this->status = 'in_progress';
        }
        $this->save();
        return $progress;
    }
}
