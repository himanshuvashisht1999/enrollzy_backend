<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoHomepageFaq extends Model
{
    use HasFactory;

    protected $table = 'seo_homepage_faqs';

    protected $fillable = [
        'question',
        'answer',
        'sort_order',
    ];
}
