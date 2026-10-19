<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use Auditable;

    protected $table = 'exm_cities';
    protected $fillable = ['name', 'state', 'status'];
    protected $casts = ['status' => 'boolean'];
}
