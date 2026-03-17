<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertSlot extends Model
{
    protected $fillable = [
        'expert_id',
        'date',
        'start_time',
        'end_time',
        'is_recurring',
        'recurring_day',
        'status',
        'mode',
        'cost',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'cost' => 'decimal:2',
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'slot_id');
    }
}
