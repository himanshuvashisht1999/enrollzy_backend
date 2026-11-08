<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An expert's profile edit from the website, waiting for (or decided by) staff: see App\Support\Experts\ProfileChanges. */
class ExpertProfileChange extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['changes' => 'array', 'previous' => 'array', 'reviewed_at' => 'datetime'];

    public const STATUSES = ['pending' => 'Waiting', 'approved' => 'Approved', 'rejected' => 'Rejected', 'withdrawn' => 'Withdrawn'];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }
}
