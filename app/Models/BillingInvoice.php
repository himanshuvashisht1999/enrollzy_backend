<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'organisation_id',
        'issue_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_tax',
        'total_amount',
        'status',
        'terms_conditions',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function items()
    {
        return $this->hasMany(BillingInvoiceItem::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(BillingPayment::class, 'invoice_id');
    }
}
