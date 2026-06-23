<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'career_coach_points' => 'array',
        'hero_trust_points' => 'array',
        'form_trust_points' => 'array',
        'why_contact_cards' => 'array',
    ];
}
