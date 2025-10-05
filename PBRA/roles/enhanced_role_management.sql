-- Enhanced Role Management SQL Script
-- Compatible with existing pbradatabases structure
-- Run this script to add new tables and columns for enhanced role management

USE `pbradatabases`;

-- Table for role appeals
CREATE TABLE IF NOT EXISTS `role_appeals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `appeal_type` enum('removal','change','objection') NOT NULL,
  `reason` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `role_appeals_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_appeals_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_appeals_reviewer_fk` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add appointment feedback columns to userroles table if they don't exist
-- Check if column exists before adding
SET @exist_appointment_status = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'pbradatabases' AND TABLE_NAME = 'userroles' AND COLUMN_NAME = 'appointment_status');

SET @sql_add_appointment_status = IF(@exist_appointment_status = 0,
    'ALTER TABLE `userroles` ADD COLUMN `appointment_status` enum(''pending'',''accepted'',''rejected'') DEFAULT ''pending''',
    'SELECT "appointment_status column already exists" as message'
);

PREPARE stmt FROM @sql_add_appointment_status;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist_rejection_reason = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'pbradatabases' AND TABLE_NAME = 'userroles' AND COLUMN_NAME = 'rejection_reason');

SET @sql_add_rejection_reason = IF(@exist_rejection_reason = 0,
    'ALTER TABLE `userroles` ADD COLUMN `rejection_reason` text DEFAULT NULL',
    'SELECT "rejection_reason column already exists" as message'
);

PREPARE stmt FROM @sql_add_rejection_reason;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist_response_date = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'pbradatabases' AND TABLE_NAME = 'userroles' AND COLUMN_NAME = 'response_date');

SET @sql_add_response_date = IF(@exist_response_date = 0,
    'ALTER TABLE `userroles` ADD COLUMN `response_date` timestamp NULL DEFAULT NULL',
    'SELECT "response_date column already exists" as message'
);

PREPARE stmt FROM @sql_add_response_date;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create role_feedback table for tracking feedback on role appointments
CREATE TABLE IF NOT EXISTS `role_feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userrole_user_id` int NOT NULL,
  `userrole_role_id` int NOT NULL,
  `feedback_type` enum('approval','denial','general') NOT NULL,
  `feedback_message` text NOT NULL,
  `given_by` int NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userrole_user_id` (`userrole_user_id`),
  KEY `userrole_role_id` (`userrole_role_id`),
  KEY `given_by` (`given_by`),
  CONSTRAINT `role_feedback_user_fk` FOREIGN KEY (`userrole_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_feedback_role_fk` FOREIGN KEY (`userrole_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_feedback_giver_fk` FOREIGN KEY (`given_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table for role assignment requests with start/end dates and times
CREATE TABLE IF NOT EXISTS `role_assignment_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `requested_by` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  KEY `requested_by` (`requested_by`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `role_requests_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_requests_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_requests_requester_fk` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_requests_reviewer_fk` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table for department requirements (if it doesn't exist)
CREATE TABLE IF NOT EXISTS `department_requirements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `requirement_type` enum('education','experience','certification','other') NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `dept_requirements_dept_fk` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_role_appeals_status ON role_appeals(status);
CREATE INDEX IF NOT EXISTS idx_role_appeals_created ON role_appeals(created_at);
CREATE INDEX IF NOT EXISTS idx_role_history_dates ON role_history(assigned_at, removed_at);
CREATE INDEX IF NOT EXISTS idx_userroles_appointment ON userroles(appointment_status);
CREATE INDEX IF NOT EXISTS idx_role_requests_status ON role_assignment_requests(status);

-- Insert some sample department requirements for testing
INSERT IGNORE INTO `department_requirements` (`department_id`, `requirement_type`, `keyword`, `description`) VALUES
(14, 'education', 'bachelor', 'Bachelor''s degree in relevant field'),
(14, 'experience', 'teaching', 'At least 2 years of teaching experience'),
(7, 'education', 'bachelor', 'Bachelor''s degree in counseling, psychology, or related field'),
(7, 'experience', 'counseling', 'Experience in student counseling or support services'),
(11, 'education', 'bachelor', 'Bachelor''s degree in communications, public relations, or related field'),
(11, 'experience', 'communications', 'Experience in corporate communications or public relations'),
(13, 'education', 'bachelor', 'Bachelor''s degree in estate management, facilities management, or related field'),
(13, 'experience', 'facilities', 'Experience in facilities or estate management'),
(9, 'education', 'bachelor', 'Bachelor''s degree in accounting, finance, or related field'),
(9, 'experience', 'finance', 'Experience in financial management or accounting');

-- Create some sample role appeals for testing (optional)
-- Note: Update user IDs and role IDs based on your actual data

SELECT 'Enhanced Role Management tables created successfully!' as message;