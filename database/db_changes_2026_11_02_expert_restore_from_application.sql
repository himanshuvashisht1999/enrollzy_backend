-- =====================================================================================================================
-- Enrollzy: put back an approved expert's gender and designation from their application                 2 Nov 2026
--
-- The old CRM expert form could not show "male" (the website form's spelling) or a designation outside its four
-- choices, so saving an expert there stored both as empty. This fills ONLY empty values on experts created from an
-- approved application, from that expert's latest approved application. Values someone has set are never changed.
-- No migration: data only. Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again.
-- =====================================================================================================================

UPDATE `experts` e
JOIN (SELECT `expert_id`, MAX(`id`) AS `id` FROM `expert_applications`
      WHERE `status` = 'approved' AND `expert_id` IS NOT NULL GROUP BY `expert_id`) latest ON latest.`expert_id` = e.`id`
JOIN `expert_applications` a ON a.`id` = latest.`id`
SET e.`gender` = IF((e.`gender` IS NULL OR e.`gender` = '') AND a.`gender` IN ('male', 'female', 'other'),
                    CONCAT(UPPER(LEFT(a.`gender`, 1)), LOWER(SUBSTRING(a.`gender`, 2))), e.`gender`),
    e.`designation` = IF((e.`designation` IS NULL OR e.`designation` = '') AND a.`designation` <> '', a.`designation`, e.`designation`),
    e.`current_institute_name` = IF((e.`current_institute_name` IS NULL OR e.`current_institute_name` = '') AND a.`organisation` <> '', a.`organisation`, e.`current_institute_name`);
