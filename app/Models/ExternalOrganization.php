<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalOrganization extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'external_organizations';
    protected $guarded = ['id'];

    public function contacts()
    {
        return $this->hasMany(ExternalContact::class, 'external_organization_id');
    }

    public function teams()
    {
        return $this->hasMany(ExternalTeam::class, 'external_organization_id');
    }

    public function projectExternalMembers()
    {
        return $this->hasMany(ProjectExternalMember::class, 'external_organization_id');
    }
}
