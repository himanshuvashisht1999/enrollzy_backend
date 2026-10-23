<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadAssignment extends Model
{
    protected $guarded = [];

    public function staff()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }

    public function assigner()
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
