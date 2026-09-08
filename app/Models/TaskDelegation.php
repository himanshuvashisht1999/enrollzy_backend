<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDelegation extends Model
{
    use HasFactory;

    protected $table = 'task_delegations';
    protected $guarded = ['id'];
    protected $dates = ['delegated_at'];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function delegator()
    {
        return $this->belongsTo(Admin::class, 'from_user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(Admin::class, 'to_user_id');
    }

    public function targetTeam()
    {
        return $this->belongsTo(Team::class, 'to_team_id');
    }

    public function targetExternalContact()
    {
        return $this->belongsTo(ExternalContact::class, 'external_contact_id');
    }

    public function targetExternalTeam()
    {
        return $this->belongsTo(ExternalTeam::class, 'external_team_id');
    }
}
