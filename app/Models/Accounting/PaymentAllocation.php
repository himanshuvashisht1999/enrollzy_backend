<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $table = 'payment_allocations';

    protected $fillable = [
        'organization_id',
        'transaction_id',
        'allocatable_type',
        'allocatable_id',
        'allocated_amount',
        'discount_allowed',
        'tds_deducted',
        'allocation_date',
        'notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'discount_allowed' => 'decimal:2',
        'tds_deducted' => 'decimal:2',
        'allocation_date' => 'date',
    ];

    public function transaction()
    {
        return $this->belongsTo(AccountingTransaction::class, 'transaction_id');
    }

    public function allocatable()
    {
        return $this->morphTo(null, 'allocatable_type', 'allocatable_id');
    }
}
