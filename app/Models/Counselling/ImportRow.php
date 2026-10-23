<?php

namespace App\Models\Counselling;

use Illuminate\Database\Eloquent\Model;

class ImportRow extends Model
{
    protected $table = 'cns_import_rows';
    public $timestamps = false;
    protected $fillable = ['batch_id', 'row_no', 'raw', 'resolved', 'status', 'messages'];
    protected $casts = ['raw' => 'array', 'resolved' => 'array', 'messages' => 'array'];
}
