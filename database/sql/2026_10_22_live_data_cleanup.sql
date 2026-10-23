-- =====================================================================================================
-- LIVE DATA, 2026-10-22. No structure changes today (the 2026_10_27 SQL files are already on live).
--
-- PART 1  deletes the client's "TEST Dynamic Exam (dummy)" (asked for by the client).
--         The database also removes, automatically (ON DELETE CASCADE), the old-module
--         "TEST Counselling (dummy)" that belongs to it and that counselling's sections.
-- PART 2  only SHOWS duplicate cycle years (same counselling / exam, year and session). Nothing is deleted:
--         decide which one to keep, then delete the other from the admin (Cycle page > Delete), which keeps
--         an audit record and can be undone by a developer.
--
-- BEFORE YOU RUN: take a full backup. Run in phpMyAdmin (SQL tab) on the live database.
-- =====================================================================================================

-- ---------- PART 1: delete TEST Dynamic Exam (dummy) ----------

-- 1a. Check first: should show exactly one row "TEST Dynamic Exam (dummy)"
SELECT id, name, slug FROM dynamic_exams WHERE slug = 'test-dynamic-exam-dummy';

-- 1b. What goes with it (old Dynamic Counselling module): should show "TEST Counselling (dummy)" or nothing
SELECT id, counselling_name FROM counsellings
WHERE dynamic_exam_id IN (SELECT id FROM dynamic_exams WHERE slug = 'test-dynamic-exam-dummy');

-- 1c. Delete (its sections, and the old counselling above with its sections, go automatically)
DELETE FROM dynamic_exams WHERE slug = 'test-dynamic-exam-dummy' AND name = 'TEST Dynamic Exam (dummy)';

-- 1d. Check: should show 0 | 0
SELECT
  (SELECT COUNT(*) FROM dynamic_exams WHERE slug = 'test-dynamic-exam-dummy') AS dummy_exam_left,
  (SELECT COUNT(*) FROM counsellings WHERE counselling_name = 'TEST Counselling (dummy)') AS dummy_old_counselling_left;


-- ---------- PART 2: duplicate cycle years (report only) ----------

-- 2a. Counselling cycles: same counselling + year + session label more than once
SELECT c.counselling_id, s.name AS counselling, c.year, IFNULL(c.session_label, '') AS session_label,
       GROUP_CONCAT(CONCAT('#', c.id, ' created ', DATE_FORMAT(c.created_at, '%d %b %Y %H:%i'),
                           IF(c.is_current, ' (current)', ''), IF(c.is_public, ' (public)', '')) ORDER BY c.id SEPARATOR ' | ') AS cycles
FROM cns_cycles c JOIN cns_counsellings s ON s.id = c.counselling_id
WHERE c.deleted_at IS NULL
GROUP BY c.counselling_id, s.name, c.year, IFNULL(c.session_label, '')
HAVING COUNT(*) > 1;

-- 2b. Exam cycles: same exam + year + session label more than once
SELECT x.exam_id, e.name AS exam, x.year, IFNULL(x.session_label, '') AS session_label,
       GROUP_CONCAT(CONCAT('#', x.id, ' created ', DATE_FORMAT(x.created_at, '%d %b %Y %H:%i'),
                           IF(x.is_current, ' (current)', ''), IF(x.is_public, ' (public)', '')) ORDER BY x.id SEPARATOR ' | ') AS cycles
FROM exm_cycles x JOIN exm_exams e ON e.id = x.exam_id
WHERE x.deleted_at IS NULL
GROUP BY x.exam_id, e.name, x.year, IFNULL(x.session_label, '')
HAVING COUNT(*) > 1;
