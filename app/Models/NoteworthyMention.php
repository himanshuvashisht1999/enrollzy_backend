<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteworthyMention extends Model
{
    protected $fillable = [
        'noteworthy_category_id',
        'title',
        'image',
        'subtitle',
        'badge_text',
        'url',
        'sort_order',
        'status'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(NoteworthyCategory::class, 'noteworthy_category_id');
    }
}
