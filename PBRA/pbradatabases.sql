-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Oct 04, 2025 at 11:49 AM
-- Server version: 8.0.43
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pbradatabases`
--
CREATE DATABASE IF NOT EXISTS `pbradatabases` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `pbradatabases`;

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement`
--

INSERT INTO `announcement` (`id`, `title`, `content`, `image_path`, `created_at`, `updated_at`) VALUES
(9, 'Welcome to PBRA!', 'This is where it <b>starts </b>and where it <b>ends</b>.', 'uploads/announcements/68064cf17efe5_PB_Logo_Updated.png', '2025-04-21 13:49:37', NULL),
(14, 'PB Students Win Runner-Up at IPTC 2025', '<span style=\"color: rgb(119, 119, 119); font-family: Lato, sans-serif; font-size: medium;\">Md Danish Syahmi Abdullah Md Nasrun and Nur’ain Nikmatol Qistina Wedy Hasfian’s group won the Runner-Up Prize at the IPTC 2025 Energy Education University Student Programme in Kuala Lumpur, Malaysia, for their presentation on Carbon Capture, Utilization, and Storage (CCUS). Their well-received presentation showcased their ability to analyze and propose innovative solutions to a key global energy challenge.</span>\r\n      ', 'uploads/announcements/68113f1a48111_3048b191-cc67-41dd-af0d-8f1df96286bc.jpeg', '2025-04-29 21:05:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text NOT NULL,
  `attachment` text,
  `status` enum('sent','delivered','read') DEFAULT 'sent',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_user_contacts`
--

CREATE TABLE `chat_user_contacts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `contact_id` int NOT NULL,
  `is_favorite` tinyint(1) DEFAULT '0',
  `is_archived` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(14, 'Centre for Innovative Teaching and Learning'),
(7, 'Centre for Student Development and Innovation'),
(11, 'Corporate Affairs Division'),
(13, 'Estate Management Division'),
(9, 'Finance Division'),
(20, 'General Studies Division'),
(8, 'Human Resource Division'),
(5, 'Industry Linkages Division'),
(12, 'Information Technology Division'),
(10, 'Legal and Procurement Division'),
(21, 'Library and Learning Centre'),
(2, 'Programme Development Division'),
(4, 'Quality Management Division'),
(1, 'Registrar and Secretary Office'),
(6, 'Research and Statistics Division'),
(16, 'School of Business'),
(18, 'School of Health Science'),
(15, 'School of ICT'),
(19, 'School of Petrochemical'),
(17, 'School of Science and Engineering'),
(3, 'Student Affairs Division');

-- --------------------------------------------------------

--
-- Table structure for table `department_requirements`
--

CREATE TABLE `department_requirements` (
  `id` int NOT NULL,
  `department_id` int DEFAULT NULL,
  `requirement_type` varchar(50) DEFAULT NULL,
  `keyword` varchar(255) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `department_requirements`
--

INSERT INTO `department_requirements` (`id`, `department_id`, `requirement_type`, `keyword`, `description`) VALUES
(1, 1, 'education', 'Bachelor', 'Bachelor\'s degree in Administration, Management, or related fields.'),
(2, 1, 'experience', 'organizational', 'Strong organizational and communication skills.'),
(3, 1, 'experience', 'regulations', 'Familiarity with academic regulations and institutional policies.'),
(4, 1, 'experience', 'administrative', 'Prior experience in administrative roles preferred.'),
(5, 2, 'education', 'Bachelor', 'Bachelor\'s degree in Education, Curriculum Development, or related .'),
(6, 2, 'experience', 'curriculum', 'Experience in curriculum design and educational planning.'),
(7, 2, 'experience', 'analytical', 'Strong analytical and project management skills.'),
(8, 3, 'education', 'Education', 'Degree in Education, Psychology, or related .'),
(9, 3, 'experience', 'interpersonal', 'Strong interpersonal and counseling skills.'),
(10, 3, 'experience', 'counseling', 'Experience in student support services.'),
(11, 4, 'education', 'Quality', 'Degree in Quality Management, Education, or related .'),
(12, 4, 'experience', 'assurance', 'Knowledge of quality assurance standards and accreditation processes.'),
(13, 4, 'experience', 'accreditation', 'Analytical skills and attention to detail.'),
(14, 5, 'education', 'Business', 'Degree in Business, Education, or related .'),
(15, 5, 'experience', 'networking', 'Strong networking and relationship-building skills.'),
(16, 5, 'experience', 'relationship', 'Experience in coordinating industry partnerships.'),
(17, 6, 'education', 'Statistics', 'Degree in Statistics, Research Methods, or related .'),
(18, 6, 'experience', 'analysis', 'Proficiency in data analysis software.'),
(19, 6, 'experience', 'software', 'Strong research and analytical skills.'),
(20, 7, 'education', 'Innovation', 'Diploma or Degree in Education, Innovation, or related .'),
(21, 7, 'experience', 'development', 'Experience in student development programs.'),
(22, 7, 'experience', 'problem-solving', 'Strong problem-solving and creative thinking skills.'),
(23, 8, 'education', 'HR', 'Degree in Human Resource Management or related .'),
(24, 8, 'experience', 'labor', 'Knowledge of HR policies and labor laws in Brunei.'),
(25, 8, 'experience', 'organizational', 'Strong interpersonal and organizational skills.'),
(26, 9, 'education', 'Accounting', 'Degree in Accounting, Finance, or related .'),
(27, 9, 'experience', 'budgeting', 'Proficiency in financial management and budgeting.'),
(28, 9, 'experience', 'financial', 'Knowledge of Brunei government financial regulations.'),
(29, 10, 'education', 'Law', 'Degree in Law, Business Administration, or related .'),
(30, 10, 'experience', 'legal', 'Knowledge of legal practices and procurement policies.'),
(31, 10, 'experience', 'procurement', 'Strong negotiation and contract management skills.'),
(32, 11, 'education', 'PR', 'Degree in Public Relations, Communications, or related fields.'),
(33, 11, 'experience', 'media', 'Strong communication and media relations skills.'),
(34, 11, 'experience', 'communication', 'Experience in corporate communications.'),
(35, 12, 'education', 'IT', 'Degree in Information Technology, Computer Science, or related fields.'),
(36, 12, 'experience', 'systems', 'Proficiency in IT systems and network management.'),
(37, 12, 'experience', 'network', 'Strong problem-solving skills and technical expertise.'),
(38, 13, 'education', 'Estate', 'Degree in Estate Management, Facilities Management, or related fields.'),
(39, 13, 'experience', 'properties', 'Experience in managing institutional properties.'),
(40, 13, 'experience', 'maintenance', 'Knowledge of health and safety regulations.'),
(41, 14, 'education', 'Technology', 'Degree in Education Technology, Instructional Design, or related fields.'),
(42, 14, 'experience', 'digital', 'Proficiency in digital learning tools and platforms.'),
(43, 14, 'experience', 'platforms', 'Innovative approach to teaching and learning methodologies.'),
(44, 15, 'education', 'Business', 'Degree in Business Administration, Management, or related fields.'),
(45, 15, 'experience', 'leadership', 'Teaching and academic leadership experience.'),
(46, 15, 'experience', 'teaching', 'Strong knowledge of business education trends.'),
(47, 16, 'education', 'ICT', 'Degree in Information Technology, Computer Science, or related fields.'),
(48, 16, 'experience', 'technical', 'Teaching and technical expertise in ICT.'),
(49, 16, 'experience', 'ICT', 'Experience with current ICT trends and technologies.'),
(50, 17, 'education', 'Engineering', 'Degree in Engineering, Science, or related fields.'),
(51, 17, 'experience', 'research', 'Strong research background and teaching experience.'),
(52, 17, 'experience', 'teaching', 'Proficiency in laboratory management and scientific methodologies.'),
(53, 18, 'education', 'Health', 'Degree in Health Sciences, Medicine, or related fields.'),
(54, 18, 'experience', 'clinical', 'Experience in clinical practice and health education.'),
(55, 18, 'experience', 'education', 'Strong research skills in health sciences.'),
(56, 19, 'education', 'Chemical', 'Degree in Chemical Engineering, Petrochemical Studies, or related fields.'),
(57, 19, 'experience', 'industry', 'Industry experience in petrochemical sector.'),
(58, 19, 'experience', 'teaching', 'Teaching and research skills in petrochemical studies.'),
(59, 20, 'education', 'General', 'Degree in General Education, Liberal Arts, or related fields.'),
(60, 20, 'experience', 'broad', 'Broad knowledge across multiple disciplines.'),
(61, 20, 'experience', 'teaching', 'Teaching and curriculum development experience.'),
(62, 21, 'education', 'Library', 'Degree in Library Science, Information Management, or related fields.'),
(63, 21, 'experience', 'resources', 'Proficiency in managing learning resources and digital libraries.'),
(64, 21, 'experience', 'digital', 'Strong organizational and research support skills.'),
(65, 1, 'education', 'engineering, engineer, technical', ''),
(66, 1, 'experience', 'project management, construction, design', ''),
(67, 2, 'education', 'teaching, teacher, instructor, educator', ''),
(68, 2, 'experience', 'classroom management, lesson planning, curriculum development', ''),
(69, 3, 'education', 'information technology, software, computer science, it', ''),
(70, 3, 'experience', 'programming, system administration, cybersecurity', ''),
(71, 4, 'education', 'finance, accounting, business', ''),
(72, 4, 'experience', 'auditing, financial reporting, budgeting', ''),
(73, 5, 'education', 'marketing, communications, business', ''),
(74, 5, 'experience', 'social media, branding, public relations', ''),
(75, 6, 'education', 'human resources, hr management, business', ''),
(76, 6, 'experience', 'recruitment, employee relations, training development', ''),
(77, 1, 'education', 'Bachelor of Engineering', ''),
(78, 1, 'education', 'Mechanical Engineering', ''),
(79, 1, 'education', 'Civil Engineering', ''),
(80, 1, 'experience', 'Project Management', ''),
(81, 1, 'experience', 'Construction Supervision', ''),
(82, 1, 'experience', 'AutoCAD', ''),
(83, 1, 'experience', 'Site Engineering', ''),
(84, 2, 'education', 'Computer Science', ''),
(85, 2, 'education', 'Information Systems', ''),
(86, 2, 'education', 'Software Engineering', ''),
(87, 2, 'experience', 'Web Development', ''),
(88, 2, 'experience', 'Network Administration', ''),
(89, 2, 'experience', 'Database Management', ''),
(90, 2, 'experience', 'IT Support', ''),
(91, 2, 'experience', 'Cloud Computing', ''),
(92, 3, 'education', 'Business Administration', ''),
(93, 3, 'education', 'Management Studies', ''),
(94, 3, 'experience', 'Strategic Planning', ''),
(95, 3, 'experience', 'Sales and Marketing', ''),
(96, 3, 'experience', 'Operations Management', ''),
(97, 3, 'experience', 'Human Resource Management', ''),
(98, 4, 'education', 'Education', ''),
(99, 4, 'education', 'Teaching Certification', ''),
(100, 4, 'education', 'Curriculum Development', ''),
(101, 4, 'experience', 'Primary Teaching', ''),
(102, 4, 'experience', 'Secondary Teaching', ''),
(103, 4, 'experience', 'Online Teaching', ''),
(104, 5, 'education', 'Accounting', ''),
(105, 5, 'education', 'Finance', ''),
(106, 5, 'education', 'Economics', ''),
(107, 5, 'experience', 'Auditing', ''),
(108, 5, 'experience', 'Financial Reporting', ''),
(109, 5, 'experience', 'Taxation', ''),
(110, 6, 'education', 'Nursing', ''),
(111, 6, 'education', 'Public Health', ''),
(112, 6, 'education', 'Medical Sciences', ''),
(113, 6, 'experience', 'Patient Care', ''),
(114, 6, 'experience', 'Medical Administration', ''),
(115, 6, 'experience', 'Healthcare Research', ''),
(116, 1, 'education', 'business', ''),
(117, 1, 'education', 'management', ''),
(118, 1, 'education', 'finance', ''),
(119, 1, 'experience', 'project management', ''),
(120, 1, 'experience', 'operations', ''),
(121, 1, 'experience', 'customer service', ''),
(122, 2, 'education', 'computer science', ''),
(123, 2, 'education', 'software engineering', ''),
(124, 2, 'education', 'information technology', ''),
(125, 2, 'experience', 'programming', ''),
(126, 2, 'experience', 'networking', ''),
(127, 2, 'experience', 'technical support', ''),
(128, 3, 'education', 'education', ''),
(129, 3, 'education', 'early childhood', ''),
(130, 3, 'experience', 'teaching', ''),
(131, 3, 'experience', 'curriculum development', ''),
(132, 4, 'education', 'mechanical engineering', ''),
(133, 4, 'education', 'electrical engineering', ''),
(134, 4, 'education', 'civil engineering', ''),
(135, 4, 'experience', 'engineering project', ''),
(136, 4, 'experience', 'maintenance', ''),
(137, 5, 'education', 'logistics', ''),
(138, 5, 'education', 'supply chain', ''),
(139, 5, 'experience', 'inventory', ''),
(140, 5, 'experience', 'warehouse management', ''),
(141, 5, 'experience', 'transportation', ''),
(142, 6, 'education', 'occupational health', ''),
(143, 6, 'education', 'environmental science', ''),
(144, 6, 'experience', 'risk management', ''),
(145, 6, 'experience', 'health and safety', ''),
(146, 7, 'education', 'mass communication', ''),
(147, 7, 'education', 'public relations', ''),
(148, 7, 'experience', 'media relations', ''),
(149, 7, 'experience', 'content writing', ''),
(150, 7, 'experience', 'marketing', ''),
(151, 8, 'education', 'accounting', ''),
(152, 8, 'education', 'economics', ''),
(153, 8, 'experience', 'bookkeeping', ''),
(154, 8, 'experience', 'audit', ''),
(155, 8, 'experience', 'administration', ''),
(156, 9, 'education', 'human resource', ''),
(157, 9, 'education', 'organizational behavior', ''),
(158, 9, 'experience', 'recruitment', ''),
(159, 9, 'experience', 'payroll', ''),
(160, 9, 'experience', 'training development', ''),
(161, 10, 'education', 'research', ''),
(162, 10, 'experience', 'product development', ''),
(163, 10, 'experience', 'data analysis', ''),
(164, 11, 'experience', 'leadership', ''),
(165, 11, 'experience', 'teamwork', ''),
(166, 11, 'experience', 'problem solving', ''),
(167, 11, 'experience', 'critical thinking', ''),
(168, 11, 'education', 'general studies', ''),
(169, 11, 'education', 'professional certificate', ''),
(170, 1, 'education', 'diploma', NULL),
(171, 1, 'education', 'degree', NULL),
(172, 1, 'education', 'engineering', NULL),
(173, 1, 'education', 'science', NULL),
(174, 2, 'education', 'diploma', NULL),
(175, 2, 'education', 'communication', NULL),
(176, 2, 'education', 'media studies', NULL),
(177, 3, 'education', 'business', NULL),
(178, 3, 'education', 'finance', NULL),
(179, 3, 'education', 'management', NULL),
(180, 4, 'education', 'science', NULL),
(181, 4, 'education', 'healthcare', NULL),
(182, 5, 'education', 'computing', NULL),
(183, 5, 'education', 'information technology', NULL),
(184, 6, 'education', 'business', NULL),
(185, 6, 'education', 'public admin', NULL),
(186, 7, 'education', 'education', NULL),
(187, 7, 'education', 'teaching', NULL),
(188, 8, 'education', 'healthcare', NULL),
(189, 8, 'education', 'nursing', NULL),
(190, 9, 'education', 'management', NULL),
(191, 9, 'education', 'logistics', NULL),
(192, 10, 'education', 'engineering', NULL),
(193, 10, 'education', 'technical', NULL),
(194, 11, 'education', 'communication', NULL),
(195, 11, 'education', 'public relations', NULL),
(196, 12, 'education', 'science', NULL),
(197, 12, 'education', 'technology', NULL),
(198, 13, 'education', 'computing', NULL),
(199, 13, 'education', 'software engineering', NULL),
(200, 14, 'education', 'business', NULL),
(201, 14, 'education', 'marketing', NULL),
(202, 15, 'education', 'finance', NULL),
(203, 15, 'education', 'accounting', NULL),
(204, 16, 'education', 'hospitality', NULL),
(205, 16, 'education', 'tourism', NULL),
(206, 17, 'education', 'environmental science', NULL),
(207, 17, 'education', 'sustainability', NULL),
(208, 18, 'education', 'law', NULL),
(209, 18, 'education', 'public admin', NULL),
(210, 19, 'education', 'architecture', NULL),
(211, 19, 'education', 'urban planning', NULL),
(212, 20, 'education', 'science', NULL),
(213, 20, 'education', 'biology', NULL),
(214, 21, 'education', 'public admin', NULL),
(215, 21, 'education', 'governance', NULL),
(216, 1, 'experience', 'management', NULL),
(217, 2, 'experience', 'finance', NULL),
(218, 3, 'experience', 'marketing', NULL),
(219, 4, 'experience', 'engineering', NULL),
(220, 5, 'experience', 'teaching', NULL),
(221, 6, 'experience', 'networking', NULL),
(222, 7, 'experience', 'programming', NULL),
(223, 8, 'experience', 'healthcare', NULL),
(224, 9, 'experience', 'logistics', NULL),
(225, 10, 'experience', 'construction', NULL),
(226, 11, 'experience', 'administration', NULL),
(227, 12, 'experience', 'design', NULL),
(228, 13, 'experience', 'communication', NULL),
(229, 14, 'experience', 'education', NULL),
(230, 15, 'experience', 'research', NULL),
(231, 16, 'experience', 'leadership', NULL),
(232, 17, 'experience', 'maintenance', NULL),
(233, 18, 'experience', 'support', NULL),
(234, 19, 'experience', 'security', NULL),
(235, 20, 'experience', 'event management', NULL),
(236, 21, 'experience', 'entrepreneurship', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int NOT NULL,
  `event_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'new',
  `admin_notes` text,
  `assigned_to` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `category`, `message`, `attachment`, `rating`, `submitted_at`, `status`, `admin_notes`, `assigned_to`) VALUES
(1, 1, 'Other', 'Inconsistent Layout', NULL, 1, '2025-03-13 01:18:43', 'new', NULL, NULL),
(2, 1, 'Other', 'test 3', '../feedbacks/uploads/feedback_67d3a15c000368.49524718.pdf', 1, '2025-03-14 03:24:12', 'new', NULL, NULL),
(3, 1, 'Other', 'test 6 ', '../feedback/uploads/feedback_67d4304fa21b98.41410710.jpg', 1, '2025-03-14 13:34:07', 'new', NULL, NULL),
(4, 1, 'Other', 'test 7', '../feedback/uploads/feedback_67d4311f4b5ea5.57762783.pdf', 2, '2025-03-14 13:37:35', 'new', NULL, NULL),
(5, 1, 'Other', 'test ', '../feedback/uploads/feedback_67d7699369f290.13491087.jpg', 1, '2025-03-17 00:15:15', 'new', NULL, NULL),
(6, 1, 'Other', 'guytu7y', NULL, 2, '2025-03-17 03:23:28', 'new', NULL, NULL),
(7, 1, 'feature_request', 'Hello', '../feedback/uploads/feedback_680dd5636fa830.25161854.pdf', 5, '2025-04-27 06:57:39', 'new', NULL, NULL),
(8, 1, 'feature_request', 'Can you make a feature where i can ask for roles?', '../feedback/uploads/feedback_6810db979d4b94.33034910.pdf', 5, '2025-04-29 14:00:55', 'new', NULL, NULL),
(9, 1, 'feature_request', 'hello', NULL, 5, '2025-04-29 14:04:16', 'new', NULL, NULL),
(10, 434, 'general_feedback', 'test', NULL, 3, '2025-09-24 02:51:25', 'new', NULL, NULL),
(11, 403, 'bug_report', 'slide', NULL, 3, '2025-09-30 03:46:03', 'new', NULL, NULL),
(12, 420, 'bug_report', 'test', '../feedback/uploads/feedback_68e093220edc91.17552622.png', 3, '2025-10-03 19:23:14', 'new', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mails`
--

CREATE TABLE `mails` (
  `id` int NOT NULL,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text,
  `folder` enum('inbox','sent','drafts','trash') DEFAULT 'inbox',
  `unread` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mails`
--

INSERT INTO `mails` (`id`, `sender`, `receiver`, `subject`, `body`, `folder`, `unread`, `created_at`) VALUES
(1, 'me@example.com', 'farah.ismail@pb.edu.bn', 'Fries', 'Fries', 'sent', 0, '2025-04-27 17:51:52'),
(2, 'me@example.com', 'farah.ismail@pb.edu.bn', 'Fries', 'Fries', 'sent', 0, '2025-04-27 17:51:52'),
(3, 'me@example.com', 'farah.ismail@pb.edu.bn', 'Fries', 'Fries', 'sent', 0, '2025-04-27 17:51:53'),
(4, 'me@example.com', 'farah.ismail@pb.edu.bn', 'Fries', 'Fries', 'sent', 0, '2025-04-27 17:54:51');

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `meeting_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `agenda` text,
  `meeting_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`meeting_id`, `title`, `agenda`, `meeting_date`, `start_time`, `end_time`, `created_by`, `created_at`) VALUES
(7, 'meeting', 'test', '2025-08-25', '14:11:00', '19:11:00', 420, '2025-08-25 06:12:08');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_chats`
--

CREATE TABLE `meeting_chats` (
  `chat_id` int NOT NULL,
  `meeting_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meeting_chats`
--

INSERT INTO `meeting_chats` (`chat_id`, `meeting_id`, `user_id`, `message`, `created_at`) VALUES
(3, 7, 420, 'test', '2025-08-25 06:12:55');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_notification`
--

CREATE TABLE `meeting_notification` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `meeting_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meeting_participants`
--

CREATE TABLE `meeting_participants` (
  `id` int NOT NULL,
  `meeting_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('Pending','Accepted','Declined') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meeting_participants`
--

INSERT INTO `meeting_participants` (`id`, `meeting_id`, `user_id`, `status`) VALUES
(10, 7, 420, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `recipient_id` int DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `folder` enum('inbox','sent','drafts','trash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'inbox',
  `is_deleted` tinyint(1) DEFAULT '0',
  `draft` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `recipient_email_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `thread_id` int DEFAULT NULL,
  `attachment_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `subject`, `body`, `is_read`, `created_at`, `folder`, `is_deleted`, `draft`, `deleted_at`, `recipient_email_text`, `thread_id`, `attachment_path`) VALUES
(181, 1, 2, 'Testing 1', 'Testing 1', 0, '2025-04-28 00:31:44', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(182, 1, 2, 'Testing 1', 'Testing 1', 0, '2025-04-28 00:31:44', 'inbox', 0, 0, NULL, NULL, 181, NULL),
(183, 2, 1, 'Testing 1', 'Testing 1.1', 1, '2025-04-28 00:56:58', 'sent', 0, 0, NULL, NULL, 182, NULL),
(185, 1, 2, 'Testing 3', 'This is testing 3', 0, '2025-04-28 01:04:41', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(186, 1, 2, 'Testing 3', 'This is testing 3', 0, '2025-04-28 01:04:41', 'inbox', 0, 0, NULL, NULL, 185, NULL),
(189, 1, 2, 'Testing 5', 'another', 0, '2025-04-28 01:14:46', 'sent', 0, 0, NULL, NULL, 188, NULL),
(190, 2, 2, 'Testing 5', 'Hello', 0, '2025-04-28 01:21:30', 'sent', 0, 0, NULL, NULL, 188, NULL),
(193, 1, 1, 'Testing 6', 'Testing 6', 0, '2025-04-28 01:26:50', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(195, 2, 2, 'Testing 7', 'Yes, are you good?', 0, '2025-04-28 01:27:54', 'sent', 0, 0, NULL, NULL, 194, NULL),
(196, 2, 2, 'Testing 7', 'Hello?', 0, '2025-04-28 01:28:30', 'sent', 0, 0, NULL, NULL, 194, NULL),
(198, 1, 2, 'Testing 9', 'Testing 9', 0, '2025-04-28 01:33:35', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(200, 1, 2, 'Testing 10', 'TEsting 10', 0, '2025-04-28 01:38:22', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(201, 2, 2, 'Testing 10', 'dpt kah ni', 0, '2025-04-28 01:38:42', 'sent', 0, 0, NULL, NULL, 199, NULL),
(202, 1, 2, 'Testing 10', 'hello', 0, '2025-04-28 01:39:34', 'sent', 0, 0, NULL, NULL, 200, NULL),
(203, 2, 2, 'Testing 10', 'bah bui', 0, '2025-04-28 01:40:19', 'sent', 0, 0, NULL, NULL, 199, NULL),
(205, 1, 2, 'Testing 11', 'Testing 11', 0, '2025-04-28 01:48:07', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(206, 2, 2, 'Testing 11', 'Another Test', 0, '2025-04-28 01:48:26', 'inbox', 0, 0, NULL, NULL, 204, NULL),
(207, 2, 2, 'Testing 11', 'Another Test', 0, '2025-04-28 01:48:26', 'sent', 0, 0, NULL, NULL, 204, NULL),
(209, 2, 1, 'Testing 12', 'Testing 12', 0, '2025-04-28 01:49:26', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(210, 1, 1, 'Testing 12', 'Another Testing 12', 0, '2025-04-28 01:49:44', 'sent', 0, 0, NULL, NULL, 208, NULL),
(211, 1, 2, 'Testing 13', 'Testing 13', 0, '2025-04-28 01:57:51', 'inbox', 0, 0, NULL, NULL, 211, NULL),
(212, 1, 2, 'Testing 13', 'Testing 13', 0, '2025-04-28 01:57:51', 'sent', 0, 0, NULL, NULL, 212, NULL),
(214, 1, 2, 'Testing 2', 'Testing 2', 0, '2025-04-28 01:58:34', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(215, 2, 2, 'Testing 2', 'Testing', 0, '2025-04-28 02:01:07', 'sent', 0, 0, NULL, NULL, 213, NULL),
(216, 1, 2, 'Testing 100', 'Testing 100', 0, '2025-04-28 02:09:15', 'inbox', 0, 0, NULL, NULL, 216, NULL),
(217, 1, 2, 'Testing 100', 'Testing 100', 1, '2025-04-28 02:09:15', 'sent', 0, 0, NULL, NULL, 216, NULL),
(218, 2, 2, 'Testing 100', '100', 0, '2025-04-28 02:09:43', 'inbox', 0, 0, NULL, NULL, 217, NULL),
(219, 2, 2, 'Testing 100', '100', 1, '2025-04-28 02:09:43', 'sent', 0, 0, NULL, NULL, 217, NULL),
(220, 1, 2, 'Testing 100', 'yes\r\n', 0, '2025-04-28 02:28:34', 'inbox', 0, 0, NULL, NULL, 216, NULL),
(221, 1, 2, 'Testing 100', 'yes\r\n', 1, '2025-04-28 02:28:34', 'sent', 0, 0, NULL, NULL, 216, NULL),
(222, 1, 2, 'testing 17', 'Testing 17', 0, '2025-04-28 02:33:21', 'inbox', 0, 0, NULL, NULL, 222, NULL),
(223, 1, 2, 'testing 17', 'Testing 17', 1, '2025-04-28 02:33:21', 'sent', 0, 0, NULL, NULL, 222, NULL),
(224, 1, 2, 'Testing 51', 'Testing 51', 1, '2025-04-28 02:37:08', 'inbox', 0, 0, NULL, NULL, 224, NULL),
(225, 1, 2, 'Testing 51', 'Testing 51', 1, '2025-04-28 02:37:08', 'sent', 0, 0, NULL, NULL, 224, NULL),
(226, 1, 2, 'ok', 'ok', 1, '2025-04-28 03:08:49', 'inbox', 0, 0, NULL, NULL, 226, NULL),
(227, 1, 2, 'ok', 'ok', 1, '2025-04-28 03:08:49', 'sent', 0, 0, NULL, NULL, 226, NULL),
(228, 1, 2, 'Testing 20', 'Testing 20', 0, '2025-04-28 03:25:56', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(230, 2, 2, 'Testing 20', 'hello', 0, '2025-04-28 03:26:43', 'sent', 0, 0, NULL, NULL, 228, NULL),
(231, 2, 2, 'Testing 20', 'hello', 0, '2025-04-28 03:26:43', 'inbox', 0, 0, NULL, NULL, 228, NULL),
(233, 1, 2, 'boi', 'boi', 0, '2025-04-28 03:44:25', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(234, 2, 2, 'boi', 'halo', 0, '2025-04-28 03:46:28', 'sent', 0, 0, NULL, NULL, 232, NULL),
(236, 1, 2, 'oppp', 'hi', 0, '2025-04-28 03:54:04', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(237, 2, 1, 'oppp', 'hi', 0, '2025-04-28 03:54:22', 'sent', 0, 0, NULL, NULL, 235, NULL),
(238, 1, 1, 'oppp', 'hi', 0, '2025-04-28 03:56:22', 'sent', 0, 0, NULL, NULL, 236, NULL),
(240, 1, 2, '78', 'qwee', 0, '2025-04-28 04:05:51', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(241, 2, 1, '78', 'hello', 0, '2025-04-28 04:06:28', 'sent', 0, 0, NULL, NULL, 239, NULL),
(242, 1, 1, '78', 'hi', 0, '2025-04-28 04:06:46', 'sent', 0, 0, NULL, NULL, 240, NULL),
(244, 1, 2, '1234', 'qwertyuiop', 0, '2025-04-28 04:11:16', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(246, 1, 2, 'ooiojo', 'qwertyuio', 0, '2025-04-28 04:26:36', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(247, 1, 1, 'ooiojo', 'hi', 0, '2025-04-28 04:27:38', 'sent', 0, 0, NULL, NULL, 246, NULL),
(248, 2, 1, 'ooiojo', 'hi', 0, '2025-04-28 04:27:52', 'sent', 0, 0, NULL, NULL, 245, NULL),
(250, 1, 2, '8888', 'qwertyuiop', 0, '2025-04-28 04:36:15', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(251, 2, 1, '8888', 'hi', 0, '2025-04-28 04:36:25', 'inbox', 0, 0, NULL, NULL, 249, NULL),
(252, 2, 1, '8888', 'hi', 0, '2025-04-28 04:36:25', 'sent', 0, 0, NULL, NULL, 249, NULL),
(254, 1, 2, '78963215', 'qwertyuiop', 0, '2025-04-28 04:37:29', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(255, 1, 1, '78963215', 'hi', 0, '2025-04-28 04:37:35', 'inbox', 0, 0, NULL, NULL, 254, NULL),
(256, 1, 1, '78963215', 'hi', 0, '2025-04-28 04:37:35', 'sent', 0, 0, NULL, NULL, 254, NULL),
(257, 2, 1, '78963215', 'hi', 0, '2025-04-28 04:38:21', 'inbox', 0, 0, NULL, NULL, 253, NULL),
(258, 2, 1, '78963215', 'hi', 0, '2025-04-28 04:38:21', 'sent', 0, 0, NULL, NULL, 253, NULL),
(260, 1, 2, '1234567890', '0987654321', 0, '2025-04-28 04:46:34', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(261, 2, 1, '1234567890', 'hi', 0, '2025-04-28 04:46:45', 'inbox', 0, 0, NULL, NULL, 259, NULL),
(262, 2, 1, '1234567890', 'hi', 0, '2025-04-28 04:46:45', 'sent', 0, 0, NULL, NULL, 259, NULL),
(264, 1, 2, 'qwertyuiop', 'poiuytrewq', 0, '2025-04-28 04:53:31', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(265, 2, 1, 'qwertyuiop', 'hi', 0, '2025-04-28 04:53:43', 'inbox', 0, 0, NULL, NULL, 263, NULL),
(266, 2, 1, 'qwertyuiop', 'hi', 0, '2025-04-28 04:53:43', 'sent', 0, 0, NULL, NULL, 263, NULL),
(268, 1, 2, '-098765432', '1234567890-', 0, '2025-04-28 05:02:19', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(269, 2, 1, '-098765432', 'IUYTREWQ', 0, '2025-04-28 05:02:30', 'inbox', 0, 0, NULL, NULL, 267, NULL),
(270, 2, 1, '-098765432', 'IUYTREWQ', 0, '2025-04-28 05:02:30', 'sent', 0, 0, NULL, NULL, 267, NULL),
(272, 1, 2, '1234567890', 'qwertyuiolkjnbvc', 0, '2025-04-28 05:05:30', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(273, 2, 1, '1234567890', 'hi', 0, '2025-04-28 05:05:59', 'inbox', 0, 0, NULL, NULL, 271, NULL),
(274, 2, 1, '1234567890', 'hi', 0, '2025-04-28 05:05:59', 'sent', 0, 0, NULL, NULL, 271, NULL),
(276, 1, 2, '78962', 'poiuytrewsdfgh', 0, '2025-04-28 05:07:39', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(282, 2, 1, 'kjhgfds', 'jhgfdsxcvb', 0, '2025-04-28 05:11:16', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(283, 2, 2, 'kjhgfds', 'hello', 0, '2025-04-28 05:11:47', 'inbox', 0, 0, NULL, NULL, 282, NULL),
(284, 2, 2, 'kjhgfds', 'hello', 0, '2025-04-28 05:11:47', 'sent', 0, 0, NULL, NULL, 282, NULL),
(285, 1, 2, 'kjhgfds', 'hi', 0, '2025-04-28 05:11:57', 'inbox', 0, 0, NULL, NULL, 281, NULL),
(286, 1, 2, 'kjhgfds', 'hi', 0, '2025-04-28 05:11:57', 'sent', 0, 0, NULL, NULL, 281, NULL),
(288, 1, 2, '0987654321', 'QWERTYJK', 0, '2025-04-28 05:18:44', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(289, 2, 1, '0987654321', 'HI', 0, '2025-04-28 05:19:02', 'inbox', 0, 0, NULL, NULL, 287, NULL),
(290, 2, 1, '0987654321', 'HI', 0, '2025-04-28 05:19:02', 'sent', 0, 0, NULL, NULL, 287, NULL),
(292, 1, 2, '78962', 'SDFGHJKJHGFD', 0, '2025-04-28 05:22:18', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(293, 2, 1, '78962', 'HI', 0, '2025-04-28 05:22:33', 'sent', 0, 0, NULL, NULL, 291, NULL),
(295, 1, 2, '7863', 'poiuytrewq', 0, '2025-04-28 05:24:17', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(296, 2, 1, '7863', 'hi', 0, '2025-04-28 05:24:26', 'sent', 0, 0, NULL, NULL, 294, NULL),
(297, 2, 1, '7863', 'hi', 0, '2025-04-28 05:24:26', 'inbox', 0, 0, NULL, NULL, 294, NULL),
(299, 2, 1, 'JHYTRD', 'OIUYTREW', 0, '2025-04-28 05:28:05', 'inbox', 0, 0, NULL, NULL, 298, NULL),
(301, 1, 2, '1234567890', 'KJHGFDS', 0, '2025-04-28 05:28:44', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(302, 2, 1, '1234567890', 'POIUYTREWQ', 0, '2025-04-28 05:28:55', 'inbox', 0, 0, NULL, NULL, 300, NULL),
(304, 1, 2, '1234567890', 'WERTYUI', 0, '2025-04-28 05:29:48', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(305, 2, 1, '1234567890', 'HI', 0, '2025-04-28 05:30:00', 'sent', 0, 0, NULL, NULL, 303, NULL),
(306, 2, 1, '1234567890', 'HI', 0, '2025-04-28 05:30:00', 'inbox', 0, 0, NULL, NULL, 303, NULL),
(308, 1, 2, '12321456987', 'WEDFGBNM', 0, '2025-04-28 05:30:59', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(309, 2, 1, '12321456987', '8998', 0, '2025-04-28 05:31:16', 'inbox', 0, 0, NULL, NULL, 307, NULL),
(310, 2, 1, '12321456987', '8998', 0, '2025-04-28 05:31:16', 'sent', 0, 0, NULL, NULL, 307, NULL),
(315, 2, 1, 'TESTING 90', 'ALHAMDULILLAH', 0, '2025-04-28 05:34:06', 'inbox', 0, 0, NULL, NULL, 313, NULL),
(316, 2, 1, 'TESTING 90', 'ALHAMDULILLAH', 0, '2025-04-28 05:34:06', 'sent', 0, 0, NULL, NULL, 313, NULL),
(317, 2, 1, 'TESTING 90', 'OKA\r\n', 0, '2025-04-28 05:34:53', 'inbox', 0, 0, NULL, NULL, 313, NULL),
(318, 2, 1, 'TESTING 90', 'OKA\r\n', 0, '2025-04-28 05:34:53', 'sent', 0, 0, NULL, NULL, 313, NULL),
(319, 2, 1, 'TESTING 90', 'OKAY', 0, '2025-04-28 05:35:03', 'inbox', 0, 0, NULL, NULL, 313, NULL),
(320, 2, 1, 'TESTING 90', 'OKAY', 0, '2025-04-28 05:35:03', 'sent', 0, 0, NULL, NULL, 313, NULL),
(322, 1, 2, 'testing 80', 'testing 80', 0, '2025-04-28 06:14:25', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(323, 2, 2, 'testing 80', 'hellow', 0, '2025-04-28 06:22:15', 'sent', 0, 0, NULL, NULL, 321, NULL),
(326, 2, 2, '1234567890', '1234567890', 0, '2025-04-28 06:27:23', 'inbox', 0, 0, NULL, NULL, 324, NULL),
(328, 1, 2, '1234567890', '1234567890', 0, '2025-04-28 06:35:41', 'sent', 0, 0, NULL, NULL, NULL, NULL),
(329, 2, 2, '1234567890', '12345678900', 0, '2025-04-28 06:35:56', 'inbox', 0, 0, NULL, NULL, 327, NULL),
(331, 2, 2, '1234567890', '12345678901234567890', 0, '2025-04-28 06:40:59', 'inbox', 0, 0, NULL, NULL, 330, NULL),
(333, 2, 2, '0987654321', '09876543210987654321', 0, '2025-04-28 06:44:13', 'inbox', 0, 0, NULL, NULL, 332, NULL),
(334, 2, 2, '0987654321', '09876543210987654321', 0, '2025-04-28 06:44:13', 'inbox', 0, 0, NULL, NULL, 332, NULL),
(336, 2, 2, 'asdfghjkl', 'asdfghjkllkjhgfdsa', 0, '2025-04-28 06:48:43', 'inbox', 0, 0, NULL, NULL, 335, NULL),
(337, 2, 2, 'asdfghjkl', 'asdfghjkllkjhgfdsa', 0, '2025-04-28 06:48:43', 'inbox', 0, 0, NULL, NULL, 335, NULL),
(340, 1, 2, '0987654321234567890', 'yoooo\r\n', 0, '2025-04-28 06:52:06', 'inbox', 0, 0, NULL, NULL, 339, NULL),
(341, 1, 2, '0987654321234567890', 'yoooo\r\n', 0, '2025-04-28 06:52:06', 'inbox', 0, 0, NULL, NULL, 339, NULL),
(342, 1, 2, '0987654321234567890', 'aaaaaaaaaaaaaaaaa', 0, '2025-04-28 06:52:30', 'inbox', 0, 0, NULL, NULL, 339, NULL),
(343, 2, 2, '0987654321234567890', 'basdfghjkgfdsdfghjk', 0, '2025-04-28 06:52:52', 'inbox', 0, 0, NULL, NULL, 339, NULL),
(344, 1, 2, '0987654321234567890', 'gfdxcvbjkoiuytfdgcvhbjknpojgfutcjk', 0, '2025-04-28 06:53:09', 'inbox', 0, 0, NULL, NULL, 339, NULL),
(346, 2, 2, 'ZXCVBNM,,MNBVCXZXCVBNM,MNCXZ', 'DFGUHIJOPUFGCHVJBKL\';LCVL;VKJL', 0, '2025-04-28 07:03:56', 'inbox', 0, 0, NULL, NULL, 345, NULL),
(347, 1, 2, 'ZXCVBNM,,MNBVCXZXCVBNM,MNCXZ', 'YGHIJFTDFGHJOFTYUGIHJO', 0, '2025-04-28 07:04:51', 'inbox', 0, 0, NULL, NULL, 345, NULL),
(349, 2, 2, '\\sfggenfdasdFGNH', 'rxdfcgvhjkopiuytredtfyguhijop[', 0, '2025-04-28 07:08:19', 'inbox', 0, 0, NULL, NULL, 348, NULL),
(350, 2, 2, '\\sfggenfdasdFGNH', 'rxdfcgvhjkopiuytredtfyguhijop[', 0, '2025-04-28 07:08:19', 'inbox', 0, 0, NULL, NULL, 348, NULL),
(352, 1, 1, 'gvbnkhgv bnkmjhub', 'xdrfcgvhbjnklgfdtfyguhijk', 0, '2025-04-28 07:09:12', 'inbox', 0, 0, NULL, NULL, 351, NULL),
(353, 1, 1, 'gvbnkhgv bnkmjhub', 'ghjkl', 0, '2025-04-28 07:09:29', 'inbox', 0, 0, NULL, NULL, 351, NULL),
(355, 1, 1, 'awesdrtfyguiohgfc', 'dfhyguijlojhgfcjkl;jhg', 0, '2025-04-28 12:00:35', 'inbox', 0, 0, NULL, NULL, 354, NULL),
(356, 2, 1, 'awesdrtfyguiohgfc', '1', 0, '2025-04-28 12:03:11', 'inbox', 0, 0, NULL, NULL, 354, NULL),
(357, 1, 1, 'awesdrtfyguiohgfc', '2', 0, '2025-04-28 12:03:29', 'inbox', 0, 0, NULL, NULL, 354, NULL),
(359, 1, 1, 'TEST 1', '2', 0, '2025-04-28 12:10:24', 'inbox', 0, 0, NULL, NULL, 358, NULL),
(360, 2, 1, 'TEST 1', '3', 0, '2025-04-28 12:20:41', 'inbox', 0, 0, NULL, NULL, 358, NULL),
(361, 1, 1, 'TEST 1', '4', 0, '2025-04-28 12:20:55', 'inbox', 0, 0, NULL, NULL, 358, NULL),
(362, 2, 1, 'TEST 1', '5', 0, '2025-04-28 12:35:17', 'inbox', 0, 0, NULL, NULL, 358, NULL),
(363, 1, 1, 'TEST 1', '6', 0, '2025-04-28 12:38:00', 'inbox', 0, 0, NULL, NULL, 358, NULL),
(365, 2, 2, 'hello there', '2', 0, '2025-04-28 12:41:16', 'inbox', 0, 0, NULL, NULL, 364, NULL),
(366, 2, 2, 'hello there', '3', 0, '2025-04-28 12:41:54', 'inbox', 0, 0, NULL, NULL, 364, NULL),
(367, 2, 2, 'hello there', '1', 0, '2025-04-28 12:42:42', 'inbox', 0, 0, NULL, NULL, 364, NULL),
(368, 2, 2, 'hello there', '23', 0, '2025-04-28 12:49:23', 'inbox', 0, 0, NULL, NULL, 364, NULL),
(370, 2, 1, 'sekali lagi', '3', 0, '2025-04-28 12:59:15', 'inbox', 0, 0, NULL, NULL, 369, NULL),
(371, 1, 1, 'sekali lagi', '12', 0, '2025-04-28 13:08:59', 'inbox', 0, 0, NULL, NULL, 369, NULL),
(372, 1, 1, 'sekali lagi', 'lk;', 0, '2025-04-28 13:17:31', 'inbox', 0, 0, NULL, NULL, 369, NULL),
(373, 1, 2, 'sekali lagi', 'asd', 0, '2025-04-28 13:18:22', 'inbox', 0, 0, NULL, NULL, 369, NULL),
(374, 2, 2, 'sekali lagi', 'asda', 0, '2025-04-28 13:18:42', 'inbox', 0, 0, NULL, NULL, 369, NULL),
(376, 2, 1, 'ui', 'ah', 0, '2025-04-28 13:21:17', 'inbox', 0, 0, NULL, NULL, 375, NULL),
(377, 1, 2, 'ui', 'sekali', 0, '2025-04-28 13:21:30', 'inbox', 0, 0, NULL, NULL, 375, NULL),
(378, 2, 1, 'ui', 'hello', 0, '2025-04-28 13:25:53', 'inbox', 0, 0, NULL, NULL, 375, NULL),
(381, 1, 2, 'Hello', 'Yes', 0, '2025-04-29 07:47:21', 'inbox', 0, 0, NULL, NULL, 380, NULL),
(382, 1, 2, 'Mail One', 'Mail One', 0, '2025-04-29 07:48:41', 'inbox', 0, 0, NULL, NULL, NULL, NULL),
(383, 1, 2, 'Mail One', 'yup', 0, '2025-04-29 07:48:53', 'inbox', 0, 0, NULL, NULL, 382, NULL),
(384, 2, 1, 'Mail One', 'Yes Hello', 0, '2025-04-29 07:49:17', 'inbox', 0, 0, NULL, NULL, 382, NULL),
(388, 1, 2, 'Mail Two', 'Mail Two', 0, '2025-04-29 08:32:56', 'inbox', 0, 0, NULL, NULL, NULL, NULL),
(389, 1, 2, 'Mail Three', 'Mail Three', 0, '2025-04-29 08:38:51', 'inbox', 0, 0, NULL, NULL, NULL, NULL),
(390, 1, 2, 'Mail Four', 'Mail Four', 0, '2025-04-29 08:42:09', 'inbox', 0, 0, NULL, NULL, NULL, NULL),
(392, 1, 2, 'Mail Five', 'Mail Five', 0, '2025-04-29 08:45:07', 'inbox', 0, 0, NULL, NULL, NULL, NULL),
(393, 2, 1, 'Mail Five', 'Mail Five', 0, '2025-04-29 08:46:24', 'inbox', 0, 0, NULL, NULL, 392, NULL),
(394, 1, 2, 'Mail Five', 'Mail Five', 0, '2025-04-29 08:46:37', 'inbox', 0, 0, NULL, NULL, 392, NULL),
(408, 2, 1, 'wqefdgbewrq', 'qwefewrqfebd', 0, '2025-04-29 09:29:37', 'inbox', 0, 0, NULL, NULL, 407, NULL),
(409, 1, 2, 'wqefdgbewrq', 'egwrqfdbgewrefd', 0, '2025-04-29 09:29:46', 'inbox', 0, 0, NULL, NULL, 407, NULL),
(411, 1, 2, 'fdbeADSFVBC', 'DSFBDSFDBV', 0, '2025-04-29 09:32:41', 'inbox', 0, 0, NULL, NULL, 410, NULL),
(413, 2, 1, 'iuhygvbjkojibh', 'efweqdfvweqfdvsewq', 0, '2025-04-29 10:24:29', 'inbox', 0, 0, NULL, NULL, 412, NULL),
(419, 2, 2, 'kjhhbnjn', 'safdsfdsafs', 0, '2025-04-29 11:40:32', 'inbox', 0, 0, NULL, NULL, 417, NULL),
(421, 2, 1, 'dsewqscvqcd', 'yfcghuioihugvcv', 0, '2025-04-29 11:45:26', 'inbox', 0, 0, NULL, NULL, 420, NULL),
(424, 1, NULL, 'Draft One', 'Draft One', 0, '2025-04-29 12:48:25', 'drafts', 0, 0, NULL, NULL, NULL, NULL),
(425, 2, 1, 'Mail Four', 'Mail Four', 0, '2025-04-29 12:49:44', 'inbox', 0, 0, NULL, NULL, 390, NULL),
(426, 1, 2, 'Delete 1', 'Delete 1', 0, '2025-04-29 12:56:20', 'trash', 0, 0, NULL, NULL, NULL, NULL),
(427, 2, 1, 'Mail One', 'This is working, right?', 0, '2025-04-29 12:57:49', 'inbox', 0, 0, NULL, NULL, 382, NULL),
(428, 420, 434, 'test', 'today times', 0, '2025-09-23 09:08:27', 'inbox', 0, 0, NULL, NULL, NULL, NULL),
(429, 434, 1, 'test', 'test', 0, '2025-09-23 09:36:15', 'inbox', 0, 0, NULL, NULL, 428, NULL),
(430, 434, 1, 'test', 'test 2', 0, '2025-09-23 09:36:47', 'inbox', 0, 0, NULL, NULL, 428, NULL),
(431, 420, 1, 'test', 'test', 0, '2025-09-23 09:37:58', 'inbox', 0, 0, NULL, NULL, 428, NULL),
(432, 420, 1, 'test', 'test 2', 0, '2025-09-23 09:46:21', 'inbox', 0, 0, NULL, NULL, 428, NULL),
(433, 420, 1, 'test', 'test', 0, '2025-09-23 23:58:41', 'inbox', 0, 0, NULL, NULL, 428, NULL),
(434, 420, 434, 'test2', 'test', 0, '2025-09-24 02:49:33', 'inbox', 0, 0, NULL, NULL, NULL, NULL),
(435, 434, 1, 'test2', 'test', 0, '2025-09-24 02:50:02', 'inbox', 0, 0, NULL, NULL, 434, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int NOT NULL,
  `message_id` int NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('unread','read') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `status`, `created_at`, `is_read`, `url`) VALUES
(1, 2, '[New Task] Student Mentoring', 'read', '2025-03-10 13:36:24', 1, NULL),
(2, 1, 'New report submitted. Report ID: 3', 'read', '2025-03-11 12:45:02', 1, NULL),
(3, 1, 'New report submitted. Report ID: 4', 'read', '2025-03-11 12:58:29', 1, NULL),
(4, 1, 'New report submitted. Report ID: 5', 'read', '2025-03-11 13:05:45', 1, NULL),
(5, 1, 'New report submitted. Report ID: 6', 'read', '2025-03-11 13:13:12', 1, NULL),
(6, 1, '[New Task] Substituting a Teacher', 'read', '2025-03-16 00:59:03', 1, NULL),
(7, 1, 'New report submitted. Report ID: 11', 'read', '2025-03-17 00:16:40', 1, NULL),
(8, 3, '[New Task] Lesson Planning', 'unread', '2025-03-17 00:22:11', 0, NULL),
(9, 2, '[New Task] Department Meeting', 'read', '2025-03-17 01:54:21', 1, NULL),
(10, 1, '[New Task] Grade Submission', 'read', '2025-03-17 02:07:01', 1, NULL),
(11, 2, '[New Task] Substituting a Teacher', 'read', '2025-04-21 01:18:49', 1, NULL),
(12, 2, '[New Task] Faculty Meeting', 'read', '2025-04-21 01:21:04', 1, NULL),
(13, 2, '[New Task] Student Attendance Checking', 'read', '2025-04-21 01:45:37', 1, NULL),
(14, 2, '[New Task] Performance Review', 'read', '2025-04-21 03:38:40', 1, NULL),
(15, 3, 'You have been assigned a new report (ID: 12)', 'unread', '2025-04-21 23:53:05', 0, NULL),
(16, 1, 'You have been assigned a new report (ID: 13)', 'read', '2025-04-22 02:16:55', 1, NULL),
(17, 2, '[New Task] Performance Review', 'read', '2025-04-22 05:35:05', 1, NULL),
(18, 2, '[New Task] Faculty Meeting', 'read', '2025-04-22 05:41:54', 1, NULL),
(19, 2, '[New Task] Department Meeting', 'read', '2025-04-22 07:26:27', 1, NULL),
(20, 2, '[New Task] Student Attendance Checking', 'read', '2025-04-22 21:09:33', 1, NULL),
(21, 2, '[New Task] Performance Review', 'read', '2025-04-22 21:33:50', 1, NULL),
(22, 2, '[New Task] Student Counseling', 'read', '2025-04-22 21:38:32', 1, NULL),
(23, 2, '[New Task] Administrative Paperwork', 'read', '2025-04-23 04:59:31', 1, NULL),
(24, 2, '[New Task] Department Meeting', 'read', '2025-04-23 05:03:38', 1, NULL),
(25, 2, '[New Task] School Event Coordination', 'read', '2025-04-23 05:05:21', 1, NULL),
(26, 2, '[New Task] Student Counseling', 'read', '2025-04-23 05:35:08', 1, NULL),
(27, 2, '[New Task] Lesson Planning', 'read', '2025-04-23 05:35:32', 1, NULL),
(28, 2, '[New Task] Administrative Paperwork', 'read', '2025-04-23 05:41:13', 1, NULL),
(29, 2, '[New Task] Faculty Meeting', 'read', '2025-04-23 05:56:04', 1, NULL),
(30, 2, '[New Task] Student Mentoring', 'read', '2025-04-23 06:04:03', 1, NULL),
(31, 2, '[New Task] Student Mentoring', 'read', '2025-04-23 06:24:30', 1, NULL),
(32, 1, 'You have been assigned a new report (ID: 14)', 'read', '2025-04-23 23:47:50', 1, NULL),
(33, 10, 'You have been appointed to the role of Public Relations Officer', 'read', '2025-04-26 00:45:38', 1, NULL),
(34, 10, 'You have been dismissed from the role of Public Relations Officer', 'read', '2025-04-26 00:54:19', 1, NULL),
(35, 10, 'You have been dismissed from the role of Public Relations Officer', 'read', '2025-04-26 01:18:02', 1, NULL),
(36, 10, 'You have been dismissed from the role of Public Relations Officer', 'read', '2025-04-26 02:56:13', 1, NULL),
(37, 17, 'You have been appointed to the role of Public Relations Officer', 'unread', '2025-04-26 02:57:26', 0, NULL),
(38, 17, 'You have been dismissed from the role of Public Relations Officer', 'unread', '2025-04-26 02:59:15', 0, NULL),
(39, 17, 'You have been appointed to the role of Public Relations Officer', 'unread', '2025-04-26 02:59:41', 0, NULL),
(40, 17, 'You have been dismissed from the role of Public Relations Officer', 'unread', '2025-04-26 02:59:48', 0, NULL),
(41, 17, 'You have been appointed to the role of Public Relations Officer', 'unread', '2025-04-26 03:00:05', 0, NULL),
(42, 2, '[New Task] Faculty Meeting', 'read', '2025-04-26 03:02:53', 1, NULL),
(43, 2, '[New Task] Paper Marking', 'read', '2025-04-26 03:15:06', 1, NULL),
(44, 2, '[New Task] Exam Question Preparation', 'read', '2025-04-26 05:06:12', 1, NULL),
(45, 17, 'You have been dismissed from the role of Public Relations Officer', 'unread', '2025-04-26 05:15:07', 0, NULL),
(46, 10, 'You have been appointed to the role of Public Relations Officer', 'read', '2025-04-26 05:15:17', 1, NULL),
(47, 10, 'You have been dismissed from the role of Public Relations Officer', 'read', '2025-04-26 05:21:03', 1, NULL),
(48, 10, 'You have been appointed to the role of Public Relations Officer', 'read', '2025-04-26 05:22:18', 1, NULL),
(49, 11, 'You have been appointed to the role of Public Relations Officer', 'unread', '2025-04-27 05:34:11', 0, NULL),
(50, 11, 'You have been dismissed from the role of Public Relations Officer', 'unread', '2025-04-27 05:34:19', 0, NULL),
(51, 2, '[New Task] Faculty Meeting', 'read', '2025-04-27 05:36:50', 1, NULL),
(52, 3, 'You have been assigned a new report (ID: 15)', 'unread', '2025-04-27 05:37:40', 0, NULL),
(53, 10, 'You have been dismissed from the role of Public Relations Officer', 'read', '2025-04-27 07:13:12', 1, NULL),
(54, 7, 'You have been appointed to the role of SKIPPA', 'read', '2025-04-27 07:14:21', 1, NULL),
(55, 7, 'You have been dismissed from the role of SKIPPA', 'read', '2025-04-27 07:14:31', 1, NULL),
(56, 404, 'You have been assigned a new report (ID: 18)', 'unread', '2025-04-28 07:16:07', 0, NULL),
(57, 2, 'You have been assigned a new report (ID: 19)', 'read', '2025-04-28 07:16:19', 1, NULL),
(58, 44, '[New Task] Paper Marking', 'read', '2025-04-28 07:21:01', 1, NULL),
(59, 2, '[New Task] Lesson Planning', 'read', '2025-04-29 13:26:45', 1, NULL),
(60, 2, '[New Task] Testing by Zuhai', 'read', '2025-04-29 14:14:28', 1, NULL),
(61, 3, '[New Task] Testing by Zuhai', 'unread', '2025-04-29 14:14:28', 0, NULL),
(62, 2, '[New Task] Teaching a Class', 'read', '2025-04-29 14:15:04', 1, NULL),
(63, 2, 'You have been appointed to the role of Teaching and Learning Innovation Assistant Officer', 'read', '2025-04-29 14:21:41', 1, NULL),
(64, 2, 'You have been dismissed from the role of Teaching and Learning Innovation Assistant Officer', 'read', '2025-04-29 14:22:33', 1, NULL),
(65, 434, 'You have been appointed for task #1', 'unread', '2025-09-24 02:44:31', 0, 'task_management/view.php?id=1'),
(66, 434, 'An offer has been extended for task #1', 'unread', '2025-09-24 02:44:49', 0, 'task_management/view.php?id=1'),
(67, 434, 'An offer has been extended for task #2', 'unread', '2025-09-24 02:46:25', 0, 'task_management/view.php?id=2'),
(68, 434, 'An offer has been extended for task #2', 'unread', '2025-09-24 02:47:00', 0, 'task_management/view.php?id=2'),
(69, 434, 'An offer has been extended for task #2', 'unread', '2025-09-24 06:34:45', 0, 'task_management/view.php?id=2'),
(70, 420, 'Your task #2 (\"test 2\") was permanently deleted by an admin.', 'read', '2025-09-24 06:46:00', 1, 'task_management/index.php'),
(71, 434, 'The task #2 (\"test 2\") you were assigned to was permanently deleted by an admin.', 'unread', '2025-09-24 06:46:00', 0, 'task_management/index.php'),
(72, 434, 'An offer has been extended for task #1', 'unread', '2025-09-24 07:09:01', 0, 'task_management/view.php?id=1'),
(73, 434, 'Your task #1 (\"test\") was permanently deleted by an admin.', 'unread', '2025-09-24 07:10:51', 0, 'task_management/task_request.php'),
(74, 434, 'The task #1 (\"test\") you were assigned to was permanently deleted by an admin.', 'unread', '2025-09-24 07:10:51', 0, 'task_management/task_request.php'),
(75, 434, 'Your task #1 (\"test\") was permanently deleted by an admin.', 'unread', '2025-09-24 07:13:23', 0, 'task_management/task_request.php'),
(76, 434, 'The task #1 (\"test\") you were assigned to was permanently deleted by an admin.', 'unread', '2025-09-24 07:13:23', 0, 'task_management/task_request.php'),
(77, 314, 'You have been appointed for task #4', 'unread', '2025-09-24 08:20:46', 0, 'task_management/view.php?id=4'),
(78, 420, 'Your task #1 (\"test\") was permanently deleted by an admin.', 'read', '2025-09-24 12:50:05', 1, 'task_management/task_request.php'),
(79, 434, 'The task #1 (\"test\") you were assigned to was permanently deleted by an admin.', 'unread', '2025-09-24 12:50:05', 0, 'task_management/task_request.php'),
(80, 434, 'You have been assigned a new task: tess', 'unread', '2025-09-25 20:26:14', 0, '/task_management/process_task.php?action=view&id=2'),
(81, 434, 'You have been assigned a new task: asasasd', 'unread', '2025-09-25 20:39:29', 0, '/task_management/process_task.php?action=view&id=3'),
(82, 434, 'You have been assigned a new task: teeaadfad', 'unread', '2025-09-26 04:08:40', 0, '/task_management/process_task.php?action=view&id=4'),
(83, 434, 'You have been assigned a new task: ghjasdkghjlasd', 'unread', '2025-09-28 11:12:26', 0, '/task_management/process_task.php?action=view&id=5'),
(84, 434, 'You have been assigned a new task: 123123', 'unread', '2025-09-29 09:15:13', 0, '/task_management/process_task.php?action=view&id=1'),
(85, 434, 'You have been assigned a new task: 123123', 'unread', '2025-09-29 13:38:35', 0, '/task_management/process_task.php?action=view&id=2'),
(86, 403, 'You have been assigned a new task: Exam', 'unread', '2025-09-30 03:25:40', 0, '/task_management/process_task.php?action=view&id=3'),
(87, 403, 'You have been assigned a new task: exam e', 'unread', '2025-09-30 03:49:21', 0, '/task_management/process_task.php?action=view&id=4'),
(88, 403, 'You have been assigned a new task: Test', 'unread', '2025-10-01 06:19:25', 0, '/task_management/process_task.php?action=view&id=5'),
(89, 403, 'You have been assigned a new task: testss', 'unread', '2025-10-01 11:37:01', 0, '/task_management/process_task.php?action=view&id=6'),
(90, 403, 'You have been assigned a new task: tesss', 'unread', '2025-10-03 06:46:22', 0, '/task_management/process_task.php?action=view&id=7'),
(91, 420, 'You have been appointed to the role of Head of School', 'read', '2025-10-03 08:02:48', 1, NULL),
(92, 420, 'You have been appointed to the role of Head of Department', 'read', '2025-10-03 08:21:14', 1, NULL),
(93, 420, 'You have been appointed to the role of Innovation and Entrepreneurship Officer', 'read', '2025-10-03 08:21:14', 1, NULL),
(94, 403, 'You have been assigned a new task: Exam Invigilator', 'unread', '2025-10-03 12:50:49', 0, '/task_management/process_task.php?action=view&id=8');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `report_to` int NOT NULL,
  `report_what` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `other_report` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','resolved','unresolved','ignored') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `report_to`, `report_what`, `other_report`, `remarks`, `file_path`, `created_at`, `status`) VALUES
(1, 1, 3, 'truancy', NULL, 'This student has been absent and needed allowance cut. He has not given any single letter', NULL, '2025-03-11 12:40:04', 'resolved'),
(2, 1, 3, 'truancy', NULL, 'This student has been absent and needed allowance cut. He has not given any single letter', NULL, '2025-03-11 12:42:09', 'resolved'),
(3, 1, 8, 'bullying', NULL, '...', NULL, '2025-03-11 12:45:02', 'pending'),
(4, 1, 8, '0', NULL, NULL, NULL, '2025-03-11 12:58:29', 'pending'),
(5, 1, 8, '0', NULL, NULL, NULL, '2025-03-11 13:05:45', 'pending'),
(6, 1, 8, '0', NULL, '...', NULL, '2025-03-11 13:13:12', 'pending'),
(7, 1, 2, '0', NULL, 'test 1 ', NULL, '2025-03-11 23:34:31', 'pending'),
(8, 1, 6, '0', NULL, 'test 2 ', NULL, '2025-03-11 23:40:59', 'pending'),
(9, 1, 6, '0', NULL, 'test 3', 'uploads/reports/1741736734_67d0cb1e18a67.pdf', '2025-03-11 23:45:34', 'pending'),
(10, 1, 2, '0', NULL, 'test 4', 'uploads/reports/1741739499_67d0d5eb29254.jpg', '2025-03-12 00:31:39', 'pending'),
(11, 1, 8, '0', NULL, 'test', 'uploads/reports/1742170600_67d769e8c8144.jpg', '2025-03-17 00:16:40', 'pending'),
(12, 2, 3, 'broken-furniture', NULL, 'B11 air conditioner is not working please fix it', 'uploads/reports/1745279585_6806da6173d75.png', '2025-04-21 23:53:05', 'ignored'),
(13, 3, 1, 'other', 'This is just a test', 'We are making sure that this feature  function correctly', NULL, '2025-04-22 02:16:55', 'resolved'),
(14, 2, 1, 'plumbing', NULL, 'Testing', 'uploads/reports/1745452070_68097c26597e5.png', '2025-04-23 23:47:50', 'resolved'),
(15, 1, 3, 'hazardous-materials', NULL, 'fallen lamps', NULL, '2025-04-27 05:37:40', 'pending'),
(18, 1, 404, 'bullying', NULL, 'not good bully', NULL, '2025-04-28 07:16:07', 'pending'),
(19, 1, 2, 'student-fight', NULL, 'yes', NULL, '2025-04-28 07:16:19', 'ignored');

-- --------------------------------------------------------

--
-- Table structure for table `resource_files`
--

CREATE TABLE `resource_files` (
  `id` int NOT NULL,
  `section_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `opened_at` datetime DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_link` tinyint(1) DEFAULT '0',
  `link_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resource_files`
--

INSERT INTO `resource_files` (`id`, `section_id`, `title`, `description`, `file_path`, `opened_at`, `due_at`, `uploaded_at`, `is_link`, `link_url`) VALUES
(7, 14, 'new', '<p>new</p>', NULL, NULL, NULL, '2025-04-24 08:43:39', 0, NULL),
(8, 15, 'another file', '<p>another file</p>', NULL, NULL, NULL, '2025-04-24 08:45:37', 0, NULL),
(13, 20, 'New Test', '<p>This is a test</p>', '68113b0bbbce9_24S2-G11_Poster.pdf', NULL, NULL, '2025-04-30 04:48:11', 0, NULL),
(14, 22, 'New', '<p>NEw</p>', NULL, NULL, NULL, '2025-04-30 05:00:19', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `resource_logs`
--

CREATE TABLE `resource_logs` (
  `id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource_sections`
--

CREATE TABLE `resource_sections` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `visible_to` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'All',
  `role_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resource_sections`
--

INSERT INTO `resource_sections` (`id`, `title`, `created_at`, `visible_to`, `role_id`) VALUES
(13, 'FIRST', '2025-04-23 16:11:00', 'All', 434),
(14, 'FIRST', '2025-04-23 16:15:49', 'All', 357),
(15, 'new', '2025-04-23 16:38:29', 'All', 357),
(16, 'NEW', '2025-04-23 16:42:51', 'All', 433),
(20, 'Testing', '2025-04-29 20:47:57', 'All', 437),
(22, 'new', '2025-04-29 21:00:06', 'All', 432);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `department_id`) VALUES
(473, 'Administrative Officer', 20),
(417, 'Assistant Librarian', 21),
(401, 'Assistant of Head of School Academic', 15),
(439, 'Assistant of Head of School Academic', 16),
(453, 'Assistant of Head of School Academic', 17),
(493, 'Assistant of Head of School Academic', 18),
(466, 'Assistant of Head of School Academic', 19),
(402, 'Assistant of Head of School Administration', 15),
(440, 'Assistant of Head of School Administration', 16),
(454, 'Assistant of Head of School Administration', 17),
(494, 'Assistant of Head of School Administration', 18),
(467, 'Assistant of Head of School Administration', 19),
(408, 'Assistant Programme Leader (Application Development)', 15),
(469, 'Assistant Programme Leader (Applied Science Technology)', 19),
(447, 'Assistant Programme Leader (Apprenticeship Hospitality Management and Operations)', 16),
(448, 'Assistant Programme Leader (Business Accounting and Finance)', 16),
(504, 'Assistant Programme Leader (Cardiovascular Technology)', 18),
(411, 'Assistant Programme Leader (Cloud and Networking)', 15),
(409, 'Assistant Programme Leader (Data Analytics)', 15),
(410, 'Assistant Programme Leader (Digital Media)', 15),
(446, 'Assistant Programme Leader (Entrepreneurship and Marketing Strategies)', 16),
(445, 'Assistant Programme Leader (Human Capital Management)', 16),
(500, 'Assistant Programme Leader (Midwifery)', 18),
(501, 'Assistant Programme Leader (Nursing)', 18),
(502, 'Assistant Programme Leader (Paramedic)', 18),
(503, 'Assistant Programme Leader (Public Health)', 18),
(412, 'Assistant Programme Leader (Web Technology)', 15),
(346, 'Assistant Registrar', 1),
(355, 'Counselor', 3),
(350, 'Curriculum Development Officer', 2),
(369, 'Employer Satisfaction Study Specialist', 6),
(428, 'Estate Management Assistant Officer', 13),
(427, 'Estate Management Officer', 13),
(384, 'Finance Assistant Officer', 9),
(383, 'Finance Officer', 9),
(368, 'Graduate Destination Study Analyst', 6),
(367, 'Graduate Satisfaction Study C;rdinator', 6),
(376, 'Head of Department', 8),
(382, 'Head of Department', 9),
(388, 'Head of Department', 10),
(420, 'Head of Department', 11),
(394, 'Head of Department', 12),
(426, 'Head of Department', 13),
(432, 'Head of Department', 14),
(400, 'Head of School', 15),
(438, 'Head of School', 16),
(452, 'Head of School', 17),
(492, 'Head of School', 18),
(465, 'Head of School', 19),
(378, 'Human Resources Assistant Officer', 8),
(377, 'Human Resources Officer', 8),
(362, 'Industrial Training Assistant Coordinator', 5),
(361, 'Industrial Training Coordinator', 5),
(363, 'Industry Liaison Officer', 5),
(374, 'Innovation and Entrepreneurship Officer', 7),
(370, 'Institutional Research Support Officer', 6),
(375, 'Internship', 7),
(396, 'IT Assistant Officer', 12),
(395, 'IT Officer', 12),
(418, 'Learning Resource Coordinator', 21),
(414, 'Lecturer (Secondment)', 15),
(413, 'Lecturer (Teaching)', 15),
(474, 'Lecturers', 20),
(450, 'Lecturers (Secondment)', 16),
(463, 'Lecturers (Secondment)', 17),
(506, 'Lecturers (Secondment)', 18),
(471, 'Lecturers (Secondment)', 19),
(449, 'Lecturers (Teaching)', 16),
(462, 'Lecturers (Teaching)', 17),
(505, 'Lecturers (Teaching)', 18),
(470, 'Lecturers (Teaching)', 19),
(390, 'Legal Assistant Officer', 10),
(389, 'Legal Officer', 10),
(416, 'Librarian', 21),
(366, 'Module Evaluation Officer', 6),
(349, 'Programme Development Officer', 2),
(403, 'Programme Leader (Application Development)', 15),
(468, 'Programme Leader (Applied Science Technology)', 19),
(443, 'Programme Leader (Apprenticeship Hospitality Management and Operations)', 16),
(455, 'Programme Leader (Architecture)', 17),
(444, 'Programme Leader (Business Accounting and Finance)', 16),
(499, 'Programme Leader (Cardiovascular Technology)', 18),
(460, 'Programme Leader (Civil Engineering)', 17),
(406, 'Programme Leader (Cloud and Networking)', 15),
(404, 'Programme Leader (Data Analytics)', 15),
(405, 'Programme Leader (Digital Media)', 15),
(457, 'Programme Leader (Electrical Engineering)', 17),
(461, 'Programme Leader (Electronic and Communication Engineering)', 17),
(442, 'Programme Leader (Entrepreneurship and Marketing Strategies)', 16),
(441, 'Programme Leader (Human Capital Management)', 16),
(456, 'Programme Leader (Interior Design)', 17),
(458, 'Programme Leader (Mechanical Engineering)', 17),
(495, 'Programme Leader (Midwifery)', 18),
(496, 'Programme Leader (Nursing)', 18),
(497, 'Programme Leader (Paramedic)', 18),
(459, 'Programme Leader (Petroleum Engineering)', 17),
(498, 'Programme Leader (Public Health)', 18),
(407, 'Programme Leader (Web Technology)', 15),
(422, 'Public Relations Assistant Officer', 11),
(421, 'Public Relations Officer', 11),
(358, 'Quality Control Officer', 4),
(345, 'Registrar', 1),
(347, 'Secondment', 1),
(351, 'Secondment', 2),
(356, 'Secondment', 3),
(359, 'Secondment', 4),
(364, 'Secondment', 5),
(371, 'Secondment', 6),
(348, 'SKIPPA', 1),
(352, 'SKIPPA', 2),
(357, 'SKIPPA', 3),
(360, 'SKIPPA', 4),
(365, 'SKIPPA', 5),
(372, 'SKIPPA', 6),
(381, 'SKIPPA', 8),
(387, 'SKIPPA', 9),
(393, 'SKIPPA', 10),
(425, 'SKIPPA', 11),
(399, 'SKIPPA', 12),
(431, 'SKIPPA', 13),
(437, 'SKIPPA', 14),
(379, 'Staff', 8),
(385, 'Staff', 9),
(391, 'Staff', 10),
(423, 'Staff', 11),
(397, 'Staff', 12),
(429, 'Staff', 13),
(435, 'Staff', 14),
(354, 'Student Discipline and Attendance Officer', 3),
(373, 'Student Enrichment Officer', 7),
(353, 'Student Welfare Officer', 3),
(434, 'Teaching and Learning Innovation Assistant Officer', 14),
(433, 'Teaching and Learning Innovation Officer', 14),
(380, 'Technical Assistant', 8),
(386, 'Technical Assistant', 9),
(392, 'Technical Assistant', 10),
(424, 'Technical Assistant', 11),
(398, 'Technical Assistant', 12),
(430, 'Technical Assistant', 13),
(436, 'Technical Assistant', 14),
(415, 'Technical Assistant', 15),
(451, 'Technical Assistant', 16),
(464, 'Technical Assistant', 17),
(507, 'Technical Assistant', 18),
(472, 'Technical Assistant', 19),
(475, 'Technical Assistant', 20),
(419, 'Technical Assistant', 21);

-- --------------------------------------------------------

--
-- Table structure for table `role_appeals`
--

CREATE TABLE `role_appeals` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `appeal_type` enum('removal','change','objection') NOT NULL,
  `reason` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_assignment_requests`
--

CREATE TABLE `role_assignment_requests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `requested_by` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `description` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_feedback`
--

CREATE TABLE `role_feedback` (
  `id` int NOT NULL,
  `userrole_user_id` int NOT NULL,
  `userrole_role_id` int NOT NULL,
  `feedback_type` enum('approval','denial','general') NOT NULL,
  `feedback_message` text NOT NULL,
  `given_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_history`
--

CREATE TABLE `role_history` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `assigned_at` datetime NOT NULL,
  `removed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_history`
--

INSERT INTO `role_history` (`id`, `user_id`, `role_id`, `assigned_at`, `removed_at`) VALUES
(3, 10, 421, '2025-04-26 13:22:18', '2025-04-27 15:13:12'),
(5, 1, 376, '2025-04-25 01:43:42', NULL),
(6, 2, 399, '2025-04-25 01:43:42', NULL),
(7, 2, 462, '2025-04-25 01:43:42', NULL),
(8, 3, 353, '2025-04-25 01:43:42', NULL),
(9, 4, 355, '2025-04-25 01:43:42', NULL),
(10, 5, 414, '2025-04-25 01:43:42', NULL),
(11, 6, 363, '2025-04-25 01:43:42', NULL),
(12, 7, 383, '2025-04-25 01:43:42', NULL),
(13, 8, 473, '2025-04-25 01:43:42', NULL),
(14, 9, 389, '2025-04-25 01:43:42', NULL),
(15, 10, 413, '2025-04-25 01:43:42', NULL),
(16, 10, 421, '2025-04-26 13:22:18', '2025-04-27 15:13:12'),
(17, 11, 401, '2025-04-25 01:43:42', NULL),
(18, 12, 417, '2025-04-25 01:43:42', NULL),
(19, 13, 439, '2025-04-25 01:43:42', NULL),
(20, 14, 453, '2025-04-25 01:43:42', NULL),
(21, 15, 474, '2025-04-25 01:43:42', NULL),
(22, 16, 493, '2025-04-25 01:43:42', NULL),
(23, 17, 466, '2025-04-25 01:43:42', NULL),
(24, 18, 402, '2025-04-25 01:43:42', NULL),
(25, 19, 416, '2025-04-25 01:43:42', NULL),
(26, 20, 440, '2025-04-25 01:43:42', NULL),
(27, 21, 454, '2025-04-25 01:43:42', NULL),
(28, 22, 450, '2025-04-25 01:43:42', NULL),
(29, 23, 494, '2025-04-25 01:43:42', NULL),
(30, 24, 467, '2025-04-25 01:43:42', NULL),
(31, 25, 408, '2025-04-25 01:43:42', NULL),
(32, 26, 469, '2025-04-25 01:43:42', NULL),
(33, 27, 447, '2025-04-25 01:43:42', NULL),
(34, 28, 448, '2025-04-25 01:43:42', NULL),
(35, 29, 463, '2025-04-25 01:43:42', NULL),
(36, 30, 504, '2025-04-25 01:43:42', NULL),
(37, 31, 411, '2025-04-25 01:43:42', NULL),
(38, 32, 506, '2025-04-25 01:43:42', NULL),
(39, 33, 395, '2025-04-25 01:43:42', NULL),
(40, 34, 409, '2025-04-25 01:43:42', NULL),
(41, 35, 410, '2025-04-25 01:43:42', NULL),
(42, 36, 446, '2025-04-25 01:43:42', NULL),
(43, 37, 445, '2025-04-25 01:43:42', NULL),
(44, 38, 500, '2025-04-25 01:43:42', NULL),
(45, 39, 471, '2025-04-25 01:43:42', NULL),
(46, 40, 501, '2025-04-25 01:43:42', NULL),
(47, 41, 502, '2025-04-25 01:43:42', NULL),
(48, 42, 449, '2025-04-25 01:43:42', NULL),
(49, 43, 462, '2025-04-25 01:43:42', NULL),
(50, 44, 503, '2025-04-25 01:43:42', NULL),
(51, 45, 412, '2025-04-25 01:43:42', NULL),
(52, 11, 421, '2025-04-27 13:34:11', NULL),
(53, 7, 352, '2025-04-27 15:14:21', '2025-04-27 15:14:31'),
(54, 2, 434, '2025-04-29 22:21:41', '2025-04-29 22:22:33'),
(55, 420, 400, '2025-10-03 16:02:48', NULL),
(56, 420, 432, '2025-10-03 16:21:14', NULL),
(57, 420, 374, '2025-10-03 16:21:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_kpis`
--

CREATE TABLE `role_kpis` (
  `id` int NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `kpi_name` varchar(255) NOT NULL,
  `description` text,
  `target` varchar(255) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT '1.00',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_requests`
--

CREATE TABLE `role_requests` (
  `id` int NOT NULL,
  `role_id` int NOT NULL,
  `requested_user_id` int NOT NULL,
  `requested_by` int DEFAULT NULL,
  `status` enum('pending','approved','denied') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `processed_by` int DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_requirements`
--

CREATE TABLE `role_requirements` (
  `id` int NOT NULL,
  `role_id` int DEFAULT NULL,
  `requirement_type` enum('education','experience') NOT NULL,
  `keyword` text NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_resources`
--

CREATE TABLE `role_resources` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_id` int NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `user_id`, `image_path`, `uploaded_at`) VALUES
(1, 1, '../schedule/uploads/schedule_1.jpg', '2025-04-27 05:36:02'),
(2, 2, '../schedule/uploads/schedule 2.jpg', '2025-03-13 00:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `signaling`
--

CREATE TABLE `signaling` (
  `id` int NOT NULL,
  `meeting_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `type` enum('offer','answer','candidate') NOT NULL,
  `sdp` text,
  `candidate` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `processed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int NOT NULL,
  `staff_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `staff_office` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `staff_role` int DEFAULT NULL,
  `role_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'low',
  `status` enum('pending_approval','pending','in_progress','pending_review','completed','archived','on_hold') DEFAULT 'pending',
  `previous_status` enum('pending_approval','pending','in_progress','pending_review','completed','archived','on_hold') DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `start_date`, `end_date`, `start_time`, `end_time`, `priority`, `status`, `previous_status`, `created_by`, `created_at`) VALUES
(1, '123123', '123123', '2025-09-29', '2025-09-29', '17:17:00', '17:19:00', 'low', 'archived', 'pending', 420, '2025-09-29 17:15:13'),
(2, '123123', '', '2025-09-05', '2025-10-11', NULL, NULL, 'medium', 'pending_review', NULL, 420, '2025-09-29 21:38:35'),
(3, 'Exam', 'Task Type: Exam Question Preparation\n\nExam', '2025-09-30', NULL, NULL, NULL, 'high', 'completed', NULL, 420, '2025-09-30 11:25:40'),
(4, 'exam e', 'Task Type: Exam Question Preparation\n\nExam', '2025-09-30', '2025-10-16', NULL, NULL, 'high', 'archived', 'pending_review', 420, '2025-09-30 11:49:21'),
(5, 'Test', 'Task Type: Test\n\nTest', '2025-09-11', '2025-09-29', NULL, NULL, 'low', 'completed', NULL, 420, '2025-10-01 14:19:25'),
(6, 'testss', 'Task Type: Substituting a Teacher\n\ntest', '2025-10-03', '2025-10-04', NULL, NULL, 'low', 'pending_review', NULL, 420, '2025-10-01 19:37:01'),
(7, 'tesss', 'Task Type: wwwwww\n\nwwwww', '2025-10-17', '2025-10-30', NULL, NULL, 'medium', 'pending_approval', NULL, 420, '2025-10-03 14:46:22'),
(8, 'Exam Invigilator', 'Task Type: Exam Supervision\n\nSupervise the students during the exam day.', '2025-11-01', '2025-11-08', NULL, NULL, 'medium', 'pending_approval', NULL, 420, '2025-10-03 20:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `task_appeals`
--

CREATE TABLE `task_appeals` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `reason` text,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_appeal_attachments`
--

CREATE TABLE `task_appeal_attachments` (
  `id` int NOT NULL,
  `appeal_id` int NOT NULL,
  `stored_name` varchar(512) NOT NULL,
  `original_name` varchar(512) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` int DEFAULT '0',
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_assignments`
--

INSERT INTO `task_assignments` (`id`, `task_id`, `user_id`, `assigned_at`) VALUES
(1, 1, 434, '2025-09-29 17:15:13'),
(2, 2, 434, '2025-09-29 21:38:35'),
(3, 3, 403, '2025-09-30 11:25:40'),
(4, 4, 403, '2025-09-30 11:49:21'),
(5, 5, 403, '2025-10-01 14:19:25'),
(6, 6, 403, '2025-10-01 19:37:01'),
(7, 7, 403, '2025-10-03 14:46:22'),
(8, 8, 403, '2025-10-03 20:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `stored_name` varchar(512) NOT NULL,
  `original_name` varchar(512) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` int DEFAULT '0',
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_attachments`
--

INSERT INTO `task_attachments` (`id`, `task_id`, `stored_name`, `original_name`, `mime_type`, `size`, `uploaded_at`) VALUES
(1, 1, '965ecc41a495565548593603d95ca066.png', 'Screenshot 2025-08-03 211726.png', 'image/png', 169262, '2025-09-29 17:15:13'),
(2, 2, 'adbb3bd8468f5b6b64b57d1d3f49f293.png', 'Screenshot 2025-08-03 211726.png', 'image/png', 169262, '2025-09-29 21:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `is_edited` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `user_id`, `comment`, `created_at`, `updated_at`, `is_edited`) VALUES
(1, 1, 420, 'noice', '2025-09-29 21:36:59', NULL, 0),
(2, 4, 403, 'fix this', '2025-09-30 11:53:28', NULL, 0),
(3, 4, 403, 'test 2', '2025-10-01 08:10:18', NULL, 0),
(4, 4, 403, 'pleasae fix the issue.', '2025-10-01 08:23:01', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `task_dependencies`
--

CREATE TABLE `task_dependencies` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `depends_on_task_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_history`
--

CREATE TABLE `task_history` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `action` varchar(120) NOT NULL,
  `actor_id` int DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_history`
--

INSERT INTO `task_history` (`id`, `task_id`, `action`, `actor_id`, `notes`, `created_at`) VALUES
(1, 1, 'created', 420, 'Task created and assigned', '2025-09-29 17:15:13'),
(2, 1, 'archived', 420, 'Task archived by super_admin', '2025-09-29 17:38:26'),
(3, 2, 'created', 420, 'Task created and assigned', '2025-09-29 21:38:35'),
(4, 2, 'marked_complete', 434, 'User marked task complete; awaiting admin review', '2025-09-29 21:39:06'),
(5, 2, 'marked_complete', 434, 'User marked task complete; awaiting admin review', '2025-09-29 21:39:17'),
(6, 2, 'marked_complete', 434, 'User marked task complete; awaiting admin review', '2025-09-30 08:10:12'),
(7, 2, 'marked_complete', 403, 'User marked task complete; awaiting admin review', '2025-09-30 10:57:47'),
(8, 3, 'created', 420, 'Task created and assigned', '2025-09-30 11:25:40'),
(9, 4, 'created', 420, 'Task created and assigned', '2025-09-30 11:49:21'),
(10, 4, 'marked_complete', 403, 'User marked task complete; awaiting admin review', '2025-09-30 11:50:25'),
(11, 4, 'marked_complete', 420, 'User marked task complete; awaiting admin review', '2025-09-30 11:50:37'),
(12, 4, 'archived', 420, 'Task archived by super_admin', '2025-09-30 11:52:41'),
(13, 3, 'marked_complete', 403, 'User marked task complete; awaiting admin review', '2025-10-01 12:30:10'),
(14, 3, 'completion_approved', 403, '', '2025-10-01 12:30:18'),
(15, 5, 'created', 420, 'Task created and assigned', '2025-10-01 14:19:25'),
(16, 5, 'marked_complete', 420, 'User marked task complete; awaiting admin review', '2025-10-01 14:20:28'),
(17, 5, 'marked_complete', 420, 'User marked task complete; awaiting admin review', '2025-10-01 14:20:34'),
(18, 5, 'completion_approved', 420, '', '2025-10-01 14:20:41'),
(21, 6, 'created', 420, 'Task created and assigned', '2025-10-01 19:37:01'),
(22, 6, 'marked_complete', 403, 'User marked task complete; awaiting admin review', '2025-10-01 20:09:44'),
(25, 6, 'punch_in', 403, 'Punched in', '2025-10-01 20:17:42'),
(26, 7, 'created', 420, 'Task created and assigned', '2025-10-03 14:46:22'),
(27, 8, 'created', 420, 'Task created and assigned', '2025-10-03 20:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `task_history_time_tracking_backup`
--

CREATE TABLE `task_history_time_tracking_backup` (
  `id` int NOT NULL DEFAULT '0',
  `task_id` int NOT NULL,
  `action` varchar(120) NOT NULL,
  `actor_id` int DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_history_time_tracking_backup`
--

INSERT INTO `task_history_time_tracking_backup` (`id`, `task_id`, `action`, `actor_id`, `notes`, `created_at`) VALUES
(19, 5, 'time_tracking_started', 403, 'Started time tracking', '2025-10-01 14:23:36'),
(20, 5, 'time_tracking_stopped', 403, 'Stopped time tracking (0 minutes)', '2025-10-01 14:23:42'),
(23, 6, 'time_tracking_started', 403, 'Started time tracking', '2025-10-01 20:10:44'),
(24, 6, 'time_tracking_stopped', 403, 'Stopped time tracking (0 minutes)', '2025-10-01 20:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `task_notifications`
--

CREATE TABLE `task_notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `task_id` int NOT NULL,
  `message` text NOT NULL,
  `type` enum('assignment','completion','appeal','comment','reminder') DEFAULT 'assignment',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_notifications`
--

INSERT INTO `task_notifications` (`id`, `user_id`, `task_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 434, 1, 'Mohamad Norsyahmin Adillah Bin ABD Latif commented on task: noice', 'comment', 0, '2025-09-29 21:36:59');

-- --------------------------------------------------------

--
-- Table structure for table `task_templates`
--

CREATE TABLE `task_templates` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `template_data` json NOT NULL,
  `created_by` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_time_logs_backup`
--

CREATE TABLE `task_time_logs_backup` (
  `id` int NOT NULL DEFAULT '0',
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_time_logs_backup`
--

INSERT INTO `task_time_logs_backup` (`id`, `task_id`, `user_id`, `start_time`, `end_time`, `duration_minutes`, `description`, `created_at`) VALUES
(1, 5, 403, '2025-10-01 14:23:36', '2025-10-01 14:23:42', 0, '', '2025-10-01 14:23:36'),
(2, 6, 403, '2025-10-01 20:10:44', '2025-10-01 20:10:51', 0, '', '2025-10-01 20:10:44'),
(3, 6, 403, '2025-10-01 20:17:42', '2025-10-01 20:17:55', 0, NULL, '2025-10-01 20:17:42');

-- --------------------------------------------------------

--
-- Table structure for table `userroles`
--

CREATE TABLE `userroles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `appointed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `appointed_by` int DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `appointment_status` enum('pending','accepted','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `response_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userroles`
--

INSERT INTO `userroles` (`user_id`, `role_id`, `appointed_at`, `appointed_by`, `remarks`, `appointment_status`, `rejection_reason`, `response_date`) VALUES
(1, 376, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(2, 399, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(2, 462, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(3, 353, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(4, 355, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(5, 414, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(6, 363, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(7, 383, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(8, 473, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(9, 389, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(10, 413, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(11, 401, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(12, 417, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(13, 439, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(14, 453, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(15, 474, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(16, 493, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(17, 466, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(18, 402, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(19, 416, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(20, 440, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(21, 454, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(22, 450, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(23, 494, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(24, 467, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(25, 408, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(26, 469, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(27, 447, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(28, 448, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(29, 463, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(30, 504, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(31, 411, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(32, 506, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(33, 395, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(34, 409, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(35, 410, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(36, 446, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(37, 445, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(38, 500, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(39, 471, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(40, 501, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(41, 502, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(42, 449, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(43, 462, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(44, 503, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(45, 412, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(46, 346, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(47, 350, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(48, 369, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(49, 505, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(50, 428, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(51, 427, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(52, 384, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(53, 368, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(54, 367, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(55, 376, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(56, 382, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(57, 388, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(58, 470, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(59, 420, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(60, 394, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(61, 426, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(62, 432, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(63, 349, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(64, 400, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(65, 438, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(66, 452, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(67, 492, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(68, 465, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(69, 378, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(70, 377, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(71, 362, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(72, 361, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(73, 374, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(74, 370, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(75, 375, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(76, 396, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(77, 418, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(78, 390, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(79, 366, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(80, 403, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(82, 443, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(83, 455, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(84, 444, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(85, 499, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(86, 460, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(87, 406, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(88, 404, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(89, 405, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(90, 457, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(91, 461, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(92, 442, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(93, 441, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(94, 456, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(95, 458, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(96, 495, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(97, 496, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(98, 497, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(99, 459, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(100, 498, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(101, 407, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(102, 422, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(103, 421, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(104, 358, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(105, 345, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(106, 347, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(107, 351, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(108, 356, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(109, 359, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(110, 364, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(111, 371, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(112, 348, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(113, 352, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(114, 357, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(115, 360, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(116, 365, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(117, 372, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(118, 381, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(119, 387, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(120, 393, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(121, 425, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(122, 399, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(123, 431, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(124, 437, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(125, 379, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(126, 385, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(127, 391, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(128, 423, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(129, 397, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(130, 429, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(131, 435, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(132, 354, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(133, 373, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(134, 353, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(135, 434, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(136, 433, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(137, 380, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(138, 386, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(139, 392, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(140, 424, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(141, 398, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(142, 430, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(143, 436, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(144, 415, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(145, 451, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(146, 464, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(147, 507, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(148, 472, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(149, 475, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(150, 419, '2025-04-25 01:43:42', NULL, NULL, 'pending', NULL, NULL),
(406, 398, '2025-08-16 11:17:49', NULL, NULL, 'pending', NULL, NULL),
(420, 374, '2025-10-03 16:21:14', 420, NULL, 'pending', NULL, NULL),
(420, 400, '2025-10-03 16:02:48', 420, NULL, 'pending', NULL, NULL),
(420, 432, '2025-10-03 16:21:14', 420, NULL, 'pending', NULL, NULL),
(422, 393, '2025-08-21 12:18:30', NULL, NULL, 'pending', NULL, NULL),
(433, 382, '2025-08-25 14:08:32', NULL, NULL, 'pending', NULL, NULL),
(434, 384, '2025-09-04 21:37:57', NULL, NULL, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `work_experience` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `education` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `profile_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '../profile/images/default-profile.jpg',
  `user_type` enum('admin','regular','super_admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'regular',
  `office` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `start_date`, `last_login`, `work_experience`, `education`, `profile_pic`, `user_type`, `office`, `is_verified`, `must_change_password`) VALUES
(1, 'Hajah Zainab bin Haji Rahman', 'zainab.rahman@pb.edu.bn', '$2y$10$EWIUzegqCU.mXkJ4HhtX1.R2WOFeT.Asy6ZT/q8rMkfJh114TLbri', '2015-09-19', NULL, 'Head of School Information and Communication Technology @Politeknik Brunei (Jan 2015 - Present)\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\nAssistant Engineer @MINDEF (Apr 2009 - Jul 2010)', 'Institut Teknologi Brunei, Bachelor\'s Degree in Internet Computing (2010 - 2013)\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\nMaktab Teknik Sultan Saiful Rijal, National Diploma in Computer Studies (2003 - 2006)', 'profile/images/default-profile.jpg', 'admin', 'OSP2', 0, 1),
(2, 'Dayangku Farah binti Pengiran Ismail', 'farah.ismail@pb.edu.bn', '$2y$12$IIHREbyceqgJmpZ3E5rINebxVlcsZlPzZu.5k/vm/elbD3rjoYILu', '2017-02-15', NULL, 'Education Officer, Information Technology Department @Politeknik Brunei (Sep 2015 - Present)\r\n\r\n\r\n\r\nSystem Support Engineer @Ishajaya Technology (Feb 2015 - Jul 2015)\r\n\r\n\r\n\r\nApprentice @Brunei Shell Petroleum (Jun 2010 - Jul 2011)\r\n\r\n\r\n\r\n', 'University of East Anglia, Bachelor of Engineering in Computer Science Engineering (2011-2014)\r\n\r\n\r\n\r\n', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 1, 0),
(3, 'Muhammad Nazri bin Haji Anwar', 'nazri.anwar@pb.edu.bn', '$6$DnTtT87a.qjtt4Yl$EUMGN6r9GAyM5Qcqys7mA3ebbCN68pP9GcW9oNmmZqDa6u/MySNa6etqHWJ4GT.zx5WbgJKUpKCjujmbGeBVb.', '2022-05-19', NULL, 'Education Officer, Student Affairs Division @Politeknik Brunei (May 2022 - Present)\r\n\r\nHealth Safety Environment Officer (Committee) @ IBTE Nakhoda Ragam Campus (Jul 2016 - Jun 2018)\r\n', 'University of Brunei Darussalam, Masterâ€™s Degree in Teaching (2019 - 2020)\r\n\r\nUniversity of Nottingham, Bachelor of Science in Electrical and Electronics Engineering (2012 - 2015)\r\n', '../profile/images/default-profile.jpg', 'admin', 'OSP10', 0, 1),
(4, 'Izzah bin Latif', 'izzah.latif@pb.edu.bn', '$2b$12$k/DNF0r1LBxi56990PTSkuV7xTUCwiDTE7GIE3xrSQmIdo2N9ckVW', '2018-01-01', NULL, 'Assistant at Local NGO (2011 – 2014); Counselor at Ministry of Health (2015 – 2017)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(5, 'Fazira binti Yaakub', 'fazira.yaakub@pb.edu.bn', '$2b$12$hvJMCVBS..IYxDlnXvkOdeFiRGEWZ8EAMtMfEojUB9pRhrjbzT6P2', '2012-01-01', NULL, 'Admin Assistant at Municipal Department (2006 – 2008); Research Assistant at Universiti Brunei Darussalam (2003 – 2005); Lecturer at Politeknik Brunei (2009 – 2011)', 'Bachelor’s in Education, Universiti Brunei Darussalam (1999 – 2002)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(6, 'Hidayah bin Tahir', 'hidayah.tahir@pb.edu.bn', '$2b$12$ZTQzjJ.EbrK8Dr//EIHkze19nFTgr1oZ8JBXA/xXmpYIJDjRd0Eye', '2020-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2014 – 2016); Clerk at Local Business (2010 – 2013); Counselor at Ministry of Health (2017 – 2019)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (2006 – 2009)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(7, 'Zarina binti Nordin', 'zarina.nordin@pb.edu.bn', '$2b$12$5d35SbG6M3bH1VzbDuKqsunyJZiexuLh/10NQJ9a3AthXtZjyEvii', '2014-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2008 – 2010); Intern at Brunei Press (2005 – 2007); Finance Executive at Baiduri Bank (2011 – 2013)', 'Bachelor’s in Business, Universiti Brunei Darussalam (2001 – 2004)', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(8, 'Nabila bin Mahmud', 'nabila.mahmud@pb.edu.bn', '$2b$12$GT452L8kqGBmZapW2RuRue1xsR.Pz9bsaonTl4dc9KRCOJ9ewHcRe', '2019-01-01', NULL, 'Assistant at Local NGO (2013 – 2015); Volunteer Coordinator at Red Crescent (2010 – 2012); Academic Coach at PB General Division (2016 – 2018)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2006 – 2009)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(9, 'Fazira binti Nordin', 'fazira.nordin@pb.edu.bn', '$2b$12$Bfk92lXGThhzHp54/5s.Ae9lIj6qFer4w8yNjSUOm/7PY9Yzdl6iO', '2015-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2009 – 2011); Intern at Brunei Press (2005 – 2008); Legal Clerk at Attorney General’s Office (2012 – 2014)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2001 – 2004)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(10, 'Jannah bin Roslan', 'jannah.roslan@pb.edu.bn', '$2b$12$c/4rgDOfgiZYShdHPnvSseB6W.HFjkoS5HuaqUNBEisiQct7q5Nxe', '2016-01-01', NULL, 'Junior Officer at Ministry of Education (2010 – 2012); Assistant at Local NGO (2007 – 2009); Media Officer at Radio Televisyen Brunei (2013 – 2015)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(11, 'Nur binti Bakar', 'nur.bakar@pb.edu.bn', '$2b$12$GDcfnqBZSN/PWTuX2l5x4eB6PufqzqTG4nX42SkFeDLk32dYWPnLK', '2021-01-01', NULL, 'Clerk at Local Business (2015 – 2017); Research Assistant at Universiti Brunei Darussalam (2011 – 2014); Media Officer at Radio Televisyen Brunei (2018 – 2020)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(12, 'Hakim bin Hassan', 'hakim.hassan@pb.edu.bn', '$2b$12$dJH97EhC5JJnPsJg8rJdGOG9Vui50tHkZIYgkHNb4NB2cCicg4Wg6', '2021-01-01', NULL, 'Intern at Brunei Press (2015 – 2017); Trainee at DST (2012 – 2014); Library Officer at UBD (2018 – 2020)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(13, 'Nurin bin Zulkifli', 'nurin.zulkifli@pb.edu.bn', '$2b$12$PCRz6h9F8MMZOOyyn5MNY.Az7ozANDvxa8n1T9/gAzMzZVyjqFp1G', '2014-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2007 – 2010); Trainee at DST (2003 – 2006); Finance Executive at Baiduri Bank (2011 – 2013)', 'Bachelor’s in Business, Universiti Brunei Darussalam (1999 – 2002)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(14, 'Syafiqah binti Ismail', 'syafiqah.ismail@pb.edu.bn', '$2b$12$HGxtLBmU5VIFltF.TZ0f0ONd7ltHjWZesoQdAIgDImvuO/hqwqX/e', '2012-01-01', NULL, 'Assistant at Local NGO (2005 – 2008); Budget Planner at Ministry of Finance (2009 – 2011)', 'Bachelor’s in Accounting, Universiti Brunei Darussalam (2001 – 2004)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(15, 'Danish binti Omar', 'danish.omar@pb.edu.bn', '$2b$12$SbuUh/6POlu2FrxXY6RFtuANDiKVMx/nezSInDFaDl6dAcWdXGt/2', '2014-01-01', NULL, 'Junior Officer at Ministry of Education (2007 – 2010); Clerk at Local Business (2004 – 2006); Property Supervisor at Housing Development Dept (2011 – 2013)', 'Bachelor’s in Estate Management, Universiti Brunei Darussalam (2000 – 2003)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(16, 'Amalina binti Abdullah', 'amalina.abdullah@pb.edu.bn', '$2b$12$EYr2OuGyx3aZbl8BJoEnieiPqK4/uc2IjPDFoV4r8NLrmnDJgNFCy', '2023-01-01', NULL, 'Assistant at Local NGO (2017 – 2019); Volunteer Coordinator at Red Crescent (2013 – 2016); Data Clerk at MoE (2020 – 2022)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(17, 'Haziq bin Aziz', 'haziq.aziz@pb.edu.bn', '$2b$12$cROXa3t61M.Z/pTpf762auyjiVXQw9.1NkqLXTuDKmpjxsnGCEdH2', '2020-01-01', NULL, 'Trainee at DST (2014 – 2016); Clerk at Local Business (2010 – 2013); Media Officer at Radio Televisyen Brunei (2017 – 2019)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2006 – 2009)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(18, 'Afiqah bin Roslan', 'afiqah.roslan@pb.edu.bn', '$2b$12$RZtIvHsJOrpBK0qzFyQy/OQ6WdNHpuxt/h3X0i.rzbdacMCBVZst6', '2018-01-01', NULL, 'Clerk at Local Business (2012 – 2014); Admin Assistant at Municipal Department (2008 – 2011); Chemical Engineer at Hengyi Industries (2015 – 2017)', 'Bachelor’s in Petrochemical Engineering, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(19, 'Faiz bin Rashid', 'faiz.rashid@pb.edu.bn', '$2b$12$swxptqBeTZbDZ/SrGhCVE..oHdSClqZxk7FwSlWKQLsp5Rhm3C3aW', '2021-01-01', NULL, 'Admin Assistant at Municipal Department (2015 – 2017); Library Officer at UBD (2018 – 2020)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2011 – 2014)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(20, 'Izzah bin Omar', 'izzah.omar@pb.edu.bn', '$2b$12$WNEwwb/YCS3qAMCsavY4AeQsFxPbSKMD1GFbIVMlDtI68se0UE.v6', '2018-01-01', NULL, 'Trainee at DST (2012 – 2014); Library Officer at UBD (2015 – 2017)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(21, 'Irwan binti Tahir', 'irwan.tahir@pb.edu.bn', '$2b$12$EpJPVBtHgJUJYGOHJbTbF.bTyBR.9C66QeXRAE1sigHm7ROMgqw3.', '2020-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2013 – 2016); Clinical Assistant at RIPAS Hospital (2017 – 2019)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(22, 'Farah binti Zulkifli', 'farah.zulkifli@pb.edu.bn', '$2b$12$SgcXYqVz/EXc4RnIzf2cQu/vieCbxoFTnlRWkYwbGUuUo9Cu1Uuee', '2018-01-01', NULL, 'Intern at Brunei Press (2011 – 2014); Lecturer at Politeknik Brunei (2015 – 2017)', 'Bachelor’s in Education, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(23, 'Hakim bin Fadzil', 'hakim.fadzil@pb.edu.bn', '$2b$12$/gjD27kWtLXETnBdSyo/7.fkpr7lva5cQ8..d3AdepUup8S72aBB.', '2020-01-01', NULL, 'Assistant at Local NGO (2013 – 2016); Data Clerk at MoE (2017 – 2019)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(24, 'Sarah binti Hassan', 'sarah.hassan@pb.edu.bn', '$2b$12$Mxp.o5HLtz5tLXj35Ntbn.qAhDB9D9SQqiQYZO74l7IHXTZFlJI0u', '2020-01-01', NULL, 'Assistant at Local NGO (2013 – 2016); Media Officer at Radio Televisyen Brunei (2017 – 2019)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(25, 'Syazwan bin Tahir', 'syazwan.tahir@pb.edu.bn', '$2b$12$A9f5VjaTwDyBk6lPB9cbO.uqma56oiSP62xZKUzr6gYydcWvrBhT2', '2018-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2011 – 2014); Data Clerk at MoE (2015 – 2017)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(26, 'Nabila bin Abdullah', 'nabila.abdullah@pb.edu.bn', '$2b$12$/fesi6Mxs5G5tUglUPtzN.SQFBlrVLKcnzZMP1G4NchBTTlxywF9K', '2015-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2009 – 2011); Trainee at DST (2006 – 2008); Data Clerk at MoE (2012 – 2014)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(27, 'Faris bin Abdullah', 'faris.abdullah@pb.edu.bn', '$2b$12$DH9JK563Xc1qzPW6cmF2l.qxh6Cz66Y5DFDpZC1vHzKAe3EC4EN2m', '2012-01-01', NULL, 'Intern at Brunei Press (2005 – 2008); Trainee at DST (2002 – 2004); Counselor at Ministry of Health (2009 – 2011)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (1998 – 2001)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(28, 'Nurin binti Tahir', 'nurin.tahir@pb.edu.bn', '$2b$12$NO5SfFn4O9uQCXlxoAd//ONbCaWX.FRfMXspto80CJQkONjAHMPy.', '2012-01-01', NULL, 'Admin Assistant at Municipal Department (2005 – 2008); Library Officer at UBD (2009 – 2011)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2001 – 2004)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(29, 'Syafiqah binti Sani', 'syafiqah.sani@pb.edu.bn', '$2b$12$/PlKj9Z4kMfItqYe9ouMG.1QjIuJkJuux/4M0pn5jiEYfCKCXRSMy', '2014-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2007 – 2010); Research Assistant at Universiti Brunei Darussalam (2004 – 2006); Lecturer at Politeknik Brunei (2011 – 2013)', 'Bachelor’s in Education, Universiti Brunei Darussalam (2000 – 2003)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(30, 'Hakim binti Zulkifli', 'hakim.zulkifli@pb.edu.bn', '$2b$12$RHpCSE8t2N5G4ftvKBc4ZutfLUnq8Maz0iwuGlBvMrBvVpdhzcvym', '2022-01-01', NULL, 'Admin Assistant at Municipal Department (2016 – 2018); Finance Executive at Baiduri Bank (2019 – 2021)', 'Bachelor’s in Business, Universiti Brunei Darussalam (2012 – 2015)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(31, 'Aisyah binti Khalid', 'aisyah.khalid@pb.edu.bn', '$2b$12$tkJDpKDNrOc79hfomqQ2uuW8uV/OTvRek6AjptqgysDe/3YkimHCG', '2021-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2015 – 2017); Clerk at Local Business (2011 – 2014); Clinical Assistant at RIPAS Hospital (2018 – 2020)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(32, 'Liyana bin Fadzil', 'liyana.fadzil@pb.edu.bn', '$2b$12$8iqOTDI32N79Bwl8iGv7vuShmhE5oEP1WrbK8nCxHemBekF3W.x.S', '2018-01-01', NULL, 'Junior Officer at Ministry of Education (2011 – 2014); System Analyst at AITI (2015 – 2017)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(33, 'Fazira bin Samsudin', 'fazira.samsudin@pb.edu.bn', '$2b$12$8EebviQOGvw1daPhvA6QbeheNygBqqmeOQxlLd04pXO2y9cZc1rW.', '2021-01-01', NULL, 'Clerk at Local Business (2014 – 2017); System Analyst at AITI (2018 – 2020)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2010 – 2013)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(34, 'Syazwan bin Ahmad', 'syazwan.hajiahmad@pb.edu.bn', '$2b$12$36vApBw0ax1GfUReCAkR0ONX6jHN.xO0RoC1twDcGPw6./J/N3Aa6', '2022-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2015 – 2018); Facilities Engineer at Public Works Department (2019 – 2021)', 'Bachelor’s in Engineering, Universiti Brunei Darussalam (2011 – 2014)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(35, 'Husna binti Latif', 'husna.latif@pb.edu.bn', '$2b$12$EmNXlzVFteHiUe2LsUgX.eum7Dno64drjiRjwXIt8kb/zUI6hHxRC', '2015-01-01', NULL, 'Admin Assistant at Municipal Department (2008 – 2011); Finance Executive at Baiduri Bank (2012 – 2014)', 'Bachelor’s in Business, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(36, 'Aqilah binti Osman', 'aqilah.osman@pb.edu.bn', '$2b$12$3eIQGClEsjyStGyfmniBDO8R73x/IY4KmoMNvV/ewpRas5.GTwrPG', '2016-01-01', NULL, 'Trainee at DST (2010 – 2012); Admin Assistant at Municipal Department (2007 – 2009); HR Executive at UNISSA (2013 – 2015)', 'Bachelor’s in Human Resource, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(37, 'Nadiah binti Khalid', 'nadiah.khalid@pb.edu.bn', '$2b$12$n0Fe2sDdW7oShZYsxLt.VukdxQylU99tLxkqCuq6ps0.4uZIvUIHi', '2020-01-01', NULL, 'Clerk at Local Business (2013 – 2016); Academic Coach at PB General Division (2017 – 2019)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(38, 'Faiz binti Ramli', 'faiz.ramli@pb.edu.bn', '$2b$12$mFRzvcHG9RqshVnbJzVyb.dY0WkUvlnux.qLzEOisGXXFJSOVz7oS', '2015-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2008 – 2011); Research Assistant at Universiti Brunei Darussalam (2004 – 2007); Counselor at Ministry of Health (2012 – 2014)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (2000 – 2003)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(39, 'Rashid binti Ramli', 'rashid.ramli@pb.edu.bn', '$2b$12$6.uWTb.J2QpfKvIC3DMu.uoKYomsdEFe29RZQJo4VnAq2OPS7oi/i', '2019-01-01', NULL, 'Intern at Brunei Press (2012 – 2015); Research Assistant at Universiti Brunei Darussalam (2008 – 2011); Lecturer at Politeknik Brunei (2016 – 2018)', 'Bachelor’s in Education, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(40, 'Nur bin Fadzil', 'nur.fadzil@pb.edu.bn', '$2b$12$i6STKv4/XOU4Ekdx4UUkfeckMVwxFmecTVLorg4dhm1vZgxuaOmuC', '2017-01-01', NULL, 'Trainee at DST (2011 – 2013); Volunteer Coordinator at Red Crescent (2007 – 2010); Library Officer at UBD (2014 – 2016)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(41, 'Ridhuan bin Rashid', 'ridhuan.rashid@pb.edu.bn', '$2b$12$U2zZG9BLUHToWNz1jZeYie6V7Nv8IlVfs3v13Wm6ADhYKW.2pw4pa', '2014-01-01', NULL, 'Trainee at DST (2008 – 2010); Research Assistant at Universiti Brunei Darussalam (2004 – 2007); Legal Clerk at Attorney General’s Office (2011 – 2013)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2000 – 2003)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(42, 'Shafiq bin Salleh', 'shafiq.salleh@pb.edu.bn', '$2b$12$dQZuxh37ywY7QRY3UVEvYeAr6ojdYUZyv6JZtp.24.Apzy.fnAOxW', '2017-01-01', NULL, 'Admin Assistant at Municipal Department (2011 – 2013); Lecturer at Politeknik Brunei (2014 – 2016)', 'Bachelor’s in Education, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(43, 'Izzah binti Yusof', 'izzah.yusof@pb.edu.bn', '$2b$12$xLgfVZPao.d6Wkutv97xyOg0rQeEAeU3.o6FnJoAFqn9HjTyYIoZ.', '2019-01-01', NULL, 'Intern at Brunei Press (2012 – 2015); Intern at Brunei Press (2008 – 2011); Lecturer at Politeknik Brunei (2016 – 2018)', 'Bachelor’s in Education, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(44, 'Nadiah bin Jamil', 'nadiah.jamil@pb.edu.bn', '$2b$12$lXHVUKQKXANZGztG66C5F.2HjrgKk5bzXaLy.KcPBjDr21/4RvzNe', '2013-01-01', NULL, 'Intern at Brunei Press (2006 – 2009); Academic Coach at PB General Division (2010 – 2012)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2002 – 2005)', 'profile/images/profile_1745825191_44.png', 'regular', 'OSP1', 0, 1),
(45, 'Izzah bin Mahmud', 'izzah.mahmud@pb.edu.bn', '$2b$12$/VN31oct.iUFXswBwZzeleNfMtfQC9tm6GSqHxH7CnD9WUrbPqjfm', '2017-01-01', NULL, 'Trainee at DST (2011 – 2013); Volunteer Coordinator at Red Crescent (2008 – 2010); Library Officer at UBD (2014 – 2016)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(46, 'Izzah binti Rahman', 'izzah.rahman@pb.edu.bn', '$2b$12$nZ1JUPAigX0CUQqxKsQOFuyFrNsJE7g3YzxnxbAmX9qNDkCScGj6y', '2017-01-01', NULL, 'Clerk at Local Business (2011 – 2013); Clinical Assistant at RIPAS Hospital (2014 – 2016)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(47, 'Nurin bin Jamil', 'nurin.jamil@pb.edu.bn', '$2b$12$UxrE6BXHJ13LPfJVSMB/beWcdTHmySaZIVVvRdhcG5rRE7KMV2FTu', '2012-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2006 – 2008); Finance Executive at Baiduri Bank (2009 – 2011)', 'Bachelor’s in Business, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(48, 'Raihan bin Roslan', 'raihan.roslan@pb.edu.bn', '$2b$12$D7XCrYop06pF4Ohr8glOh.z.VEiin310N1r9FteCv1QPmB2lN1DtW', '2019-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2012 – 2015); Intern at Brunei Press (2009 – 2011); Legal Clerk at Attorney General’s Office (2016 – 2018)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2005 – 2008)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(49, 'Afiqah bin Salleh', 'afiqah.salleh@pb.edu.bn', '$2b$12$puZ1f/j4ReKct7RHjYUyZuiei0VLUpLiF.2m7x/Pu42WqEKwGQZP2', '2022-01-01', NULL, 'Junior Officer at Ministry of Education (2015 – 2018); System Analyst at AITI (2019 – 2021)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2011 – 2014)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(50, 'Azim bin Fadzil', 'azim.fadzil@pb.edu.bn', '$2b$12$4hFdG2JHizqomjqt5IL7Z.lq1p6QKqTrtG.GZ51TbmeE3tdSbO9US', '2023-01-01', NULL, 'Intern at Brunei Press (2016 – 2019); Data Clerk at MoE (2020 – 2022)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2012 – 2015)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(51, 'Siti binti Ramli', 'siti.ramli@pb.edu.bn', '$2b$12$23aS0KXuQvuHiFbjCL7byerOj850GIkHZtjhlWUECi9ErLeI3DSrO', '2020-01-01', NULL, 'Clerk at Local Business (2014 – 2016); Clinical Assistant at RIPAS Hospital (2017 – 2019)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2010 – 2013)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(52, 'Zul binti Salleh', 'zul.salleh@pb.edu.bn', '$2b$12$RTXOV0/a3HXIiBQjdQ1VMO13WW9v74cKWx.UNED4dxDybJ9330Q9S', '2020-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2014 – 2016); Media Officer at Radio Televisyen Brunei (2017 – 2019)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2010 – 2013)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(53, 'Siti binti Tahir', 'siti.tahir@pb.edu.bn', '$2b$12$DtK7uo75v0x8ElEoTpb8rup8I2sXG51TNG1REP2W0Qov6A.khPT6C', '2018-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2011 – 2014); Academic Coach at PB General Division (2015 – 2017)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(54, 'Aiman bin Fadzil', 'aiman.fadzil@pb.edu.bn', '$2b$12$5EHDr./jYQwA9ZU26mapfOLF5qiNP.ioCQ3hzOsZq0CG8E5fjqP/2', '2022-01-01', NULL, 'Admin Assistant at Municipal Department (2016 – 2018); Chemical Engineer at Hengyi Industries (2019 – 2021)', 'Bachelor’s in Petrochemical Engineering, Universiti Brunei Darussalam (2012 – 2015)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(55, 'Aqilah bin Yusof', 'aqilah.yusof@pb.edu.bn', '$2b$12$eBQSKVieZqOekEeRNSiI/OtMb5SO5oGsbBoPXcQJSC88JbWiOueVy', '2013-01-01', NULL, 'Trainee at DST (2006 – 2009); Library Officer at UBD (2010 – 2012)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(56, 'Zul binti Nordin', 'zul.nordin@pb.edu.bn', '$2b$12$vEeXGOfLCYAP4sg4CUtg7eX2L3zf0H.fI3PQuyXoO0vUFvMkD.p56', '2020-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2013 – 2016); Trainee at DST (2009 – 2012); Budget Planner at Ministry of Finance (2017 – 2019)', 'Bachelor’s in Accounting, Universiti Brunei Darussalam (2005 – 2008)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(57, 'Danish binti Yusof', 'danish.yusof@pb.edu.bn', '$2b$12$SzxJvh6e.OCdf1EgNS5pouy4ZxykkV/nZco6b19piacXVuSP21Eia', '2012-01-01', NULL, 'Trainee at DST (2005 – 2008); Property Supervisor at Housing Development Dept (2009 – 2011)', 'Bachelor’s in Estate Management, Universiti Brunei Darussalam (2001 – 2004)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(58, 'Zarina bin Hassan', 'zarina.hassan@pb.edu.bn', '$2b$12$1ydeDlD7eQdBV3GafvUhj.2vaKsWibYnxf.I.UR.XwMzZ2qOonfkC', '2015-01-01', NULL, 'Junior Officer at Ministry of Education (2008 – 2011); Intern at Brunei Press (2004 – 2007); Property Supervisor at Housing Development Dept (2012 – 2014)', 'Bachelor’s in Estate Management, Universiti Brunei Darussalam (2000 – 2003)', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(59, 'Irwan binti Salleh', 'irwan.salleh@pb.edu.bn', '$2b$12$l0d/7ag.L5G4PKnQcyCj2ujoaocR6UK9mJWhwDRCq/4emPj9aApb6', '2012-01-01', NULL, 'Intern at Brunei Press (2006 – 2008); Clinical Assistant at RIPAS Hospital (2009 – 2011)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(60, 'Nur binti Ahmad', 'nur.hajiahmad@pb.edu.bn', '$2b$12$7pR7NHEICRSoBkXRa2I3fuqMEPzUt/.e7qwfnntWcJjeZp5LMBdv.', '2022-01-01', NULL, 'Intern at Brunei Press (2016 – 2018); Trainee at DST (2012 – 2015); Legal Clerk at Attorney General’s Office (2019 – 2021)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(61, 'Nurin binti Nordin', 'nurin.nordin@pb.edu.bn', '$2b$12$Ktuoj8yZwtzdf2QkG1KCQuhvkp4x10rlat8jwuDY4P1kHMj0zIPXG', '2013-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2007 – 2009); Trainee at DST (2003 – 2006); Data Clerk at MoE (2010 – 2012)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (1999 – 2002)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(62, 'Nur bin Hakim', 'nur.hakim@pb.edu.bn', '$2b$12$pO6t0MXGYvddHHfN2WWKz.U0mwDez8k9Ob12GQH.rc42uUlFWt6u.', '2012-01-01', NULL, 'Intern at Brunei Press (2005 – 2008); Trainee at DST (2001 – 2004); Budget Planner at Ministry of Finance (2009 – 2011)', 'Bachelor’s in Accounting, Universiti Brunei Darussalam (1997 – 2000)', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(63, 'Aisyah binti Tahir', 'aisyah.tahir@pb.edu.bn', '$2b$12$rBO1aeRO5i61duJcCDIjZOC3scxeFaH0Pbz1RqPYjMS8toiaGBkrW', '2016-01-01', NULL, 'Junior Officer at Ministry of Education (2009 – 2012); Media Officer at Radio Televisyen Brunei (2013 – 2015)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2005 – 2008)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(64, 'Afiqah bin Sabri', 'afiqah.sabri@pb.edu.bn', '$2b$12$ppto.5nxSrogIjw2k7sNCeDVz8EuhC1B1yAVZ2FEKXF8tNhOFwkQi', '2018-01-01', NULL, 'Trainee at DST (2011 – 2014); Admin Assistant at Municipal Department (2007 – 2010); Budget Planner at Ministry of Finance (2015 – 2017)', 'Bachelor’s in Accounting, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(65, 'Shafiq binti Khalid', 'shafiq.khalid@pb.edu.bn', '$2b$12$n2v4DYaCaFZprOpoPjtN../47W7eki0/UB0j4iX1yWdaBcUOwd8.C', '2023-01-01', NULL, 'Admin Assistant at Municipal Department (2016 – 2019); System Analyst at AITI (2020 – 2022)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2012 – 2015)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(66, 'Jannah bin Omar', 'jannah.omar@pb.edu.bn', '$2b$12$4d8JMnqkL42esv15OlRkkeZU4Zh/6h/LUoyvYaE9Hjmmj3ogNaPAW', '2014-01-01', NULL, 'Admin Assistant at Municipal Department (2008 – 2010); Library Officer at UBD (2011 – 2013)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(67, 'Liyana bin Kamal', 'liyana.kamal@pb.edu.bn', '$2b$12$Urqgh4SkLxTXl0Kday199uRHNUAFVo6PTVJvMVjr9OCuvFJYTnTAO', '2020-01-01', NULL, 'Junior Officer at Ministry of Education (2013 – 2016); Chemical Engineer at Hengyi Industries (2017 – 2019)', 'Bachelor’s in Petrochemical Engineering, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(68, 'Sarah binti Khalid', 'sarah.khalid@pb.edu.bn', '$2b$12$JOmLwYwyV/nQAdBlvtz57ORUKYOXL2QbSCXNhnyxzMDWk6IKZkpSm', '2014-01-01', NULL, 'Intern at Brunei Press (2007 – 2010); Counselor at Ministry of Health (2011 – 2013)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(69, 'Faris binti Khalid', 'faris.khalid@pb.edu.bn', '$2b$12$wiKvLAW3BYnkOPZR028lWOq/0K.bEydp3xmC6DhD/2b1wVbcz3pR6', '2019-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2013 – 2015); Data Clerk at MoE (2016 – 2018)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(70, 'Siti binti Hakim', 'siti.hakim@pb.edu.bn', '$2b$12$iDMc4MaTPph4ER3FVptTie4Q49.ZuByWiaixnGKDNoY9YuHMz9JO2', '2021-01-01', NULL, 'Assistant at Local NGO (2015 – 2017); Data Clerk at MoE (2018 – 2020)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2011 – 2014)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(71, 'Afiqah bin Sani', 'afiqah.sani@pb.edu.bn', '$2b$12$BYEATU1gv6tqD2hITctRE.w.VM8d22wr4DshdXXK3mmAGaWqxcn2G', '2018-01-01', NULL, 'Junior Officer at Ministry of Education (2012 – 2014); Trainee at DST (2009 – 2011); Media Officer at Radio Televisyen Brunei (2015 – 2017)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2005 – 2008)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(72, 'Liyana bin Bakar', 'liyana.bakar@pb.edu.bn', '$2b$12$udq8.PMgJODgAUPfFZlyPeauP2vNsOoaqri2cdbytUJPABy9o6Rbe', '2019-01-01', NULL, 'Assistant at Local NGO (2013 – 2015); Facilities Engineer at Public Works Department (2016 – 2018)', 'Bachelor’s in Engineering, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(73, 'Nur bin Rashid', 'nur.rashid@pb.edu.bn', '$2b$12$8ArGNzeCa.TXXdtcyi6GY.K6n6UODkVWm17wj58mjzLOKulVhKh6a', '2022-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2016 – 2018); Chemical Engineer at Hengyi Industries (2019 – 2021)', 'Bachelor’s in Petrochemical Engineering, Universiti Brunei Darussalam (2012 – 2015)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(74, 'Aisyah binti Yusof', 'aisyah.yusof@pb.edu.bn', '$2b$12$R8X4Ms.f/9S/fSLIyOcvP.quEQNYgnq4fw86VwL3YxtuXWsB9qbXO', '2020-01-01', NULL, 'Junior Officer at Ministry of Education (2014 – 2016); System Analyst at AITI (2017 – 2019)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2010 – 2013)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(75, 'Aqil binti Khalid', 'aqil.khalid@pb.edu.bn', '$2b$12$envpqayKjxDEO7eFEbXg3eksp8tuo9O3k4SVWyxl.HgE2gJUWJolO', '2018-01-01', NULL, 'Clerk at Local Business (2012 – 2014); Assistant at Local NGO (2009 – 2011); System Analyst at AITI (2015 – 2017)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2005 – 2008)', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(76, 'Siti binti Ahmad', 'siti.hajiahmad@pb.edu.bn', '$2b$12$ZM.n7n4HiVC3aBfyRtk7tuCVqw/Ft/hq4GEDWlAOaf5COF9gDHlSe', '2021-01-01', NULL, 'Assistant at Local NGO (2015 – 2017); Clerk at Local Business (2011 – 2014); System Analyst at AITI (2018 – 2020)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(77, 'Faris binti Latif', 'faris.latif@pb.edu.bn', '$2b$12$7hOVlTcXuApduBtFfGNVQ.jMVJFHrU9o.Gw3PI64i3IPdM6LW.dca', '2019-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2013 – 2015); Admin Assistant at Municipal Department (2010 – 2012); Facilities Engineer at Public Works Department (2016 – 2018)', 'Bachelor’s in Engineering, Universiti Brunei Darussalam (2006 – 2009)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(78, 'Jannah binti Sabri', 'jannah.sabri@pb.edu.bn', '$2b$12$T.o2tIDXjlllctafNX.lcOf3.53cUWJgfuLXeFzHcxNql1QZZOkwu', '2023-01-01', NULL, 'Junior Officer at Ministry of Education (2016 – 2019); Library Officer at UBD (2020 – 2022)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2012 – 2015)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(79, 'Husna binti Basri', 'husna.basri@pb.edu.bn', '$2b$12$xYWU9nFNzns3QCJjDYQG3ugTyLLOioWYwkoZDMvnKWEw7jautnCsW', '2019-01-01', NULL, 'Trainee at DST (2013 – 2015); HR Executive at UNISSA (2016 – 2018)', 'Bachelor’s in Human Resource, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(80, 'Liyana bin Latif', 'liyana.latif@pb.edu.bn', '$2b$12$1kyDQD5zYcTwGgH6cF8arOUN/.XM5yv5Bq7Jzo0BFhMmR2grMZnli', '2014-01-01', NULL, 'Admin Assistant at Municipal Department (2008 – 2010); Clerk at Local Business (2005 – 2007); Lecturer at Politeknik Brunei (2011 – 2013)', 'Bachelor’s in Education, Universiti Brunei Darussalam (2001 – 2004)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(82, 'Haziq binti Ahmad', 'haziq.hajiahmad@pb.edu.bn', '$2b$12$5igWNaSOEWx.bP3vlrg8juJPLmSCzBs4gdjBUQLDXaVhw0BO9v4vi', '2016-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2009 – 2012); Research Assistant at Universiti Brunei Darussalam (2005 – 2008); Property Supervisor at Housing Development Dept (2013 – 2015)', 'Bachelor’s in Estate Management, Universiti Brunei Darussalam (2001 – 2004)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(83, 'Faris bin Salleh', 'faris.salleh@pb.edu.bn', '$2b$12$om1Z/IhTwcLTwtXqDj78NOCv7fhmI98EEuKb3isIA9a5dVSGuKcFa', '2012-01-01', NULL, 'Assistant at Local NGO (2006 – 2008); Media Officer at Radio Televisyen Brunei (2009 – 2011)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(84, 'Syazwan bin Rahman', 'syazwan.rahman@pb.edu.bn', '$2b$12$sdLHxMBLCqLAKVns9lIrZuXSQyKgobFm7jPZjl6M9ZbIAgsiqmsC.', '2013-01-01', NULL, 'Junior Officer at Ministry of Education (2006 – 2009); Assistant at Local NGO (2003 – 2005); Media Officer at Radio Televisyen Brunei (2010 – 2012)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (1999 – 2002)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(85, 'Jannah bin Sani', 'jannah.sani@pb.edu.bn', '$2b$12$ZFpENNlHtntpM4iJxIGrhuUhmg2xvRQuqozeDJhdFi223eHEiGpWi', '2013-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2007 – 2009); Data Clerk at MoE (2010 – 2012)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(86, 'Sarah binti Nordin', 'sarah.nordin@pb.edu.bn', '$2b$12$ZPTw.BXePhIQp0bLI50kReS4KgPow9mqeCEOUJ9XalY/1IltGhW2q', '2022-01-01', NULL, 'Junior Officer at Ministry of Education (2016 – 2018); Intern at Brunei Press (2013 – 2015); Academic Coach at PB General Division (2019 – 2021)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(87, 'Adib bin Sabri', 'adib.sabri@pb.edu.bn', '$2b$12$rwJbgm5TkYhOBA1H536Tv.Kef1NcJEbp1aa5/E4/szjmlWQxzXClm', '2023-01-01', NULL, 'Junior Officer at Ministry of Education (2016 – 2019); Admin Assistant at Municipal Department (2013 – 2015); Data Clerk at MoE (2020 – 2022)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(88, 'Faris binti Omar', 'faris.omar@pb.edu.bn', '$2b$12$tymnyLWoYWeKj8XBX/7YC.ha62p6YjKE6xkkTUxjvn53Oo3D/ZBke', '2012-01-01', NULL, 'Intern at Brunei Press (2005 – 2008); HR Executive at UNISSA (2009 – 2011)', 'Bachelor’s in Human Resource, Universiti Brunei Darussalam (2001 – 2004)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(89, 'Amalina binti Sallehan', 'amalina.sallehan@pb.edu.bn', '$2b$12$Slr3ojZJlLpCniF0QxepbOG3b3IyXLiW48DyfuTBqiIyfN5Cuckye', '2021-01-01', NULL, 'Clerk at Local Business (2015 – 2017); Assistant at Local NGO (2011 – 2014); System Analyst at AITI (2018 – 2020)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(90, 'Afiqah bin Kamal', 'afiqah.kamal@pb.edu.bn', '$2b$12$oYyAubh4hlFR2AwV/apwe.mWEF1yk.CdKfAHQWnHBQ.APpjdM/ioy', '2012-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2006 – 2008); Facilities Engineer at Public Works Department (2009 – 2011)', 'Bachelor’s in Engineering, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(91, 'Faris binti Hassan', 'faris.hassan@pb.edu.bn', '$2b$12$we5gW2ydcuTsCAJvFGXlaO.5BFbwbQynk7kt8fbt7yxz/Lvt8S8f6', '2019-01-01', NULL, 'Junior Officer at Ministry of Education (2012 – 2015); Junior Officer at Ministry of Education (2008 – 2011); Facilities Engineer at Public Works Department (2016 – 2018)', 'Bachelor’s in Engineering, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(92, 'Fazira bin Kamal', 'fazira.kamal@pb.edu.bn', '$2b$12$fdJgVFqHe8Nsmjcv9vBdU.AqtzPXf3usK84HVqTq4japvUdAH.4PS', '2019-01-01', NULL, 'Admin Assistant at Municipal Department (2013 – 2015); Finance Executive at Baiduri Bank (2016 – 2018)', 'Bachelor’s in Business, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(93, 'Faiz bin Mahmud', 'faiz.mahmud@pb.edu.bn', '$2b$12$xkSoJEn19hhaOnwwmmp1bOsasQfLSN6JicqFE8izZNlrq/OwZUDXC', '2022-01-01', NULL, 'Clerk at Local Business (2015 – 2018); Admin Assistant at Municipal Department (2011 – 2014); Legal Clerk at Attorney General’s Office (2019 – 2021)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(94, 'Amira bin Kamal', 'amira.kamal@pb.edu.bn', '$2b$12$vzpK6ew.0HsifvsBgNAV8uHY81IRqx09LiFnAHjhyrc/mHCowFJwq', '2022-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2015 – 2018); Legal Clerk at Attorney General’s Office (2019 – 2021)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2011 – 2014)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(95, 'Irwan bin Roslan', 'irwan.roslan@pb.edu.bn', '$2b$12$EX.7SLZYjuM2rDz5xO1wBez72ajOsNvsTLKMBrvlF9lO3BvTxKMhS', '2017-01-01', NULL, 'Admin Assistant at Municipal Department (2011 – 2013); Trainee at DST (2007 – 2010); Library Officer at UBD (2014 – 2016)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(96, 'Liyana bin Nordin', 'liyana.nordin@pb.edu.bn', '$2b$12$SaHe0PKc6wSosmX8VgfL1OIxdO80jHfgUrb6UhG7oc1SQOme1YUUq', '2020-01-01', NULL, 'Trainee at DST (2013 – 2016); Chemical Engineer at Hengyi Industries (2017 – 2019)', 'Bachelor’s in Petrochemical Engineering, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(97, 'Syazwan bin Kamal', 'syazwan.kamal@pb.edu.bn', '$2b$12$jrFYL4MJDKvJv.XDI7RNI.2lkw9QtBBYnrlFx4Txj7haT9PD7Ebpe', '2015-01-01', NULL, 'Clerk at Local Business (2008 – 2011); Clinical Assistant at RIPAS Hospital (2012 – 2014)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(98, 'Aisyah bin Roslan', 'aisyah.roslan@pb.edu.bn', '$2b$12$m3QGrQibfg5iMP2Eg6CmPeHTYqSUR4SAqz7BZcNv/2WhPgYsyCoIy', '2018-01-01', NULL, 'Clerk at Local Business (2012 – 2014); Counselor at Ministry of Health (2015 – 2017)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(99, 'Izzah bin Basri', 'izzah.basri@pb.edu.bn', '$2b$12$kL0p9xmX0pOmLMrJvFUdduoFm/vZn5J6F53F8j86WM3x.0lU4cv6q', '2019-01-01', NULL, 'Intern at Brunei Press (2013 – 2015); Data Clerk at MoE (2016 – 2018)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(100, 'Nabila bin Aziz', 'nabila.aziz@pb.edu.bn', '$2b$12$PAOm3BrNV.6cfM1T8s65hOUZMs9/4nq4Iemg82KB8W/DQK82hjDtK', '2014-01-01', NULL, 'Assistant at Local NGO (2007 – 2010); Media Officer at Radio Televisyen Brunei (2011 – 2013)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(101, 'Aqilah bin Khalid', 'aqilah.khalid@pb.edu.bn', '$2b$12$N3wzGYrPXYvJ14pZlnMxseAW5UC3C.6dqmSu0qlGPOPaL6zR27vh6', '2022-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2016 – 2018); Finance Executive at Baiduri Bank (2019 – 2021)', 'Bachelor’s in Business, Universiti Brunei Darussalam (2012 – 2015)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(102, 'Danish binti Rashid', 'danish.rashid@pb.edu.bn', '$2b$12$QPH7TySnP0QKwgYryLsAzel4FagzoZwpfzevvy3teyYdTeZBTAPbi', '2022-01-01', NULL, 'Junior Officer at Ministry of Education (2015 – 2018); Trainee at DST (2012 – 2014); Counselor at Ministry of Health (2019 – 2021)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(103, 'Siti bin Omar', 'siti.omar@pb.edu.bn', '$2b$12$SZDk/jXhtN8hSFjNpYsFz.o9bZOiMHiMDBrfZEUnqxBYEHDndhov6', '2015-01-01', NULL, 'Assistant at Local NGO (2008 – 2011); Research Assistant at Universiti Brunei Darussalam (2004 – 2007); Clinical Assistant at RIPAS Hospital (2012 – 2014)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2000 – 2003)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(104, 'Ridhuan bin Basri', 'ridhuan.basri@pb.edu.bn', '$2b$12$NhJ8c5UQL7qTAO6ZgklTWOKrtAsgtrEABQs2gFFVroGjkDFWTpa3G', '2019-01-01', NULL, 'Assistant at Local NGO (2013 – 2015); Chemical Engineer at Hengyi Industries (2016 – 2018)', 'Bachelor’s in Petrochemical Engineering, Universiti Brunei Darussalam (2009 – 2012)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1);
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `start_date`, `last_login`, `work_experience`, `education`, `profile_pic`, `user_type`, `office`, `is_verified`, `must_change_password`) VALUES
(105, 'Aqilah binti Jamil', 'aqilah.jamil@pb.edu.bn', '$2b$12$HBCzTgSooofMRm1/oaFWaORhfw7BAKnT1BueBCUgpp0AWBpsak362', '2015-01-01', NULL, 'Assistant at Local NGO (2008 – 2011); Media Officer at Radio Televisyen Brunei (2012 – 2014)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(106, 'Faris binti Ramli', 'faris.ramli@pb.edu.bn', '$2b$12$Bm6j9pMiEw.cHjR5aaGT5.fGQpWflMtRSXGM7DYNGBEcWQfGB/RP2', '2014-01-01', NULL, 'Trainee at DST (2007 – 2010); HR Executive at UNISSA (2011 – 2013)', 'Bachelor’s in Human Resource, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(107, 'Izzah binti Khalid', 'izzah.khalid@pb.edu.bn', '$2b$12$tU7J4FANlhAV1wd/M81TN.0vsroVs2CwQPzQrIBHsDz2XtzdL/sfi', '2020-01-01', NULL, 'Assistant at Local NGO (2013 – 2016); Intern at Brunei Press (2010 – 2012); HR Executive at UNISSA (2017 – 2019)', 'Bachelor’s in Human Resource, Universiti Brunei Darussalam (2006 – 2009)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(108, 'Hafiz bin Omar', 'hafiz.omar@pb.edu.bn', '$2b$12$ne7D6Z0JVhVdXCsvqwgNhuQJSYHAiMBn3GI2guEU4l3Wv9Lh92WWS', '2021-01-01', NULL, 'Trainee at DST (2015 – 2017); Trainee at DST (2011 – 2014); HR Executive at UNISSA (2018 – 2020)', 'Bachelor’s in Human Resource, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(109, 'Amalina bin Sabri', 'amalina.sabri@pb.edu.bn', '$2b$12$xysmxycm5v1oHeGV/lKwveO/3.tE8HbWsmscCz9vlxABZAOzZ9Byi', '2016-01-01', NULL, 'Admin Assistant at Municipal Department (2010 – 2012); Library Officer at UBD (2013 – 2015)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2006 – 2009)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(110, 'Haziq binti Hassan', 'haziq.hassan@pb.edu.bn', '$2b$12$RK1O7p7Z.ehyl4kT0Dwkee/FjV0URn0e5bAuvXGfmBZrLGIVpOzxO', '2023-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2017 – 2019); Facilities Engineer at Public Works Department (2020 – 2022)', 'Bachelor’s in Engineering, Universiti Brunei Darussalam (2013 – 2016)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(111, 'Nur bin Basri', 'nur.basri@pb.edu.bn', '$2b$12$d3ReC6rhutS1HDhU5h8k/.0257iReRPHsBEm4fx5ZU0v2uQiuFPha', '2014-01-01', NULL, 'Junior Officer at Ministry of Education (2007 – 2010); Finance Executive at Baiduri Bank (2011 – 2013)', 'Bachelor’s in Business, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(112, 'Aiman binti Hakim', 'aiman.hakim@pb.edu.bn', '$2b$12$5KNkWNBqBSzj2mK2PxKgm.GXGzq7sxzFCxyobaV801lHlO9a9Uyme', '2022-01-01', NULL, 'Admin Assistant at Municipal Department (2016 – 2018); Assistant at Local NGO (2012 – 2015); Legal Clerk at Attorney General’s Office (2019 – 2021)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(113, 'Aiman bin Kamal', 'aiman.kamal@pb.edu.bn', '$2b$12$UGndDlIp93DKGOxsGuBg4uR90vRfzfEAKpz2FPOLc/crygBcxoLeG', '2016-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2009 – 2012); Budget Planner at Ministry of Finance (2013 – 2015)', 'Bachelor’s in Accounting, Universiti Brunei Darussalam (2005 – 2008)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(114, 'Hakim bin Tahir', 'hakim.tahir@pb.edu.bn', '$2b$12$9.LFl.tUYEfdiFl1XFTxzOcSPNE6jvBi5kUj/9LVu6/yrbdRYHgU.', '2015-01-01', NULL, 'Intern at Brunei Press (2008 – 2011); Chemical Engineer at Hengyi Industries (2012 – 2014)', 'Bachelor’s in Petrochemical Engineering, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(115, 'Nur binti Omar', 'nur.omar@pb.edu.bn', '$2b$12$Sf6p7hQDgSngzmCCEiUmzOLlqsAXTHPM26pRNHIU1OCAz3n6OyROy', '2023-01-01', NULL, 'Assistant at Local NGO (2016 – 2019); Research Assistant at Universiti Brunei Darussalam (2012 – 2015); Budget Planner at Ministry of Finance (2020 – 2022)', 'Bachelor’s in Accounting, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(116, 'Fazira bin Tahir', 'fazira.tahir@pb.edu.bn', '$2b$12$2lOyKUfGc89MTNtCyBmVKegdzdTStAi1Z52PSHLOK7bdCtJ2tIK5C', '2020-01-01', NULL, 'Clerk at Local Business (2014 – 2016); Junior Officer at Ministry of Education (2011 – 2013); Data Clerk at MoE (2017 – 2019)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(117, 'Aqilah binti Fadzil', 'aqilah.fadzil@pb.edu.bn', '$2b$12$.KFoXAuHfX1ERJbkRSbVBePboSxmtxiIoXFLf48R9Cb0qgdVRsGum', '2019-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2012 – 2015); Media Officer at Radio Televisyen Brunei (2016 – 2018)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(118, 'Farah binti Ramli', 'farah.ramli@pb.edu.bn', '$2b$12$SW1KZ3aEuaWbZjUV35hRI.uWzI8e.Kr49zPZPBS558sjrTuAORWGi', '2022-01-01', NULL, 'Assistant at Local NGO (2015 – 2018); Trainee at DST (2012 – 2014); System Analyst at AITI (2019 – 2021)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(119, 'Fazira bin Omar', 'fazira.omar@pb.edu.bn', '$2b$12$XjQy8d7SJqgsT2WWiSygguvv61Yoxr/Zo98L1zxLlA5YGlyExQb4y', '2012-01-01', NULL, 'Junior Officer at Ministry of Education (2006 – 2008); Lecturer at Politeknik Brunei (2009 – 2011)', 'Bachelor’s in Education, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(120, 'Azim binti Mahmud', 'azim.mahmud@pb.edu.bn', '$2b$12$0.lxc6AqturYJOKXDv0j9ubVCZgQYgG/ZVyM1iAMsREMPRay479Ye', '2019-01-01', NULL, 'Admin Assistant at Municipal Department (2012 – 2015); Counselor at Ministry of Health (2016 – 2018)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(121, 'Zarina bin Khalid', 'zarina.khalid@pb.edu.bn', '$2b$12$Vpo/fIfOPy67uEb1dMO8jOWWClU3ecJ/CC3GSrh3lxIFrrwKkQvKa', '2022-01-01', NULL, 'Admin Assistant at Municipal Department (2015 – 2018); Assistant at Local NGO (2012 – 2014); Budget Planner at Ministry of Finance (2019 – 2021)', 'Bachelor’s in Accounting, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(122, 'Raihan bin Hakim', 'raihan.hakim@pb.edu.bn', '$2b$12$pI2Wz6I4cSso93nlHft12eYzZ3.dFW6fRNPoy.cOU/7MMdCBB1QDS', '2014-01-01', NULL, 'Junior Officer at Ministry of Education (2007 – 2010); Academic Coach at PB General Division (2011 – 2013)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(123, 'Aqilah binti Sani', 'aqilah.sani@pb.edu.bn', '$2b$12$ebC3U/uNBCLpRaVUT03WueWjHCcushyTCkecwS8S6.EYpBmgrSwCy', '2017-01-01', NULL, 'Clerk at Local Business (2011 – 2013); Library Officer at UBD (2014 – 2016)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2007 – 2010)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(124, 'Aqilah bin Ramli', 'aqilah.ramli@pb.edu.bn', '$2b$12$gBavf0I08HfizSV5kdGETefT6pd/h0Lhbq7hqW.QaJtSwoR8atK9u', '2013-01-01', NULL, 'Junior Officer at Ministry of Education (2007 – 2009); Media Officer at Radio Televisyen Brunei (2010 – 2012)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(125, 'Zarina binti Ahmad', 'zarina.hajiahmad@pb.edu.bn', '$2b$12$mBcBT8TjHLfVHHd2HM.rDOjmm5fvLn9twjxDpiv647S8VKpQ8dr.O', '2015-01-01', NULL, 'Assistant at Local NGO (2008 – 2011); Data Clerk at MoE (2012 – 2014)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(126, 'Aqil binti Samsudin', 'aqil.samsudin@pb.edu.bn', '$2b$12$qYe6kyJsLgwz/6AIGaD3FOdve8z1kwcRKQrWv/l8axGCth/sFFJcm', '2014-01-01', NULL, 'Intern at Brunei Press (2008 – 2010); Facilities Engineer at Public Works Department (2011 – 2013)', 'Bachelor’s in Engineering, Universiti Brunei Darussalam (2004 – 2007)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(127, 'Nurin bin Aziz', 'nurin.aziz@pb.edu.bn', '$2b$12$i4SDX5bg7mUaqRlgUMLwAO/VWvF3j/l3RilL2yI9ixv1q0porDzv2', '2013-01-01', NULL, 'Junior Officer at Ministry of Education (2007 – 2009); Clerk at Local Business (2003 – 2006); Finance Executive at Baiduri Bank (2010 – 2012)', 'Bachelor’s in Business, Universiti Brunei Darussalam (1999 – 2002)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(128, 'Izzah binti Zulkifli', 'izzah.zulkifli@pb.edu.bn', '$2b$12$FdpU0sxxivvyC5InnyOIGO9.VtaC10Fi0WzgKDbgwbGRa72iBuKo2', '2021-01-01', NULL, 'Admin Assistant at Municipal Department (2015 – 2017); Intern at Brunei Press (2012 – 2014); Legal Clerk at Attorney General’s Office (2018 – 2020)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(129, 'Aqil binti Yaakub', 'aqil.yaakub@pb.edu.bn', '$2b$12$mDBsnwmhPJA7R8/LPUNb5e2GkhyN70piUvTla.Dyt39h0Yxpy/awG', '2012-01-01', NULL, 'Intern at Brunei Press (2005 – 2008); Research Assistant at Universiti Brunei Darussalam (2001 – 2004); Counselor at Ministry of Health (2009 – 2011)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (1997 – 2000)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(130, 'Liyana binti Hassan', 'liyana.hassan@pb.edu.bn', '$2b$12$6nAahW62CvzlE80dqcX80epkiDmp4yOZtdqex/jJgBRXtOLst8FKa', '2019-01-01', NULL, 'Trainee at DST (2012 – 2015); Clinical Assistant at RIPAS Hospital (2016 – 2018)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(131, 'Rashid binti Hassan', 'rashid.hassan@pb.edu.bn', '$2b$12$EzQVEHEnyozdHCtc/otmquOc882FUgJRuSkZ.RRTJTaM6u3vHZwj.', '2017-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2010 – 2013); Finance Executive at Baiduri Bank (2014 – 2016)', 'Bachelor’s in Business, Universiti Brunei Darussalam (2006 – 2009)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(132, 'Haziq bin Basri', 'haziq.basri@pb.edu.bn', '$2b$12$L1fBvRZv6.R3jESeDen0Teyo2H9cHX9y57U6TKdm.6wx9LYJ86hNa', '2016-01-01', NULL, 'Junior Officer at Ministry of Education (2009 – 2012); System Analyst at AITI (2013 – 2015)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2005 – 2008)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(133, 'Syafiqah bin Jamil', 'syafiqah.jamil@pb.edu.bn', '$2b$12$kNy4s4DvEEDBwUQAxh1GZOZHD3JRsw5K4HxjDDoprEJ1avR62HqkC', '2016-01-01', NULL, 'Assistant at Local NGO (2009 – 2012); Clerk at Local Business (2006 – 2008); System Analyst at AITI (2013 – 2015)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(134, 'Raihan bin Khalid', 'raihan.khalid@pb.edu.bn', '$2b$12$zAJ8RL/tWBMXy3wER.SZy.9YQf2h/JPEflEVNW3jfjCLkUfb8dmkG', '2018-01-01', NULL, 'Junior Officer at Ministry of Education (2012 – 2014); Clerk at Local Business (2009 – 2011); System Analyst at AITI (2015 – 2017)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2005 – 2008)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(135, 'Zarina binti Rahman', 'zarina.rahman@pb.edu.bn', '$2b$12$HkLrExMyohxM5o5os5WAbOFFGFBbpUIN6HSGEvGtJzAPG/FIfaDHK', '2018-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2011 – 2014); Junior Officer at Ministry of Education (2007 – 2010); Counselor at Ministry of Health (2015 – 2017)', 'Bachelor’s in Psychology, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(136, 'Farah binti Jamil', 'farah.jamil@pb.edu.bn', '$2b$12$eCZBnNZBfYrmDm1gYtEk8evAOEI1936a0W3kt.NtTWXk1z54jnmLm', '2022-01-01', NULL, 'Intern at Brunei Press (2016 – 2018); Clerk at Local Business (2012 – 2015); Media Officer at Radio Televisyen Brunei (2019 – 2021)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(137, 'Danish binti Sabri', 'danish.sabri@pb.edu.bn', '$2b$12$wzUC/tJc/2vYUY8jacCvcOP1SzjOs7ohfuGakRSw/nT9z0cS4./Wu', '2015-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2008 – 2011); Research Assistant at Universiti Brunei Darussalam (2004 – 2007); Clinical Assistant at RIPAS Hospital (2012 – 2014)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2000 – 2003)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(138, 'Faiz bin Basri', 'faiz.basri@pb.edu.bn', '$2b$12$NeJJ3VOaZNatX5VkirprCe7BAHrzGksgHbOWOJu5kTy4tC4ailsiG', '2014-01-01', NULL, 'Intern at Brunei Press (2007 – 2010); Media Officer at Radio Televisyen Brunei (2011 – 2013)', 'Bachelor’s in Public Relations, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(139, 'Shafiq bin Mahmud', 'shafiq.mahmud@pb.edu.bn', '$2b$12$SFA2wtoqq9d/YID3kzpQA.55SzFP0zJ24S7kAszwl9DSc9Sd7iPFO', '2019-01-01', NULL, 'Admin Assistant at Municipal Department (2012 – 2015); Data Clerk at MoE (2016 – 2018)', 'Bachelor’s in Information Management, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(140, 'Farah bin Salleh', 'farah.salleh@pb.edu.bn', '$2b$12$blcFzsXgQGvSLEDwnn2tO.lWBhnOgH4ksF8VWCGav/CG8suocHMZu', '2021-01-01', NULL, 'Admin Assistant at Municipal Department (2014 – 2017); Academic Coach at PB General Division (2018 – 2020)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2010 – 2013)', '../profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(141, 'Danish bin Zulkifli', 'danish.zulkifli@pb.edu.bn', '$2b$12$C3KHVST3mwasW4nWrkvMf.C0oNj3Z3d6Ln8X/lLb9Qpr22WJ5dVPm', '2015-01-01', NULL, 'Clerk at Local Business (2009 – 2011); Trainee at DST (2006 – 2008); Legal Clerk at Attorney General’s Office (2012 – 2014)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(142, 'Haziq binti Jamil', 'haziq.jamil@pb.edu.bn', '$2b$12$Hg7vdlLfMxci0l2L7qcjG.UC.vScy4nvqAVsO2xC7QgzUCxHg8fna', '2015-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2009 – 2011); Junior Officer at Ministry of Education (2006 – 2008); Legal Clerk at Attorney General’s Office (2012 – 2014)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(143, 'Nadiah bin Tahir', 'nadiah.tahir@pb.edu.bn', '$2b$12$kvvd.0KBBxdtLygP2a0o8.9rV4fawDGJ5y1ZSNRTW4he1uYE143bi', '2023-01-01', NULL, 'Clerk at Local Business (2016 – 2019); Clerk at Local Business (2012 – 2015); Library Officer at UBD (2020 – 2022)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2008 – 2011)', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(144, 'Azim bin Hassan', 'azim.hassan@pb.edu.bn', '$2b$12$hubxzmtmMjpk33dkmXmINujFNuDmnKe1gkt.C.V68bH5ICd7uOIIy', '2012-01-01', NULL, 'Clerk at Local Business (2006 – 2008); Library Officer at UBD (2009 – 2011)', 'Bachelor’s in Library Science, Universiti Brunei Darussalam (2002 – 2005)', '../profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(145, 'Nabila bin Salleh', 'nabila.salleh@pb.edu.bn', '$2b$12$dlOsXi1XIUw9MJ0Yo2mJretNEihX.76fNS77Y.S3Ruo6RHcRXrZkO', '2014-01-01', NULL, 'Research Assistant at Universiti Brunei Darussalam (2007 – 2010); Academic Coach at PB General Division (2011 – 2013)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(146, 'Aqilah bin Kamal', 'aqilah.kamal@pb.edu.bn', '$2b$12$vMu61sneDk89DmCNR2O53OQszQDfdMgJldMR3OQKJt4MQTAOeL2Ve', '2014-01-01', NULL, 'Clerk at Local Business (2007 – 2010); Legal Clerk at Attorney General’s Office (2011 – 2013)', 'Bachelor’s in Law, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(147, 'Aisyah bin Osman', 'aisyah.osman@pb.edu.bn', '$2b$12$eFBTB5zWkNLvgnJFmzZkCeMkNDBgX738XpA2nD0.eN68oUKi7Yrp6', '2013-01-01', NULL, 'Volunteer Coordinator at Red Crescent (2007 – 2009); Academic Coach at PB General Division (2010 – 2012)', 'Bachelor’s in General Studies, Universiti Brunei Darussalam (2003 – 2006)', '../profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(148, 'Nadiah bin Rahman', 'nadiah.rahman@pb.edu.bn', '$2b$12$/INLrUpE1JE3DDxAfRl7d.5NyotRI8iEkUG5fR4qiWkHZfDAhvH/i', '2017-01-01', NULL, 'Admin Assistant at Municipal Department (2010 – 2013); System Analyst at AITI (2014 – 2016)', 'Bachelor’s in Computer Science, Universiti Brunei Darussalam (2006 – 2009)', '../profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(149, 'Nabila bin Samsudin', 'nabila.samsudin@pb.edu.bn', '$2b$12$D65Mhqis/nA4h.leydEB6.KJ1uZEL4kocqA4.nqfBBbKg3ZPaegGS', '2021-01-01', NULL, 'Intern at Brunei Press (2015 – 2017); Facilities Engineer at Public Works Department (2018 – 2020)', 'Bachelor’s in Engineering, Universiti Brunei Darussalam (2011 – 2014)', '../profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(150, 'Haziq binti Hakim', 'haziq.hakim@pb.edu.bn', '$2b$12$ZY27Gyn7h/.WgMvbCpAZ4OfoYlbcv2YcgKY01BGnUj8bIQxQFyISK', '2023-01-01', NULL, 'Assistant at Local NGO (2016 – 2019); Clinical Assistant at RIPAS Hospital (2020 – 2022)', 'Bachelor’s in Health Science, Universiti Brunei Darussalam (2012 – 2015)', '../profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(305, 'Maya Ibrahim', 'maya.ibrahim1@example.com', 'ca4f0ae8758f7f70f87e0b93c719ba7c8f29d094fab37f7a6bd44a89fe810c28', '2022-01-10', NULL, 'Preschool Teacher', 'Business regularistration', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(306, 'Ethan Awang', 'ethan.awang2@example.com', 'a6b4fca6bc8623cb39a04907fd4d080b0a8291a49955f8a36a1e91fb715258d9', '2022-03-10', NULL, 'Mechanical Technician', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(307, 'Eva Zain', 'eva.zain3@example.com', '692ec52fe6942f3ae40a518b15688aee77ac62957dfb22e545adb60253dd6ca5', '2022-04-18', NULL, 'Cybersecurity Analyst', 'Software Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(308, 'Ella Mahmud', 'ella.mahmud4@example.com', '99b43bf72d2307e06782bc36432e15b15044c34bf8210ee945bc44810d490f92', '2022-09-12', NULL, 'Network Engineer', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(309, 'Leo Chong', 'leo.chong5@example.com', 'ffa3a50355ad2c797487592349f4cb439505a6638a0d253d921f64552db46c76', '2022-01-13', NULL, 'Legal Assistant', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(310, 'Eva Zain', 'eva.zain6@example.com', '692ec52fe6942f3ae40a518b15688aee77ac62957dfb22e545adb60253dd6ca5', '2022-03-04', NULL, 'Software Developer', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(311, 'Layla Salleh', 'layla.salleh7@example.com', '30cc689201c16900299015736c7e7824f4d31c5dcce1007d2f44cd51021e805f', '2022-12-20', NULL, 'Content Creator', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(312, 'Sophia Hamid', 'sophia.hamid8@example.com', 'ea11c7ee0ac9dabc2a9404a1a7d9a2d68867b7da018e8d0b6d84204160c3e582', '2022-11-26', NULL, 'Mechanical Technician', 'Mechanical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(313, 'Nora Awang', 'nora.awang9@example.com', 'b0e8f7ff56d8eb746b01f7f95f0480e8e4dd19a25c63fa218dd6adbb76472676', '2022-08-28', NULL, 'Business Manager', 'Accountancy', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(314, 'Marcus Kamal', 'marcus.kamal10@example.com', 'e4074893c414e7a6f8d5e0cfdd42eeb74532a9e15395ce85b89925b3ee456821', '2022-10-03', NULL, 'Data Analyst', 'Law', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(315, 'Layla Tan', 'layla.tan11@example.com', '8781378e76d9a9210b0cfbef08266c45c7a514217c67d7cbe9805850afe0ea45', '2022-10-13', NULL, 'Logistics Coordinator', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(316, 'Sophia Salleh', 'sophia.salleh12@example.com', '1d2b981e44204854eeeb5fce4a9b582421c3dd4539caa61c1611e5008555cb0d', '2022-07-16', NULL, 'Marketing Executive', 'Civil Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(317, 'Eva Ismail', 'eva.ismail13@example.com', '799e87ccf38d6bae78c05ecc7bf6370aa5776919daaa22cd026980b2e855d711', '2022-11-27', NULL, 'Software Developer', 'Law', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(318, 'Maya Rahman', 'maya.rahman14@example.com', '4314bc4d5861f4350052cee96fde5cf7c116c3c8d3912156d198091191fa8139', '2022-06-15', NULL, 'Logistics Coordinator', 'Logistics Management', 'profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(319, 'Eva Yusof', 'eva.yusof15@example.com', 'd0c791e86ddd08f0d3748cf01392453aa245f5f42579c1aa5f9a5a85391408c2', '2022-11-27', NULL, 'Mechanical Technician', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(320, 'Maya Ali', 'maya.ali16@example.com', '4beb9d67616f4ddd9c40b0e5485fc157438883eaf6b7a59b8c0fc7a452ce4c55', '2022-11-21', NULL, 'Graphic Designer', 'Computer Science', 'profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(321, 'Isaac Kamal', 'isaac.kamal17@example.com', '87e424b20ef7ed3c073442abff34c28ffe38f80fd9c1f39d252ba1be60c99872', '2022-04-27', NULL, 'Marketing Executive', 'Cybersecurity', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(322, 'Layla Mahmud', 'layla.mahmud18@example.com', '76c0dd63a78bd9be8f7850affa2068d0262bb3f86c1fea933bb7cfb65f941e8d', '2022-01-19', NULL, 'Legal Assistant', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(323, 'Lucas Rahman', 'lucas.rahman19@example.com', 'eb64439491a50372b2fae730aafba66b9962eb5964114fbb71d8a296c286630a', '2022-06-25', NULL, 'Marketing Executive', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(324, 'Aidan Lim', 'aidan.lim20@example.com', '8eacd45e1d1ff93cd807866b6348c4a45a2e3e63e13d3fca1566178dee40f812', '2022-01-16', NULL, 'Network Engineer', 'Mechanical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(325, 'Nora Zain', 'nora.zain21@example.com', '3847ea371971e16e90e384d58e17a4cfb000b03b7731dda0f9da033810c58def', '2022-08-21', NULL, 'Data Analyst', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(326, 'Ivy Ibrahim', 'ivy.ibrahim22@example.com', 'f54e9b6507c141bd2742647dc497a3e7b6e9e8534bac8a1b28f1a408e143c76f', '2022-06-15', NULL, 'Operations Manager', 'Law', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(327, 'Ivy Ibrahim', 'ivy.ibrahim23@example.com', 'f54e9b6507c141bd2742647dc497a3e7b6e9e8534bac8a1b28f1a408e143c76f', '2022-09-23', NULL, 'Legal Assistant', 'Law', 'profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(328, 'Owen Lee', 'owen.lee24@example.com', '197c57237a88cff403b1b43c94a09158ba70254e89b105e195d832d127f27b81', '2022-12-08', NULL, 'Mechanical Technician', 'Cybersecurity', 'profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(329, 'Noah Wong', 'noah.wong25@example.com', '08143ab84792928ecf75e701e61a7faeb89ceb707c530ab54e7926a9da72d4c4', '2022-02-12', NULL, 'Legal Assistant', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(330, 'Noah Ibrahim', 'noah.ibrahim26@example.com', 'e4bf26a52b45b6c14ca0f96e7d9005dfe3e98c64a9568ec5acf466efa4036a77', '2022-12-02', NULL, 'Operations Manager', 'Cybersecurity', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(331, 'Sophia Sallehuddin', 'sophia.sallehuddin27@example.com', '5c5ed3c7bc76ddc91ce026e54895ce9d4c88ec7b3bba0ff5296e4b00e9c676a4', '2022-02-20', NULL, 'Business Manager', 'Logistics Management', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(332, 'Julian Yusof', 'julian.yusof28@example.com', '670906248d38d8698185dc8706ecff8b3ab8b0f0079dc344ad971b487a175bab', '2022-01-11', NULL, 'Legal Assistant', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(333, 'Noah Kamal', 'noah.kamal29@example.com', 'd28668f6e71abf030e3656a7bbb3863b6614971b5f3645e65432b3edc7037f93', '2022-07-01', NULL, 'Cybersecurity Analyst', 'Cybersecurity', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(334, 'Eva Tan', 'eva.tan30@example.com', '07a97bdef4d867b194d01e1d124b9cffa53a6e19544ad1e7e4bed892f6265e0d', '2022-01-07', NULL, 'Data Analyst', 'Law', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(335, 'Ethan Lim', 'ethan.lim31@example.com', '1b8c630ed555bce2ca3d631d8bdcf802d33a36d368e332fb00e9ed1160b3040d', '2022-10-25', NULL, 'Lecturer', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(336, 'Leo Awang', 'leo.awang32@example.com', '9dc522aac2a859ad960e0f8c25d891381196302b63c837aa693dff3a89421f87', '2022-07-22', NULL, 'Lecturer', 'Mechanical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(337, 'Sophia Lim', 'sophia.lim33@example.com', 'b81a16e9a1a13b8c7afda4ee750dc53278223daf791cc7880c4272301b4e3db3', '2022-08-22', NULL, 'Content Creator', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(338, 'Ella Kamal', 'ella.kamal34@example.com', 'b5f57fcf5499d18ca4e6560e0b7989f837c9c0bb3e1273746589fa440303c6c9', '2022-03-28', NULL, 'Operations Manager', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(339, 'Owen Awang', 'owen.awang35@example.com', 'a861ebe951614eac8a94b77a1f9cd14025a935cea29b48010f1c25f52bb385f5', '2022-12-28', NULL, 'Content Creator', 'Software Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(340, 'Hannah Chong', 'hannah.chong36@example.com', '86b7e25f34a202b4cbdfb4c8c81605bd527c6bf03dcae597846ed3373edbfc4a', '2022-05-01', NULL, 'Legal Assistant', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(341, 'Isaac Kamal', 'isaac.kamal37@example.com', '87e424b20ef7ed3c073442abff34c28ffe38f80fd9c1f39d252ba1be60c99872', '2022-06-03', NULL, 'Business Manager', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(342, 'Hannah Sallehuddin', 'hannah.sallehuddin38@example.com', '5ec368d6e01d147a6bb1ba12ae2dde6784c8d509793d30e6505fa593d285b7cc', '2022-10-18', NULL, 'Network Engineer', 'Civil Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(343, 'Owen Chong', 'owen.chong39@example.com', '0e809b1cfa829335961649976adc4cdf8dce69eae5f181f43de15d55f86a1b73', '2022-01-02', NULL, 'Cybersecurity Analyst', 'Software Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(344, 'Maya Ali', 'maya.ali40@example.com', '4beb9d67616f4ddd9c40b0e5485fc157438883eaf6b7a59b8c0fc7a452ce4c55', '2022-08-12', NULL, 'Data Analyst', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(345, 'Sophia Ali', 'sophia.ali41@example.com', '3aeb89fbcd287b05da1fbc5bd6f9b0897e7ed012428c39d487b658ceb12be6f5', '2022-10-26', NULL, 'Business Manager', 'Creative Media', 'profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(346, 'Layla Hashim', 'layla.hashim42@example.com', 'b8af4021b8d63c9cc39b42a659344f96c12636d2bcd998c8efd24052d77a2412', '2022-09-03', NULL, 'Graphic Designer', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(347, 'Ethan Yusof', 'ethan.yusof43@example.com', '623fc90284c17dcfaf2da266065059d8f298705aa9f9f5695323e142f16da07e', '2022-01-07', NULL, 'Cybersecurity Analyst', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(348, 'Zara Sallehuddin', 'zara.sallehuddin44@example.com', 'c50739d94b22f9242b04404ba3ed047e4f7786c109b3c787198b458cda7642fb', '2022-04-27', NULL, 'Mechanical Technician', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(349, 'Marcus Teo', 'marcus.teo45@example.com', 'e4a2be6bc06113edc101e8c5118e9a9bc16bdc3d67de16856886911483ce77ff', '2022-08-26', NULL, 'Lecturer', 'Business regularistration', 'profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(350, 'Marcus Ismail', 'marcus.ismail46@example.com', 'f439e84b2dd20f036edb040e108fc4e88382a9e596f8e3397a85b171075e3f1a', '2022-10-17', NULL, 'Legal Assistant', 'Business regularistration', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(351, 'Lucas Awang', 'lucas.awang47@example.com', '06be75644a021626c5f54877c10db8b7024e9d144d61eb5aa483277b9d98cb14', '2022-04-16', NULL, 'Preschool Teacher', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(352, 'Ella Rahman', 'ella.rahman48@example.com', '40e44145a5fbf07cc83715b95137aa77ca8db5d2bef7a5e1e6b8cf0f10c8955c', '2022-05-12', NULL, 'Content Creator', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(353, 'Marcus Ibrahim', 'marcus.ibrahim49@example.com', '77d5f09b7b8384038aed92f38148d66db9f3c6a89147ce2e8adf628745b4dc52', '2022-02-06', NULL, 'Mechanical Technician', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(354, 'Adam Tan', 'adam.tan50@example.com', 'e8a3b17dd6d514587991cfe40669c57b6f01845b5062d3b659606aa9f12629db', '2022-07-14', NULL, 'Network Engineer', 'Mechanical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(355, 'Amelia Lee', 'amelia.lee51@example.com', '9cbd97d8e45c8c1a0bce79e5e14137e5439ef5b953a14c5ea098b460e38cadf7', '2022-07-05', NULL, 'Mechanical Technician', 'Business regularistration', 'profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(356, 'Nora Hamid', 'nora.hamid52@example.com', '88c7cdcb2ff6a8b4233c17047a82505915a4470e2a7c07521b7e323d82325123', '2022-05-22', NULL, 'Software Developer', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(357, 'Leo Ali', 'leo.ali53@example.com', 'f38f200080d3d45c73feb521f8c0d55412146a1295931f7c47a738b007050207', '2022-04-13', NULL, 'Software Developer', 'Computer Science', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(358, 'Julian Abdullah', 'julian.abdullah54@example.com', '18855ee50663430dff8d50ec33b539a5a15e57f54c0debbff5d76fd6faa0abf1', '2022-03-20', NULL, 'Content Creator', 'Computer Science', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(359, 'Marcus Lim', 'marcus.lim55@example.com', 'a8004542638a16ca04a2c6cf47c3dd27db261080a67003b7d6d888b13ab29048', '2022-02-05', NULL, 'Graphic Designer', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(360, 'Layla Salleh', 'layla.salleh56@example.com', '30cc689201c16900299015736c7e7824f4d31c5dcce1007d2f44cd51021e805f', '2022-01-26', NULL, 'Graphic Designer', 'Software Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(361, 'Ivy Ibrahim', 'ivy.ibrahim57@example.com', 'f54e9b6507c141bd2742647dc497a3e7b6e9e8534bac8a1b28f1a408e143c76f', '2022-11-17', NULL, 'Marketing Executive', 'Software Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(362, 'Marcus Yusof', 'marcus.yusof58@example.com', '1916ba97a9cc4bd8dfc2ec81dd26cf6a545dafa80c141175a65edbc6a43983cc', '2022-08-23', NULL, 'Marketing Executive', 'Computer Science', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(363, 'Ella Hamid', 'ella.hamid59@example.com', '205c1fab2c33d60cd30c335ede7ebc2aee068794f50c6f36f23f55f3894f045a', '2022-06-09', NULL, 'Cybersecurity Analyst', 'Computer Science', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(364, 'Marcus Tan', 'marcus.tan60@example.com', 'c2d29d831b6372df55acd6dc279fa9673cc63b5fd016111088cc30bc7d5c0c3a', '2022-11-10', NULL, 'Content Creator', 'Logistics Management', 'profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(365, 'Nora Sallehuddin', 'nora.sallehuddin61@example.com', '6e359a176e67fc5e0aee3f792f2a7cd464f3d58424cd45d21abb81b264d3b631', '2022-04-09', NULL, 'Lecturer', 'Business regularistration', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(366, 'Ethan Yusof', 'ethan.yusof62@example.com', '623fc90284c17dcfaf2da266065059d8f298705aa9f9f5695323e142f16da07e', '2022-11-04', NULL, 'Operations Manager', 'Cybersecurity', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(367, 'Isaac Lee', 'isaac.lee63@example.com', '208fcd5977e6aca22dfcbcf65acfcb6fc716cf8bfe4318bc37489d3614b13908', '2022-01-24', NULL, 'Cybersecurity Analyst', 'Cybersecurity', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(368, 'Eva Ali', 'eva.ali64@example.com', 'ef26c0282a4a6d496044b4321ed7b0e51d36acd95c6890716e86b02882fb815e', '2022-08-09', NULL, 'Data Analyst', 'Business regularistration', 'profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(369, 'Noah Zain', 'noah.zain65@example.com', '3755fd6664d8c557f32380bfbcd7b9118b306528838412560a370cc215ad2d81', '2022-12-14', NULL, 'Logistics Coordinator', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(370, 'Amelia Hashim', 'amelia.hashim66@example.com', '21fb0741efceb8fd736dd283891d28ddd231305725334459cb65b5d03f2c3c7d', '2022-05-07', NULL, 'Business Manager', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(371, 'Ella Lim', 'ella.lim67@example.com', '0ec9312140f3131d06f02c7cc1c64a1b048346b5dbc1ab8de926c1092f1edd47', '2022-10-09', NULL, 'Legal Assistant', 'Accountancy', 'profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(372, 'Owen Ismail', 'owen.ismail68@example.com', 'b25b76b7d85e886c1649fc18c77cd4085399591738959a005fe4b7d1e195f174', '2022-08-23', NULL, 'Preschool Teacher', 'Creative Media', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(373, 'Isaac Ibrahim', 'isaac.ibrahim69@example.com', 'f21fbf017f0a55dc491a5636c6ba2d2d2373631ed2b407fa1a548068b25eebdc', '2022-08-20', NULL, 'Mechanical Technician', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(374, 'Leo Rahman', 'leo.rahman70@example.com', 'b917bd6025022fa45325bc24855415693af39ce27e4816ae12617d4c57670af3', '2022-10-12', NULL, 'Lecturer', 'Law', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(375, 'Leo Hamid', 'leo.hamid71@example.com', '7ae48765e55ef5ec1c05b4faf49922ef4e2034e716547251a4ed42827e2251ef', '2022-09-23', NULL, 'Cybersecurity Analyst', 'Computer Science', 'profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(376, 'Lucas Abdullah', 'lucas.abdullah72@example.com', 'a6968b0aea5792bd6f3d2c72d4e6e489632c1c2add3d8a2f1a79510274585ccf', '2022-04-12', NULL, 'Software Developer', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(377, 'Nora Ali', 'nora.ali73@example.com', '96ce5d115eb4c4a33304d659feb53ff5d95c9a5475dba3d4a27680f46a842761', '2022-12-15', NULL, 'Business Manager', 'Computer Science', 'profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(378, 'Amelia Abdullah', 'amelia.abdullah74@example.com', 'b45abd501c0681b3bb1b709237fe0bcab96e75ba4b55167596be8debcce02799', '2022-07-05', NULL, 'Software Developer', 'Information Technology', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(379, 'Nora Lee', 'nora.lee75@example.com', '53d3d557795922749396cb41d71e078167874d1ecbf86e95ccdd3af30600aef2', '2022-12-15', NULL, 'Network Engineer', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(380, 'Zara Salleh', 'zara.salleh76@example.com', '6f46551257ef5c17f9b14887a5628c8ae49239f763f93efe2309e95d15b61bc1', '2022-05-22', NULL, 'Lecturer', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(381, 'Noah Zain', 'noah.zain77@example.com', '3755fd6664d8c557f32380bfbcd7b9118b306528838412560a370cc215ad2d81', '2022-04-03', NULL, 'Marketing Executive', 'Information Technology', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(382, 'Nora Ismail', 'nora.ismail78@example.com', 'e90f66814695737eff3c007e9398aa9880506ae06ac9e13ef46adb5946a0233f', '2022-10-22', NULL, 'Mechanical Technician', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(383, 'Aidan Lee', 'aidan.lee79@example.com', '9d46d7becdbedc3eb1cf1415643b261ffb1dfaabd6ba50a5ae62229205d5d631', '2022-08-08', NULL, 'Marketing Executive', 'Civil Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(384, 'Maya Ibrahim', 'maya.ibrahim80@example.com', 'ca4f0ae8758f7f70f87e0b93c719ba7c8f29d094fab37f7a6bd44a89fe810c28', '2022-02-20', NULL, 'Network Engineer', 'Information Technology', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(385, 'Nora Ali', 'nora.ali81@example.com', '96ce5d115eb4c4a33304d659feb53ff5d95c9a5475dba3d4a27680f46a842761', '2022-07-05', NULL, 'Marketing Executive', 'Law', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(386, 'Ivy Hashim', 'ivy.hashim82@example.com', 'a71f77a5bfdee01ff378da8e2fc133e4b6f2502f596303b5485d524ebc7a5c31', '2022-09-28', NULL, 'Preschool Teacher', 'Civil Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP8', 0, 1),
(387, 'Aidan Zain', 'aidan.zain83@example.com', 'dfaad5287cbbbdd0f2d92146d45956a3397de754971c6c422eb78ca68c9130a5', '2022-07-19', NULL, 'Marketing Executive', 'Law', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(388, 'Ethan Tan', 'ethan.tan84@example.com', 'b8422e4190071aeebdf5a7b1f485532bab9a626c86e6e2bd3479bac34a35ccde', '2022-04-24', NULL, 'Data Analyst', 'Logistics Management', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(389, 'Lucas Hamid', 'lucas.hamid85@example.com', '13c86f33d42d9c1966b277546fda721410d40a6308508a891ce5cbbc9c2a0957', '2022-09-05', NULL, 'Accountant', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(390, 'Lucas Sallehuddin', 'lucas.sallehuddin86@example.com', '4e60be561753c38bcdaa50ddac910ddf6eef146460836d6134b1cd12983fc27b', '2022-09-01', NULL, 'Cybersecurity Analyst', 'Software Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(391, 'Adam Yusof', 'adam.yusof87@example.com', '242b1e319fb07fcc35e2ebed4bc33e0cc0c1baef935051e444e9b6dd199c6225', '2022-08-25', NULL, 'Operations Manager', 'Electrical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(392, 'Isaac Tan', 'isaac.tan88@example.com', '807118372f18b0f07df4ce74bd68b89d867ce2bae0cbceb906fbbc248a580dcb', '2022-04-24', NULL, 'Business Manager', 'Mechanical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 0, 1),
(393, 'Ethan Sallehuddin', 'ethan.sallehuddin89@example.com', 'b7dcd48b2bec6c7f8176db0098509fcdcb3a85f2fa12337f2aac90a3b17b8a36', '2022-06-08', NULL, 'Operations Manager', 'Accountancy', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(394, 'Maya Salleh', 'maya.salleh90@example.com', 'bea5518f6a6a497a990d76f521e7fe197735c9cf38c00166dedc273d063c38af', '2022-03-14', NULL, 'Lecturer', 'Cybersecurity', 'profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(395, 'Zara Ismail', 'zara.ismail91@example.com', '5037ae33c6af5a127150def1932bce29a364ab0b07559ec6f9ba778e4e39541c', '2022-07-15', NULL, 'Graphic Designer', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP6', 0, 1),
(396, 'Nora Zain', 'nora.zain92@example.com', '3847ea371971e16e90e384d58e17a4cfb000b03b7731dda0f9da033810c58def', '2022-10-08', NULL, 'Data Analyst', 'Cybersecurity', 'profile/images/default-profile.jpg', 'regular', 'OSP1', 0, 1),
(397, 'Ivy Yusof', 'ivy.yusof93@example.com', 'ded786d01e127223202ed00ea38609653324300dd47a306c7326d0fcc93d8665', '2022-04-18', NULL, 'Logistics Coordinator', 'Creative Media', 'profile/images/default-profile.jpg', 'regular', 'OSP9', 0, 1),
(398, 'Noah Abdullah', 'noah.abdullah94@example.com', '28a67c332bcc16d96d82da5fc60633f1eb8ff190399a5d3a21a4fb16e9fd5132', '2022-08-14', NULL, 'Accountant', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP3', 0, 1),
(399, 'Ethan Tan', 'ethan.tan95@example.com', 'b8422e4190071aeebdf5a7b1f485532bab9a626c86e6e2bd3479bac34a35ccde', '2022-10-23', NULL, 'Mechanical Technician', 'Mechanical Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(400, 'Maya Chong', 'maya.chong96@example.com', '32fa3b89d3f7985111df894dc4954f91838ec624b55f0f371833f7e8410d9c04', '2022-03-26', NULL, 'Data Analyst', 'Teaching', 'profile/images/default-profile.jpg', 'regular', 'OSP10', 0, 1),
(401, 'Leo Lim', 'leo.lim97@example.com', '53ccce6a5b1b7574dda8fa6bc0715a59e82e1bf7ca6ca7bf7de6d6b7152f1963', '2022-06-14', NULL, 'Business Manager', 'Software Engineering', 'profile/images/default-profile.jpg', 'regular', 'OSP7', 0, 1),
(402, 'Ivy Lim', 'test@example.com', '$2y$12$IIHREbyceqgJmpZ3E5rINebxVlcsZlPzZu.5k/vm/elbD3rjoYILu', '2022-01-03', NULL, 'Content Creator', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP5', 1, 0),
(403, 'Aidan Yusof', 'aiden@example.com', '$2y$12$IIHREbyceqgJmpZ3E5rINebxVlcsZlPzZu.5k/vm/elbD3rjoYILu', '2022-02-23', NULL, 'Lecturer', 'Marketing', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 1, 0),
(404, 'Noah Ibrahim', 'noah.ibrahim100@example.com', 'e4bf26a52b45b6c14ca0f96e7d9005dfe3e98c64a9568ec5acf466efa4036a77', '2022-08-18', NULL, 'Logistics Coordinator', 'Early Childhood Education', 'profile/images/default-profile.jpg', 'regular', 'OSP4', 0, 1),
(405, 'Back Door', 'backdoor@example.com', 'backdoor', '2025-08-01', NULL, 'Back Door', 'Hacker', 'profile/images/default-profile.jpg', 'regular', 'OSP2', 0, 1),
(406, 'yes sir', 'admin@email.com', '$2y$12$fAp.rZOEvNNrk8.65YBjnOh9NTFmP3etzHPeyY8CubMeHmnjXd9um', '2025-01-01', NULL, 'professional lockpicker', '', '../profile/images/default-profile.jpg', 'admin', 'OSP1', 1, 1),
(419, 'Regular', 'regular@email.com', '$2y$10$80nNLelz/SM3Q99JSyPTSeGlE9JR8U.oYBt9u2fJMcEfWlMlQTi2S', NULL, NULL, NULL, NULL, '../profile/images/default-profile.jpg', 'regular', NULL, 0, 1),
(420, 'Mohamad Norsyahmin Adillah Bin ABD Latif', 'syahminadillah@gmail.com', '$2y$12$IIHREbyceqgJmpZ3E5rINebxVlcsZlPzZu.5k/vm/elbD3rjoYILu', NULL, '2025-09-29 01:30:32', NULL, NULL, '../profile/images/default-profile.jpg', 'super_admin', 'OSP1', 1, 0),
(422, 'farhan', '23ftt1828@ymail.com', '$2y$12$Wtk3a54No4z4sYEREQ2Ioero.KrfWQUI/XuYE3Ak.6o6WgqD.ZbRi', '2025-08-07', NULL, '', '', '../profile/images/default-profile.jpg', 'admin', 'OSP5', 0, 1),
(433, 'Syahmin', '23ftt1890@pb.edu.bn', '$2y$12$5SOCqWLZCy11R8iOd0.yzegBRqHRKvKCf36gqfo1ywQa8X3yh.9/K', '2025-08-01', NULL, '', '', '../profile/images/default-profile.jpg', 'regular', 'L3C', 0, 1),
(434, 'jackfrostxmoon@gmail.com', 'jackfrostxmoon@gmail.com', '$2y$12$57Cci1HTWIzbJrcMWURjDuXRNRK3tbgeP1YrEhLvnmbeYULmCUJhq', '2025-09-05', NULL, '', '', '../profile/images/default-profile.jpg', 'regular', 'OSP1', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `usersupport`
--

CREATE TABLE `usersupport` (
  `support_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `support_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `page_name` varchar(255) NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `chat_user_contacts`
--
ALTER TABLE `chat_user_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `department_requirements`
--
ALTER TABLE `department_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_feedback_status` (`status`),
  ADD KEY `idx_feedback_category` (`category`),
  ADD KEY `idx_feedback_rating` (`rating`),
  ADD KEY `feedback_assigned_to_fk` (`assigned_to`);

--
-- Indexes for table `mails`
--
ALTER TABLE `mails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`meeting_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `meeting_chats`
--
ALTER TABLE `meeting_chats`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `meeting_id` (`meeting_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `meeting_notification`
--
ALTER TABLE `meeting_notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `meeting_id` (`meeting_id`);

--
-- Indexes for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meeting_id` (`meeting_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `report_to` (`report_to`);

--
-- Indexes for table `resource_files`
--
ALTER TABLE `resource_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `resource_logs`
--
ALTER TABLE `resource_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resource_sections`
--
ALTER TABLE `resource_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_role_id` (`role_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`,`department_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `role_appeals`
--
ALTER TABLE `role_appeals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_assignment_requests`
--
ALTER TABLE `role_assignment_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_feedback`
--
ALTER TABLE `role_feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_history`
--
ALTER TABLE `role_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_role_history_dates` (`assigned_at`,`removed_at`);

--
-- Indexes for table `role_kpis`
--
ALTER TABLE `role_kpis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_idx` (`role_name`);

--
-- Indexes for table `role_requests`
--
ALTER TABLE `role_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `requested_user_id` (`requested_user_id`),
  ADD KEY `requested_by` (`requested_by`);

--
-- Indexes for table `role_requirements`
--
ALTER TABLE `role_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `role_resources`
--
ALTER TABLE `role_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_2` (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `signaling`
--
ALTER TABLE `signaling`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `staff_email` (`staff_email`),
  ADD KEY `staff_role` (`staff_role`),
  ADD KEY `fk_staff_role` (`role_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `task_appeals`
--
ALTER TABLE `task_appeals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ta_task_idx` (`task_id`);

--
-- Indexes for table `task_appeal_attachments`
--
ALTER TABLE `task_appeal_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `taa_appeal_idx` (`appeal_id`);

--
-- Indexes for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_idx` (`task_id`),
  ADD KEY `user_idx` (`user_id`);

--
-- Indexes for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ta_task_idx` (`task_id`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tc_task_idx` (`task_id`),
  ADD KEY `tc_user_idx` (`user_id`);

--
-- Indexes for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dependency` (`task_id`,`depends_on_task_id`),
  ADD KEY `td_task_idx` (`task_id`),
  ADD KEY `td_depends_idx` (`depends_on_task_id`);

--
-- Indexes for table `task_history`
--
ALTER TABLE `task_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `th_task_idx` (`task_id`);

--
-- Indexes for table `task_notifications`
--
ALTER TABLE `task_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tn_user_idx` (`user_id`),
  ADD KEY `tn_task_idx` (`task_id`);

--
-- Indexes for table `task_templates`
--
ALTER TABLE `task_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `userroles`
--
ALTER TABLE `userroles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_userroles_appointment` (`appointment_status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `usersupport`
--
ALTER TABLE `usersupport`
  ADD PRIMARY KEY (`support_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_user_contacts`
--
ALTER TABLE `chat_user_contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `department_requirements`
--
ALTER TABLE `department_requirements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mails`
--
ALTER TABLE `mails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `meeting_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `meeting_chats`
--
ALTER TABLE `meeting_chats`
  MODIFY `chat_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `meeting_notification`
--
ALTER TABLE `meeting_notification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=436;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `resource_files`
--
ALTER TABLE `resource_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `resource_logs`
--
ALTER TABLE `resource_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resource_sections`
--
ALTER TABLE `resource_sections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=508;

--
-- AUTO_INCREMENT for table `role_appeals`
--
ALTER TABLE `role_appeals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_assignment_requests`
--
ALTER TABLE `role_assignment_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_feedback`
--
ALTER TABLE `role_feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_history`
--
ALTER TABLE `role_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `role_kpis`
--
ALTER TABLE `role_kpis`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_requests`
--
ALTER TABLE `role_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_requirements`
--
ALTER TABLE `role_requirements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_resources`
--
ALTER TABLE `role_resources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `signaling`
--
ALTER TABLE `signaling`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `task_appeals`
--
ALTER TABLE `task_appeals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_appeal_attachments`
--
ALTER TABLE `task_appeal_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `task_notifications`
--
ALTER TABLE `task_notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `task_templates`
--
ALTER TABLE `task_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=435;

--
-- AUTO_INCREMENT for table `usersupport`
--
ALTER TABLE `usersupport`
  MODIFY `support_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_user_contacts`
--
ALTER TABLE `chat_user_contacts`
  ADD CONSTRAINT `chat_user_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_user_contacts_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `department_requirements`
--
ALTER TABLE `department_requirements`
  ADD CONSTRAINT `department_requirements_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_assigned_to_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meetings`
--
ALTER TABLE `meetings`
  ADD CONSTRAINT `meetings_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `meeting_chats`
--
ALTER TABLE `meeting_chats`
  ADD CONSTRAINT `meeting_chats_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`meeting_id`),
  ADD CONSTRAINT `meeting_chats_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `meeting_notification`
--
ALTER TABLE `meeting_notification`
  ADD CONSTRAINT `meeting_notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `meeting_notification_ibfk_2` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`meeting_id`) ON DELETE CASCADE;

--
-- Constraints for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  ADD CONSTRAINT `meeting_participants_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`meeting_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meeting_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `message_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`report_to`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resource_files`
--
ALTER TABLE `resource_files`
  ADD CONSTRAINT `resource_files_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `resource_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resource_sections`
--
ALTER TABLE `resource_sections`
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_history`
--
ALTER TABLE `role_history`
  ADD CONSTRAINT `role_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_history_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_requests`
--
ALTER TABLE `role_requests`
  ADD CONSTRAINT `role_requests_fk_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `role_requests_fk_requested_user` FOREIGN KEY (`requested_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_requests_fk_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_requirements`
--
ALTER TABLE `role_requirements`
  ADD CONSTRAINT `role_requirements_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_resources`
--
ALTER TABLE `role_resources`
  ADD CONSTRAINT `role_resources_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `fk_staff_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_appeals`
--
ALTER TABLE `task_appeals`
  ADD CONSTRAINT `ta_appeals_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_appeal_attachments`
--
ALTER TABLE `task_appeal_attachments`
  ADD CONSTRAINT `taa_appeal_fk` FOREIGN KEY (`appeal_id`) REFERENCES `task_appeals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD CONSTRAINT `ta_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `ta_attachments_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `tc_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD CONSTRAINT `td_depends_fk` FOREIGN KEY (`depends_on_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `td_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `th_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_notifications`
--
ALTER TABLE `task_notifications`
  ADD CONSTRAINT `tn_task_fk` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `userroles`
--
ALTER TABLE `userroles`
  ADD CONSTRAINT `userroles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `userroles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usersupport`
--
ALTER TABLE `usersupport`
  ADD CONSTRAINT `usersupport_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
