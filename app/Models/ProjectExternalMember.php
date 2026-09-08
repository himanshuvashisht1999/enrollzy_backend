<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectExternalMember extends Model
{
    use HasFactory;

    protected $table = 'project_external_members';
    protected $guarded = ['id'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function organization()
    {
        return $this->belongsTo(ExternalOrganization::class, 'external_organization_id');
    }

    public function contact()
    {
        return $this->belongsTo(ExternalContact::class, 'external_contact_id');
    }

    public function team()
    {
        return $this->belongsTo(ExternalTeam::class, 'external_team_id');
    }
}
