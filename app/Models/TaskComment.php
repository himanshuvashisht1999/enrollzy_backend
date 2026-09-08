<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;
    
    protected $table = 'task_comments';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function replies()
    {
        return $this->hasMany(TaskComment::class, 'parent_comment_id');
    }
}
