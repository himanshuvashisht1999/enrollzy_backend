<?php

namespace App\Models\Accounting;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_bills';

    protected $fillable = [
        'organization_id',
        'bill_number',
        'vendor_bill_ref',
        'party_id',
        'bill_date',
        'due_date',
        'subtotal',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_tax',
        'tds_rate_id',
        'tds_rate',
        'tds_amount',
        'total_amount',
        'net_payable',
        'paid_amount',
        'balance_due',
        'payment_status',
        'status',
        'notes',
        'created_by',
        'posted_by',
        'posted_at',
        'transaction_id',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'tds_rate' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function items()
    {
        return $this->hasMany(VendorBillItem::class, 'vendor_bill_id');
    }

    public function tdsRate()
    {
        return $this->belongsTo(TaxRate::class, 'tds_rate_id');
    }

    public function transaction()
    {
        return $this->belongsTo(AccountingTransaction::class, 'transaction_id');
    }

    public function paymentAllocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'allocatable_id')->where('allocatable_type', 'vendor_bill');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(Admin::class, 'posted_by');
    }

    public function isOverdue()
    {
        return $this->balance_due > 0 && $this->due_date < now()->toDateString();
    }
}
