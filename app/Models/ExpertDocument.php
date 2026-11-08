<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A qualification, certificate, award, ID... of an application or an expert; the file is private (App\Support\Experts\Docs). */
class ExpertDocument extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['show_on_profile' => 'boolean', 'reviewed_at' => 'datetime'];

    public const TYPES = [
        'qualification' => 'Degree / qualification', 'certificate' => 'Certification', 'award' => 'Award / recognition',
        'achievement' => 'Achievement', 'experience' => 'Experience letter', 'id_proof' => 'ID proof', 'other' => 'Other',
    ];
    public const STATUSES = ['pending' => 'Pending check', 'verified' => 'Verified', 'rejected' => 'Rejected'];

    /** listed on the public profile: verified, the expert's choice, and never an ID proof */
    public function isPublic(): bool
    {
        return $this->status === 'verified' && $this->show_on_profile && $this->type !== 'id_proof';
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}
