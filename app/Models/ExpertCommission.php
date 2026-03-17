<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertCommission extends Model
{
    protected $fillable = [
        'expert_id',
        'commission_type',
        'commission_value',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}
