<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionPolicy extends Model
{
    protected $fillable = [
        'policy_type', // global, category
        'expert_category',
        'expert_category_id',
        'commission_type', // percentage, flat_fee
        'commission_value',
        'gst_applicable',
        'tds_applicable',
        'is_active',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'gst_applicable' => 'boolean',
        'tds_applicable' => 'boolean',
        'is_active' => 'boolean',
    ];
}
