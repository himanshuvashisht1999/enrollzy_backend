<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationPartner extends Model
{
    protected $table = 'organisation_partners';

    protected $fillable = [
        'organisation_id',
        'partner_type',
        'partner_name',
        'partner_logo',
        'partner_website',
        'relationship_type',
        'co_branded_credential',
        'programs_count',
        'description',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'co_branded_credential' => 'boolean',
        'status' => 'boolean',
        'programs_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
