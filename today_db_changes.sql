-- Today's Database Changes (SQL Queries)
-- Generated on: 2026-04-11

-- 1. Create dynamic_exams table
CREATE TABLE IF NOT EXISTS dynamic_exams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    status VARCHAR(255) DEFAULT 'Active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- 2. Create dynamic_exam_sections table
CREATE TABLE IF NOT EXISTS dynamic_exam_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dynamic_exam_id BIGINT UNSIGNED NOT NULL,
    heading VARCHAR(255) NOT NULL,
    content JSON NULL,
    `order` INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_sections_exam_id FOREIGN KEY (dynamic_exam_id) REFERENCES dynamic_exams(id) ON DELETE CASCADE
);

-- 3. Add Core fields to dynamic_exams table
ALTER TABLE dynamic_exams
ADD COLUMN short_name VARCHAR(255) NULL AFTER name,
ADD COLUMN exam_type VARCHAR(255) NULL AFTER short_name,
ADD COLUMN exam_category JSON NULL AFTER exam_type,
ADD COLUMN conducting_body_type VARCHAR(255) NULL AFTER exam_category,
ADD COLUMN exam_frequency VARCHAR(255) NULL AFTER conducting_body_type,
ADD COLUMN conducting_authority_name VARCHAR(255) NULL AFTER exam_frequency,
ADD COLUMN logo VARCHAR(255) NULL AFTER conducting_authority_name,
ADD COLUMN cover_image VARCHAR(255) NULL AFTER logo,
ADD COLUMN exam_source_type VARCHAR(255) DEFAULT 'External' AFTER cover_image,
ADD COLUMN owning_organisation_id BIGINT UNSIGNED NULL AFTER exam_source_type,
ADD COLUMN about_exam LONGTEXT NULL AFTER owning_organisation_id,
ADD CONSTRAINT fk_dynamic_exams_org FOREIGN KEY (owning_organisation_id) REFERENCES organisations(id) ON DELETE SET NULL;

-- 4. Add dynamic_exam_id to counsellings table
ALTER TABLE counsellings
ADD COLUMN dynamic_exam_id BIGINT UNSIGNED NULL AFTER exam_id,
ADD CONSTRAINT fk_counsellings_dynamic_exam FOREIGN KEY (dynamic_exam_id) REFERENCES dynamic_exams(id) ON DELETE CASCADE;

-- 5. Make exam_id nullable in counsellings table (for dynamic exam support)
ALTER TABLE counsellings MODIFY exam_id BIGINT UNSIGNED NULL;

-- 6. Add remaining core fields and align visibility settings
ALTER TABLE dynamic_exams
ADD COLUMN official_website VARCHAR(255) NULL AFTER about_exam,
ADD COLUMN visibility VARCHAR(255) DEFAULT 'Public' AFTER official_website,
ADD COLUMN featured_exam TINYINT(1) DEFAULT 0 AFTER visibility,
ADD COLUMN has_stages TINYINT(1) DEFAULT 0 AFTER featured_exam,
ADD COLUMN selected_stages JSON NULL AFTER has_stages;
