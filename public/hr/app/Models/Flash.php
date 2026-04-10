<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flash extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'flash';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'title',
        'image',
        'place',
        'url',
        'status',
    ];
}
