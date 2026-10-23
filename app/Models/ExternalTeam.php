<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalTeam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'external_teams';
    protected $guarded = ['id'];

    public function organization()
    {
        return $this->belongsTo(ExternalOrganization::class, 'external_organization_id');
    }

    public function leaderContact()
    {
        return $this->belongsTo(ExternalContact::class, 'team_leader_contact_id');
    }

    public function members()
    {
        return $this->belongsToMany(ExternalContact::class, 'external_team_members', 'external_team_id', 'external_contact_id')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }
}
