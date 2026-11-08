-- =====================================================================================================================
-- Enrollzy: check the calling data for duplicate leads   4 Nov 2026
-- READ ONLY: these are SELECT queries, they change nothing. In phpMyAdmin: select the database, SQL tab, paste, Go.
-- =====================================================================================================================

-- 1. Summary
SELECT
  (SELECT COUNT(DISTINCT customer_id) FROM lead_assignments)                                                AS assigned_leads,
  (SELECT COUNT(*) FROM lead_assignments)                                                                   AS assignment_rows,
  (SELECT COUNT(*) FROM (SELECT customer_id FROM lead_assignments GROUP BY customer_id HAVING COUNT(*) > 1) t) AS leads_with_several_rows,
  (SELECT COUNT(*) FROM (SELECT RIGHT(REGEXP_REPLACE(u.phone, '[^0-9]', ''), 10) p FROM users u
     JOIN lead_assignments la ON la.customer_id = u.id WHERE u.phone <> ''
     GROUP BY p HAVING COUNT(DISTINCT u.id) > 1) t)                                                          AS phones_on_several_leads;

-- 2. Leads that have more than one assignment row (there should be none): who they sit with
SELECT la.customer_id, u.name, u.phone, COUNT(*) AS assignment_rows,
       GROUP_CONCAT(CONCAT(a.name, ' (', la.updated_at, ')') ORDER BY la.id SEPARATOR ' | ') AS assigned_to
FROM lead_assignments la
JOIN users u ON u.id = la.customer_id
LEFT JOIN admin a ON a.id = la.staff_id
GROUP BY la.customer_id, u.name, u.phone
HAVING COUNT(*) > 1
ORDER BY assignment_rows DESC
LIMIT 200;

-- 3. One phone number saved as several leads (separate student records), and who each one sits with
SELECT RIGHT(REGEXP_REPLACE(u.phone, '[^0-9]', ''), 10) AS phone, COUNT(DISTINCT u.id) AS leads,
       GROUP_CONCAT(DISTINCT CONCAT('#', u.id, ' ', u.name, ' → ', IFNULL(a.name, '?')) ORDER BY u.id SEPARATOR ' | ') AS leads_and_staff
FROM users u
JOIN lead_assignments la ON la.customer_id = u.id
LEFT JOIN admin a ON a.id = la.staff_id
WHERE u.phone IS NOT NULL AND u.phone <> ''
GROUP BY phone
HAVING COUNT(DISTINCT u.id) > 1
ORDER BY leads DESC
LIMIT 200;
