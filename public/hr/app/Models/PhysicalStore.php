<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalStore extends Model
{

    protected $table = 'physical_stores';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
}
