<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminImage extends Model
{
    protected $table = 'admin_images';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

}
