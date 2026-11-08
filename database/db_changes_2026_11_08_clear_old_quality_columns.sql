-- =====================================================================================================
-- Empty the OLD single-value quality fields on every organisation
-- Date: 8 Nov 2026
--
-- These fields were replaced by the Rankings & Accreditation records (entity_rankings / entity_accreditations /
-- entity_approvals). The CRM no longer shows, asks the AI for, or saves them, and the website no longer reads them.
--
--   ugc_recognized, ugc_approval_number, aicte_approved, naac_accredited, naac_grade,
--   nirf_rank_overall, nirf_rank_category, international_accreditations, statutory_approvals
--
-- The columns themselves are kept (empty), so nothing that still mentions them breaks.
-- No other organisation data is touched. Safe to run more than once. Take a backup (phpMyAdmin > Export) first.
-- =====================================================================================================

START TRANSACTION;

UPDATE organisations
   SET ugc_recognized = NULL,
       ugc_approval_number = NULL,
       aicte_approved = NULL,
       naac_accredited = NULL,
       naac_grade = NULL,
       nirf_rank_overall = NULL,
       nirf_rank_category = NULL,
       international_accreditations = NULL,
       statutory_approvals = NULL;

COMMIT;

-- ---------------------------------------------------------------- check (should show 0)
SELECT COUNT(*) AS organisations_with_old_values_left
  FROM organisations
 WHERE ugc_recognized IS NOT NULL OR ugc_approval_number IS NOT NULL OR aicte_approved IS NOT NULL
    OR naac_accredited IS NOT NULL OR naac_grade IS NOT NULL OR nirf_rank_overall IS NOT NULL
    OR nirf_rank_category IS NOT NULL OR international_accreditations IS NOT NULL OR statutory_approvals IS NOT NULL;
