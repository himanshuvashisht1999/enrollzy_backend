<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScholarshipDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id', 'document_name', 'is_mandatory'
    ];

    protected $casts = [
        'is_mandatory' => 'integer'
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
