<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $guarded = [];

    public function expert()
    {
        return $this->belongsTo(Expert::class, 'expert_id');
    }
}
