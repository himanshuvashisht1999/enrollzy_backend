-- ======================================================
-- Enrollzy - Mega Dropdown Menu Database Setup
-- Date: 2026-07-30
-- Description: Creates mega_menus table and inserts default categories & links.
-- ======================================================

CREATE TABLE IF NOT EXISTS `mega_menus` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `column_title` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_highlighted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mega_menus_parent_id_foreign` (`parent_id`),
  CONSTRAINT `mega_menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `mega_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Default Seed Data for Mega Menu Categories & Links
INSERT INTO `mega_menus` (`id`, `parent_id`, `title`, `url`, `column_title`, `sort_order`, `status`, `is_highlighted`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Boarding Schools', '/all-schools', NULL, 1, 1, 0, NOW(), NOW()),
(2, 1, 'Boys Boarding Schools', '/all-schools?school_type=Boys+Boarding', 'School Type', 1, 1, 0, NOW(), NOW()),
(3, 1, 'Girls Boarding Schools', '/all-schools?school_type=Girls+Boarding', 'School Type', 2, 1, 0, NOW(), NOW()),
(4, 1, 'Co-Ed Boarding Schools', '/all-schools?school_type=Co-Ed+Boarding', 'School Type', 3, 1, 0, NOW(), NOW()),
(5, 1, 'Residential Schools', '/all-schools?school_type=Residential', 'School Type', 4, 1, 0, NOW(), NOW()),
(6, 1, 'CBSE Boarding', '/all-schools?board=CBSE', 'Curriculum', 1, 1, 0, NOW(), NOW()),
(7, 1, 'ICSE Boarding', '/all-schools?board=ICSE', 'Curriculum', 2, 1, 0, NOW(), NOW()),
(8, 1, 'IB Boarding', '/all-schools?board=IB', 'Curriculum', 3, 1, 0, NOW(), NOW()),
(9, 1, 'Cambridge Boarding', '/all-schools?board=Cambridge', 'Curriculum', 4, 1, 0, NOW(), NOW()),
(10, 1, 'Uttarakhand', '/all-schools?state=Uttarakhand', 'Browse by State', 1, 1, 0, NOW(), NOW()),
(11, 1, 'Himachal Pradesh', '/all-schools?state=Himachal+Pradesh', 'Browse by State', 2, 1, 0, NOW(), NOW()),
(12, 1, 'Rajasthan', '/all-schools?state=Rajasthan', 'Browse by State', 3, 1, 0, NOW(), NOW()),
(13, 1, 'Karnataka', '/all-schools?state=Karnataka', 'Browse by State', 4, 1, 0, NOW(), NOW()),
(14, NULL, 'Universities', '/university', NULL, 2, 1, 0, NOW(), NOW()),
(15, 14, 'Engineering', '/university?search=Engineering', 'Browse by Stream', 1, 1, 0, NOW(), NOW()),
(16, 14, 'Medical', '/university?search=Medical', 'Browse by Stream', 2, 1, 0, NOW(), NOW()),
(17, 14, 'Management', '/university?search=Management', 'Browse by Stream', 3, 1, 0, NOW(), NOW()),
(18, 14, 'Law', '/university?search=Law', 'Browse by Stream', 4, 1, 0, NOW(), NOW()),
(19, 14, 'Undergraduate', '/university?search=Undergraduate', 'Browse by Degree', 1, 1, 0, NOW(), NOW()),
(20, 14, 'Postgraduate', '/university?search=Postgraduate', 'Browse by Degree', 2, 1, 0, NOW(), NOW()),
(21, NULL, 'Integrated Coaching', '/all-coaching', NULL, 3, 1, 0, NOW(), NOW()),
(22, 21, 'IIT JEE Coaching', '/all-coaching?search=JEE', 'Coaching Categories', 1, 1, 0, NOW(), NOW()),
(23, 21, 'NEET Medical Coaching', '/all-coaching?search=NEET', 'Coaching Categories', 2, 1, 0, NOW(), NOW()),
(24, 21, 'NDA Defence Coaching', '/all-coaching?search=NDA', 'Coaching Categories', 3, 1, 0, NOW(), NOW()),
(25, NULL, 'Top Exams', '/top-exams', NULL, 4, 1, 0, NOW(), NOW()),
(26, 25, 'JEE Main', '/top-exams', 'Engineering Exams', 1, 1, 0, NOW(), NOW()),
(27, 25, 'JEE Advanced', '/top-exams', 'Engineering Exams', 2, 1, 0, NOW(), NOW()),
(28, 25, 'NEET UG', '/top-exams', 'Medical Exams', 1, 1, 0, NOW(), NOW());
