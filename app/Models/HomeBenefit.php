<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeBenefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'icon',
        'content',
        'reward_amount',
        'sort_order',
        'status'
    ];
}
