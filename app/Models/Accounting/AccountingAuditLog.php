<?php

namespace App\Models\Accounting;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'accounting_audit_logs';

    protected $fillable = [
        'organization_id',
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
}
