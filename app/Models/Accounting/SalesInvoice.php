<?php

namespace App\Models\Accounting;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_invoices';

    protected $fillable = [
        'organization_id',
        'invoice_number',
        'party_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_tax',
        'tds_deducted',
        'total_amount',
        'paid_amount',
        'balance_due',
        'payment_status',
        'status',
        'place_of_supply',
        'notes',
        'terms',
        'created_by',
        'posted_by',
        'posted_at',
        'transaction_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'tds_deducted' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
        return $this->hasMany(SalesInvoiceItem::class, 'sales_invoice_id');
    }

    public function transaction()
    {
        return $this->belongsTo(AccountingTransaction::class, 'transaction_id');
    }

    public function paymentAllocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'allocatable_id')->where('allocatable_type', 'sales_invoice');
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
