<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'admin_id',
    ];

    public function values()
    {
        return $this->hasMany(OptionValue::class);
    }
}
