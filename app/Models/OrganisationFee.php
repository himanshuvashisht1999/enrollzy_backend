<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationFee extends Model
{
    protected $fillable = [
        'organisation_id', 'grade', 'tuition_fee_annual', 'admission_fee',
        'development_fee', 'transport_fee', 'hostel_fee', 'other_charges',
        'scholarship_details'
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
