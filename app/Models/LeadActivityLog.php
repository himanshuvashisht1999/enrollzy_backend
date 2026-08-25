<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivityLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'properties' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
