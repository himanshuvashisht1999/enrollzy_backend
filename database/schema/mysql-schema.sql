/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `about_us_advisory_boards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about_us_advisory_boards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `about_us_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about_us_features` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `icon_image` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `about_us_impacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about_us_impacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `icon_image` varchar(255) DEFAULT NULL,
  `count_text` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `about_us_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about_us_offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `icon_image` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `about_us_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about_us_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `section_orders` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '["hero","story","core_values","offers","features","impacts","founders","teams","cta"]' CHECK (json_valid(`section_orders`)),
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` varchar(255) DEFAULT NULL,
  `hero_description` text DEFAULT NULL,
  `hero_tagline` text DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `simplify_decisions_image` varchar(255) DEFAULT NULL,
  `story_title` varchar(255) DEFAULT NULL,
  `story_subtitle` varchar(255) DEFAULT NULL,
  `story_description` text DEFAULT NULL,
  `story_purpose_text` varchar(255) DEFAULT NULL,
  `story_image` varchar(255) DEFAULT NULL,
  `offers_title` varchar(255) DEFAULT NULL,
  `offers_subtitle` varchar(255) DEFAULT NULL,
  `offers_description` text DEFAULT NULL,
  `impacts_title` varchar(255) DEFAULT NULL,
  `features_title` varchar(255) DEFAULT NULL,
  `features_subtitle` varchar(255) DEFAULT NULL,
  `cta_title` varchar(255) DEFAULT NULL,
  `cta_description` text DEFAULT NULL,
  `cta_button_1_text` varchar(255) DEFAULT NULL,
  `cta_button_1_link` varchar(255) DEFAULT NULL,
  `cta_button_2_text` varchar(255) DEFAULT NULL,
  `cta_button_2_link` varchar(255) DEFAULT NULL,
  `cta_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mission_text` text DEFAULT NULL,
  `mission_image` varchar(255) DEFAULT NULL,
  `vision_text` text DEFAULT NULL,
  `vision_image` varchar(255) DEFAULT NULL,
  `philosophy_text` text DEFAULT NULL,
  `philosophy_image` varchar(255) DEFAULT NULL,
  `founder_1_name` varchar(255) DEFAULT NULL,
  `founder_1_title` varchar(255) DEFAULT NULL,
  `founder_1_image` varchar(255) DEFAULT NULL,
  `founder_1_facebook` varchar(255) DEFAULT NULL,
  `founder_1_linkedin` varchar(255) DEFAULT NULL,
  `founder_1_twitter` varchar(255) DEFAULT NULL,
  `founder_1_message` text DEFAULT NULL,
  `founder_2_name` varchar(255) DEFAULT NULL,
  `founder_2_title` varchar(255) DEFAULT NULL,
  `founder_2_image` varchar(255) DEFAULT NULL,
  `founder_2_facebook` varchar(255) DEFAULT NULL,
  `founder_2_linkedin` varchar(255) DEFAULT NULL,
  `founder_2_twitter` varchar(255) DEFAULT NULL,
  `founder_2_message` text DEFAULT NULL,
  `founders_common_message` text DEFAULT NULL,
  `founders_title` varchar(255) DEFAULT NULL,
  `team_title` varchar(255) DEFAULT NULL,
  `team_subtitle` varchar(255) DEFAULT NULL,
  `advisory_title` varchar(255) DEFAULT NULL,
  `advisory_subtitle` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `about_us_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about_us_teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `job_profile` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `accreditation_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accreditation_approvals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `documents` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `about` text DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `pay_based` varchar(100) DEFAULT NULL,
  `salary` varchar(100) NOT NULL,
  `working_days` text DEFAULT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `designation_id` int(10) unsigned DEFAULT NULL,
  `shift_hours` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `marital_status` varchar(255) DEFAULT NULL,
  `probation_end_date` date DEFAULT NULL,
  `notice_period_start` date DEFAULT NULL,
  `notice_period_end` date DEFAULT NULL,
  `employment_type` varchar(100) DEFAULT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admission_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admission_routes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `exam_id` bigint(20) unsigned DEFAULT NULL,
  `exam_source_type` varchar(255) NOT NULL DEFAULT 'external_exam',
  `min_eligibility_qualification` varchar(255) DEFAULT NULL,
  `min_eligibility_marks` varchar(255) DEFAULT NULL,
  `min_exam_rank` varchar(255) DEFAULT NULL,
  `min_exam_score` varchar(255) DEFAULT NULL,
  `cutoff_year_wise` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cutoff_year_wise`)),
  `seat_matrix` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`seat_matrix`)),
  `admission_process_note` text DEFAULT NULL,
  `application_url` varchar(255) DEFAULT NULL,
  `counselling_authority` varchar(255) DEFAULT NULL,
  `is_primary_route` tinyint(1) NOT NULL DEFAULT 0,
  `priority_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admission_routes_course_id_foreign` (`course_id`),
  KEY `admission_routes_exam_id_foreign` (`exam_id`),
  KEY `admission_routes_organisation_id_course_id_index` (`organisation_id`,`course_id`),
  CONSTRAINT `admission_routes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admission_routes_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admission_routes_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `advance_pay_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advance_pay_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `amount` varchar(255) DEFAULT NULL,
  `advance_pay_ids` varchar(255) DEFAULT NULL,
  `transaction_type` varchar(255) DEFAULT NULL,
  `transaction_for` varchar(255) DEFAULT NULL,
  `log` varchar(255) DEFAULT NULL,
  `staff_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `debit_account` varchar(255) DEFAULT NULL,
  `month` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alumnis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumnis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `experience_years` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_alumni_count` varchar(255) DEFAULT NULL,
  `alumni_per_graduation_batch` varchar(255) DEFAULT NULL,
  `alumni_growth_rate` varchar(255) DEFAULT NULL,
  `active_alumni_countries_count` varchar(255) DEFAULT NULL,
  `top_alumni_geographies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`top_alumni_geographies`)),
  `percent_alumni_working_abroad` varchar(255) DEFAULT NULL,
  `placed_in_top_companies` tinyint(1) NOT NULL DEFAULT 0,
  `leadership_roles_count` varchar(255) DEFAULT NULL,
  `average_salary_bands` varchar(255) DEFAULT NULL,
  `alumni_in_govt_civil_services` tinyint(1) NOT NULL DEFAULT 0,
  `tech_industry_percent` varchar(255) DEFAULT NULL,
  `finance_industry_percent` varchar(255) DEFAULT NULL,
  `healthcare_industry_percent` varchar(255) DEFAULT NULL,
  `law_industry_percent` varchar(255) DEFAULT NULL,
  `consulting_industry_percent` varchar(255) DEFAULT NULL,
  `entrepreneurship_industry_percent` varchar(255) DEFAULT NULL,
  `sports_arts_industry_percent` varchar(255) DEFAULT NULL,
  `is_mentor` tinyint(1) NOT NULL DEFAULT 0,
  `alumni_interaction_frequency` varchar(255) DEFAULT NULL,
  `participation_rate` varchar(255) DEFAULT NULL,
  `student_mentorship_ratio` varchar(255) DEFAULT NULL,
  `formal_mentorship_available` tinyint(1) NOT NULL DEFAULT 0,
  `career_guidance_sessions` tinyint(1) NOT NULL DEFAULT 0,
  `academic_mentoring` tinyint(1) NOT NULL DEFAULT 0,
  `startup_mentoring` tinyint(1) NOT NULL DEFAULT 0,
  `alumni_driven_placements_count` varchar(255) DEFAULT NULL,
  `referral_programs_active` tinyint(1) NOT NULL DEFAULT 0,
  `internship_support_via_alumni` tinyint(1) NOT NULL DEFAULT 0,
  `campus_hiring_initiated_by_alumni` tinyint(1) NOT NULL DEFAULT 0,
  `alumni_founded_startups_count` varchar(255) DEFAULT NULL,
  `unicorn_funded_startups_count` varchar(255) DEFAULT NULL,
  `alumni_investors_angels_count` varchar(255) DEFAULT NULL,
  `startup_incubators_led_by_alumni` tinyint(1) NOT NULL DEFAULT 0,
  `directory_access` tinyint(1) NOT NULL DEFAULT 0,
  `network_platform_available` tinyint(1) NOT NULL DEFAULT 0,
  `linkedin_integration_active` tinyint(1) NOT NULL DEFAULT 0,
  `contact_via_portal_active` tinyint(1) NOT NULL DEFAULT 0,
  `total_alumni_donations` varchar(255) DEFAULT NULL,
  `scholarships_funded_by_alumni` varchar(255) DEFAULT NULL,
  `infrastructure_funded_by_alumni` varchar(255) DEFAULT NULL,
  `endowment_contributions` varchar(255) DEFAULT NULL,
  `network_strength_score` varchar(255) DEFAULT NULL,
  `mentorship_effectiveness_score` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumnis_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `availability_slot_id` bigint(20) unsigned NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `meeting_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointments_user_id_foreign` (`user_id`),
  KEY `appointments_availability_slot_id_foreign` (`availability_slot_id`),
  CONSTRAINT `appointments_availability_slot_id_foreign` FOREIGN KEY (`availability_slot_id`) REFERENCES `availability_slots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `work_from` varchar(50) DEFAULT NULL,
  `date` date NOT NULL,
  `check_in` timestamp NULL DEFAULT NULL,
  `check_in_image` varchar(255) DEFAULT NULL,
  `check_out` timestamp NULL DEFAULT NULL,
  `check_out_image` varchar(255) DEFAULT NULL,
  `status` enum('absent','present','leave') NOT NULL DEFAULT 'absent',
  `duration` bigint(10) DEFAULT NULL,
  `start_comment` longtext DEFAULT NULL,
  `end_comment` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `availability_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `availability_slots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `provider_type` varchar(255) NOT NULL,
  `provider_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `availability_slots_provider_type_provider_id_index` (`provider_type`,`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `banks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `billing_invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `billing_service_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `billing_invoice_items_billing_service_id_foreign` (`billing_service_id`),
  CONSTRAINT `billing_invoice_items_billing_service_id_foreign` FOREIGN KEY (`billing_service_id`) REFERENCES `billing_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `billing_invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `billing_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `billing_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) NOT NULL,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `campus_id` varchar(255) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','unpaid','partial','paid','cancelled') NOT NULL DEFAULT 'draft',
  `terms_conditions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_invoices_invoice_number_unique` (`invoice_number`),
  KEY `billing_invoices_organisation_id_foreign` (`organisation_id`),
  KEY `billing_invoices_campus_id_foreign` (`campus_id`),
  CONSTRAINT `billing_invoices_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `billing_invoices_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `billing_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_mode` enum('Bank Transfer','UPI','Cash','Cheque') NOT NULL DEFAULT 'Bank Transfer',
  `transaction_id` varchar(255) DEFAULT NULL,
  `bank_details` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_payments_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `billing_payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `billing_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `billing_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `hsn_sac_code` varchar(255) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 18.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blogs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `published_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_slug_unique` (`slug`),
  KEY `blogs_category_id_foreign` (`category_id`),
  CONSTRAINT `blogs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `expert_id` bigint(20) unsigned NOT NULL,
  `slot_id` bigint(20) unsigned NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `platform_fee` decimal(10,2) NOT NULL,
  `expert_earning` decimal(10,2) NOT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `meeting_link` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `commission_override_type` varchar(255) DEFAULT NULL,
  `commission_override_value` decimal(10,2) DEFAULT NULL,
  `override_reason` text DEFAULT NULL,
  `override_by` bigint(20) unsigned DEFAULT NULL,
  `applied_commission_type` varchar(255) DEFAULT NULL,
  `applied_commission_rate` decimal(10,2) DEFAULT NULL,
  `applied_gst_rate` decimal(5,2) NOT NULL DEFAULT 18.00,
  `applied_tds_rate` decimal(5,2) NOT NULL DEFAULT 10.00,
  `commission_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`commission_breakdown`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_booking_id_unique` (`booking_id`),
  KEY `bookings_user_id_foreign` (`user_id`),
  KEY `bookings_expert_id_foreign` (`expert_id`),
  KEY `bookings_slot_id_foreign` (`slot_id`),
  CONSTRAINT `bookings_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `expert_slots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `breaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `breaks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `start` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end` timestamp NULL DEFAULT NULL,
  `duration` bigint(10) DEFAULT NULL,
  `type` enum('lunch','personal') DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `lunch_was` varchar(200) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calling_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calling_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calling_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calling_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) DEFAULT NULL,
  `user_type` varchar(100) DEFAULT NULL,
  `user_id` varchar(255) NOT NULL,
  `category_id` varchar(255) DEFAULT NULL,
  `institute_id` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_phone` varchar(255) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `date_required` varchar(255) DEFAULT NULL,
  `comment` longtext DEFAULT NULL,
  `updated_by` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_done` int(11) NOT NULL DEFAULT 1,
  `calling_action_id` int(11) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calling_history_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calling_history_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `history_id` varchar(255) DEFAULT NULL,
  `calling_action_id` int(11) DEFAULT NULL,
  `log_type` varchar(255) DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calling_manual_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calling_manual_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `category_id` varchar(255) DEFAULT NULL,
  `institute_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calling_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calling_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date_require` enum('yes','no') NOT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `campus_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campus_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `campuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campuses` (
  `id` char(36) NOT NULL,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `campus_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `campus_type` varchar(50) NOT NULL,
  `established_year` year(4) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) NOT NULL DEFAULT 'India',
  `pincode` varchar(255) DEFAULT NULL,
  `full_address` text DEFAULT NULL,
  `google_map_url` varchar(255) DEFAULT NULL,
  `nearest_transport_hub` varchar(255) DEFAULT NULL,
  `campus_area_acres` decimal(8,2) DEFAULT NULL,
  `campus_area_unit` varchar(255) NOT NULL DEFAULT 'Acres',
  `academic_blocks_count` int(11) NOT NULL DEFAULT 0,
  `classrooms_count` int(11) NOT NULL DEFAULT 0,
  `smart_classrooms` tinyint(1) NOT NULL DEFAULT 0,
  `laboratories_count` int(11) NOT NULL DEFAULT 0,
  `library_available` tinyint(1) NOT NULL DEFAULT 0,
  `library_books_count` int(11) NOT NULL DEFAULT 0,
  `digital_library_access` tinyint(1) NOT NULL DEFAULT 0,
  `research_centers_count` int(11) NOT NULL DEFAULT 0,
  `hostel_available` tinyint(1) NOT NULL DEFAULT 0,
  `hostel_type` enum('Boys','Girls','Both','None') DEFAULT NULL,
  `hostel_capacity` int(11) NOT NULL DEFAULT 0,
  `food_facility` varchar(255) DEFAULT NULL,
  `medical_facility_available` tinyint(1) NOT NULL DEFAULT 0,
  `sports_facilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sports_facilities`)),
  `transport_available` tinyint(1) NOT NULL DEFAULT 0,
  `bus_routes_count` int(11) NOT NULL DEFAULT 0,
  `bus_routes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bus_routes`)),
  `parking_available` tinyint(1) NOT NULL DEFAULT 0,
  `cctv_coverage` tinyint(1) NOT NULL DEFAULT 0,
  `security_staff_count` int(11) NOT NULL DEFAULT 0,
  `fire_safety_certified` tinyint(1) NOT NULL DEFAULT 0,
  `disaster_management_plan` tinyint(1) NOT NULL DEFAULT 0,
  `campus_contact_numbers` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `campus_email` varchar(255) DEFAULT NULL,
  `campus_website` varchar(255) DEFAULT NULL,
  `verification_status` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `exams_prepared_for` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exams_prepared_for`)),
  `target_classes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_classes`)),
  `about_institute` longtext DEFAULT NULL,
  `last_updated_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ownership_model` enum('Owned','Franchise','Partner') DEFAULT NULL,
  `franchise_partner_name` varchar(255) DEFAULT NULL,
  `franchise_start_year` year(4) DEFAULT NULL,
  `brand_compliance_verified` int(11) DEFAULT NULL,
  `nearest_landmark` varchar(255) DEFAULT NULL,
  `science_labs_available` tinyint(1) NOT NULL DEFAULT 0,
  `computer_labs_available` tinyint(1) NOT NULL DEFAULT 0,
  `playground_available` tinyint(1) NOT NULL DEFAULT 0,
  `bus_fleet_size` int(11) DEFAULT NULL,
  `gps_enabled_buses` tinyint(1) NOT NULL DEFAULT 0,
  `brand_type` varchar(255) DEFAULT NULL,
  `visitor_management_system` tinyint(1) NOT NULL DEFAULT 0,
  `class_profile` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`class_profile`)),
  `facilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`facilities`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `campuses_slug_unique` (`slug`),
  KEY `campuses_organisation_id_foreign` (`organisation_id`),
  CONSTRAINT `campuses_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `career_roadmap_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_roadmap_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_roadmap_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `career_roadmap_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_roadmap_stages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_roadmap_stages_slug_unique` (`slug`),
  KEY `career_roadmap_stages_category_id_foreign` (`category_id`),
  CONSTRAINT `career_roadmap_stages_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `career_roadmap_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `career_roadmap_sub_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_roadmap_sub_modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stage_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `long_description` longtext DEFAULT NULL,
  `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_roadmap_sub_modules_slug_unique` (`slug`),
  KEY `career_roadmap_sub_modules_stage_id_foreign` (`stage_id`),
  KEY `career_roadmap_sub_modules_parent_id_foreign` (`parent_id`),
  CONSTRAINT `career_roadmap_sub_modules_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `career_roadmap_sub_modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `career_roadmap_sub_modules_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `career_roadmap_stages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caste_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caste_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `caste_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `profile_image` varchar(200) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `landmark` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pin_code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commission_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(255) NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `action_by` bigint(20) unsigned DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commission_policies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `policy_type` varchar(255) NOT NULL DEFAULT 'global',
  `expert_category` varchar(255) DEFAULT NULL,
  `expert_category_id` bigint(20) unsigned DEFAULT NULL,
  `commission_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `commission_value` decimal(10,2) NOT NULL,
  `gst_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `tds_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commission_policies_expert_category_id_foreign` (`expert_category_id`),
  CONSTRAINT `commission_policies_expert_category_id_foreign` FOREIGN KEY (`expert_category_id`) REFERENCES `expert_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `community_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `community_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `community_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `likable_type` varchar(255) NOT NULL,
  `likable_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `community_likes_user_id_foreign` (`user_id`),
  KEY `community_likes_likable_type_likable_id_index` (`likable_type`,`likable_id`),
  CONSTRAINT `community_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `community_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `question_text` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `community_questions_user_id_foreign` (`user_id`),
  KEY `community_questions_category_id_foreign` (`category_id`),
  CONSTRAINT `community_questions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `community_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `community_questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `community_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_replies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `community_replies_user_id_foreign` (`user_id`),
  KEY `community_replies_question_id_foreign` (`question_id`),
  KEY `community_replies_parent_id_foreign` (`parent_id`),
  CONSTRAINT `community_replies_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `community_replies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `community_replies_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `community_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `community_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultant_access_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_access_levels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultant_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultant_category_pivots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_category_pivots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `consultant_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consultant_category_pivots_consultant_id_foreign` (`consultant_id`),
  CONSTRAINT `consultant_category_pivots_consultant_id_foreign` FOREIGN KEY (`consultant_id`) REFERENCES `consultants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultant_lead_visibilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_lead_visibilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultant_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_statuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultant_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `consultant_id` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `alternate_mobile` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `consultant_type` varchar(255) DEFAULT NULL,
  `is_gst_registered` tinyint(1) NOT NULL DEFAULT 0,
  `gst_number` varchar(255) DEFAULT NULL,
  `pan_number` varchar(255) DEFAULT NULL,
  `aadhaar_number` varchar(255) DEFAULT NULL,
  `years_of_experience` int(11) DEFAULT NULL,
  `team_size` varchar(255) DEFAULT NULL,
  `office_address` text DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `linkedin_profile` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `sub_category_id` bigint(20) unsigned DEFAULT NULL,
  `sub_sub_category_id` bigint(20) unsigned DEFAULT NULL,
  `expertise_level` varchar(255) DEFAULT NULL,
  `preferred_universities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_universities`)),
  `preferred_courses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_courses`)),
  `preferred_modes_of_study` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_modes_of_study`)),
  `generates_own_leads` tinyint(1) NOT NULL DEFAULT 0,
  `requires_company_leads` tinyint(1) NOT NULL DEFAULT 0,
  `runs_ads` tinyint(1) NOT NULL DEFAULT 0,
  `has_counseling_office` tinyint(1) NOT NULL DEFAULT 0,
  `walk_in_students` tinyint(1) NOT NULL DEFAULT 0,
  `approx_leads_per_month` int(11) DEFAULT NULL,
  `working_states` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_states`)),
  `working_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_cities`)),
  `can_handle_pan_india` tinyint(1) NOT NULL DEFAULT 0,
  `languages_known` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`languages_known`)),
  `account_holder_name` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `ifsc_code` varchar(255) DEFAULT NULL,
  `upi_id` varchar(255) DEFAULT NULL,
  `qr_code_upload` varchar(255) DEFAULT NULL,
  `cancelled_cheque_upload` varchar(255) DEFAULT NULL,
  `pan_card_upload` varchar(255) DEFAULT NULL,
  `aadhaar_upload` varchar(255) DEFAULT NULL,
  `pan_upload` varchar(255) DEFAULT NULL,
  `gst_certificate_upload` varchar(255) DEFAULT NULL,
  `business_registration_upload` varchar(255) DEFAULT NULL,
  `visiting_card_upload` varchar(255) DEFAULT NULL,
  `msme_upload` varchar(255) DEFAULT NULL,
  `mou_upload` varchar(255) DEFAULT NULL,
  `office_photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`office_photos`)),
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `status_reason` text DEFAULT NULL,
  `access_level` varchar(255) DEFAULT NULL,
  `lead_visibility` varchar(255) DEFAULT NULL,
  `lead_assignment_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consultant_id` (`consultant_id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_us_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_us_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` varchar(255) DEFAULT NULL,
  `phone_general` varchar(255) DEFAULT NULL,
  `phone_toll_free` varchar(255) DEFAULT NULL,
  `phone_international` varchar(255) DEFAULT NULL,
  `address_head_office` text DEFAULT NULL,
  `address_regional_office` text DEFAULT NULL,
  `address_us_office` text DEFAULT NULL,
  `office_timings` varchar(255) DEFAULT NULL,
  `email_queries` varchar(255) DEFAULT NULL,
  `email_support` varchar(255) DEFAULT NULL,
  `co_founder_name` varchar(255) DEFAULT NULL,
  `co_founder_title` varchar(255) DEFAULT NULL,
  `co_founder_message` text DEFAULT NULL,
  `co_founder_image` varchar(255) DEFAULT NULL,
  `co_founder_email` varchar(255) DEFAULT NULL,
  `co_founder_linkedin` varchar(255) DEFAULT NULL,
  `co_founder_instagram` varchar(255) DEFAULT NULL,
  `map_embed_url` text DEFAULT NULL,
  `career_coach_title` varchar(255) DEFAULT NULL,
  `career_coach_points` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`career_coach_points`)),
  `career_coach_image` varchar(255) DEFAULT NULL,
  `btn_book_session_url` varchar(255) DEFAULT NULL,
  `btn_talk_advisor_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hero_badge` varchar(255) DEFAULT NULL,
  `hero_description` text DEFAULT NULL,
  `hero_trust_points` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`hero_trust_points`)),
  `hero_image` varchar(255) DEFAULT NULL,
  `btn_hero_primary_text` varchar(255) DEFAULT NULL,
  `btn_hero_primary_url` varchar(255) DEFAULT NULL,
  `btn_hero_secondary_text` varchar(255) DEFAULT NULL,
  `btn_hero_secondary_url` varchar(255) DEFAULT NULL,
  `phone_sales` varchar(255) DEFAULT NULL,
  `email_sales` varchar(255) DEFAULT NULL,
  `founder_badge` varchar(255) DEFAULT NULL,
  `founder_heading` varchar(255) DEFAULT NULL,
  `btn_founder_book_text` varchar(255) DEFAULT NULL,
  `btn_founder_book_url` varchar(255) DEFAULT NULL,
  `form_trust_points` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`form_trust_points`)),
  `why_contact_heading` varchar(255) DEFAULT NULL,
  `why_contact_cards` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`why_contact_cards`)),
  `cta_heading` varchar(255) DEFAULT NULL,
  `btn_cta_secondary_text` varchar(255) DEFAULT NULL,
  `btn_cta_secondary_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `counsellings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counsellings` (
  `id` char(36) NOT NULL,
  `exam_id` bigint(20) unsigned DEFAULT NULL,
  `dynamic_exam_id` bigint(20) unsigned DEFAULT NULL,
  `counselling_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counselling_type` varchar(255) NOT NULL,
  `counselling_mode` varchar(255) NOT NULL DEFAULT 'Online',
  `conducting_authority_name` varchar(255) NOT NULL,
  `conducting_authority_type` varchar(255) NOT NULL,
  `official_counselling_website` varchar(255) DEFAULT NULL,
  `applicable_course_levels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_course_levels`)),
  `applicable_quotas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_quotas`)),
  `applicable_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_categories`)),
  `domicile_required` tinyint(1) NOT NULL DEFAULT 0,
  `state_applicability` text DEFAULT NULL,
  `minimum_exam_qualification_required` tinyint(1) NOT NULL DEFAULT 1,
  `minimum_score_or_rank_required` varchar(255) DEFAULT NULL,
  `category_wise_eligibility` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_wise_eligibility`)),
  `attempts_allowed` varchar(255) DEFAULT NULL,
  `age_criteria_for_counselling` text DEFAULT NULL,
  `eligibility_notes` text DEFAULT NULL,
  `number_of_rounds` int(11) NOT NULL DEFAULT 1,
  `rounds` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rounds`)),
  `registration_process_steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`registration_process_steps`)),
  `choice_filling_process` longtext DEFAULT NULL,
  `choice_locking_required` tinyint(1) NOT NULL DEFAULT 1,
  `seat_allotment_process` longtext DEFAULT NULL,
  `reporting_process` longtext DEFAULT NULL,
  `document_verification_process` longtext DEFAULT NULL,
  `upgradation_rules` longtext DEFAULT NULL,
  `exit_and_refund_rules` longtext DEFAULT NULL,
  `counselling_year` varchar(255) DEFAULT NULL,
  `registration_start_date` date DEFAULT NULL,
  `registration_end_date` date DEFAULT NULL,
  `choice_filling_start_date` date DEFAULT NULL,
  `choice_filling_end_date` date DEFAULT NULL,
  `seat_allotment_result_date` date DEFAULT NULL,
  `reporting_start_date` date DEFAULT NULL,
  `reporting_end_date` date DEFAULT NULL,
  `round_wise_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`round_wise_schedule`)),
  `important_dates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`important_dates`)),
  `seat_allocation_basis` varchar(255) NOT NULL DEFAULT 'Exam Rank',
  `tie_breaking_rules` longtext DEFAULT NULL,
  `reservation_policy_reference` varchar(255) DEFAULT NULL,
  `seat_matrix_source` varchar(255) DEFAULT NULL,
  `seat_conversion_rules` longtext DEFAULT NULL,
  `documents_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_required`)),
  `document_format_requirements` longtext DEFAULT NULL,
  `original_documents_required_at_reporting` tinyint(1) NOT NULL DEFAULT 1,
  `participating_institutions_count` int(11) DEFAULT NULL,
  `participating_institutions_note` text DEFAULT NULL,
  `institution_type_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`institution_type_supported`)),
  `helpdesk_contact_number` varchar(255) DEFAULT NULL,
  `helpdesk_email` varchar(255) DEFAULT NULL,
  `faq_url` varchar(255) DEFAULT NULL,
  `grievance_redressal_process` longtext DEFAULT NULL,
  `official_notifications_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`official_notifications_urls`)),
  `information_source` varchar(255) DEFAULT NULL,
  `last_verified_on` datetime DEFAULT NULL,
  `data_confidence_score` decimal(5,2) NOT NULL DEFAULT 100.00,
  `disclaimer_text` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `focus_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`focus_keywords`)),
  `schema_type` varchar(255) NOT NULL DEFAULT 'EducationalOccupationalProgram',
  `canonical_url` varchar(255) DEFAULT NULL,
  `indexing_status` varchar(255) NOT NULL DEFAULT 'index, follow',
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `visibility` varchar(255) NOT NULL DEFAULT 'Public',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `last_updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `registration_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `registration_fee_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`registration_fee_structure`)),
  `late_registration_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `late_fee_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`late_fee_rules`)),
  `security_deposit_required` tinyint(1) NOT NULL DEFAULT 0,
  `partial_refund_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `security_deposit_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`security_deposit_structure`)),
  `round_specific_fee_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`round_specific_fee_rules`)),
  `refund_policy_summary` text DEFAULT NULL,
  `refund_timeline` varchar(255) DEFAULT NULL,
  `refund_mode` varchar(255) DEFAULT NULL,
  `forfeiture_scenarios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`forfeiture_scenarios`)),
  `payment_modes_allowed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_modes_allowed`)),
  `transaction_charges_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `transaction_charge_borne_by` varchar(255) DEFAULT NULL,
  `payment_gateway_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `counsellings_slug_unique` (`slug`),
  KEY `counsellings_exam_id_foreign` (`exam_id`),
  KEY `fk_counsellings_dynamic_exam` (`dynamic_exam_id`),
  CONSTRAINT `counsellings_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_counsellings_dynamic_exam` FOREIGN KEY (`dynamic_exam_id`) REFERENCES `dynamic_exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `program_level_id` varchar(255) DEFAULT NULL,
  `stream_offered_id` varchar(255) DEFAULT NULL,
  `discipline_id` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_academic_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_academic_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `examination` varchar(255) NOT NULL,
  `board_university` varchar(255) DEFAULT NULL,
  `school_college` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `percentage` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_academic_details_user_id_foreign` (`user_id`),
  CONSTRAINT `customer_academic_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  `customer_type` enum('Standard','Credit','Manual') NOT NULL DEFAULT 'Standard',
  `organization_id` int(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_documents_user_id_foreign` (`user_id`),
  CONSTRAINT `customer_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `is_required` int(11) NOT NULL DEFAULT 1,
  `sequence` int(11) NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `declared_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `declared_holidays` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `description` text DEFAULT NULL,
  `department_ids` varchar(255) DEFAULT NULL,
  `designation_ids` varchar(255) DEFAULT NULL,
  `staff_ids` varchar(255) DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` char(36) NOT NULL,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `campus_id` char(36) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `department_code` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `department_type` enum('Academic','Clinical','Research','Interdisciplinary') NOT NULL DEFAULT 'Academic',
  `established_year` year(4) DEFAULT NULL,
  `about_department` text DEFAULT NULL,
  `discipline_area` varchar(255) DEFAULT NULL,
  `specializations_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specializations_supported`)),
  `education_levels_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`education_levels_supported`)),
  `is_interdisciplinary` tinyint(1) NOT NULL DEFAULT 0,
  `collaborating_departments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`collaborating_departments`)),
  `head_of_department_name` varchar(255) DEFAULT NULL,
  `head_of_department_designation` varchar(255) DEFAULT NULL,
  `hod_appointment_type` enum('Permanent','Acting') DEFAULT NULL,
  `hod_email` varchar(255) DEFAULT NULL,
  `department_office_contact` varchar(255) DEFAULT NULL,
  `faculty_count` int(11) NOT NULL DEFAULT 0,
  `curriculum_design_responsibility` tinyint(1) NOT NULL DEFAULT 0,
  `exam_setting_responsibility` tinyint(1) NOT NULL DEFAULT 0,
  `research_programs_managed` tinyint(1) NOT NULL DEFAULT 0,
  `phd_supervision_available` tinyint(1) NOT NULL DEFAULT 0,
  `industry_collaboration_supported` tinyint(1) NOT NULL DEFAULT 0,
  `department_labs_count` varchar(255) DEFAULT NULL,
  `specialized_labs_available` tinyint(1) NOT NULL DEFAULT 0,
  `research_centers_under_department` varchar(255) DEFAULT NULL,
  `department_library_section` tinyint(1) NOT NULL DEFAULT 0,
  `classrooms_count` varchar(255) DEFAULT NULL,
  `research_publications_count` int(11) NOT NULL DEFAULT 0,
  `funded_projects_count` int(11) NOT NULL DEFAULT 0,
  `patents_filed_count` int(11) NOT NULL DEFAULT 0,
  `industry_projects_count` int(11) NOT NULL DEFAULT 0,
  `department_website_url` varchar(255) DEFAULT NULL,
  `department_email` varchar(255) DEFAULT NULL,
  `department_notice_board_url` varchar(255) DEFAULT NULL,
  `online_meeting_tools_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`online_meeting_tools_used`)),
  `schema_type` varchar(255) NOT NULL DEFAULT 'EducationalOrganization',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `focus_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`focus_keywords`)),
  `canonical_url` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Archived') NOT NULL DEFAULT 'Active',
  `visibility` enum('Public','Internal') NOT NULL DEFAULT 'Public',
  `data_source` varchar(255) DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `last_updated_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `college_reviews` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`college_reviews`)),
  `placement_statistics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`placement_statistics`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_slug_unique` (`slug`),
  KEY `departments_organisation_id_foreign` (`organisation_id`),
  KEY `departments_campus_id_foreign` (`campus_id`),
  CONSTRAINT `departments_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `departments_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `designation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `designation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `department_id` int(11) NOT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `disciplines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disciplines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dynamic_counselling_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_counselling_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `counselling_id` char(36) NOT NULL,
  `heading` varchar(255) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `counselling_id` (`counselling_id`),
  CONSTRAINT `dynamic_counselling_sections_ibfk_1` FOREIGN KEY (`counselling_id`) REFERENCES `counsellings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dynamic_exam_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_exam_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dynamic_exam_id` bigint(20) unsigned NOT NULL,
  `heading` varchar(255) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sections_exam_id` (`dynamic_exam_id`),
  CONSTRAINT `fk_sections_exam_id` FOREIGN KEY (`dynamic_exam_id`) REFERENCES `dynamic_exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dynamic_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_exams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `exam_type` varchar(255) DEFAULT NULL,
  `exam_category` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exam_category`)),
  `conducting_body_type` varchar(255) DEFAULT NULL,
  `exam_frequency` varchar(255) DEFAULT NULL,
  `conducting_authority_name` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `exam_source_type` varchar(255) DEFAULT 'External',
  `owning_organisation_id` bigint(20) unsigned DEFAULT NULL,
  `about_exam` longtext DEFAULT NULL,
  `official_website` varchar(255) DEFAULT NULL,
  `visibility` varchar(255) DEFAULT 'Public',
  `featured_exam` tinyint(1) DEFAULT 0,
  `has_stages` tinyint(1) DEFAULT 0,
  `selected_stages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_stages`)),
  `slug` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_dynamic_exams_org` (`owning_organisation_id`),
  CONSTRAINT `fk_dynamic_exams_org` FOREIGN KEY (`owning_organisation_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_payout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_payout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payslip_id` varchar(100) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `month` varchar(20) NOT NULL,
  `year` varchar(20) DEFAULT NULL,
  `total_salary_amount` varchar(255) DEFAULT NULL,
  `deducted_amount` varchar(255) DEFAULT NULL,
  `amount` varchar(50) NOT NULL,
  `slip_data` longtext DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `statement_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_payout_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_payout_temp` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `pay_slip_id` varchar(255) DEFAULT NULL,
  `month` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `staff_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `debit` varchar(50) NOT NULL,
  `credit` varchar(50) NOT NULL,
  `balance` int(255) DEFAULT NULL,
  `payslip_id` varchar(50) DEFAULT NULL,
  `debit_account` varchar(255) DEFAULT NULL,
  `payment_method` varchar(155) DEFAULT NULL,
  `bank_charges` varchar(50) DEFAULT NULL,
  `clearance_date` date DEFAULT NULL,
  `initiation_date` date DEFAULT NULL,
  `transaction_for` varchar(255) DEFAULT NULL,
  `log` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `txn_id` varchar(255) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_selected_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_selected_stages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` bigint(20) unsigned NOT NULL,
  `exam_stage_id` bigint(20) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_selected_stages_exam_id_exam_stage_id_unique` (`exam_id`,`exam_stage_id`),
  KEY `exam_selected_stages_exam_stage_id_foreign` (`exam_stage_id`),
  CONSTRAINT `exam_selected_stages_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_selected_stages_exam_stage_id_foreign` FOREIGN KEY (`exam_stage_id`) REFERENCES `exam_stages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` bigint(20) unsigned NOT NULL,
  `academic_year` varchar(255) DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `application_start_date` date DEFAULT NULL,
  `application_end_date` date DEFAULT NULL,
  `correction_window_dates` varchar(255) DEFAULT NULL,
  `admit_card_release_date` date DEFAULT NULL,
  `admit_card_url` varchar(255) DEFAULT NULL,
  `exam_start_date` date DEFAULT NULL,
  `exam_end_date` date DEFAULT NULL,
  `answer_key_release_date` date DEFAULT NULL,
  `result_declaration_date` date DEFAULT NULL,
  `result_url` varchar(255) DEFAULT NULL,
  `counselling_start_date` date DEFAULT NULL,
  `counselling_end_date` date DEFAULT NULL,
  `is_current_session` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'Upcoming',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exam_sessions_exam_id_foreign` (`exam_id`),
  CONSTRAINT `exam_sessions_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_stage_interviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_stage_interviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `interview_stage_id` char(36) NOT NULL,
  `exam_id` bigint(20) unsigned NOT NULL,
  `exam_stage_id` bigint(20) unsigned NOT NULL,
  `stage_name` varchar(255) NOT NULL DEFAULT 'Interview',
  `stage_order` int(11) NOT NULL DEFAULT 1,
  `mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `stage_contribution_type` varchar(255) NOT NULL DEFAULT 'merit_deciding',
  `interview_conducting_body` varchar(255) DEFAULT NULL,
  `interview_panel_type` varchar(255) DEFAULT NULL,
  `panel_constitution_guidelines` text DEFAULT NULL,
  `interview_centres_scope` varchar(255) DEFAULT NULL,
  `official_interview_guidelines_url` varchar(255) DEFAULT NULL,
  `interview_mode` varchar(255) DEFAULT NULL,
  `interview_duration_minutes` int(11) DEFAULT NULL,
  `number_of_panellists` int(11) DEFAULT NULL,
  `language_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`language_options`)),
  `medium_switch_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `evaluation_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`evaluation_criteria`)),
  `criteria_weightage_defined` tinyint(1) NOT NULL DEFAULT 0,
  `marks_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `maximum_marks` decimal(8,2) DEFAULT NULL,
  `minimum_qualifying_marks` decimal(8,2) DEFAULT NULL,
  `category_wise_cutoff_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `weightage_percentage` decimal(5,2) DEFAULT NULL,
  `normalization_applied` tinyint(1) NOT NULL DEFAULT 0,
  `previous_stage_qualification_required` tinyint(1) NOT NULL DEFAULT 1,
  `shortlisting_basis` varchar(255) DEFAULT NULL,
  `documents_required_for_interview_call` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_required_for_interview_call`)),
  `interview_process_steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interview_process_steps`)),
  `identity_verification_required` tinyint(1) NOT NULL DEFAULT 1,
  `biometric_verification_required` tinyint(1) NOT NULL DEFAULT 0,
  `slot_booking_required` tinyint(1) NOT NULL DEFAULT 0,
  `slot_allocation_method` varchar(255) DEFAULT NULL,
  `rescheduling_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `rescheduling_conditions` text DEFAULT NULL,
  `late_reporting_policy` text DEFAULT NULL,
  `interview_result_type` varchar(255) DEFAULT NULL,
  `interview_result_visibility` varchar(255) DEFAULT NULL,
  `interview_result_declaration_date` date DEFAULT NULL,
  `appeal_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `appeal_process_description` text DEFAULT NULL,
  `appeal_time_limit_days` int(11) DEFAULT NULL,
  `appeal_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `appeal_fee_amount` decimal(8,2) DEFAULT NULL,
  `final_decision_authority` varchar(255) DEFAULT NULL,
  `category_relaxations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_relaxations`)),
  `pwd_accommodations_available` tinyint(1) NOT NULL DEFAULT 0,
  `ex_servicemen_relaxations` text DEFAULT NULL,
  `gender_specific_guidelines` text DEFAULT NULL,
  `interview_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `interview_fee_amount` decimal(8,2) DEFAULT NULL,
  `fee_refundable` tinyint(1) NOT NULL DEFAULT 0,
  `payment_modes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_modes`)),
  `interview_disclaimer_text` text DEFAULT NULL,
  `information_source` varchar(255) DEFAULT NULL,
  `last_verified_on` date DEFAULT NULL,
  `stage_status` varchar(255) NOT NULL DEFAULT 'Scheduled',
  `visibility` varchar(255) NOT NULL DEFAULT 'Public',
  `remarks` text DEFAULT NULL,
  `last_updated_on` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_stage_interviews_interview_stage_id_unique` (`interview_stage_id`),
  KEY `exam_stage_interviews_exam_id_foreign` (`exam_id`),
  KEY `exam_stage_interviews_exam_stage_id_foreign` (`exam_stage_id`),
  CONSTRAINT `exam_stage_interviews_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_stage_interviews_exam_stage_id_foreign` FOREIGN KEY (`exam_stage_id`) REFERENCES `exam_stages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_stage_mains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_stage_mains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `main_stage_id` char(36) NOT NULL,
  `exam_id` bigint(20) unsigned NOT NULL,
  `exam_stage_id` bigint(20) unsigned NOT NULL,
  `stage_name` varchar(255) DEFAULT NULL,
  `stage_order` int(11) NOT NULL DEFAULT 2,
  `mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `subjects_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subjects_required`)),
  `attempt_limit` int(11) DEFAULT NULL,
  `gap_year_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `eligibility_notes` text DEFAULT NULL,
  `exam_mode` varchar(255) DEFAULT NULL,
  `exam_format` varchar(255) DEFAULT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `negative_marking` tinyint(1) NOT NULL DEFAULT 0,
  `negative_marking_scheme` varchar(255) DEFAULT NULL,
  `syllabus_url` varchar(255) DEFAULT NULL,
  `difficulty_level` varchar(255) DEFAULT NULL,
  `syllabus_source` text DEFAULT NULL,
  `subjects_covered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subjects_covered`)),
  `sessions_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sessions_data`)),
  `admit_card_download_procedure` text DEFAULT NULL,
  `result_check_procedure` text DEFAULT NULL,
  `score_type` varchar(255) DEFAULT NULL,
  `rank_type` varchar(255) DEFAULT NULL,
  `normalization_applied` tinyint(1) NOT NULL DEFAULT 0,
  `tie_breaking_rules` text DEFAULT NULL,
  `score_validity_period` varchar(255) DEFAULT NULL,
  `result_format_url` varchar(255) DEFAULT NULL,
  `cutoff_type` varchar(255) DEFAULT NULL,
  `cutoff_year_wise` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cutoff_year_wise`)),
  `cutoff_reference_note` text DEFAULT NULL,
  `registration_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `registration_fee_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`registration_fee_structure`)),
  `late_registration_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `late_fee_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`late_fee_rules`)),
  `security_deposit_required` tinyint(1) NOT NULL DEFAULT 0,
  `security_deposit_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`security_deposit_structure`)),
  `payment_modes_allowed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_modes_allowed`)),
  `transaction_charges_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `transaction_charge_borne_by` varchar(255) DEFAULT NULL,
  `last_updated_on` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_stage_mains_main_stage_id_unique` (`main_stage_id`),
  KEY `exam_stage_mains_exam_id_foreign` (`exam_id`),
  KEY `exam_stage_mains_exam_stage_id_foreign` (`exam_stage_id`),
  CONSTRAINT `exam_stage_mains_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_stage_mains_exam_stage_id_foreign` FOREIGN KEY (`exam_stage_id`) REFERENCES `exam_stages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_stage_medicals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_stage_medicals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medical_stage_id` char(36) NOT NULL,
  `exam_id` bigint(20) unsigned NOT NULL,
  `exam_stage_id` bigint(20) unsigned NOT NULL,
  `stage_name` varchar(255) DEFAULT NULL,
  `stage_order` int(11) NOT NULL DEFAULT 4,
  `mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `stage_contribution_type` varchar(255) NOT NULL DEFAULT 'qualifying_only',
  `medical_conducting_authority` varchar(255) DEFAULT NULL,
  `medical_board_type` varchar(255) DEFAULT NULL,
  `medical_centres_scope` varchar(255) DEFAULT NULL,
  `medical_centres_list_url` varchar(255) DEFAULT NULL,
  `official_medical_guidelines_url` varchar(255) DEFAULT NULL,
  `general_health_required` tinyint(1) NOT NULL DEFAULT 1,
  `free_from_chronic_diseases` tinyint(1) NOT NULL DEFAULT 1,
  `physical_fitness_required` tinyint(1) NOT NULL DEFAULT 1,
  `height_requirement` text DEFAULT NULL,
  `weight_standard_reference` text DEFAULT NULL,
  `chest_measurement_required` tinyint(1) NOT NULL DEFAULT 0,
  `chest_expansion_required` tinyint(1) NOT NULL DEFAULT 0,
  `vision_test_required` tinyint(1) NOT NULL DEFAULT 1,
  `visual_acuity_standards` text DEFAULT NULL,
  `color_vision_required` tinyint(1) NOT NULL DEFAULT 1,
  `night_blindness_disqualifying` tinyint(1) NOT NULL DEFAULT 1,
  `spectacles_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `hearing_standard_required` tinyint(1) NOT NULL DEFAULT 1,
  `speech_standard_required` tinyint(1) NOT NULL DEFAULT 1,
  `cardiovascular_system_check` tinyint(1) NOT NULL DEFAULT 1,
  `respiratory_system_check` tinyint(1) NOT NULL DEFAULT 1,
  `neurological_system_check` tinyint(1) NOT NULL DEFAULT 1,
  `musculoskeletal_system_check` tinyint(1) NOT NULL DEFAULT 1,
  `mental_health_evaluation_required` tinyint(1) NOT NULL DEFAULT 1,
  `temporary_disqualifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`temporary_disqualifications`)),
  `permanent_disqualifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permanent_disqualifications`)),
  `tattoo_policy` text DEFAULT NULL,
  `surgical_history_rules` text DEFAULT NULL,
  `pregnancy_rules` text DEFAULT NULL,
  `medical_exam_procedure_steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`medical_exam_procedure_steps`)),
  `tests_conducted` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tests_conducted`)),
  `fasting_required` tinyint(1) NOT NULL DEFAULT 0,
  `medical_exam_duration` varchar(255) DEFAULT NULL,
  `medical_review_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `appeal_time_limit_days` int(11) DEFAULT NULL,
  `review_medical_board_details` text DEFAULT NULL,
  `appeal_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `appeal_fee_amount` decimal(8,2) DEFAULT NULL,
  `final_decision_authority` varchar(255) DEFAULT NULL,
  `medical_result_type` varchar(255) DEFAULT NULL,
  `temporary_unfit_retest_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `retest_timeline_days` int(11) DEFAULT NULL,
  `medical_result_visibility` varchar(255) DEFAULT NULL,
  `medical_documents_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`medical_documents_required`)),
  `medical_certificate_format_url` varchar(255) DEFAULT NULL,
  `medical_exam_start_date` date DEFAULT NULL,
  `medical_exam_end_date` date DEFAULT NULL,
  `slot_booking_required` tinyint(1) NOT NULL DEFAULT 0,
  `reporting_time_guidelines` text DEFAULT NULL,
  `medical_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `medical_fee_amount` decimal(8,2) DEFAULT NULL,
  `fee_refundable` tinyint(1) NOT NULL DEFAULT 0,
  `payment_mode` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_mode`)),
  `gender_based_relaxation_rules` text DEFAULT NULL,
  `category_based_relaxation_rules` text DEFAULT NULL,
  `ex_servicemen_relaxation` text DEFAULT NULL,
  `pwd_medical_rules` text DEFAULT NULL,
  `medical_disclaimer_text` text DEFAULT NULL,
  `information_source` varchar(255) DEFAULT NULL,
  `last_verified_on` date DEFAULT NULL,
  `stage_status` varchar(255) DEFAULT NULL,
  `visibility` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `last_updated_on` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_stage_medicals_medical_stage_id_unique` (`medical_stage_id`),
  KEY `exam_stage_medicals_exam_id_foreign` (`exam_id`),
  KEY `exam_stage_medicals_exam_stage_id_foreign` (`exam_stage_id`),
  CONSTRAINT `exam_stage_medicals_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_stage_medicals_exam_stage_id_foreign` FOREIGN KEY (`exam_stage_id`) REFERENCES `exam_stages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_stage_preliminaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_stage_preliminaries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `preliminary_stage_id` char(36) NOT NULL,
  `exam_id` bigint(20) unsigned NOT NULL,
  `exam_stage_id` bigint(20) unsigned NOT NULL,
  `stage_name` varchar(255) DEFAULT NULL,
  `stage_order` int(11) NOT NULL DEFAULT 1,
  `mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `subjects_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subjects_required`)),
  `attempt_limit` int(11) DEFAULT NULL,
  `gap_year_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `eligibility_notes` text DEFAULT NULL,
  `exam_mode` varchar(255) DEFAULT NULL,
  `exam_format` varchar(255) DEFAULT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `negative_marking` tinyint(1) NOT NULL DEFAULT 0,
  `negative_marking_scheme` varchar(255) DEFAULT NULL,
  `syllabus_url` varchar(255) DEFAULT NULL,
  `difficulty_level` varchar(255) DEFAULT NULL,
  `syllabus_source` text DEFAULT NULL,
  `subjects_covered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subjects_covered`)),
  `sessions_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sessions_data`)),
  `admit_card_download_procedure` text DEFAULT NULL,
  `result_check_procedure` text DEFAULT NULL,
  `score_type` varchar(255) DEFAULT NULL,
  `rank_type` varchar(255) DEFAULT NULL,
  `normalization_applied` tinyint(1) NOT NULL DEFAULT 0,
  `tie_breaking_rules` text DEFAULT NULL,
  `score_validity_period` varchar(255) DEFAULT NULL,
  `result_format_url` varchar(255) DEFAULT NULL,
  `cutoff_type` varchar(255) DEFAULT NULL,
  `cutoff_year_wise` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cutoff_year_wise`)),
  `cutoff_reference_note` text DEFAULT NULL,
  `registration_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `registration_fee_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`registration_fee_structure`)),
  `late_registration_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `late_fee_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`late_fee_rules`)),
  `security_deposit_required` tinyint(1) NOT NULL DEFAULT 0,
  `security_deposit_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`security_deposit_structure`)),
  `payment_modes_allowed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_modes_allowed`)),
  `transaction_charges_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `transaction_charge_borne_by` varchar(255) DEFAULT NULL,
  `last_updated_on` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_stage_preliminaries_preliminary_stage_id_unique` (`preliminary_stage_id`),
  KEY `exam_stage_preliminaries_exam_id_foreign` (`exam_id`),
  KEY `exam_stage_preliminaries_exam_stage_id_foreign` (`exam_stage_id`),
  CONSTRAINT `exam_stage_preliminaries_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_stage_preliminaries_exam_stage_id_foreign` FOREIGN KEY (`exam_stage_id`) REFERENCES `exam_stages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_stage_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_stage_skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `skill_stage_id` char(36) NOT NULL,
  `exam_id` bigint(20) unsigned NOT NULL,
  `exam_stage_id` bigint(20) unsigned NOT NULL,
  `stage_name` varchar(255) DEFAULT NULL,
  `stage_order` int(11) NOT NULL DEFAULT 5,
  `mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `stage_contribution_type` varchar(255) NOT NULL DEFAULT 'qualifying_only',
  `skill_test_category` varchar(255) DEFAULT NULL,
  `skill_test_purpose` varchar(255) DEFAULT NULL,
  `skills_evaluated` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills_evaluated`)),
  `typing_language_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`typing_language_options`)),
  `minimum_typing_speed` varchar(255) DEFAULT NULL,
  `accuracy_required_percentage` decimal(5,2) DEFAULT NULL,
  `error_tolerance_percentage` decimal(5,2) DEFAULT NULL,
  `backspace_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `software_tools_tested` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`software_tools_tested`)),
  `task_based_evaluation` tinyint(1) NOT NULL DEFAULT 0,
  `task_completion_time_minutes` int(11) DEFAULT NULL,
  `marks_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `maximum_marks` decimal(8,2) DEFAULT NULL,
  `minimum_qualifying_score` decimal(8,2) DEFAULT NULL,
  `pass_fail_only` tinyint(1) NOT NULL DEFAULT 1,
  `normalization_applied` tinyint(1) NOT NULL DEFAULT 0,
  `previous_stage_qualification_required` tinyint(1) NOT NULL DEFAULT 1,
  `shortlisting_basis` varchar(255) DEFAULT NULL,
  `post_wise_skill_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`post_wise_skill_requirements`)),
  `category_wise_relaxations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_wise_relaxations`)),
  `test_mode` varchar(255) DEFAULT NULL,
  `test_environment` varchar(255) DEFAULT NULL,
  `assistive_devices_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `pwd_accommodations_available` tinyint(1) NOT NULL DEFAULT 0,
  `skill_test_centres_scope` varchar(255) DEFAULT NULL,
  `lab_infrastructure_required` text DEFAULT NULL,
  `reporting_time_guidelines` text DEFAULT NULL,
  `identity_verification_required` tinyint(1) NOT NULL DEFAULT 1,
  `attempts_allowed` int(11) NOT NULL DEFAULT 1,
  `retest_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `retest_conditions` text DEFAULT NULL,
  `temporary_failure_recovery_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `skill_test_result_type` varchar(255) DEFAULT NULL,
  `result_visibility` varchar(255) DEFAULT NULL,
  `result_declaration_date` date DEFAULT NULL,
  `appeal_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `appeal_process` text DEFAULT NULL,
  `appeal_time_limit_days` int(11) DEFAULT NULL,
  `appeal_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `appeal_fee_amount` decimal(8,2) DEFAULT NULL,
  `documents_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_required`)),
  `instruction_guidelines_url` varchar(255) DEFAULT NULL,
  `mock_test_available` tinyint(1) NOT NULL DEFAULT 0,
  `demo_environment_available` tinyint(1) NOT NULL DEFAULT 0,
  `skill_test_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `skill_test_fee_amount` decimal(8,2) DEFAULT NULL,
  `fee_refundable` tinyint(1) NOT NULL DEFAULT 0,
  `payment_modes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_modes`)),
  `skill_test_disclaimer_text` text DEFAULT NULL,
  `information_source` varchar(255) DEFAULT NULL,
  `last_verified_on` date DEFAULT NULL,
  `stage_status` varchar(255) DEFAULT NULL,
  `visibility` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `last_updated_on` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_stage_skills_skill_stage_id_unique` (`skill_stage_id`),
  KEY `exam_stage_skills_exam_id_foreign` (`exam_id`),
  KEY `exam_stage_skills_exam_stage_id_foreign` (`exam_stage_id`),
  CONSTRAINT `exam_stage_skills_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_stage_skills_exam_stage_id_foreign` FOREIGN KEY (`exam_stage_id`) REFERENCES `exam_stages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_stages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exam_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_subjects` (
  `id` char(36) NOT NULL,
  `exam_id` bigint(20) unsigned NOT NULL,
  `exam_stage_id` bigint(20) unsigned NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `subject_code` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `subject_type` enum('Mandatory','Optional','Language','Qualifying') NOT NULL DEFAULT 'Mandatory',
  `subject_group` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `max_subjects_allowed` int(11) NOT NULL DEFAULT 1,
  `subject_choice_required` tinyint(1) NOT NULL DEFAULT 0,
  `subject_combination_rules` text DEFAULT NULL,
  `applicable_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_categories`)),
  `subject_mediums_available` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subject_mediums_available`)),
  `syllabus_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`syllabus_structure`)),
  `syllabus_description` text DEFAULT NULL,
  `official_syllabus_pdf_url` varchar(255) DEFAULT NULL,
  `reference_books` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reference_books`)),
  `syllabus_version` varchar(255) DEFAULT NULL,
  `syllabus_effective_year` varchar(255) DEFAULT NULL,
  `number_of_papers` int(11) NOT NULL DEFAULT 1,
  `paper_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`paper_names`)),
  `total_marks` decimal(8,2) DEFAULT NULL,
  `marks_per_paper` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`marks_per_paper`)),
  `negative_marking` tinyint(1) NOT NULL DEFAULT 0,
  `qualifying_marks` decimal(8,2) DEFAULT NULL,
  `normalization_applied` tinyint(1) NOT NULL DEFAULT 0,
  `applicable_exam_stages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_exam_stages`)),
  `stage_weightage_override` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`stage_weightage_override`)),
  `minimum_qualification_required` text DEFAULT NULL,
  `background_subject_required` tinyint(1) NOT NULL DEFAULT 0,
  `subject_specific_eligibility_notes` text DEFAULT NULL,
  `subject_contributes_to_merit` tinyint(1) NOT NULL DEFAULT 1,
  `subject_weightage_percentage` decimal(5,2) DEFAULT NULL,
  `subject_result_type` enum('Marks','Pass/Fail') NOT NULL DEFAULT 'Marks',
  `subject_registration_required` tinyint(1) NOT NULL DEFAULT 0,
  `subject_change_allowed_till_date` date DEFAULT NULL,
  `schema_type` varchar(255) NOT NULL DEFAULT 'ExamSubject',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `focus_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`focus_keywords`)),
  `canonical_url` varchar(255) DEFAULT NULL,
  `status` enum('Active','Deprecated') NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `last_updated_on` timestamp NULL DEFAULT NULL,
  `information_source` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_subjects_slug_unique` (`slug`),
  KEY `exam_subjects_exam_stage_id_foreign` (`exam_stage_id`),
  KEY `exam_subjects_created_by_foreign` (`created_by`),
  KEY `exam_subjects_exam_id_exam_stage_id_index` (`exam_id`,`exam_stage_id`),
  CONSTRAINT `exam_subjects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `exam_subjects_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_subjects_exam_stage_id_foreign` FOREIGN KEY (`exam_stage_id`) REFERENCES `exam_stages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` char(36) NOT NULL,
  `name` text NOT NULL,
  `short_name` text DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `exam_type` varchar(255) DEFAULT NULL,
  `exam_category` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exam_category`)),
  `conducting_authority_name` varchar(255) DEFAULT NULL,
  `conducting_body_type` varchar(255) DEFAULT NULL,
  `official_website` text DEFAULT NULL,
  `about_exam` longtext DEFAULT NULL,
  `exam_purpose` varchar(255) DEFAULT NULL,
  `exam_source_type` varchar(255) NOT NULL DEFAULT 'External',
  `owning_organisation_id` bigint(20) unsigned DEFAULT NULL,
  `owning_organisation_name` varchar(255) DEFAULT NULL,
  `minimum_qualification` varchar(255) DEFAULT NULL,
  `minimum_marks_required` varchar(255) DEFAULT NULL,
  `subjects_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subjects_required`)),
  `minimum_age` int(11) DEFAULT NULL,
  `maximum_age` int(11) DEFAULT NULL,
  `attempt_limit` varchar(255) DEFAULT NULL,
  `gap_year_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `nationality_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nationality_criteria`)),
  `reservation_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `eligibility_notes` longtext DEFAULT NULL,
  `exam_mode` varchar(255) DEFAULT NULL,
  `exam_format` varchar(255) DEFAULT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `negative_marking` tinyint(1) NOT NULL DEFAULT 0,
  `negative_marking_scheme` text DEFAULT NULL,
  `sections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sections`)),
  `duration_minutes` int(11) DEFAULT NULL,
  `languages_available` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`languages_available`)),
  `syllabus_source` text DEFAULT NULL,
  `syllabus_url` varchar(255) DEFAULT NULL,
  `subjects_covered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subjects_covered`)),
  `difficulty_level` varchar(255) DEFAULT NULL,
  `recommended_classes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recommended_classes`)),
  `previous_year_question_papers_available` tinyint(1) NOT NULL DEFAULT 0,
  `admit_card_download_procedure` longtext DEFAULT NULL,
  `result_check_procedure` longtext DEFAULT NULL,
  `score_type` varchar(255) DEFAULT NULL,
  `rank_type` varchar(255) DEFAULT NULL,
  `normalization_applied` tinyint(1) NOT NULL DEFAULT 0,
  `tie_breaking_rules` text DEFAULT NULL,
  `score_validity_period` varchar(255) DEFAULT NULL,
  `result_format_url` varchar(255) DEFAULT NULL,
  `cutoff_type` varchar(255) DEFAULT NULL,
  `cutoff_year_wise` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cutoff_year_wise`)),
  `cutoff_reference_note` text DEFAULT NULL,
  `counselling_conducted` tinyint(1) NOT NULL DEFAULT 0,
  `counselling_authority` varchar(255) DEFAULT NULL,
  `counselling_mode` varchar(255) DEFAULT NULL,
  `number_of_rounds` int(11) DEFAULT NULL,
  `seat_allocation_basis` varchar(255) DEFAULT NULL,
  `reservation_policy_reference` varchar(255) DEFAULT NULL,
  `official_counselling_website` varchar(255) DEFAULT NULL,
  `accepting_organization_count` int(11) DEFAULT NULL,
  `accepting_organizations_sample` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`accepting_organizations_sample`)),
  `course_types_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`course_types_supported`)),
  `exam_frequency` varchar(255) DEFAULT NULL,
  `first_conducted_year` int(11) DEFAULT NULL,
  `years_active` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`years_active`)),
  `exam_discontinued` tinyint(1) NOT NULL DEFAULT 0,
  `replaced_by_exam_name` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `focus_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`focus_keywords`)),
  `schema_type` varchar(255) NOT NULL DEFAULT 'EducationalAssessment',
  `canonical_url` varchar(255) DEFAULT NULL,
  `indexing_status` varchar(255) NOT NULL DEFAULT 'index, follow',
  `breadcrumb_category` varchar(255) DEFAULT NULL,
  `official_notification_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`official_notification_urls`)),
  `information_source` varchar(255) DEFAULT NULL,
  `last_verified_on` date DEFAULT NULL,
  `data_confidence_score` int(11) NOT NULL DEFAULT 0,
  `disclaimer_text` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `visibility` varchar(255) NOT NULL DEFAULT 'Public',
  `featured_exam` tinyint(1) NOT NULL DEFAULT 0,
  `has_stages` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `registration_fee_required` tinyint(1) NOT NULL DEFAULT 0,
  `registration_fee_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`registration_fee_structure`)),
  `late_registration_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `late_fee_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`late_fee_rules`)),
  `security_deposit_required` tinyint(1) NOT NULL DEFAULT 0,
  `partial_refund_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `security_deposit_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`security_deposit_structure`)),
  `round_specific_fee_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`round_specific_fee_rules`)),
  `refund_policy_summary` text DEFAULT NULL,
  `refund_timeline` varchar(255) DEFAULT NULL,
  `refund_mode` varchar(255) DEFAULT NULL,
  `forfeiture_scenarios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`forfeiture_scenarios`)),
  `payment_modes_allowed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_modes_allowed`)),
  `transaction_charges_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `transaction_charge_borne_by` varchar(255) DEFAULT NULL,
  `payment_gateway_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exams_exam_id_unique` (`exam_id`),
  UNIQUE KEY `exams_slug_unique` (`slug`),
  KEY `exams_owning_organisation_id_foreign` (`owning_organisation_id`),
  KEY `exams_created_by_foreign` (`created_by`),
  KEY `exams_updated_by_foreign` (`updated_by`),
  CONSTRAINT `exams_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `exams_owning_organisation_id_foreign` FOREIGN KEY (`owning_organisation_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exams_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expert_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expert_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expert_categories_name_unique` (`name`),
  UNIQUE KEY `expert_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expert_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expert_commissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expert_id` bigint(20) unsigned NOT NULL,
  `commission_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `commission_value` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expert_commissions_expert_id_foreign` (`expert_id`),
  CONSTRAINT `expert_commissions_expert_id_foreign` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expert_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expert_slots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expert_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `recurring_day` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'available',
  `mode` varchar(255) NOT NULL DEFAULT 'video',
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expert_slots_expert_id_foreign` (`expert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `experts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `experts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `faculty_id` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `public_contact_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `profile_visibility` varchar(255) NOT NULL DEFAULT 'Public',
  `profile_claimed` tinyint(1) NOT NULL DEFAULT 0,
  `verification_status` varchar(255) NOT NULL DEFAULT 'Pending',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `focus_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`focus_keywords`)),
  `schema_type` varchar(255) NOT NULL DEFAULT 'Person',
  `canonical_url` varchar(255) DEFAULT NULL,
  `indexing_status` varchar(255) NOT NULL DEFAULT 'index',
  `data_source` varchar(255) NOT NULL DEFAULT 'Manual',
  `confidence_score` double DEFAULT NULL,
  `last_updated_on` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `gender` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `short_bio` varchar(255) DEFAULT NULL,
  `detailed_bio` text DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL,
  `expert_category_id` bigint(20) unsigned DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `subject_specialization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subject_specialization`)),
  `degree` varchar(255) DEFAULT NULL,
  `exp` varchar(255) DEFAULT NULL,
  `years_of_experience_total` double DEFAULT NULL,
  `years_of_experience_current_institute` double DEFAULT NULL,
  `previous_institutes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`previous_institutes`)),
  `industry_experience` tinyint(1) NOT NULL DEFAULT 0,
  `exams_cleared` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exams_cleared`)),
  `notable_achievements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notable_achievements`)),
  `current_institute_id` bigint(20) unsigned DEFAULT NULL,
  `current_institute_name` varchar(255) DEFAULT NULL,
  `faculty_type` varchar(255) DEFAULT NULL,
  `joining_year` year(4) DEFAULT NULL,
  `courses_taught` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`courses_taught`)),
  `target_batches` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_batches`)),
  `average_batch_size_handled` int(11) DEFAULT NULL,
  `teaching_style` varchar(255) DEFAULT NULL,
  `language_of_teaching` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`language_of_teaching`)),
  `lecture_mode` varchar(255) DEFAULT NULL,
  `weekly_classes_count` int(11) DEFAULT NULL,
  `doubt_solving_sessions` tinyint(1) NOT NULL DEFAULT 0,
  `one_to_one_mentoring` tinyint(1) NOT NULL DEFAULT 0,
  `rating` varchar(255) NOT NULL DEFAULT '5.0',
  `total_reviews` int(11) NOT NULL DEFAULT 0,
  `verified_student_reviews_only` tinyint(1) NOT NULL DEFAULT 0,
  `student_testimonials` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`student_testimonials`)),
  `peer_reviews` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`peer_reviews`)),
  `awards_recognition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`awards_recognition`)),
  `count` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `profile_photo_url` varchar(255) DEFAULT NULL,
  `cover_photo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `highest_qualification` varchar(255) DEFAULT NULL,
  `other_qualifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`other_qualifications`)),
  `certifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`certifications`)),
  `domain_certification` varchar(255) DEFAULT NULL,
  `teaching_credentials` varchar(255) DEFAULT NULL,
  `industry_licenses` varchar(255) DEFAULT NULL,
  `primary_domain` varchar(255) DEFAULT NULL,
  `sub_specialization` varchar(255) DEFAULT NULL,
  `years_of_domain_experience` varchar(255) DEFAULT NULL,
  `academic_vs_industry_expertise` varchar(255) DEFAULT NULL,
  `total_counseling_experience` varchar(255) DEFAULT NULL,
  `no_of_students_counseled` varchar(255) DEFAULT NULL,
  `counseling_specialization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`counseling_specialization`)),
  `students_admitted_to_top_university` text DEFAULT NULL,
  `exam_success_rate` varchar(255) DEFAULT NULL,
  `scholarship_conversion_rate` varchar(255) DEFAULT NULL,
  `career_placement_outcomes` text DEFAULT NULL,
  `years_of_industry_experience` varchar(255) DEFAULT NULL,
  `current_past_employer_quality` varchar(255) DEFAULT NULL,
  `consulting_advisory_roles` text DEFAULT NULL,
  `live_industry_project_exposure` text DEFAULT NULL,
  `one_on_one_counseling` tinyint(1) NOT NULL DEFAULT 0,
  `years_with_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`years_with_results`)),
  `students_selected_count` int(11) DEFAULT NULL,
  `top_rank_students` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`top_rank_students`)),
  `best_result_year` year(4) DEFAULT NULL,
  `result_verification_source` varchar(255) DEFAULT NULL,
  `intro_video_url` varchar(255) DEFAULT NULL,
  `demo_lecture_videos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`demo_lecture_videos`)),
  `articles_written` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`articles_written`)),
  `youtube_channel_url` varchar(255) DEFAULT NULL,
  `linkedin_profile_url` varchar(255) DEFAULT NULL,
  `instagram_profile_url` varchar(255) DEFAULT NULL,
  `telegram_channel_url` varchar(255) DEFAULT NULL,
  `average_student_feedback_rating` double DEFAULT NULL,
  `group_counseling` tinyint(1) NOT NULL DEFAULT 0,
  `psychometric_based_counseling` tinyint(1) NOT NULL DEFAULT 0,
  `data_driven_career_mapping` tinyint(1) NOT NULL DEFAULT 0,
  `goal_oriented_planning` tinyint(1) NOT NULL DEFAULT 0,
  `session_modes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_modes`)),
  `languages_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`languages_supported`)),
  `average_wait_time` varchar(255) DEFAULT NULL,
  `session_duration` varchar(255) DEFAULT NULL,
  `flexible_scheduling` tinyint(1) NOT NULL DEFAULT 0,
  `academic_network_reach` varchar(255) DEFAULT NULL,
  `industry_connection` varchar(255) DEFAULT NULL,
  `university_admission_office_access` varchar(255) DEFAULT NULL,
  `alumni_recruiter_connections` varchar(255) DEFAULT NULL,
  `feedback_sentiment_score` varchar(255) DEFAULT NULL,
  `verified_counseling_reviews` varchar(255) DEFAULT NULL,
  `repeat_counseling_rate` varchar(255) DEFAULT NULL,
  `research_publications` text DEFAULT NULL,
  `patents` text DEFAULT NULL,
  `conference_talks` text DEFAULT NULL,
  `curriculum_design_experience` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `experts_email_unique` (`email`),
  KEY `experts_expert_category_id_foreign` (`expert_category_id`),
  CONSTRAINT `experts_expert_category_id_foreign` FOREIGN KEY (`expert_category_id`) REFERENCES `expert_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facilities` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `faq_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faq_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faq_categories_slug_unique` (`slug`),
  KEY `faq_categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `faq_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `faq_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `faq_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faq_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `faq_category_id` bigint(20) unsigned NOT NULL,
  `question` text NOT NULL,
  `answer` longtext NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faq_items_faq_category_id_foreign` (`faq_category_id`),
  CONSTRAINT `faq_items_faq_category_id_foreign` FOREIGN KEY (`faq_category_id`) REFERENCES `faq_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `answer` longtext NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `footer_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `footer_menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `show_view_all` tinyint(1) NOT NULL DEFAULT 0,
  `view_all_link` varchar(255) DEFAULT NULL,
  `bottom_badge_text` varchar(255) DEFAULT NULL,
  `bottom_badge_subtext` varchar(255) DEFAULT NULL,
  `bottom_badge_icon` varchar(255) DEFAULT NULL,
  `bottom_badge_rating` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `footer_menus_parent_id_foreign` (`parent_id`),
  CONSTRAINT `footer_menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `footer_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `header_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `header_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `header_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `header_menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `header_menus_parent_id_foreign` (`parent_id`),
  CONSTRAINT `header_menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `header_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hero_sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hero_sliders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `image_type` varchar(255) DEFAULT 'Text',
  `heading` varchar(255) DEFAULT NULL,
  `subheading` text DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `badge_text` varchar(255) DEFAULT NULL,
  `stat_1_count` varchar(255) DEFAULT NULL,
  `stat_1_label` varchar(255) DEFAULT NULL,
  `stat_2_count` varchar(255) DEFAULT NULL,
  `stat_2_label` varchar(255) DEFAULT NULL,
  `stat_3_count` varchar(255) DEFAULT NULL,
  `stat_3_label` varchar(255) DEFAULT NULL,
  `pill_1_label` varchar(255) DEFAULT NULL,
  `pill_1_url` varchar(255) DEFAULT NULL,
  `pill_2_label` varchar(255) DEFAULT NULL,
  `pill_2_url` varchar(255) DEFAULT NULL,
  `pill_3_label` varchar(255) DEFAULT NULL,
  `pill_3_url` varchar(255) DEFAULT NULL,
  `pill_4_label` varchar(255) DEFAULT NULL,
  `pill_4_url` varchar(255) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `home_benefits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_benefits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `reward_amount` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `home_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `footer_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `homepage_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `cta_title` varchar(255) DEFAULT NULL,
  `cta_url` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `homepage_sections_section_key_unique` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `homepage_stream_tabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_stream_tabs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `keywords` text DEFAULT NULL,
  `default_exams` text DEFAULT NULL,
  `default_states` text DEFAULT NULL,
  `default_courses` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `homepage_stream_tabs_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `import_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_task` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `file_path` varchar(255) NOT NULL,
  `result` longtext DEFAULT NULL,
  `progress` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `institute_marquees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institute_marquees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `logo` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `heading` varchar(255) DEFAULT NULL,
  `subheading` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `direction` varchar(255) NOT NULL DEFAULT 'rtl',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `institutes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institutes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `interested_ins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interested_ins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `type` enum('Student','Expert','Alumni') NOT NULL DEFAULT 'Student',
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'New',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `leadable_type` varchar(255) DEFAULT NULL,
  `leadable_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_leadable_type_leadable_id_index` (`leadable_type`,`leadable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_policies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `policy` longtext DEFAULT NULL,
  `department_ids` varchar(255) DEFAULT NULL,
  `designation_ids` varchar(255) DEFAULT NULL,
  `staff_ids` varchar(255) DEFAULT NULL,
  `organization_id` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `allotment_type` enum('monthly','yearly') NOT NULL,
  `monthly_leave` int(11) DEFAULT 0,
  `yearly_leave` int(11) DEFAULT 0,
  `pay_status` enum('paid','unpaid') NOT NULL,
  `effective_after` int(11) DEFAULT 0,
  `unused_leave` varchar(255) NOT NULL,
  `allow_in_noticePeroid` varchar(100) DEFAULT NULL,
  `allow_in_probation` varchar(100) DEFAULT NULL,
  `over_utilization` varchar(100) DEFAULT NULL,
  `gender` varchar(191) DEFAULT NULL,
  `marital_status` varchar(191) DEFAULT NULL,
  `department_ids` text DEFAULT NULL,
  `designation_ids` text DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leaves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `leave_type_id` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_till` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `files` longtext DEFAULT NULL,
  `fine` varchar(50) DEFAULT NULL,
  `apply_date` timestamp NULL DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `admin_message` text DEFAULT NULL,
  `log` longtext DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `marquee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marquee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mega_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mega_menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
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
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_availability_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_availability_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_profile_id` bigint(20) unsigned NOT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `slots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`slots`)),
  `advance_notice` varchar(255) DEFAULT NULL,
  `max_sessions` int(11) DEFAULT NULL,
  `pause_bookings` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unavailability_dates` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_availability_details_mentor_profile_id_foreign` (`mentor_profile_id`),
  CONSTRAINT `mentor_availability_details_mentor_profile_id_foreign` FOREIGN KEY (`mentor_profile_id`) REFERENCES `mentor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_commissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `commission_percentage` decimal(5,2) NOT NULL DEFAULT 15.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `priority_order` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`priority_order`)),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_degrees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_degrees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_educations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_educations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_profile_id` bigint(20) unsigned NOT NULL,
  `degree_type` varchar(255) NOT NULL,
  `specialisation` varchar(255) DEFAULT NULL,
  `institution` varchar(255) NOT NULL,
  `year_of_graduation` int(11) NOT NULL,
  `degree_certificate` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_educations_mentor_profile_id_foreign` (`mentor_profile_id`),
  CONSTRAINT `mentor_educations_mentor_profile_id_foreign` FOREIGN KEY (`mentor_profile_id`) REFERENCES `mentor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_experiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_experiences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_profile_id` bigint(20) unsigned NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `industry` varchar(255) NOT NULL,
  `years_of_experience` varchar(255) NOT NULL,
  `start_year` int(11) NOT NULL,
  `end_year` int(11) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_experiences_mentor_profile_id_foreign` (`mentor_profile_id`),
  CONSTRAINT `mentor_experiences_mentor_profile_id_foreign` FOREIGN KEY (`mentor_profile_id`) REFERENCES `mentor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_industries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_industries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_languages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_mentee_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_mentee_levels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_mentorship_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_mentorship_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_profile_id` bigint(20) unsigned NOT NULL,
  `areas_of_mentorship` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`areas_of_mentorship`)),
  `target_mentee_levels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_mentee_levels`)),
  `session_formats` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_formats`)),
  `session_durations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_durations`)),
  `preferred_platform` varchar(255) DEFAULT NULL,
  `mentoring_style` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_mentorship_details_mentor_profile_id_foreign` (`mentor_profile_id`),
  CONSTRAINT `mentor_mentorship_details_mentor_profile_id_foreign` FOREIGN KEY (`mentor_profile_id`) REFERENCES `mentor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_preferences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_profile_id` bigint(20) unsigned NOT NULL,
  `new_booking_request` tinyint(1) NOT NULL DEFAULT 1,
  `session_reminders` tinyint(1) NOT NULL DEFAULT 1,
  `new_review_posted` tinyint(1) NOT NULL DEFAULT 1,
  `weekly_analytics_digest` tinyint(1) NOT NULL DEFAULT 1,
  `platform_announcements` tinyint(1) NOT NULL DEFAULT 1,
  `whatsapp_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `notification_email` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_preferences_mentor_profile_id_foreign` (`mentor_profile_id`),
  CONSTRAINT `mentor_preferences_mentor_profile_id_foreign` FOREIGN KEY (`mentor_profile_id`) REFERENCES `mentor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_pricing_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_pricing_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_profile_id` bigint(20) unsigned NOT NULL,
  `fee_30_min` int(11) DEFAULT NULL,
  `fee_60_min` int(11) DEFAULT NULL,
  `offer_free_first_session` tinyint(1) NOT NULL DEFAULT 0,
  `pro_bono_sessions` int(11) NOT NULL DEFAULT 0,
  `payout_method` varchar(255) DEFAULT NULL,
  `upi_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `upi_qr_code` varchar(255) DEFAULT NULL,
  `bank_account_holder_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_ifsc_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_pricing_details_mentor_profile_id_foreign` (`mentor_profile_id`),
  CONSTRAINT `mentor_pricing_details_mentor_profile_id_foreign` FOREIGN KEY (`mentor_profile_id`) REFERENCES `mentor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_profile_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_profile_languages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_profile_id` bigint(20) unsigned NOT NULL,
  `mentor_language_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_profile_languages_mentor_profile_id_foreign` (`mentor_profile_id`),
  KEY `mentor_profile_languages_mentor_language_id_foreign` (`mentor_language_id`),
  CONSTRAINT `mentor_profile_languages_mentor_language_id_foreign` FOREIGN KEY (`mentor_language_id`) REFERENCES `mentor_languages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentor_profile_languages_mentor_profile_id_foreign` FOREIGN KEY (`mentor_profile_id`) REFERENCES `mentor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `professional_headline` varchar(255) DEFAULT NULL,
  `short_bio` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state_country` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `mentor_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_profile_id` bigint(20) unsigned NOT NULL,
  `gov_id_path` varchar(255) DEFAULT NULL,
  `gov_id_status` enum('not_submitted','pending','verified','rejected') NOT NULL DEFAULT 'not_submitted',
  `gov_id_comment` text DEFAULT NULL,
  `linkedin_status` enum('not_submitted','pending','verified','rejected') NOT NULL DEFAULT 'not_submitted',
  `linkedin_comment` text DEFAULT NULL,
  `background_check_status` enum('not_submitted','pending','verified','rejected') NOT NULL DEFAULT 'not_submitted',
  `background_check_comment` text DEFAULT NULL,
  `degree_status` enum('not_submitted','pending','verified','rejected') NOT NULL DEFAULT 'not_submitted',
  `degree_comment` text DEFAULT NULL,
  `platform_agreement_signed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentor_verifications_mentor_profile_id_foreign` (`mentor_profile_id`),
  CONSTRAINT `mentor_verifications_mentor_profile_id_foreign` FOREIGN KEY (`mentor_profile_id`) REFERENCES `mentor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `start_date` varchar(100) DEFAULT NULL,
  `due_date` varchar(100) DEFAULT NULL,
  `price` varchar(100) DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `noteworthy_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `noteworthy_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `noteworthy_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `noteworthy_mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `noteworthy_mentions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `noteworthy_category_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `badge_text` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `noteworthy_mentions_slug_unique` (`slug`),
  KEY `noteworthy_mentions_noteworthy_category_id_foreign` (`noteworthy_category_id`),
  CONSTRAINT `noteworthy_mentions_noteworthy_category_id_foreign` FOREIGN KEY (`noteworthy_category_id`) REFERENCES `noteworthy_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_academic_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_academic_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `exam_year` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `students_appeared` int(11) DEFAULT NULL,
  `pass_percentage` decimal(5,2) DEFAULT NULL,
  `distinction_percentage` decimal(5,2) DEFAULT NULL,
  `average_score` decimal(5,2) DEFAULT NULL,
  `highest_score` decimal(5,2) DEFAULT NULL,
  `topper_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`topper_names`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisation_academic_results_organisation_id_foreign` (`organisation_id`),
  CONSTRAINT `organisation_academic_results_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_accreditations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_accreditations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `accreditation_approval_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `university_accreditations_university_id_foreign` (`organisation_id`),
  KEY `university_accreditations_accreditation_approval_id_foreign` (`accreditation_approval_id`),
  CONSTRAINT `university_accreditations_accreditation_approval_id_foreign` FOREIGN KEY (`accreditation_approval_id`) REFERENCES `accreditation_approvals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `university_accreditations_university_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_awards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_awards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `university_awards_university_id_foreign` (`organisation_id`),
  CONSTRAINT `university_awards_university_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `campus_id` char(36) DEFAULT NULL,
  `department_id` char(36) DEFAULT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `academic_unit_name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `entrance_exam_id` bigint(20) unsigned DEFAULT NULL,
  `entrance_exam_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`entrance_exam_ids`)),
  `entrance_exam_category` varchar(255) DEFAULT NULL,
  `mode` varchar(255) DEFAULT NULL,
  `fees` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `admission_process` text DEFAULT NULL,
  `provisional_admission` tinyint(1) NOT NULL DEFAULT 0,
  `eligibility` text DEFAULT NULL,
  `fees_structure` text DEFAULT NULL,
  `roi` enum('Low','Medium','High') DEFAULT NULL,
  `curriculum` text DEFAULT NULL,
  `career_prospects` text DEFAULT NULL,
  `placement_details` text DEFAULT NULL,
  `program_level_id` bigint(20) unsigned DEFAULT NULL,
  `stream_offered_id` bigint(20) unsigned DEFAULT NULL,
  `discipline_id` bigint(20) unsigned DEFAULT NULL,
  `specialization_id` bigint(20) unsigned DEFAULT NULL,
  `specialization_ids` longtext DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `industrial_collaboration` text DEFAULT NULL,
  `internship_ranking` text DEFAULT NULL,
  `course_languages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`course_languages`)),
  `total_fees` decimal(10,2) DEFAULT NULL,
  `school_type` varchar(255) DEFAULT NULL,
  `education_board` varchar(255) DEFAULT NULL,
  `board_affiliation_number` varchar(255) DEFAULT NULL,
  `affiliation_valid_from` date DEFAULT NULL,
  `affiliation_valid_to` date DEFAULT NULL,
  `medium_of_instruction` varchar(255) DEFAULT NULL,
  `grade_range` varchar(255) DEFAULT NULL,
  `streams_offered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`streams_offered`)),
  `student_strength` varchar(255) DEFAULT NULL,
  `total_teachers` varchar(255) DEFAULT NULL,
  `trained_teachers_percentage` varchar(255) DEFAULT NULL,
  `student_teacher_ratio` varchar(255) DEFAULT NULL,
  `special_educator_available` tinyint(1) NOT NULL DEFAULT 0,
  `school_counsellor_available` tinyint(1) NOT NULL DEFAULT 0,
  `average_class_size` varchar(255) DEFAULT NULL,
  `assessment_pattern` varchar(255) DEFAULT NULL,
  `homework_policy` varchar(255) DEFAULT NULL,
  `parent_teacher_meet_frequency` varchar(255) DEFAULT NULL,
  `remedial_classes_available` tinyint(1) NOT NULL DEFAULT 0,
  `board_result_classes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`board_result_classes`)),
  `average_board_result_percentage` varchar(255) DEFAULT NULL,
  `highest_score` varchar(255) DEFAULT NULL,
  `distinction_percentage` varchar(255) DEFAULT NULL,
  `olympiad_participation` tinyint(1) NOT NULL DEFAULT 0,
  `competitive_exam_preparation_support` tinyint(1) NOT NULL DEFAULT 0,
  `annual_fee_range` varchar(255) DEFAULT NULL,
  `admission_fee` varchar(255) DEFAULT NULL,
  `transport_fee` varchar(255) DEFAULT NULL,
  `hostel_fee` varchar(255) DEFAULT NULL,
  `fee_payment_frequency` varchar(255) DEFAULT NULL,
  `parent_app_available` tinyint(1) NOT NULL DEFAULT 0,
  `attendance_tracking_available` tinyint(1) NOT NULL DEFAULT 0,
  `sports_offered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sports_offered`)),
  `arts_music_programs_available` tinyint(1) NOT NULL DEFAULT 0,
  `clubs_and_societies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`clubs_and_societies`)),
  `annual_events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`annual_events`)),
  `delivery_mode` varchar(255) DEFAULT NULL,
  `integrated_schooling_available` tinyint(1) NOT NULL DEFAULT 0,
  `total_batches` int(11) DEFAULT NULL,
  `average_batch_size` int(11) DEFAULT NULL,
  `min_batch_size` int(11) DEFAULT NULL,
  `max_batch_size` int(11) DEFAULT NULL,
  `separate_batches_for_droppers` tinyint(1) NOT NULL DEFAULT 0,
  `merit_based_batching` tinyint(1) NOT NULL DEFAULT 0,
  `total_faculty_count` int(11) DEFAULT NULL,
  `senior_faculty_count` int(11) DEFAULT NULL,
  `average_faculty_experience_years` int(11) DEFAULT NULL,
  `full_time_faculty_percentage` varchar(255) DEFAULT NULL,
  `visiting_faculty_available` tinyint(1) NOT NULL DEFAULT 0,
  `doubt_solving_mode` varchar(255) DEFAULT NULL,
  `personal_mentorship_available` tinyint(1) NOT NULL DEFAULT 0,
  `extra_classes_for_weak_students` tinyint(1) NOT NULL DEFAULT 0,
  `parent_counselling_available` tinyint(1) NOT NULL DEFAULT 0,
  `study_material_type` varchar(255) DEFAULT NULL,
  `dpp_provided` tinyint(1) NOT NULL DEFAULT 0,
  `test_series_available` tinyint(1) NOT NULL DEFAULT 0,
  `tests_per_month` int(11) DEFAULT NULL,
  `full_syllabus_tests_count` int(11) DEFAULT NULL,
  `online_test_platform_available` tinyint(1) NOT NULL DEFAULT 0,
  `results_years_available` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`results_years_available`)),
  `total_selections_all_time` varchar(255) DEFAULT NULL,
  `selections_last_year` varchar(255) DEFAULT NULL,
  `highest_rank_achieved` varchar(255) DEFAULT NULL,
  `average_selection_rate` varchar(255) DEFAULT NULL,
  `result_verification_status` varchar(255) DEFAULT NULL,
  `average_course_fee_range` varchar(255) DEFAULT NULL,
  `installment_available` tinyint(1) NOT NULL DEFAULT 0,
  `scholarship_available` tinyint(1) NOT NULL DEFAULT 0,
  `refund_policy_available` tinyint(1) NOT NULL DEFAULT 0,
  `verified_reviews_only` tinyint(1) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `university_courses_university_id_foreign` (`organisation_id`),
  KEY `university_courses_course_id_foreign` (`course_id`),
  KEY `university_courses_program_level_id_foreign` (`program_level_id`),
  KEY `university_courses_stream_offered_id_foreign` (`stream_offered_id`),
  KEY `university_courses_discipline_id_foreign` (`discipline_id`),
  KEY `organisation_courses_campus_id_foreign` (`campus_id`),
  KEY `organisation_courses_entrance_exam_id_foreign` (`entrance_exam_id`),
  KEY `organisation_courses_department_id_foreign` (`department_id`),
  KEY `university_courses_specialization_id_foreign` (`specialization_id`),
  CONSTRAINT `organisation_courses_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organisation_courses_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organisation_courses_entrance_exam_id_foreign` FOREIGN KEY (`entrance_exam_id`) REFERENCES `exams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `university_courses_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `university_courses_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `university_courses_program_level_id_foreign` FOREIGN KEY (`program_level_id`) REFERENCES `program_levels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `university_courses_specialization_id_foreign` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `university_courses_stream_offered_id_foreign` FOREIGN KEY (`stream_offered_id`) REFERENCES `stream_offereds` (`id`) ON DELETE SET NULL,
  CONSTRAINT `university_courses_university_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_fees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `grade` varchar(255) NOT NULL,
  `tuition_fee_annual` decimal(10,2) DEFAULT NULL,
  `admission_fee` decimal(10,2) DEFAULT NULL,
  `development_fee` decimal(10,2) DEFAULT NULL,
  `transport_fee` decimal(10,2) DEFAULT NULL,
  `hostel_fee` decimal(10,2) DEFAULT NULL,
  `other_charges` decimal(10,2) DEFAULT NULL,
  `scholarship_details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisation_fees_organisation_id_foreign` (`organisation_id`),
  CONSTRAINT `organisation_fees_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_school_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_school_courses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) DEFAULT NULL,
  `campus_id` bigint(20) DEFAULT NULL,
  `course_id` bigint(20) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `school_type` varchar(255) DEFAULT NULL,
  `established_year` varchar(255) DEFAULT NULL,
  `about_school` varchar(255) DEFAULT NULL,
  `exams_prepared_for` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exams_prepared_for`)),
  `target_classes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_classes`)),
  `total_batches` int(11) DEFAULT NULL,
  `average_batch_size` int(11) DEFAULT NULL,
  `min_batch_size` int(11) DEFAULT NULL,
  `max_batch_size` int(11) DEFAULT NULL,
  `integrated_schooling_available` tinyint(1) NOT NULL DEFAULT 0,
  `separate_batches_for_droppers` tinyint(1) NOT NULL DEFAULT 0,
  `merit_based_batching` tinyint(1) NOT NULL DEFAULT 0,
  `student_teacher_ratio` varchar(255) DEFAULT NULL,
  `delivery_mode` varchar(255) DEFAULT NULL,
  `education_board` varchar(255) DEFAULT NULL,
  `board_affiliation_number` varchar(255) DEFAULT NULL,
  `affiliation_valid_from` varchar(255) DEFAULT NULL,
  `affiliation_valid_to` varchar(255) DEFAULT NULL,
  `medium_of_instruction` varchar(255) DEFAULT NULL,
  `grade_range` varchar(255) DEFAULT NULL,
  `streams_offered` varchar(255) DEFAULT NULL,
  `total_teachers` varchar(255) DEFAULT NULL,
  `trained_teachers_percentage` varchar(255) DEFAULT NULL,
  `average_teacher_experience_years` varchar(255) DEFAULT NULL,
  `student_strength` varchar(255) DEFAULT NULL,
  `special_educator_available` varchar(255) DEFAULT NULL,
  `school_counsellor_available` varchar(255) DEFAULT NULL,
  `average_class_size` varchar(255) DEFAULT NULL,
  `assessment_pattern` varchar(255) DEFAULT NULL,
  `remedial_classes_available` varchar(255) DEFAULT NULL,
  `average_rating` varchar(255) NOT NULL,
  `total_reviews` varchar(255) DEFAULT NULL,
  `verified_reviews_only` varchar(255) DEFAULT NULL,
  `meta_title` longtext DEFAULT NULL,
  `meta_description` longtext DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `sort_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_sports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `sport_id` bigint(20) unsigned NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `university_sports_university_id_foreign` (`organisation_id`),
  KEY `university_sports_sport_id_foreign` (`sport_id`),
  CONSTRAINT `university_sports_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `university_sports_university_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_sub_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_sub_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_type_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisation_sub_types_organisation_type_id_foreign` (`organisation_type_id`),
  CONSTRAINT `organisation_sub_types_organisation_type_id_foreign` FOREIGN KEY (`organisation_type_id`) REFERENCES `organisation_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisation_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id_number` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `organisation_type_id` bigint(20) unsigned DEFAULT NULL,
  `brand_type` varchar(255) DEFAULT NULL,
  `central_authority` varchar(255) DEFAULT NULL,
  `franchise_partner_name` varchar(255) DEFAULT NULL,
  `franchise_start_year` varchar(255) DEFAULT NULL,
  `brand_compliance_verified` varchar(255) DEFAULT NULL,
  `head_office_location` varchar(255) DEFAULT NULL,
  `university_id` char(36) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `abbreviation` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `cover_image_url` varchar(255) DEFAULT NULL,
  `established_year` int(11) DEFAULT NULL,
  `university_type` varchar(255) DEFAULT NULL,
  `ownership_type` varchar(255) DEFAULT NULL,
  `about_university` text DEFAULT NULL,
  `vision_mission` text DEFAULT NULL,
  `core_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`core_values`)),
  `degree_awarding_authority` tinyint(1) DEFAULT NULL,
  `ugc_recognized` tinyint(1) DEFAULT NULL,
  `ugc_approval_number` varchar(255) DEFAULT NULL,
  `aicte_approved` tinyint(1) DEFAULT NULL,
  `naac_accredited` tinyint(1) DEFAULT NULL,
  `naac_grade` varchar(255) DEFAULT NULL,
  `nirf_rank_overall` int(11) DEFAULT NULL,
  `nirf_rank_category` int(11) DEFAULT NULL,
  `international_accreditations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`international_accreditations`)),
  `statutory_approvals` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`statutory_approvals`)),
  `recognition_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recognition_documents`)),
  `governing_body_name` varchar(255) DEFAULT NULL,
  `chancellor_name` varchar(255) DEFAULT NULL,
  `vice_chancellor_name` varchar(255) DEFAULT NULL,
  `autonomous_status` tinyint(1) DEFAULT NULL,
  `university_category` varchar(255) DEFAULT NULL,
  `number_of_campuses` int(11) DEFAULT NULL,
  `number_of_constituent_colleges` int(11) DEFAULT NULL,
  `number_of_affiliated_colleges` int(11) DEFAULT NULL,
  `levels_offered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`levels_offered`)),
  `institute_id` char(36) DEFAULT NULL,
  `about_organisation` text DEFAULT NULL,
  `registered_entity_name` varchar(255) DEFAULT NULL,
  `registration_number` varchar(255) DEFAULT NULL,
  `gst_registered` tinyint(1) DEFAULT NULL,
  `gst_number` varchar(255) DEFAULT NULL,
  `pan_number` varchar(255) DEFAULT NULL,
  `legal_documents_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`legal_documents_urls`)),
  `school_id` char(36) DEFAULT NULL,
  `exam_conducting_body_id` char(36) DEFAULT NULL,
  `counselling_body_id` char(36) DEFAULT NULL,
  `managing_trust_or_society_name` varchar(255) DEFAULT NULL,
  `minority_status` tinyint(1) DEFAULT NULL,
  `minority_type` varchar(255) DEFAULT NULL,
  `education_boards_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`education_boards_supported`)),
  `medium_of_instruction_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`medium_of_instruction_supported`)),
  `international_curriculum_supported` tinyint(1) DEFAULT NULL,
  `education_levels_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`education_levels_supported`)),
  `streams_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`streams_supported`)),
  `pedagogy_model` varchar(255) DEFAULT NULL,
  `focus_areas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`focus_areas`)),
  `centralized_curriculum_framework` tinyint(1) DEFAULT NULL,
  `centralized_teacher_training` tinyint(1) DEFAULT NULL,
  `centralized_assessment_policy` tinyint(1) DEFAULT NULL,
  `centralized_lms_available` tinyint(1) DEFAULT NULL,
  `centralized_parent_communication_system` tinyint(1) DEFAULT NULL,
  `child_safety_policy_available` tinyint(1) DEFAULT NULL,
  `posco_compliance_policy` tinyint(1) DEFAULT NULL,
  `anti_bullying_policy` tinyint(1) DEFAULT NULL,
  `mental_health_policy` tinyint(1) DEFAULT NULL,
  `teacher_background_verification_policy` tinyint(1) DEFAULT NULL,
  `total_schools_count` int(11) DEFAULT NULL,
  `cities_present_in` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cities_present_in`)),
  `states_present_in` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`states_present_in`)),
  `national_presence` tinyint(1) DEFAULT NULL,
  `international_presence` tinyint(1) DEFAULT NULL,
  `flagship_schools` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`flagship_schools`)),
  `official_website` varchar(255) DEFAULT NULL,
  `admission_portal_url` varchar(255) DEFAULT NULL,
  `parent_portal_url` varchar(255) DEFAULT NULL,
  `student_portal_url` varchar(255) DEFAULT NULL,
  `mobile_app_available` tinyint(1) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT NULL,
  `total_reviews` int(11) DEFAULT NULL,
  `awards_and_recognition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`awards_and_recognition`)),
  `schema_type` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `claimed_by_organization` tinyint(1) DEFAULT NULL,
  `verification_status` varchar(255) NOT NULL DEFAULT 'Pending',
  `mandate_description` text DEFAULT NULL,
  `authority_type` varchar(255) DEFAULT NULL,
  `parent_ministry` varchar(255) DEFAULT NULL,
  `parent_ministry_or_department` text DEFAULT NULL,
  `established_by` varchar(255) DEFAULT NULL,
  `legal_act_reference` varchar(255) DEFAULT NULL,
  `headquarters_location` varchar(255) DEFAULT NULL,
  `jurisdiction_scope` varchar(255) DEFAULT NULL,
  `functions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`functions`)),
  `exam_types_conducted` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exam_types_conducted`)),
  `evaluation_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`evaluation_methods`)),
  `exams_conducted_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exams_conducted_ids`)),
  `annual_exam_volume_estimate` varchar(255) DEFAULT NULL,
  `average_candidates_per_year` varchar(255) DEFAULT NULL,
  `exam_modes_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exam_modes_supported`)),
  `question_bank_managed` tinyint(1) NOT NULL DEFAULT 0,
  `normalization_process_available` tinyint(1) NOT NULL DEFAULT 0,
  `multi_language_support` tinyint(1) NOT NULL DEFAULT 0,
  `remote_proctoring_supported` tinyint(1) NOT NULL DEFAULT 0,
  `exam_centres_management_type` varchar(255) DEFAULT NULL,
  `technology_partners` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technology_partners`)),
  `logistics_partners` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`logistics_partners`)),
  `data_security_standards` varchar(255) DEFAULT NULL,
  `result_declaration_policy_summary` text DEFAULT NULL,
  `score_validity_period` varchar(255) DEFAULT NULL,
  `re_evaluation_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `re_evaluation_process_summary` text DEFAULT NULL,
  `data_retention_policy` text DEFAULT NULL,
  `grievance_redressal_mechanism` text DEFAULT NULL,
  `candidate_portal_url` varchar(255) DEFAULT NULL,
  `helpdesk_contact_number` varchar(255) DEFAULT NULL,
  `helpdesk_email` varchar(255) DEFAULT NULL,
  `official_notifications_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`official_notifications_urls`)),
  `faq_url` varchar(255) DEFAULT NULL,
  `rti_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `audit_conducted` tinyint(1) NOT NULL DEFAULT 0,
  `exam_fairness_policy` text DEFAULT NULL,
  `anti_malpractice_measures` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`anti_malpractice_measures`)),
  `whistleblower_policy_available` tinyint(1) NOT NULL DEFAULT 0,
  `awards_or_recognition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`awards_or_recognition`)),
  `media_mentions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`media_mentions`)),
  `public_trust_score` int(11) DEFAULT NULL,
  `focus_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`focus_keywords`)),
  `claimed_by_authority` tinyint(1) NOT NULL DEFAULT 0,
  `data_source` varchar(255) DEFAULT NULL,
  `confidence_score` int(11) DEFAULT NULL,
  `last_updated_on` timestamp NULL DEFAULT NULL,
  `legal_reference_document_url` varchar(255) DEFAULT NULL,
  `jurisdiction_states` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`jurisdiction_states`)),
  `counselling_types_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`counselling_types_supported`)),
  `education_domains_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`education_domains_supported`)),
  `levels_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`levels_supported`)),
  `exams_used_for_counselling_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exams_used_for_counselling_ids`)),
  `allocation_basis` varchar(255) DEFAULT NULL,
  `rank_source_validation_required` tinyint(1) NOT NULL DEFAULT 0,
  `multiple_exam_support` tinyint(1) NOT NULL DEFAULT 0,
  `seat_matrix_management` tinyint(1) NOT NULL DEFAULT 0,
  `seat_matrix_source` varchar(255) DEFAULT NULL,
  `quota_types_managed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`quota_types_managed`)),
  `reservation_policy_reference` text DEFAULT NULL,
  `seat_conversion_rules_supported` tinyint(1) NOT NULL DEFAULT 0,
  `rounds_supported` varchar(255) DEFAULT NULL,
  `round_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`round_types`)),
  `choice_locking_mandatory` tinyint(1) NOT NULL DEFAULT 0,
  `seat_upgradation_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `withdrawal_rules_summary` text DEFAULT NULL,
  `exit_rules_summary` text DEFAULT NULL,
  `counselling_fee_collection_supported` tinyint(1) NOT NULL DEFAULT 0,
  `fee_collection_mode` varchar(255) DEFAULT NULL,
  `refund_processing_responsibility` varchar(255) DEFAULT NULL,
  `security_deposit_handling` tinyint(1) NOT NULL DEFAULT 0,
  `counselling_portal_url` varchar(255) DEFAULT NULL,
  `candidate_login_system_available` tinyint(1) NOT NULL DEFAULT 0,
  `choice_filling_system_available` tinyint(1) NOT NULL DEFAULT 0,
  `auto_seat_allocation_engine` tinyint(1) NOT NULL DEFAULT 0,
  `api_integration_supported` tinyint(1) NOT NULL DEFAULT 0,
  `institution_reporting_interface_available` tinyint(1) NOT NULL DEFAULT 0,
  `document_verification_mode` varchar(255) DEFAULT NULL,
  `institution_confirmation_process_summary` text DEFAULT NULL,
  `mis_reporting_controls` text DEFAULT NULL,
  `appeal_process_summary` text DEFAULT NULL,
  `grievance_contact_details` text DEFAULT NULL,
  `candidate_guidelines_url` varchar(255) DEFAULT NULL,
  `years_of_operation` int(11) DEFAULT NULL,
  `counselling_functions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`counselling_functions`)),
  `counselling_levels_supported` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`counselling_levels_supported`)),
  `annual_candidate_volume` text DEFAULT NULL,
  `institutions_covered_count` int(11) DEFAULT NULL,
  `states_covered_count` int(11) DEFAULT NULL,
  `is_top` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organisations_slug_unique` (`slug`),
  KEY `universities_organisation_type_id_foreign` (`organisation_type_id`),
  CONSTRAINT `universities_organisation_type_id_foreign` FOREIGN KEY (`organisation_type_id`) REFERENCES `organisation_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `otp_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otp_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(15) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `valid_for` varchar(5) DEFAULT NULL,
  `otp_from` varchar(200) DEFAULT NULL,
  `user_request` longtext DEFAULT NULL,
  `token` varchar(200) DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) DEFAULT NULL,
  `booking_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'INR',
  `gateway` varchar(255) NOT NULL DEFAULT 'razorpay',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `response_log` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_booking_id_foreign` (`booking_id`),
  KEY `payments_user_id_foreign` (`user_id`),
  CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expert_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `reference_id` varchar(255) DEFAULT NULL,
  `payout_method` varchar(255) NOT NULL DEFAULT 'bank_transfer',
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payouts_expert_id_foreign` (`expert_id`),
  CONSTRAINT `payouts_expert_id_foreign` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physical_stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `program_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `program_levels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `program_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `program_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `lead_source_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `employee_ids` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `staff_id` varchar(100) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `expert_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `comment` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_booking_id_foreign` (`booking_id`),
  KEY `reviews_expert_id_foreign` (`expert_id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `reviews_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_expert_id_foreign` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `role_for` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_marquees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_marquees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `logo` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `heading` varchar(255) DEFAULT NULL,
  `subheading` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `direction` varchar(255) NOT NULL DEFAULT 'rtl',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seo_defaults`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seo_defaults` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `default_meta_title` varchar(255) DEFAULT NULL,
  `default_meta_description` text DEFAULT NULL,
  `default_og_image` varchar(255) DEFAULT NULL,
  `default_twitter_image` varchar(255) DEFAULT NULL,
  `default_robots` varchar(255) DEFAULT NULL,
  `default_schema_type` varchar(255) DEFAULT NULL,
  `default_author` varchar(255) DEFAULT NULL,
  `default_publisher` varchar(255) DEFAULT NULL,
  `default_language` varchar(255) DEFAULT NULL,
  `default_country` varchar(255) DEFAULT NULL,
  `separator` varchar(255) DEFAULT NULL,
  `title_format` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seo_homepage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seo_homepage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `focus_keyword` varchar(255) DEFAULT NULL,
  `secondary_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`secondary_keywords`)),
  `canonical_url` varchar(255) DEFAULT NULL,
  `robots` varchar(255) DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `twitter_title` varchar(255) DEFAULT NULL,
  `twitter_description` text DEFAULT NULL,
  `twitter_image` varchar(255) DEFAULT NULL,
  `breadcrumb_title` varchar(255) DEFAULT NULL,
  `ai_summary` text DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` varchar(255) DEFAULT NULL,
  `hero_description` text DEFAULT NULL,
  `hero_cta_text` varchar(255) DEFAULT NULL,
  `hero_cta_link` varchar(255) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `schema_type` varchar(255) DEFAULT NULL,
  `custom_schema_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_schema_json`)),
  `allow_index` tinyint(1) NOT NULL DEFAULT 1,
  `allow_snippet` tinyint(1) NOT NULL DEFAULT 1,
  `allow_image_preview` tinyint(1) NOT NULL DEFAULT 1,
  `allow_video_preview` tinyint(1) NOT NULL DEFAULT 1,
  `sitemap_priority` varchar(255) DEFAULT NULL,
  `change_frequency` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seo_homepage_faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seo_homepage_faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seo_homepage_schema_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seo_homepage_schema_blocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `schema_type` varchar(255) DEFAULT NULL,
  `json_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_data`)),
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seo_homepage_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seo_homepage_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `section_name` varchar(255) DEFAULT NULL,
  `section_slug` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seo_organization_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seo_organization_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_name` varchar(255) DEFAULT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `alternate_name` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `long_description` longtext DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `white_logo` varchar(255) DEFAULT NULL,
  `dark_logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `apple_touch_icon` varchar(255) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(255) DEFAULT NULL,
  `support_email` varchar(255) DEFAULT NULL,
  `founding_date` date DEFAULT NULL,
  `founder_name` varchar(255) DEFAULT NULL,
  `organization_type` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `gst_number` varchar(255) DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `opening_hours` text DEFAULT NULL,
  `price_range` varchar(255) DEFAULT NULL,
  `default_currency` varchar(255) DEFAULT NULL,
  `google_map_embed` text DEFAULT NULL,
  `copyright_text` varchar(255) DEFAULT NULL,
  `copyright_year` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `same_as` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`same_as`)),
  `search_url` varchar(255) DEFAULT NULL,
  `default_og_title` varchar(255) DEFAULT NULL,
  `default_og_description` text DEFAULT NULL,
  `default_og_image` varchar(255) DEFAULT NULL,
  `default_twitter_title` varchar(255) DEFAULT NULL,
  `default_twitter_description` text DEFAULT NULL,
  `default_twitter_image` varchar(255) DEFAULT NULL,
  `ga4_id` varchar(255) DEFAULT NULL,
  `gtm_id` varchar(255) DEFAULT NULL,
  `meta_pixel_id` varchar(255) DEFAULT NULL,
  `linkedin_insight_tag` varchar(255) DEFAULT NULL,
  `clarity_id` varchar(255) DEFAULT NULL,
  `schema_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `google_site_verification` varchar(255) DEFAULT NULL,
  `bing_site_verification` varchar(255) DEFAULT NULL,
  `yandex_verification` varchar(255) DEFAULT NULL,
  `pinterest_verification` varchar(255) DEFAULT NULL,
  `facebook_domain_verification` varchar(255) DEFAULT NULL,
  `default_robots` varchar(255) DEFAULT NULL,
  `default_sitemap_priority` varchar(255) DEFAULT NULL,
  `default_change_frequency` varchar(255) DEFAULT NULL,
  `organization_schema` tinyint(1) NOT NULL DEFAULT 1,
  `search_action_schema` tinyint(1) NOT NULL DEFAULT 1,
  `website_schema` tinyint(1) NOT NULL DEFAULT 1,
  `breadcrumb_schema` tinyint(1) NOT NULL DEFAULT 1,
  `logo_schema` tinyint(1) NOT NULL DEFAULT 1,
  `social_profile_schema` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option` varchar(100) DEFAULT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `footer_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_description` text DEFAULT NULL,
  `hero_features` text DEFAULT NULL,
  `hero_cta_1_text` varchar(255) DEFAULT NULL,
  `hero_cta_1_link` varchar(255) DEFAULT NULL,
  `hero_cta_1_new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `hero_cta_2_text` varchar(255) DEFAULT NULL,
  `hero_cta_2_link` varchar(255) DEFAULT NULL,
  `hero_cta_2_new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `is_show_full_banner` tinyint(1) DEFAULT 0,
  `footer_description` text DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `toll_free_number` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(255) DEFAULT NULL,
  `play_store_link` varchar(255) DEFAULT NULL,
  `app_store_link` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `footer_general_title` varchar(255) DEFAULT 'General',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `specializations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `specializations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int(10) unsigned NOT NULL,
  `table` varchar(255) DEFAULT NULL,
  `primary_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('create','read','update','delete','other') NOT NULL,
  `log` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stream_offereds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stream_offereds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` char(36) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `profile_photo_url` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `year_of_birth` year(4) DEFAULT NULL,
  `short_intro` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `current_class` varchar(255) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `board` varchar(255) DEFAULT NULL,
  `previous_year_percentage` decimal(5,2) DEFAULT NULL,
  `stream` varchar(255) DEFAULT NULL,
  `competitive_exam_target` varchar(255) DEFAULT NULL,
  `attempt_type` varchar(255) DEFAULT NULL,
  `year_of_admission` year(4) DEFAULT NULL,
  `organisation_id` bigint(20) unsigned DEFAULT NULL,
  `institute_name` varchar(255) DEFAULT NULL,
  `course_enrolled` varchar(255) DEFAULT NULL,
  `batch_type` varchar(255) DEFAULT NULL,
  `mode_of_study` varchar(255) DEFAULT NULL,
  `admission_through` varchar(255) DEFAULT NULL,
  `test_scores_summary` text DEFAULT NULL,
  `average_test_score` decimal(5,2) DEFAULT NULL,
  `rank_trend` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rank_trend`)),
  `attendance_percentage` decimal(5,2) DEFAULT NULL,
  `academic_improvement_indicator` varchar(255) DEFAULT NULL,
  `strengths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`strengths`)),
  `weak_areas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`weak_areas`)),
  `exam_attempted` varchar(255) DEFAULT NULL,
  `exam_year` year(4) DEFAULT NULL,
  `exam_score` varchar(255) DEFAULT NULL,
  `exam_rank` varchar(255) DEFAULT NULL,
  `selection_status` varchar(255) DEFAULT NULL,
  `college_allotted` varchar(255) DEFAULT NULL,
  `category_rank` varchar(255) DEFAULT NULL,
  `result_verified` tinyint(1) NOT NULL DEFAULT 0,
  `student_testimonial` text DEFAULT NULL,
  `rating_for_institute` decimal(2,1) DEFAULT NULL,
  `rating_for_faculty` decimal(2,1) DEFAULT NULL,
  `would_recommend` tinyint(1) NOT NULL DEFAULT 0,
  `preparation_duration_months` int(11) DEFAULT NULL,
  `study_hours_per_day` decimal(3,1) DEFAULT NULL,
  `study_groups_joined` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`study_groups_joined`)),
  `discussion_forum_participation` tinyint(1) NOT NULL DEFAULT 0,
  `mentor_assigned` varchar(255) DEFAULT NULL,
  `doubt_sessions_attended` int(11) NOT NULL DEFAULT 0,
  `profile_visibility` varchar(255) NOT NULL DEFAULT 'Private',
  `fields_visible_public` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fields_visible_public`)),
  `contact_visible` tinyint(1) NOT NULL DEFAULT 0,
  `testimonial_visible` tinyint(1) NOT NULL DEFAULT 0,
  `consent_for_data_use` tinyint(1) NOT NULL DEFAULT 0,
  `profile_indexing_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `schema_type` varchar(255) NOT NULL DEFAULT 'Person',
  `canonical_url` varchar(255) DEFAULT NULL,
  `profile_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_source` varchar(255) DEFAULT NULL,
  `data_source` varchar(255) NOT NULL DEFAULT 'Manual',
  `confidence_score` decimal(3,1) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_slug_unique` (`slug`),
  UNIQUE KEY `students_student_id_unique` (`student_id`),
  KEY `students_organisation_id_foreign` (`organisation_id`),
  CONSTRAINT `students_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `documents` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `id_recursive_task` varchar(100) DEFAULT NULL,
  `recursive_interval` varchar(255) DEFAULT NULL,
  `recursive_repeat` varchar(255) DEFAULT NULL,
  `recursive_manualy` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `assigned_to` varchar(50) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `milestone` varchar(255) DEFAULT NULL,
  `estimated_hours` varchar(155) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','on_hold','pending') DEFAULT 'not_started',
  `documents` varchar(255) DEFAULT NULL,
  `organization_id` int(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trending_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trending_courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `instructor` varchar(255) DEFAULT NULL,
  `price` varchar(255) DEFAULT NULL,
  `rating` varchar(255) NOT NULL DEFAULT '4.9',
  `image` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trending_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trending_skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_customer_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_customer_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_field_id` int(11) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `category_id` int(11) DEFAULT NULL,
  `institute_id` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `aadhaar_number` varchar(255) DEFAULT NULL,
  `alternate_mobile` varchar(255) DEFAULT NULL,
  `interested_in_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interested_in_ids`)),
  `interested_in_course` varchar(255) DEFAULT NULL,
  `program_level` varchar(255) DEFAULT NULL,
  `mode` varchar(255) DEFAULT NULL,
  `session_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_ids`)),
  `father_name` varchar(255) DEFAULT NULL,
  `father_mobile` varchar(255) DEFAULT NULL,
  `father_email` varchar(255) DEFAULT NULL,
  `father_occupation` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_mobile` varchar(255) DEFAULT NULL,
  `mother_email` varchar(255) DEFAULT NULL,
  `mother_occupation` varchar(255) DEFAULT NULL,
  `sibling_enrolled` tinyint(1) NOT NULL DEFAULT 0,
  `sibling_name` varchar(255) DEFAULT NULL,
  `sibling_age` varchar(255) DEFAULT NULL,
  `referred_by` varchar(255) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `registration_no` varchar(255) DEFAULT NULL,
  `class_batch` varchar(255) DEFAULT NULL,
  `counselor_name` varchar(255) DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `video_testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `video_testimonials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `course` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `autoplay` tinyint(1) NOT NULL DEFAULT 0,
  `muted` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `whatsapp_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) DEFAULT NULL,
  `whatsapp_sender_id` int(11) DEFAULT NULL,
  `time_gap_from_previous_message` int(11) NOT NULL DEFAULT 20,
  `number` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `caption` varchar(1024) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','sent','failed','draft','cancelled') NOT NULL DEFAULT 'pending',
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_pausing_time` timestamp NULL DEFAULT NULL,
  `end_pausing_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `whatsapp_senders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_senders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) DEFAULT NULL,
  `numbers` longtext DEFAULT NULL,
  `min_time_gap` int(11) NOT NULL DEFAULT 40,
  `max_time_gap` int(11) NOT NULL DEFAULT 1,
  `batch_size` int(11) NOT NULL DEFAULT 40,
  `batch_gap` int(11) NOT NULL DEFAULT 40,
  `user_categories` longtext DEFAULT NULL,
  `message` longtext DEFAULT NULL,
  `caption` varchar(1024) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_pausing_time` timestamp NULL DEFAULT NULL,
  `end_pausing_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `whatsapp_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `message` longtext DEFAULT NULL,
  `caption` varchar(1024) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_12_30_141011_add_is_admin_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_12_30_142838_create_experts_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_12_30_143138_create_blogs_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_12_30_143503_create_faqs_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_12_30_144210_create_testimonials_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_12_30_144424_create_leads_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_12_30_145457_create_categories_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_12_30_145604_add_category_and_seo_to_blogs_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_12_30_151325_create_universities_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_12_30_152412_create_settings_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_12_30_163019_create_hero_sliders_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_12_30_163027_add_hero_fields_to_settings_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_12_30_163659_create_video_testimonials_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_01_02_000000_create_noteworthy_tables',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_01_02_054644_create_university_courses_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_01_02_060217_create_courses_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_01_02_060218_add_course_id_to_university_courses_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_01_02_061433_add_status_to_universities_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_01_02_063810_create_homepage_sections_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_01_02_071445_add_mobile_to_users_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_01_02_085453_create_community_categories_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_01_02_085454_create_community_questions_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_01_02_085454_create_community_replies_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_01_02_085455_create_community_likes_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_01_02_151800_create_home_services_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_01_02_153100_create_home_benefits_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_01_02_102954_add_cta_new_tab_to_settings_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_01_03_101000_create_program_levels_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_01_03_101001_create_program_types_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_01_03_101002_create_stream_offereds_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_01_03_101003_create_disciplines_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_01_03_101004_create_specializations_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_01_03_101005_create_organisation_types_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_01_03_101006_create_accreditation_approvals_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_01_03_101007_create_campus_types_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_01_03_101008_create_sports_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_01_03_110000_update_universities_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_01_03_110001_create_university_accreditations_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_01_03_110002_create_university_awards_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_01_03_110003_create_university_sports_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_01_03_113911_update_university_courses_table_add_new_fields',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_01_03_114809_create_alumnis_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_01_05_050922_make_user_fields_nullable_in_users_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_01_05_060406_add_detailed_fields_to_experts_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_01_05_061511_add_detailed_fields_to_alumnis_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_01_05_062840_create_availability_slots_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_01_05_062844_create_appointments_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_01_09_113548_create_organisation_sub_types_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_01_09_114213_rename_university_to_organisation_tables',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_01_09_175000_create_languages_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_01_09_175500_add_language_id_to_organisation_courses',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_01_09_122916_add_type_to_leads_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_01_09_125651_add_institute_fields_to_organisations_table',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_01_09_131902_add_school_fields_to_organisations_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_01_09_131906_create_organisation_academic_results_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_01_09_131910_create_organisation_fees_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_01_10_052825_add_enhanced_fields_to_experts_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_01_10_055814_create_students_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_01_11_100000_make_organisations_fields_nullable',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_01_11_110000_create_exams_table',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_01_11_120000_create_admission_routes_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_01_12_052204_add_meeting_link_to_appointments_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_01_12_164720_add_details_to_exam_and_sessions',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_01_14_100116_add_new_fields_to_organisations_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_01_14_100802_drop_unused_columns_from_organisations_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_01_14_103040_add_university_fields_to_organisations_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_01_14_104255_add_institute_fields_to_organisations_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_01_14_105539_add_school_fields_to_organisations_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_01_14_121153_create_campuses_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_01_14_123049_add_institute_fields_to_campuses_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_01_14_123727_add_school_fields_to_campuses_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_01_14_125650_add_campus_id_to_organisation_courses_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_01_15_011048_add_entrance_exam_id_to_organisation_courses_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_01_15_012507_create_expert_module_tables',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_01_15_080000_create_commission_module_tables',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_01_15_090000_refactor_expert_categories',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_01_23_173236_add_institute_fields_to_organisation_school_courses_table',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_01_23_175027_add_school_institute_fields_to_organisation_courses_table',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_01_23_184546_add_bus_routes_to_campuses_table',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_01_23_185145_add_campus_area_unit_to_campuses_table',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_01_24_101323_modify_organisation_courses_table',61);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_01_24_104133_drop_language_id_from_organisation_courses_table',62);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_01_24_105752_add_exams_and_target_classes_to_campuses_table',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_01_24_113921_create_counsellings_table',64);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_01_24_115210_add_missing_fields_to_counsellings_table',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_01_27_165500_change_counselling_year_to_string_in_counsellings_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_01_27_120010_remove_application_and_fees_from_exams_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_01_27_120631_add_advanced_fee_fields_to_counsellings_table',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_01_27_121552_add_advanced_fee_fields_to_exams_table',69);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_01_27_122013_add_partial_refund_allowed_to_counsellings_and_exams',70);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2026_01_30_064500_increase_exam_fields_length',71);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2026_01_30_013944_add_entrance_exam_ids_to_organisation_courses_table',72);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2026_02_11_160657_add_exam_conducting_body_fields_to_organisations_table',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2026_02_12_043046_add_counselling_body_fields_to_organisations_table',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2026_02_12_054501_create_departments_table',75);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2026_02_12_054522_add_department_id_to_organisation_courses_table',75);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2026_02_12_054524_add_department_id_to_organisation_courses_table',75);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2026_02_12_120000_create_exam_stages_table',76);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_02_12_114028_add_has_stages_to_exams_table',77);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_02_13_041402_create_exam_stage_interviews_table',78);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_02_13_041403_create_exam_selected_stages_table',78);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_02_13_044453_create_exam_stage_skills_table',79);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_02_13_045035_create_exam_stage_medicals_table',80);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_02_13_050704_create_exam_stage_preliminaries_table',81);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_02_13_050705_create_exam_stage_mains_table',81);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2026_02_13_053142_create_exam_subjects_table',82);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2026_03_17_090922_change_exam_category_to_json_in_exams_table',83);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2026_07_31_103214_create_homepage_stream_tabs_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2026_07_31_105059_create_trending_courses_table',85);
