<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A request to join as an expert, made on the website and reviewed in the CRM (see App\Support\Experts\Applications). */
class ExpertApplication extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'expertise_areas' => 'array', 'levels' => 'array', 'languages' => 'array', 'session_modes' => 'array',
        'submitted_at' => 'datetime', 'decided_at' => 'datetime', 'reapply_allowed_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft' => 'Draft (not sent)', 'pending' => 'Pending review', 'under_review' => 'Under review', 'needs_changes' => 'Changes needed',
        'approved' => 'Approved', 'rejected' => 'Not approved',
    ];
    /** sent and waiting for staff: what the review list shows */
    public const REVIEW = ['pending', 'under_review', 'needs_changes'];
    /** the applicant can still edit the form and documents (a draft is not sent yet) */
    public const OPEN = ['draft', 'pending', 'under_review', 'needs_changes'];

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN, true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function notes()
    {
        return $this->hasMany(ExpertApplicationNote::class, 'application_id')->orderBy('id');
    }

    public function documents()
    {
        return $this->hasMany(ExpertDocument::class, 'application_id')->orderBy('id');
    }

    public function decider()
    {
        return $this->belongsTo(Admin::class, 'decided_by');
    }
}
