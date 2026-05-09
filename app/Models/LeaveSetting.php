<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }
}
