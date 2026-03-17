<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionLog extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'old_value',
        'new_value',
        'action_by',
        'reason',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];
}
