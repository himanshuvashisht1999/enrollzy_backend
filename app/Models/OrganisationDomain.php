<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationDomain extends Model
{
    protected $table = 'organisation_domains';

    protected $fillable = [
        'organisation_id',
        'domain_name',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
