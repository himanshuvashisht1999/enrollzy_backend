<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Quota extends Model
{
    use Auditable;

    protected $table = 'cns_quotas';
    protected $fillable = ['name', 'short_name', 'description', 'sort_order', 'status'];
    protected $casts = ['status' => 'boolean'];
}
