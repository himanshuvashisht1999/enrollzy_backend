<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationSubType extends Model
{
    protected $fillable = ['organisation_type_id', 'title', 'status', 'sort_order'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function organisationType()
    {
        return $this->belongsTo(OrganisationType::class);
    }
}
