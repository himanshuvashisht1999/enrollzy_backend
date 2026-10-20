<?php

namespace App\Models\Quality\Concerns;

use App\Models\Quality\EntityAccreditation;
use App\Models\Quality\EntityRanking;

/** Gives organisation / campus / department / course their own ranking and accreditation records. */
trait HasQualityRecords
{
    public function rankings()
    {
        return $this->morphMany(EntityRanking::class, 'entity', 'entity_type', 'entity_id');
    }

    public function accreditations()
    {
        return $this->morphMany(EntityAccreditation::class, 'entity', 'entity_type', 'entity_id');
    }
}
