<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallingManualUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'phone',
        'status',
        'category_id',
        'institute_id',
    ];

    public function category()
    {
        return $this->belongsTo(CustomerCategories::class, 'category_id');
    }
}
