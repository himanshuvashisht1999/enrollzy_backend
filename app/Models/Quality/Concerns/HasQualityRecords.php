<?php

namespace App\Models\Quality\Concerns;

use App\Models\Quality\EntityAccreditation;
use App\Models\Quality\EntityApproval;
use App\Models\Quality\EntityAward;
use App\Models\Quality\EntityPlacement;
use App\Models\Quality\EntityRanking;
use App\Models\Quality\EntityReview;

/** Gives organisation / campus / department / course their own ranking and accreditation records. */
trait HasQualityRecords
{
    public function rankings()
    {
        return $this->morphMany(EntityRanking::class, 'entity', 'entity_type', 'entity_id');
    }

    public function approvals()
    {
        return $this->morphMany(EntityApproval::class, 'entity', 'entity_type', 'entity_id');
    }

    /** Named entityAwards() because organisations already have an awards() relation to the old, empty organisation_awards table. */
    public function entityAwards()
    {
        return $this->morphMany(EntityAward::class, 'entity', 'entity_type', 'entity_id');
    }

    public function entityPlacements()
    {
        return $this->morphMany(EntityPlacement::class, 'entity', 'entity_type', 'entity_id');
    }

    /** Named entityReviews() because campuses already have a legacy `reviews` column that would shadow a relation called reviews. */
    public function entityReviews()
    {
        return $this->morphMany(EntityReview::class, 'entity', 'entity_type', 'entity_id');
    }

    public function accreditations()
    {
        return $this->morphMany(EntityAccreditation::class, 'entity', 'entity_type', 'entity_id');
    }
}
