<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class RoundType extends Model
{
    use Auditable;

    protected $table = 'cns_round_types';
    protected $fillable = ['name', 'is_fresh_registration_default', 'sort_order', 'status'];
    protected $casts = ['is_fresh_registration_default' => 'boolean', 'status' => 'boolean'];
}
