<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCategories extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customer_categories';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->hasMany(User::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(CustomerCategories::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CustomerCategories::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive')->withCount('users');
    }
}
