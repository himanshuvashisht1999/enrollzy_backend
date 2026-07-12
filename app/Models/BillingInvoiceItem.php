<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'billing_service_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }

    public function service()
    {
        return $this->belongsTo(BillingService::class, 'billing_service_id');
    }
}
