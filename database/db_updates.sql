-- ==========================================
-- Database Updates for Enrollzy Project
-- Date: August 3, 2026
-- Description: GST Number settings, TDS payment method, Mentor Profiles fix, and Scholarship Module tables.
-- ==========================================

-- 1. General Settings GST Number Update
ALTER TABLE `settings` ADD `gst_number` VARCHAR(255) NULL DEFAULT NULL AFTER `address`;
UPDATE `settings` SET `gst_number` = '05AADCU4904F1ZM' LIMIT 1;

-- 2. TDS Payment Method Enum Expansion
ALTER TABLE `billing_payments` MODIFY COLUMN `payment_mode` ENUM('Bank Transfer', 'UPI', 'Cash', 'Cheque', 'TDS') NOT NULL;

-- 3. Mentor Profiles Missing Column Fix
ALTER TABLE `mentor_profiles` ADD `price_per_min` DECIMAL(10, 2) NOT NULL DEFAULT 500.00 AFTER `professional_headline`;

-- 4. Scholarship Module Normalized Schema (9 Tables)

-- Table 4.1: scholarships
CREATE TABLE IF NOT EXISTS `scholarships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `scholarship_code` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `overview` longtext DEFAULT NULL,
  `about_scholarship` longtext DEFAULT NULL,
  `why_apply` longtext DEFAULT NULL,
  `selection_process` longtext DEFAULT NULL,
  `terms_conditions` longtext DEFAULT NULL,
  `important_notes` longtext DEFAULT NULL,
  `additional_information` longtext DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `scholarship_type` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `max_amount` decimal(12,2) DEFAULT NULL,
  `amount_prefix` varchar(255) DEFAULT NULL,
  `amount_suffix` varchar(255) DEFAULT NULL,
  `provider_name` varchar(255) DEFAULT NULL,
  `provider_logo` varchar(255) DEFAULT NULL,
  `application_mode` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `featured` tinyint(4) NOT NULL DEFAULT 0,
  `featured_on_homepage` tinyint(4) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `cta_text` varchar(255) DEFAULT 'Check Eligibility',
  `cta_url` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scholarships_slug_unique` (`slug`),
  KEY `scholarships_created_by_foreign` (`created_by`),
  KEY `scholarships_updated_by_foreign` (`updated_by`),
  CONSTRAINT `scholarships_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scholarships_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4.2: scholarship_eligibilities
CREATE TABLE IF NOT EXISTS `scholarship_eligibilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `minimum_class` varchar(255) DEFAULT NULL,
  `maximum_class` varchar(255) DEFAULT NULL,
  `minimum_percentage` decimal(5,2) DEFAULT NULL,
  `maximum_age` int(11) DEFAULT NULL,
  `gender` varchar(255) NOT NULL DEFAULT 'Any',
  `nationality` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `annual_family_income` varchar(255) DEFAULT NULL,
  `course_level` varchar(255) DEFAULT NULL,
  `course_type` varchar(255) DEFAULT NULL,
  `academic_stream` varchar(255) DEFAULT NULL,
  `entrance_exam` varchar(255) DEFAULT NULL,
  `minimum_exam_score` decimal(8,2) DEFAULT NULL,
  `currently_studying` varchar(255) DEFAULT NULL,
  `graduation_required` tinyint(4) NOT NULL DEFAULT 0,
  `work_experience` varchar(255) DEFAULT NULL,
  `other_conditions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_eligibilities_scholarship_id_foreign` (`scholarship_id`),
  CONSTRAINT `scholarship_eligibilities_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4.3: scholarship_benefits
CREATE TABLE IF NOT EXISTS `scholarship_benefits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `benefit_title` varchar(255) NOT NULL,
  `benefit_description` text DEFAULT NULL,
  `benefit_amount` decimal(12,2) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_benefits_scholarship_id_foreign` (`scholarship_id`),
  CONSTRAINT `scholarship_benefits_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4.4: scholarship_courses
CREATE TABLE IF NOT EXISTS `scholarship_courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_courses_scholarship_id_foreign` (`scholarship_id`),
  KEY `scholarship_courses_course_id_foreign` (`course_id`),
  CONSTRAINT `scholarship_courses_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scholarship_courses_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4.5: scholarship_universities
CREATE TABLE IF NOT EXISTS `scholarship_universities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_universities_scholarship_id_foreign` (`scholarship_id`),
  KEY `scholarship_universities_organisation_id_foreign` (`organisation_id`),
  CONSTRAINT `scholarship_universities_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scholarship_universities_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4.6: scholarship_documents
CREATE TABLE IF NOT EXISTS `scholarship_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `is_mandatory` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_documents_scholarship_id_foreign` (`scholarship_id`),
  CONSTRAINT `scholarship_documents_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4.7: scholarship_dates
CREATE TABLE IF NOT EXISTS `scholarship_dates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `application_start_date` date DEFAULT NULL,
  `application_end_date` date DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `result_date` date DEFAULT NULL,
  `document_verification_date` date DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_dates_scholarship_id_foreign` (`scholarship_id`),
  CONSTRAINT `scholarship_dates_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4.8: scholarship_faqs
CREATE TABLE IF NOT EXISTS `scholarship_faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_faqs_scholarship_id_foreign` (`scholarship_id`),
  CONSTRAINT `scholarship_faqs_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4.9: scholarship_gallery
CREATE TABLE IF NOT EXISTS `scholarship_gallery` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `image` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_gallery_scholarship_id_foreign` (`scholarship_id`),
  CONSTRAINT `scholarship_gallery_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Update: August 3, 2026 (Autosave Session)
-- Description: Add soft deletes (deleted_at) to scholarships table
-- ==========================================

-- 5. Soft Deletes for Scholarships
ALTER TABLE `scholarships`
  ADD `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- ==========================================
-- Update: August 3, 2026 (Docx Full Implementation)
-- Description: Add missing card/banner/provider fields, highlights table, seo_metas table
-- ==========================================

-- 6. Homepage Card & Banner fields for scholarships
ALTER TABLE `scholarships`
  ADD `display_title`         VARCHAR(255) NULL DEFAULT NULL AFTER `short_name`,
  ADD `card_icon`             VARCHAR(255) NULL DEFAULT NULL AFTER `display_title`,
  ADD `card_background_color` VARCHAR(20)  NULL DEFAULT NULL AFTER `card_icon`,
  ADD `card_text_color`       VARCHAR(20)  NULL DEFAULT NULL AFTER `card_background_color`,
  ADD `banner_title`          VARCHAR(255) NULL DEFAULT NULL AFTER `banner_image`,
  ADD `banner_subtitle`       VARCHAR(255) NULL DEFAULT NULL AFTER `banner_title`,
  ADD `provider_url`          VARCHAR(255) NULL DEFAULT NULL AFTER `provider_logo`;

-- 7. Scholarship Highlights Table (chip/badge system)
CREATE TABLE IF NOT EXISTS `scholarship_highlights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `highlight_text` varchar(255) NOT NULL,
  `highlight_icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scholarship_highlights_scholarship_id_foreign` (`scholarship_id`),
  CONSTRAINT `scholarship_highlights_scholarship_id_foreign`
    FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. SEO Metas (Polymorphic) Table
CREATE TABLE IF NOT EXISTS `seo_metas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seoable_type` varchar(255) NOT NULL,
  `seoable_id` bigint(20) unsigned NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `no_index` tinyint(4) NOT NULL DEFAULT 0,
  `no_follow` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seo_metas_seoable_type_seoable_id_index` (`seoable_type`, `seoable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
