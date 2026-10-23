<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'external_contacts';
    protected $guarded = ['id'];

    public function organization()
    {
        return $this->belongsTo(ExternalOrganization::class, 'external_organization_id');
    }

    public function externalTeams()
    {
        return $this->belongsToMany(ExternalTeam::class, 'external_team_members', 'external_contact_id', 'external_team_id')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }
}
