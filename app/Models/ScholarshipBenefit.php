<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScholarshipBenefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id', 'benefit_title', 'benefit_description', 'benefit_amount', 'sort_order'
    ];

    protected $casts = [
        'benefit_amount' => 'decimal:2',
        'sort_order' => 'integer'
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
