<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One entry in an application's thread: a reply (staff or applicant), a staff-only note, or a status change. */
class ExpertApplicationNote extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['internal' => 'boolean'];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
