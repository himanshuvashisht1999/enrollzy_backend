<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterestedIn extends Model
{
    protected $fillable = ['name', 'status', 'organization_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
