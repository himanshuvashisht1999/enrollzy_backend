<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTimeEntry extends Model
{
    use HasFactory;

    protected $table = 'task_time_entries';
    protected $guarded = ['id'];
    protected $dates = ['started_at', 'ended_at'];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function externalContact()
    {
        return $this->belongsTo(ExternalContact::class, 'external_contact_id');
    }
}
