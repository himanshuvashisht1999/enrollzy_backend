<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A photo (uploaded file) or video (YouTube / Vimeo link) of an institute; campus_id '' is the institute itself,
 * otherwise the branch it belongs to. Managed by App\Support\Institutes\InstituteFields::syncMedia.
 */
class OrganisationMedia extends Model
{
    protected $table = 'organisation_media';

    protected $fillable = ['organisation_id', 'campus_id', 'type', 'file_path', 'video_url', 'caption', 'sort_order'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
