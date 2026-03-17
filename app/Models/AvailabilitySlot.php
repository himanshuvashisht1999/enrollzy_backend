<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilitySlot extends Model
{
    protected $fillable = [
        'provider_id',
        'provider_type',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    public function provider()
    {
        return $this->morphTo();
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }
}
