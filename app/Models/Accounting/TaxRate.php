<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $table = 'tax_rates';

    protected $fillable = [
        'organization_id',
        'name',
        'tax_type',
        'rate',
        'component',
        'section',
        'ledger_account_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function ledgerAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'ledger_account_id');
    }

    public function scopeGst($query)
    {
        return $query->where('tax_type', 'gst');
    }

    public function scopeTds($query)
    {
        return $query->where('tax_type', 'tds');
    }
}
