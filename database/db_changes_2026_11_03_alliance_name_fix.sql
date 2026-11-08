-- Fix the "Alliance Universityy" typo: the university's name and its web address (/university-detail/alliance-university).
-- Safe to run again: it only changes the row while it still has the typo.
UPDATE organisations SET name = 'Alliance University', updated_at = NOW() WHERE name = 'Alliance Universityy';
UPDATE organisations SET slug = 'alliance-university', updated_at = NOW() WHERE slug = 'alliance-universityy';
