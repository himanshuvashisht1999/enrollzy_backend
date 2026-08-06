<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoMeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'seoable_type', 'seoable_id',
        'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'og_image',
        'canonical_url', 'no_index', 'no_follow',
    ];

    public function seoable()
    {
        return $this->morphTo();
    }
}
