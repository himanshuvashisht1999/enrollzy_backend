<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customer_categories';

    protected $fillable = [
        'name',
        'parent_id',
        'customer_type',
        'status',
        'organization_id'
    ];

    public function parent()
    {
        return $this->belongsTo(CustomerCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CustomerCategory::class, 'parent_id');
    }
}
