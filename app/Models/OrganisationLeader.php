<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationLeader extends Model
{
    protected $table = 'organisation_leaders';

    protected $fillable = [
        'organisation_id',
        'name',
        'designation',
        'photo_url',
        'bio',
        'linkedin_url',
        'is_founder',
        'sort_order',
    ];

    protected $casts = [
        'is_founder' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
