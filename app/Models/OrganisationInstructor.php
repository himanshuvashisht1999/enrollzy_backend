<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationInstructor extends Model
{
    protected $table = 'organisation_instructors';

    protected $fillable = [
        'organisation_id',
        'name',
        'photo_url',
        'designation',
        'current_company_or_institution',
        'experience_years',
        'linkedin_url',
        'bio',
        'rating',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'experience_years' => 'integer',
        'rating' => 'decimal:2',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
