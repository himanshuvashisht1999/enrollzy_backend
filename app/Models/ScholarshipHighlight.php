<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScholarshipHighlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id', 'highlight_text', 'highlight_icon', 'sort_order'
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
