<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company_type',
        'contact_person',
        'email',
        'phone',
        'gstin',
        'tan_number',
        'pan_number',
        'cin_number',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public static function companyTypes(): array
    {
        return [
            'Private Limited',
            'Public Limited',
            'LLP',
            'Partnership Firm',
            'Sole Proprietorship',
            'Trust / Society / NGO',
            'Educational Institution / University',
            'Individual',
            'Other',
        ];
    }
}
