<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultantCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'consultant_categories';

    protected $fillable = [
        'name',
        'parent_id',
        'status',
        'organization_id'
    ];

    public function parent()
    {
        return $this->belongsTo(ConsultantCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ConsultantCategory::class, 'parent_id');
    }
}
