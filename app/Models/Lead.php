<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'type',
        'leadable_id',
        'leadable_type',
    ];

    public function leadable()
    {
        return $this->morphTo();
    }
}
