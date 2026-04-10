<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marque extends Model
{
    protected $table = 'marquee';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
}
