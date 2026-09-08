<?php

namespace App\Models\Accounting;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_transactions';

    protected $fillable = [
        'organization_id',
        'transaction_number',
        'transaction_date',
        'transaction_type',
        'reference_type',
        'reference_id',
        'total_amount',
        'description',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function poster()
    {
        return $this->belongsTo(Admin::class, 'posted_by');
    }

    public function reference()
    {
        return $this->morphTo(null, 'reference_type', 'reference_id');
    }

    public function paymentAllocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'transaction_id');
    }
}
