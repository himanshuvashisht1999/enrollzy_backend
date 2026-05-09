<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvancePayTransaction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'amount',
        'advance_pay_ids',
        'transaction_type',
        'transaction_for',
        'log',
        'staff_id',
        'status',
        'month',
        'year',
        'organization_id'
    ];

    public function staff()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }
}
