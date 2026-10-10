<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'sort_order',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset($this->file_path) : null;
    }
}
