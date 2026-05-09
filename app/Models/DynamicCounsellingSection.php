<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicCounsellingSection extends Model
{
    protected $fillable = ['counselling_id', 'heading', 'content', 'order', 'status'];

    protected $casts = [
        'content' => 'array',
    ];

    public function counselling()
    {
        return $this->belongsTo(Counselling::class, 'counselling_id');
    }
}
