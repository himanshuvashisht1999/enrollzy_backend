<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(Staff::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }
}
