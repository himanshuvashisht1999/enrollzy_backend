-- ==============================================================================
-- ENROLLZY BACKEND - ENTERPRISE WORK MANAGEMENT MODULE DATABASE SCHEMA
-- Version: 1.0.0
-- Generated: 2026-09-08
-- Description: Complete SQL migration script for Teams Hierarchy, Decoupled Work
--              Hierarchy (Projects, Milestones, Tasks, Subtasks), Universal Assignment,
--              Strict Single-Assignee Custody, External Partners, Meetings, and Auditing.
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ==============================================================================
-- 1. ENHANCEMENTS TO PRE-EXISTING TABLES
-- ==============================================================================

-- 1.1 Modify projects table
ALTER TABLE `projects` 
    MODIFY `category_id` INT(11) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `code` VARCHAR(50) NULL AFTER `title`,
    ADD COLUMN IF NOT EXISTS `department_id` BIGINT(20) UNSIGNED NULL AFTER `code`,
    ADD COLUMN IF NOT EXISTS `team_id` BIGINT(20) UNSIGNED NULL AFTER `department_id`,
    ADD COLUMN IF NOT EXISTS `owner_id` INT(10) UNSIGNED NULL AFTER `team_id`,
    ADD COLUMN IF NOT EXISTS `created_by` INT(10) UNSIGNED NULL AFTER `owner_id`,
    ADD COLUMN IF NOT EXISTS `lead_user_id` INT(10) UNSIGNED NULL AFTER `created_by`,
    ADD COLUMN IF NOT EXISTS `target_end_date` DATE NULL AFTER `due_date`,
    ADD COLUMN IF NOT EXISTS `actual_end_date` DATE NULL AFTER `target_end_date`,
    ADD COLUMN IF NOT EXISTS `progress_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `status`,
    ADD COLUMN IF NOT EXISTS `health_status` VARCHAR(50) NOT NULL DEFAULT 'on_track' AFTER `progress_percentage`,
    ADD COLUMN IF NOT EXISTS `risk_level` VARCHAR(50) NOT NULL DEFAULT 'low' AFTER `health_status`,
    ADD COLUMN IF NOT EXISTS `budget` DECIMAL(15,2) NULL AFTER `price`,
    ADD COLUMN IF NOT EXISTS `actual_cost` DECIMAL(15,2) NULL AFTER `budget`;

-- 1.2 Modify milestones table
ALTER TABLE `milestones`
    MODIFY `status` VARCHAR(100) NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS `team_id` BIGINT(20) UNSIGNED NULL AFTER `project_id`,
    ADD COLUMN IF NOT EXISTS `owner_id` INT(10) UNSIGNED NULL AFTER `team_id`,
    ADD COLUMN IF NOT EXISTS `lead_user_id` INT(10) UNSIGNED NULL AFTER `owner_id`,
    ADD COLUMN IF NOT EXISTS `sequence` INT(11) NOT NULL DEFAULT 1 AFTER `lead_user_id`,
    ADD COLUMN IF NOT EXISTS `weight_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `sequence`,
    ADD COLUMN IF NOT EXISTS `progress_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `weight_percentage`,
    ADD COLUMN IF NOT EXISTS `target_end_date` DATE NULL AFTER `due_date`,
    ADD COLUMN IF NOT EXISTS `actual_end_date` DATE NULL AFTER `target_end_date`,
    ADD COLUMN IF NOT EXISTS `is_billable` TINYINT(1) NOT NULL DEFAULT 0 AFTER `price`;

-- 1.3 Modify tasks table
ALTER TABLE `tasks`
    MODIFY `priority` VARCHAR(50) NOT NULL DEFAULT 'medium',
    MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'not_started',
    ADD COLUMN IF NOT EXISTS `parent_task_id` BIGINT(20) UNSIGNED NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `task_code` VARCHAR(50) NULL AFTER `parent_task_id`,
    ADD COLUMN IF NOT EXISTS `team_id` BIGINT(20) UNSIGNED NULL AFTER `project_id`,
    ADD COLUMN IF NOT EXISTS `created_by` INT(10) UNSIGNED NULL AFTER `team_id`,
    ADD COLUMN IF NOT EXISTS `actual_hours` DECIMAL(8,2) NOT NULL DEFAULT 0.00 AFTER `estimated_hours`,
    ADD COLUMN IF NOT EXISTS `completed_at` TIMESTAMP NULL DEFAULT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `verified_at` TIMESTAMP NULL DEFAULT NULL AFTER `completed_at`,
    ADD COLUMN IF NOT EXISTS `verified_by` INT(10) UNSIGNED NULL AFTER `verified_at`,
    ADD COLUMN IF NOT EXISTS `closed_at` TIMESTAMP NULL DEFAULT NULL AFTER `verified_by`,
    ADD COLUMN IF NOT EXISTS `closed_by` INT(10) UNSIGNED NULL AFTER `closed_at`,
    ADD COLUMN IF NOT EXISTS `is_milestone_deliverable` TINYINT(1) NOT NULL DEFAULT 0 AFTER `closed_by`;

-- ==============================================================================
-- 2. ORGANIZATIONAL TEAMS & HIERARCHY TABLES
-- ==============================================================================

-- 2.1 Internal Teams table (Supports multi-level parent-child team tree)
CREATE TABLE IF NOT EXISTS `teams` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `department_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `parent_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(50) DEFAULT NULL,
    `team_leader_id` INT(10) UNSIGNED DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `teams_organization_id_index` (`organization_id`),
    KEY `teams_department_id_index` (`department_id`),
    KEY `teams_parent_id_index` (`parent_id`),
    KEY `teams_team_leader_id_index` (`team_leader_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.2 Team Members table (Staff can belong to multiple teams with distinct roles)
CREATE TABLE IF NOT EXISTS `team_members` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `team_id` BIGINT(20) UNSIGNED NOT NULL,
    `user_id` INT(10) UNSIGNED NOT NULL,
    `role` VARCHAR(100) NOT NULL DEFAULT 'member',
    `is_team_leader` TINYINT(1) NOT NULL DEFAULT 0,
    `joined_at` DATE DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `team_members_team_id_user_id_unique` (`team_id`, `user_id`),
    KEY `team_members_team_id_index` (`team_id`),
    KEY `team_members_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 3. EXTERNAL PARTNERS, AGENCIES & CONTRACTORS TABLES
-- ==============================================================================

-- 3.1 External Partner Organizations table
CREATE TABLE IF NOT EXISTS `external_organizations` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `organization_type` VARCHAR(100) NOT NULL DEFAULT 'agency',
    `contact_person` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `external_organizations_organization_id_index` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.2 External Partner Contacts table
CREATE TABLE IF NOT EXISTS `external_contacts` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `external_organization_id` BIGINT(20) UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `designation` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `password` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `external_contacts_organization_id_index` (`organization_id`),
    KEY `external_contacts_external_organization_id_index` (`external_organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.3 External Teams table (3rd-party dedicated squads)
CREATE TABLE IF NOT EXISTS `external_teams` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `external_organization_id` BIGINT(20) UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `team_leader_contact_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `external_teams_organization_id_index` (`organization_id`),
    KEY `external_teams_external_organization_id_index` (`external_organization_id`),
    KEY `external_teams_team_leader_contact_id_index` (`team_leader_contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.4 External Team Members table
CREATE TABLE IF NOT EXISTS `external_team_members` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `external_team_id` BIGINT(20) UNSIGNED NOT NULL,
    `external_contact_id` BIGINT(20) UNSIGNED NOT NULL,
    `role` VARCHAR(100) NOT NULL DEFAULT 'member',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ext_team_member_unique` (`external_team_id`, `external_contact_id`),
    KEY `external_team_members_external_team_id_index` (`external_team_id`),
    KEY `external_team_members_external_contact_id_index` (`external_contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 4. PROJECT MEMBERS & REPOSITORIES TABLES
-- ==============================================================================

-- 4.1 Internal Project Members table
CREATE TABLE IF NOT EXISTS `project_members` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT(20) UNSIGNED NOT NULL,
    `user_id` INT(10) UNSIGNED NOT NULL,
    `role` VARCHAR(100) NOT NULL DEFAULT 'Contributor',
    `access_level` ENUM('full', 'edit', 'view') NOT NULL DEFAULT 'edit',
    `joined_at` DATE DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `project_members_project_id_user_id_unique` (`project_id`, `user_id`),
    KEY `project_members_project_id_index` (`project_id`),
    KEY `project_members_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.2 External Project Members table
CREATE TABLE IF NOT EXISTS `project_external_members` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT(20) UNSIGNED NOT NULL,
    `external_organization_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `external_contact_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `external_team_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `role` VARCHAR(100) NOT NULL DEFAULT 'External Contributor',
    `access_level` ENUM('edit', 'view') NOT NULL DEFAULT 'edit',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `project_external_members_project_id_index` (`project_id`),
    KEY `project_external_members_external_organization_id_index` (`external_organization_id`),
    KEY `project_external_members_external_contact_id_index` (`external_contact_id`),
    KEY `project_external_members_external_team_id_index` (`external_team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.3 Project Documents table
CREATE TABLE IF NOT EXISTS `project_documents` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `project_id` BIGINT(20) UNSIGNED NOT NULL,
    `uploaded_by` INT(10) UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(100) DEFAULT NULL,
    `file_size` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    `document_type` VARCHAR(100) NOT NULL DEFAULT 'General',
    `version` VARCHAR(20) NOT NULL DEFAULT '1.0',
    `visibility` ENUM('internal_only', 'all_members') NOT NULL DEFAULT 'all_members',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `project_documents_organization_id_index` (`organization_id`),
    KEY `project_documents_project_id_index` (`project_id`),
    KEY `project_documents_uploaded_by_index` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 5. WORK EXECUTION, CUSTODY, DELEGATION & TASK SUPPORT TABLES
-- ==============================================================================

-- 5.1 Universal Task Assignees table (Enforces Strict Single-Assignee Custody on Subtasks)
CREATE TABLE IF NOT EXISTS `task_assignees` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` BIGINT(20) UNSIGNED NOT NULL,
    `assignee_type` ENUM('internal_user', 'internal_team', 'external_contact', 'external_team') NOT NULL DEFAULT 'internal_user',
    `user_id` INT(10) UNSIGNED DEFAULT NULL,
    `team_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `external_contact_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `external_team_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `role` VARCHAR(100) NOT NULL DEFAULT 'Assignee',
    `assigned_by` INT(10) UNSIGNED DEFAULT NULL,
    `assigned_at` TIMESTAMP NULL DEFAULT NULL,
    `due_date` DATE DEFAULT NULL,
    `status` ENUM('active', 'transferred', 'completed', 'removed') NOT NULL DEFAULT 'active',
    `is_primary` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `task_assignees_task_id_index` (`task_id`),
    KEY `task_assignees_user_id_index` (`user_id`),
    KEY `task_assignees_team_id_index` (`team_id`),
    KEY `task_assignees_external_contact_id_index` (`external_contact_id`),
    KEY `task_assignees_external_team_id_index` (`external_team_id`),
    KEY `task_assignees_assigned_by_index` (`assigned_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.2 Task Delegations table (Tracks cross-team delegation lineage)
CREATE TABLE IF NOT EXISTS `task_delegations` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` BIGINT(20) UNSIGNED NOT NULL,
    `from_user_id` INT(10) UNSIGNED DEFAULT NULL,
    `to_type` ENUM('internal_user', 'internal_team', 'external_contact', 'external_team') NOT NULL DEFAULT 'internal_user',
    `to_user_id` INT(10) UNSIGNED DEFAULT NULL,
    `to_team_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `external_contact_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `external_team_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `delegated_at` TIMESTAMP NULL DEFAULT NULL,
    `remarks` TEXT DEFAULT NULL,
    `status` ENUM('active', 'accepted', 'recalled', 'completed') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `task_delegations_task_id_index` (`task_id`),
    KEY `task_delegations_from_user_id_index` (`from_user_id`),
    KEY `task_delegations_to_user_id_index` (`to_user_id`),
    KEY `task_delegations_to_team_id_index` (`to_team_id`),
    KEY `task_delegations_external_contact_id_index` (`external_contact_id`),
    KEY `task_delegations_external_team_id_index` (`external_team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.3 Task Dependencies table (Finish-to-Start blocking relationships)
CREATE TABLE IF NOT EXISTS `task_dependencies` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` BIGINT(20) UNSIGNED NOT NULL,
    `depends_on_task_id` BIGINT(20) UNSIGNED NOT NULL,
    `dependency_type` ENUM('finish_to_start', 'start_to_start', 'finish_to_finish') NOT NULL DEFAULT 'finish_to_start',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `task_dependencies_task_id_depends_on_task_id_unique` (`task_id`, `depends_on_task_id`),
    KEY `task_dependencies_task_id_index` (`task_id`),
    KEY `task_dependencies_depends_on_task_id_index` (`depends_on_task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.4 Task Checklists table
CREATE TABLE IF NOT EXISTS `task_checklists` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` BIGINT(20) UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
    `completed_by` INT(10) UNSIGNED DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `task_checklists_task_id_index` (`task_id`),
    KEY `task_checklists_completed_by_index` (`completed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.5 Task Time Tracking Entries table
CREATE TABLE IF NOT EXISTS `task_time_entries` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` BIGINT(20) UNSIGNED NOT NULL,
    `user_id` INT(10) UNSIGNED DEFAULT NULL,
    `external_contact_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `started_at` TIMESTAMP NULL DEFAULT NULL,
    `ended_at` TIMESTAMP NULL DEFAULT NULL,
    `duration_minutes` INT(11) NOT NULL DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `task_time_entries_task_id_index` (`task_id`),
    KEY `task_time_entries_user_id_index` (`user_id`),
    KEY `task_time_entries_external_contact_id_index` (`external_contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.6 Task Recurrence Configurations table
CREATE TABLE IF NOT EXISTS `task_recurrences` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` BIGINT(20) UNSIGNED NOT NULL,
    `frequency` ENUM('daily', 'weekly', 'monthly', 'custom') NOT NULL DEFAULT 'daily',
    `interval` INT(11) NOT NULL DEFAULT 1,
    `days_of_week` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`days_of_week`)),
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `next_run_at` TIMESTAMP NULL DEFAULT NULL,
    `status` ENUM('active', 'paused', 'completed') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `task_recurrences_task_id_index` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.7 Task Activity Logs / Custody Audit Trail table
CREATE TABLE IF NOT EXISTS `task_activity_logs` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `project_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `milestone_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `task_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `action_type` VARCHAR(100) NOT NULL,
    `performed_by` INT(10) UNSIGNED DEFAULT NULL,
    `performed_by_name` VARCHAR(255) DEFAULT NULL,
    `from_state` VARCHAR(255) DEFAULT NULL,
    `to_state` VARCHAR(255) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `extra_data` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra_data`)),
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `task_activity_logs_organization_id_index` (`organization_id`),
    KEY `task_activity_logs_project_id_index` (`project_id`),
    KEY `task_activity_logs_milestone_id_index` (`milestone_id`),
    KEY `task_activity_logs_task_id_index` (`task_id`),
    KEY `task_activity_logs_performed_by_index` (`performed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.8 Task File Attachments table
CREATE TABLE IF NOT EXISTS `task_attachments` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` BIGINT(20) UNSIGNED NOT NULL,
    `uploaded_by` INT(10) UNSIGNED DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(100) DEFAULT NULL,
    `file_size` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    `version` VARCHAR(20) NOT NULL DEFAULT '1.0',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `task_attachments_task_id_index` (`task_id`),
    KEY `task_attachments_uploaded_by_index` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.9 Task Threaded Comments table
CREATE TABLE IF NOT EXISTS `task_comments` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` VARCHAR(255) NOT NULL,
    `user_id` VARCHAR(255) NOT NULL,
    `comment` TEXT NOT NULL,
    `documents` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 6. MEETINGS & DECISION-TO-TASK TABLES
-- ==============================================================================

-- 6.1 Project Meetings table
CREATE TABLE IF NOT EXISTS `meetings` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `project_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `meeting_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME DEFAULT NULL,
    `created_by` INT(10) UNSIGNED DEFAULT NULL,
    `meeting_type` VARCHAR(50) NOT NULL DEFAULT 'online',
    `meeting_link` VARCHAR(255) DEFAULT NULL,
    `location` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    `minutes_of_meeting` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `meetings_organization_id_index` (`organization_id`),
    KEY `meetings_project_id_index` (`project_id`),
    KEY `meetings_created_by_index` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6.2 Meeting Participants table
CREATE TABLE IF NOT EXISTS `meeting_participants` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `meeting_id` BIGINT(20) UNSIGNED NOT NULL,
    `user_id` INT(10) UNSIGNED DEFAULT NULL,
    `external_contact_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `response` ENUM('pending', 'accepted', 'declined', 'tentative') NOT NULL DEFAULT 'pending',
    `attended` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `meeting_participants_meeting_id_index` (`meeting_id`),
    KEY `meeting_participants_user_id_index` (`user_id`),
    KEY `meeting_participants_external_contact_id_index` (`external_contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 7. SPATIE PERMISSION SEED DATA
-- ==============================================================================

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('work-management-browse', 'admin', NOW(), NOW()),
('work-management-projects-browse', 'admin', NOW(), NOW()),
('work-management-projects-create', 'admin', NOW(), NOW()),
('work-management-projects-edit', 'admin', NOW(), NOW()),
('work-management-projects-delete', 'admin', NOW(), NOW()),
('work-management-milestones-browse', 'admin', NOW(), NOW()),
('work-management-milestones-create', 'admin', NOW(), NOW()),
('work-management-milestones-edit', 'admin', NOW(), NOW()),
('work-management-milestones-delete', 'admin', NOW(), NOW()),
('work-management-tasks-browse', 'admin', NOW(), NOW()),
('work-management-tasks-create', 'admin', NOW(), NOW()),
('work-management-tasks-edit', 'admin', NOW(), NOW()),
('work-management-tasks-delete', 'admin', NOW(), NOW()),
('work-management-tasks-reassign', 'admin', NOW(), NOW()),
('work-management-tasks-delegate', 'admin', NOW(), NOW()),
('work-management-teams-browse', 'admin', NOW(), NOW()),
('work-management-teams-create', 'admin', NOW(), NOW()),
('work-management-teams-edit', 'admin', NOW(), NOW()),
('work-management-teams-delete', 'admin', NOW(), NOW()),
('work-management-partners-browse', 'admin', NOW(), NOW()),
('work-management-partners-create', 'admin', NOW(), NOW()),
('work-management-partners-edit', 'admin', NOW(), NOW()),
('work-management-partners-delete', 'admin', NOW(), NOW()),
('work-management-meetings-browse', 'admin', NOW(), NOW()),
('work-management-meetings-create', 'admin', NOW(), NOW()),
('work-management-meetings-delete', 'admin', NOW(), NOW()),
('work-management-reports-browse', 'admin', NOW(), NOW());

-- Assign all new permissions to superadmin and admin roles (if exists)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.`id`, r.`id`
FROM `permissions` p
CROSS JOIN `roles` r
WHERE p.`name` LIKE 'work-management-%'
  AND r.`name` IN ('superadmin', 'admin', 'Super Admin', 'Admin')
  AND r.`guard_name` = 'admin';

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- END OF WORK MANAGEMENT SCHEMA SCRIPT
-- ==============================================================================
