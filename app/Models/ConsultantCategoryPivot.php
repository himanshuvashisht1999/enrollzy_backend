<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultantCategoryPivot extends Model
{
    use HasFactory;

    protected $table = 'consultant_category_pivots';

    protected $fillable = [
        'consultant_id', 'category_id'
    ];

    public function category()
    {
        return $this->belongsTo(ConsultantCategory::class, 'category_id');
    }
}
