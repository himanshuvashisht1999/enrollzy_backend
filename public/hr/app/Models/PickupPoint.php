<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickupPoint extends Model
{

    use SoftDeletes;
    protected $table = 'pickup_points';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
}
