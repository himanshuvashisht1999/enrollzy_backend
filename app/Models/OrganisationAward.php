<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationAward extends Model
{
    protected $fillable = ['organisation_id', 'title', 'images'];

    protected $casts = [
        'images' => 'array',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
