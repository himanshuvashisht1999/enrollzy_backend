<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ownership Type master (Masters → Ownership Types): the choices for organisations.ownership_type on the Institute form. */
class OwnershipType extends Model
{
    protected $fillable = ['title', 'status', 'sort_order'];

    protected $casts = ['status' => 'boolean'];
}
