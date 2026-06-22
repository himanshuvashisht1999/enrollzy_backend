<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FaqCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($faqCategory) {
            if (empty($faqCategory->slug)) {
                $faqCategory->slug = Str::slug($faqCategory->name);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(FaqCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FaqCategory::class, 'parent_id');
    }

    public function faqs()
    {
        return $this->hasMany(FaqItem::class, 'faq_category_id');
    }
}
