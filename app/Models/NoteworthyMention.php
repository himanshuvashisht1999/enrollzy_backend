<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteworthyMention extends Model
{
    protected $fillable = [
        'noteworthy_category_id',
        'title',
        'slug',
        'image',
        'subtitle',
        'description',
        'badge_text',
        'url',
        'sort_order',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NoteworthyCategory::class, 'noteworthy_category_id');
    }
}
