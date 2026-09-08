<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRecurrence extends Model
{
    use HasFactory;

    protected $table = 'task_recurrences';
    protected $guarded = ['id'];
    protected $dates = ['start_date', 'end_date', 'next_run_at'];
    protected $casts = [
        'days_of_week' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }
}
