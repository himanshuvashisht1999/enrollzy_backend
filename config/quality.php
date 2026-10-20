<?php

/**
 * Rules for the values that are COMPUTED (never typed) in the Rankings & Accreditation module.
 * These are business decisions: change them here, nothing is stored.
 */
return [
    // "Top" institution: the latest VERIFIED NIRF Overall ranking is at or better than this rank (a band counts by its upper end).
    'top_nirf_overall_rank_max' => 50,

    // ROI label from "years of the median annual salary needed to repay the total course fee" (total fee / median salary).
    // At or below 'high' = High, at or below 'medium' = Medium, above = Low. Needs a VERIFIED placement row with a median salary.
    'roi_payback_years' => ['high' => 2.0, 'medium' => 4.0],

    // How many reviews are needed before an average is shown to students.
    'min_reviews_to_show_average' => 1,
];
