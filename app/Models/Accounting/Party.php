<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parties';

    protected $fillable = [
        'organization_id',
        'party_type',
        'name',
        'legal_name',
        'phone',
        'email',
        'gstin',
        'pan',
        'billing_address',
        'shipping_address',
        'state',
        'state_code',
        'is_customer',
        'is_vendor',
        'is_investor',
        'is_founder',
        'is_employee',
        'opening_balance',
        'current_balance',
        'status',
    ];

    protected $casts = [
        'is_customer' => 'boolean',
        'is_vendor' => 'boolean',
        'is_investor' => 'boolean',
        'is_founder' => 'boolean',
        'is_employee' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class, 'party_id');
    }

    public function vendorBills()
    {
        return $this->hasMany(VendorBill::class, 'party_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'party_id');
    }

    public function fundingRecords()
    {
        return $this->hasMany(FundingRecord::class, 'party_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'party_id');
    }

    public function scopeCustomers($query)
    {
        return $query->where('is_customer', true);
    }

    public function scopeVendors($query)
    {
        return $query->where('is_vendor', true);
    }

    public function scopeFounders($query)
    {
        return $query->where('is_founder', true);
    }

    public function scopeInvestors($query)
    {
        return $query->where('is_investor', true);
    }
}
