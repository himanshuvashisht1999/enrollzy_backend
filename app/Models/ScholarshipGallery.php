<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScholarshipGallery extends Model
{
    use HasFactory;

    protected $table = 'scholarship_gallery';

    protected $fillable = [
        'scholarship_id', 'image', 'title', 'alt_text', 'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer'
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
