<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationSport extends Model
{
    protected $fillable = ['organisation_id', 'sport_id', 'images'];

    protected $casts = [
        'images' => 'array',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }
}
