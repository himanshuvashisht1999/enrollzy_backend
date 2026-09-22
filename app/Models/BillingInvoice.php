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
        'client_type', // 'organisation' or 'client'
        'billing_client_id',
        'organisation_id',
        'campus_id',
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

    public function client()
    {
        return $this->belongsTo(BillingClient::class, 'billing_client_id');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function items()
    {
        return $this->hasMany(BillingInvoiceItem::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(BillingPayment::class, 'invoice_id');
    }

    /**
     * Get the recipient entity display name
     */
    public function getRecipientNameAttribute(): string
    {
        if ($this->client_type === 'client' && $this->client) {
            return $this->client->name;
        }

        if ($this->organisation) {
            return $this->campus ? $this->organisation->name . ' (' . $this->campus->campus_name . ')' : $this->organisation->name;
        }

        return 'N/A';
    }
}
