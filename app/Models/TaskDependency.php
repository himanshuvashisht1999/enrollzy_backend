<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDependency extends Model
{
    use HasFactory;

    protected $table = 'task_dependencies';
    protected $guarded = ['id'];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function prerequisiteTask()
    {
        return $this->belongsTo(Tasks::class, 'depends_on_task_id');
    }
}
