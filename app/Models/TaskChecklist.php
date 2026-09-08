<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    use HasFactory;

    protected $table = 'task_checklists';
    protected $guarded = ['id'];
    protected $dates = ['completed_at'];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(Admin::class, 'completed_by');
    }
}
