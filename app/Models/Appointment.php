<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'availability_slot_id',
        'description',
        'meeting_link',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function availability_slot()
    {
        return $this->belongsTo(AvailabilitySlot::class);
    }
}
