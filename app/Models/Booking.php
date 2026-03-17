<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'expert_id',
        'slot_id',
        'booking_date',
        'status',
        'amount',
        'platform_fee',
        'expert_earning',
        'payment_status',
        'meeting_link',
        'notes',
        // Commission Snapshot
        'commission_override_type',
        'commission_override_value',
        'override_reason',
        'override_by',
        'applied_commission_type',
        'applied_commission_rate',
        'applied_gst_rate',
        'applied_tds_rate',
        'commission_breakdown',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'expert_earning' => 'decimal:2',
        'commission_breakdown' => 'array',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->booking_id = 'BK-' . strtoupper(Str::random(10));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function slot()
    {
        return $this->belongsTo(ExpertSlot::class, 'slot_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
