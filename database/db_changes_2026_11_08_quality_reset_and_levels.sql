-- =====================================================================================================
-- Rankings & Accreditation: one-time reset (site not live yet) + corrected levels
-- Date: 8 Nov 2026
--
-- 1. DELETES every ranking, accreditation and statutory-approval record (all organisations, campuses,
--    departments and courses). Awards, placements, reviews and the body / category lists are NOT touched.
-- 2. Sets at which level (organisation / campus / department / course) each body and category may be added.
--
-- Safe to run more than once. Take a backup (phpMyAdmin > Export) before running it.
-- =====================================================================================================

START TRANSACTION;

-- ---------------------------------------------------------------- 1. delete the records
DELETE FROM entity_rankings;
DELETE FROM entity_accreditations;
DELETE FROM entity_approvals;

-- ---------------------------------------------------------------- 2. levels: approval bodies
-- Statutory approvals are issued for a campus address, with seat intake per course
UPDATE accreditation_bodies SET applicable_levels = '["campus","course"]'
 WHERE short_name IN ('AICTE', 'NMC', 'BCI', 'PCI', 'DCI', 'COA', 'NCTE', 'INC');

-- AACSB accredits a business school (a standalone B-school, or a university's school)
UPDATE accreditation_bodies SET applicable_levels = '["organisation","department"]' WHERE short_name = 'AACSB';

-- AMBA accredits MBA programmes (and the school offering them)
UPDATE accreditation_bodies SET applicable_levels = '["organisation","department","course"]' WHERE short_name = 'AMBA';

-- unchanged and correct: NAAC (organisation, campus), NBA + ABET (department, course), UGC + AIU (organisation), ICAR (organisation, campus)

-- ---------------------------------------------------------------- 2. levels: ranking categories
-- NIRF discipline rankings rank an institution or a school, never a campus or a course
UPDATE ranking_categories c JOIN ranking_bodies b ON b.id = c.ranking_body_id
   SET c.applicable_levels = '["organisation","department"]'
 WHERE b.short_name = 'NIRF'
   AND c.name IN ('Engineering', 'Management', 'Pharmacy', 'Medical', 'Dental', 'Law', 'Architecture', 'Agriculture');

-- QS / THE world, regional, India and impact tables rank the whole university
UPDATE ranking_categories c JOIN ranking_bodies b ON b.id = c.ranking_body_id
   SET c.applicable_levels = '["organisation"]'
 WHERE b.short_name IN ('QS', 'THE') AND c.name IN ('World', 'Asia', 'India', 'Impact');

-- QS / THE subject rankings: the university, or its school for that subject
UPDATE ranking_categories c JOIN ranking_bodies b ON b.id = c.ranking_body_id
   SET c.applicable_levels = '["organisation","department"]'
 WHERE b.short_name IN ('QS', 'THE') AND c.name = 'Subject';

-- Media rankings (India Today, Outlook-ICARE, The Week, Education World) rank colleges and campuses, never a course
UPDATE ranking_categories c JOIN ranking_bodies b ON b.id = c.ranking_body_id
   SET c.applicable_levels = '["organisation","campus","department"]'
 WHERE b.short_name IN ('India Today', 'Outlook', 'The Week', 'EW');

-- unchanged and correct: NIRF Overall / Colleges / Research Institutions / Innovation (organisation, campus),
-- NIRF University / Open University / State Public University / Skill University (organisation)

COMMIT;

-- ---------------------------------------------------------------- check (should show 0, 0, 0)
SELECT (SELECT COUNT(*) FROM entity_rankings) AS rankings_left,
       (SELECT COUNT(*) FROM entity_accreditations) AS accreditations_left,
       (SELECT COUNT(*) FROM entity_approvals) AS approvals_left;
