<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'expert_id',
        'amount',
        'status',
        'reference_id',
        'payout_method',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}
