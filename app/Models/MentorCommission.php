<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorCommission extends Model
{
    use HasFactory;
    
    protected $fillable = ['commission_percentage', 'priority_order'];

    protected $casts = [
        'priority_order' => 'array',
    ];
}
