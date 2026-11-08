<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpertSlot extends Model
{
    use SoftDeletes;
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

    /** A pending or confirmed booking on this slot: then its time cannot change and it cannot be removed. */
    public function activeBooking(): ?Booking
    {
        return $this->bookings()->whereIn('status', ['pending', 'confirmed'])->first();
    }

    /** Open for booking: available and not started yet. */
    public function scopeOpen($q)
    {
        return $q->where('status', 'available')->where(function ($q) {
            $q->whereDate('date', '>', today())
              ->orWhere(fn ($t) => $t->whereDate('date', today())->where('start_time', '>', now()->format('H:i:s')));
        });
    }

    /** Another live slot of the expert on that date overlapping start-end (one ending as the next starts is fine). */
    public static function overlapping(int $expertId, string $date, string $start, string $end, ?int $exceptId = null): ?self
    {
        return static::where('expert_id', $expertId)->whereDate('date', $date)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('start_time', '<', $end)->where('end_time', '>', $start)
            ->first();
    }
}
