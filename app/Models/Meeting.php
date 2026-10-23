<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'meetings';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at', 'meeting_date'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class, 'meeting_id');
    }

    public function internalParticipants()
    {
        return $this->belongsToMany(Admin::class, 'meeting_participants', 'meeting_id', 'user_id')
            ->withPivot(['response', 'attended'])
            ->withTimestamps();
    }
}
