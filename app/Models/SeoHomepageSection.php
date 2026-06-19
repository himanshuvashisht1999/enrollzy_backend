<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoHomepageSection extends Model
{
    use HasFactory;

    protected $table = 'seo_homepage_sections';

    protected $fillable = [
        'section_name',
        'section_slug',
        'title',
        'subtitle',
        'description',
        'image',
        'button_text',
        'button_link',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
