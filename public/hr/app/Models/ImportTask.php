<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportTask extends Model
{
    protected $table = 'import_task';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'status',
        'file_path',
        'progress',
        'result',
    ];
}
