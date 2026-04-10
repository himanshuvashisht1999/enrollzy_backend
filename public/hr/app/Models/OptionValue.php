<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OptionValue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'option_id',
        'value',
        'admin_id',
    ];

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
