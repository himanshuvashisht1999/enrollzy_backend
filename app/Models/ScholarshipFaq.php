<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScholarshipFaq extends Model
{
    use HasFactory;

    protected $table = 'scholarship_faqs';

    protected $fillable = [
        'scholarship_id', 'question', 'answer', 'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer'
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
