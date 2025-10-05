-- Simplified Enhanced Role Management SQL Script
-- Compatible with existing pbradatabases structure
-- This version removes complex conditional statements for better compatibility

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
  KEY `reviewed_by` (`reviewed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  KEY `given_by` (`given_by`)
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
  KEY `reviewed_by` (`reviewed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table for department requirements
CREATE TABLE IF NOT EXISTS `department_requirements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `requirement_type` enum('education','experience','certification','other') NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add sample department requirements
INSERT IGNORE INTO `department_requirements` (`department_id`, `requirement_type`, `keyword`, `description`) VALUES
(14, 'education', 'bachelor', 'Bachelor degree in relevant field'),
(14, 'experience', 'teaching', 'At least 2 years of teaching experience'),
(7, 'education', 'bachelor', 'Bachelor degree in counseling, psychology, or related field'),
(7, 'experience', 'counseling', 'Experience in student counseling or support services'),
(11, 'education', 'bachelor', 'Bachelor degree in communications, public relations, or related field'),
(11, 'experience', 'communications', 'Experience in corporate communications or public relations'),
(13, 'education', 'bachelor', 'Bachelor degree in estate management, facilities management, or related field'),
(13, 'experience', 'facilities', 'Experience in facilities or estate management'),
(9, 'education', 'bachelor', 'Bachelor degree in accounting, finance, or related field'),
(9, 'experience', 'finance', 'Experience in financial management or accounting');

SELECT 'Enhanced Role Management tables created successfully!' as message;