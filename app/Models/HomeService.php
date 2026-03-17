<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeService extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'footer_text',
        'sort_order',
        'status'
    ];
}
