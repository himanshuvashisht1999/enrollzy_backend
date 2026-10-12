<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationType extends Model
{
    protected $fillable = ['title', 'status', 'sort_order'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function subTypes()
    {
        return $this->hasMany(OrganisationSubType::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_organisation_type', 'organisation_type_id', 'facility_id')->withTimestamps();
    }
}
