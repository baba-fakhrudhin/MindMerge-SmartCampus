-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2026 at 07:12 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mindmerge`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `attendance_time` time DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `teacher_name` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `period_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_assignment_id` int(11) DEFAULT NULL,
  `attendance_day` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `attendance_mode` enum('daily','period') NOT NULL DEFAULT 'period'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `class_id`, `section_id`, `attendance_date`, `attendance_time`, `remarks`, `created_by`, `teacher_id`, `teacher_name`, `created_at`, `period_id`, `subject_id`, `teacher_assignment_id`, `attendance_day`, `updated_at`, `attendance_mode`) VALUES
(10, 12, 11, '2026-06-11', NULL, 'All present', 12, NULL, NULL, '2026-06-11 06:38:40', NULL, NULL, NULL, 'thursday', NULL, 'daily'),
(11, 12, 11, '2026-06-10', NULL, '', 12, NULL, NULL, '2026-06-11 13:54:48', NULL, NULL, NULL, 'wednesday', NULL, 'daily'),
(12, 12, 11, '2026-06-09', NULL, '', 12, NULL, NULL, '2026-06-11 13:55:03', NULL, NULL, NULL, 'tuesday', NULL, 'daily'),
(13, 12, 11, '2026-06-08', NULL, '', 12, NULL, NULL, '2026-06-11 13:55:23', NULL, NULL, NULL, 'monday', NULL, 'daily'),
(14, 12, 11, '2026-06-07', NULL, '', 12, NULL, NULL, '2026-06-11 13:55:44', NULL, NULL, NULL, 'sunday', NULL, 'daily'),
(15, 12, 11, '2026-06-06', NULL, '', 12, NULL, NULL, '2026-06-11 13:56:24', NULL, NULL, NULL, 'saturday', NULL, 'daily'),
(16, 13, 13, '2026-06-11', NULL, '', 12, NULL, NULL, '2026-06-11 13:59:03', NULL, NULL, NULL, 'thursday', NULL, 'daily'),
(17, 13, 13, '2026-06-10', NULL, '', 12, NULL, NULL, '2026-06-11 14:05:53', NULL, NULL, NULL, 'wednesday', NULL, 'daily');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `record_id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('present','absent','late','leave','medical_leave','od') DEFAULT 'present',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`record_id`, `attendance_id`, `student_id`, `status`, `remarks`, `created_at`) VALUES
(15, 10, 6, 'present', NULL, '2026-06-11 06:38:40'),
(16, 10, 7, 'present', NULL, '2026-06-11 06:38:40'),
(17, 11, 6, 'present', NULL, '2026-06-11 13:54:48'),
(18, 11, 7, 'present', NULL, '2026-06-11 13:54:48'),
(19, 12, 6, 'present', NULL, '2026-06-11 13:55:03'),
(20, 12, 7, 'present', NULL, '2026-06-11 13:55:03'),
(21, 13, 6, 'absent', NULL, '2026-06-11 13:55:23'),
(22, 13, 7, 'absent', NULL, '2026-06-11 13:55:23'),
(23, 14, 6, 'late', NULL, '2026-06-11 13:55:44'),
(24, 14, 7, 'late', NULL, '2026-06-11 13:55:44'),
(25, 15, 6, 'present', NULL, '2026-06-11 13:56:24'),
(26, 15, 7, 'medical_leave', NULL, '2026-06-11 13:56:24'),
(27, 16, 10, 'present', NULL, '2026-06-11 13:59:03'),
(28, 17, 10, 'present', NULL, '2026-06-11 14:05:53');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `bus_id` int(11) NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bus_assignments`
--

CREATE TABLE `bus_assignments` (
  `assignment_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `pickup_stop_id` int(11) DEFAULT NULL,
  `drop_stop_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_code` varchar(20) DEFAULT NULL,
  `class_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_code`, `class_name`, `description`, `status`, `created_at`) VALUES
(12, 'CSE', 'Computer Science & Engineering', '', 'active', '2026-06-11 05:25:04'),
(13, 'CAI', 'Computer Science & Engineering AI', '', 'active', '2026-06-11 05:26:06');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `driver_id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `license_number` varchar(80) NOT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `exam_id` int(11) NOT NULL,
  `exam_type_id` int(11) DEFAULT NULL,
  `exam_name` varchar(150) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `status` enum('draft','published','completed','archived') NOT NULL DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exam_halls`
--

CREATE TABLE `exam_halls` (
  `hall_id` int(11) NOT NULL,
  `hall_name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exam_hall_allocations`
--

CREATE TABLE `exam_hall_allocations` (
  `allocation_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `hall_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `seat_no` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exam_invigilators`
--

CREATE TABLE `exam_invigilators` (
  `invigilator_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `duty_role` varchar(50) DEFAULT 'invigilator'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedule`
--

CREATE TABLE `exam_schedule` (
  `schedule_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exam_types`
--

CREATE TABLE `exam_types` (
  `exam_type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `grading_system`
--

CREATE TABLE `grading_system` (
  `grading_id` int(11) NOT NULL,
  `grade_name` varchar(20) NOT NULL,
  `min_marks` decimal(6,2) NOT NULL,
  `max_marks` decimal(6,2) NOT NULL,
  `grade_point` decimal(4,2) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('general','exam','fee','attendance','result','announcement','holiday','event','transport','emergency') NOT NULL DEFAULT 'general',
  `source_module` varchar(50) DEFAULT NULL,
  `source_ref` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `source_module`, `source_ref`, `created_by`, `created_at`) VALUES
(1, 'All students should attend to class properly', 'Message to all students', 'attendance', NULL, NULL, 24, '2026-06-12 12:42:31'),
(2, 'Results Published', 'Examination results are now available. Log in to the portal to view your detailed mark sheet and performance summary.', 'result', NULL, NULL, 26, '2026-06-13 06:27:57'),
(3, 'Exam Schedule Published', 'The examination schedule has been published. Please check the exam dates, timings, and venue details on the portal.', 'exam', NULL, NULL, 26, '2026-06-13 06:32:24'),
(4, 'Holiday Announcement', 'The campus will remain closed on the announced holiday. Regular classes and activities will resume on the next working day.', 'holiday', NULL, NULL, 26, '2026-06-14 10:40:44');

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `read_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notification_reads`
--

INSERT INTO `notification_reads` (`read_id`, `notification_id`, `user_id`, `read_at`) VALUES
(1, 1, 26, '2026-06-13 06:25:59'),
(2, 2, 26, '2026-06-13 06:27:57'),
(3, 2, 16, '2026-06-13 06:31:43'),
(4, 3, 26, '2026-06-13 06:32:24'),
(5, 1, 24, '2026-06-13 06:32:56'),
(6, 2, 24, '2026-06-13 06:33:08'),
(7, 3, 24, '2026-06-13 06:33:15'),
(8, 4, 26, '2026-06-14 10:40:44'),
(9, 4, 16, '2026-06-14 10:41:34');

-- --------------------------------------------------------

--
-- Table structure for table `notification_targets`
--

CREATE TABLE `notification_targets` (
  `target_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `target_type` enum('role','class','section','student','teacher') DEFAULT NULL,
  `target_value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notification_targets`
--

INSERT INTO `notification_targets` (`target_id`, `notification_id`, `target_type`, `target_value`) VALUES
(1, 1, 'student', 'STU00007'),
(2, 2, 'section', '11'),
(3, 3, 'role', 'teacher'),
(4, 4, 'role', 'student');

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `template_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` varchar(50) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `relationship_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `periods`
--

CREATE TABLE `periods` (
  `period_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `period_type` enum('regular','break','lunch','lab','exam','activity') DEFAULT 'regular',
  `attendance_allowed` enum('yes','no') DEFAULT 'yes',
  `is_teaching_period` enum('yes','no') DEFAULT 'yes',
  `display_color` varchar(20) DEFAULT '#3b82f6',
  `room_required` enum('yes','no') DEFAULT 'yes',
  `notes` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `periods`
--

INSERT INTO `periods` (`period_id`, `template_id`, `period_name`, `start_time`, `end_time`, `period_type`, `attendance_allowed`, `is_teaching_period`, `display_color`, `room_required`, `notes`, `sort_order`, `status`, `created_at`) VALUES
(10, 7, 'P1', '09:10:00', '10:00:00', 'regular', 'yes', 'yes', '#ffd500', 'yes', '', 1, 'active', '2026-06-09 09:59:39'),
(11, 7, 'P2', '10:00:00', '10:50:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 2, 'active', '2026-06-09 10:00:16'),
(12, 7, 'P3', '10:50:00', '11:40:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 3, 'active', '2026-06-09 10:01:33'),
(13, 7, 'P4', '11:40:00', '12:30:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 4, 'active', '2026-06-09 10:02:14'),
(15, 7, 'P5', '13:30:00', '14:30:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 6, 'active', '2026-06-09 10:05:27'),
(16, 7, 'P6', '14:30:00', '15:30:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 7, 'active', '2026-06-09 10:06:20'),
(17, 7, 'P7', '15:30:00', '16:30:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 8, 'active', '2026-06-09 10:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `period_templates`
--

CREATE TABLE `period_templates` (
  `template_id` int(11) NOT NULL,
  `template_code` varchar(50) DEFAULT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_type` enum('regular','exam','lab','hostel','custom') DEFAULT 'regular',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `period_templates`
--

INSERT INTO `period_templates` (`template_id`, `template_code`, `template_name`, `template_type`, `description`, `status`, `created_at`) VALUES
(7, 'REGULAR', 'Classes', 'regular', '', 'active', '2026-06-09 09:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `module_key` varchar(50) NOT NULL,
  `action_key` varchar(20) NOT NULL,
  `label` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `module_key`, `action_key`, `label`, `sort_order`) VALUES
(1, 'dashboard', 'view', 'View Dashboard', 10),
(2, 'profile', 'view', 'View Profile', 20),
(3, 'profile', 'edit', 'Edit Profile', 21),
(4, 'classes', 'view', 'View Classes', 100),
(5, 'classes', 'create', 'Create Classes', 101),
(6, 'classes', 'edit', 'Edit Classes', 102),
(7, 'classes', 'delete', 'Delete Classes', 103),
(8, 'sections', 'view', 'View Sections', 110),
(9, 'sections', 'create', 'Create Sections', 111),
(10, 'sections', 'edit', 'Edit Sections', 112),
(11, 'sections', 'delete', 'Delete Sections', 113),
(12, 'students', 'view', 'View Students', 120),
(13, 'students', 'create', 'Create Students', 121),
(14, 'students', 'edit', 'Edit Students', 122),
(15, 'students', 'delete', 'Delete Students', 123),
(16, 'teachers', 'view', 'View Teachers', 130),
(17, 'teachers', 'create', 'Create Teachers', 131),
(18, 'teachers', 'edit', 'Edit Teachers', 132),
(19, 'teachers', 'delete', 'Delete Teachers', 133),
(20, 'schedules', 'view', 'View Schedules', 140),
(21, 'schedules', 'create', 'Create Schedules', 141),
(22, 'schedules', 'edit', 'Edit Schedules', 142),
(23, 'schedules', 'delete', 'Delete Schedules', 143),
(24, 'timetables', 'view', 'View Timetables', 150),
(25, 'timetables', 'create', 'Create Timetables', 151),
(26, 'timetables', 'edit', 'Edit Timetables', 152),
(27, 'timetables', 'delete', 'Delete Timetables', 153),
(28, 'attendance', 'view', 'View Attendance', 160),
(29, 'attendance', 'create', 'Create Attendance', 161),
(30, 'attendance', 'edit', 'Edit Attendance', 162),
(31, 'attendance', 'delete', 'Delete Attendance', 163),
(32, 'teacher_attendance', 'view', 'View Teacher Attendance', 170),
(33, 'teacher_attendance', 'create', 'Create Teacher Attendance', 171),
(34, 'teacher_attendance', 'edit', 'Edit Teacher Attendance', 172),
(35, 'teacher_attendance', 'delete', 'Delete Teacher Attendance', 173),
(36, 'notifications', 'view', 'View Notifications', 180),
(37, 'notifications', 'create', 'Create Notifications', 181),
(38, 'notifications', 'edit', 'Edit Notifications', 182),
(39, 'notifications', 'delete', 'Delete Notifications', 183),
(40, 'exams', 'view', 'View Exams', 190),
(41, 'exams', 'create', 'Create Exams', 191),
(42, 'exams', 'edit', 'Edit Exams', 192),
(43, 'exams', 'delete', 'Delete Exams', 193),
(44, 'permissions', 'view', 'View Permission Settings', 200),
(45, 'permissions', 'edit', 'Edit Permission Settings', 201),
(46, 'results', 'view', 'View Results', 185),
(47, 'results', 'create', 'Create Results', 186),
(48, 'results', 'edit', 'Edit Results', 187),
(49, 'results', 'delete', 'Delete Results', 188),
(50, 'transport', 'view', 'View Transport', 195),
(51, 'transport', 'create', 'Create Transport', 196),
(52, 'transport', 'edit', 'Edit Transport', 197),
(53, 'transport', 'delete', 'Delete Transport', 198);

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `result_type` varchar(50) NOT NULL DEFAULT 'semester',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `result_entries`
--

CREATE TABLE `result_entries` (
  `entry_id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `internal_marks` decimal(6,2) NOT NULL DEFAULT 0.00,
  `external_marks` decimal(6,2) NOT NULL DEFAULT 0.00,
  `lab_marks` decimal(6,2) NOT NULL DEFAULT 0.00,
  `attendance_marks` decimal(6,2) NOT NULL DEFAULT 0.00,
  `total_marks` decimal(6,2) NOT NULL DEFAULT 0.00,
  `grade` varchar(20) DEFAULT NULL,
  `grade_point` decimal(4,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_permission_id` int(11) NOT NULL,
  `role` enum('admin','teacher','student','parent') NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_permission_id`, `role`, `permission_id`, `granted`) VALUES
(1, 'admin', 28, 1),
(2, 'admin', 29, 1),
(3, 'admin', 30, 1),
(4, 'admin', 31, 1),
(5, 'admin', 4, 1),
(6, 'admin', 5, 1),
(7, 'admin', 6, 1),
(8, 'admin', 7, 1),
(9, 'admin', 1, 1),
(10, 'admin', 40, 1),
(11, 'admin', 41, 1),
(12, 'admin', 42, 1),
(13, 'admin', 43, 1),
(14, 'admin', 36, 1),
(15, 'admin', 37, 1),
(16, 'admin', 38, 1),
(17, 'admin', 39, 1),
(18, 'admin', 44, 1),
(19, 'admin', 45, 1),
(20, 'admin', 2, 1),
(21, 'admin', 3, 1),
(22, 'admin', 20, 1),
(23, 'admin', 21, 1),
(24, 'admin', 22, 1),
(25, 'admin', 23, 1),
(26, 'admin', 8, 1),
(27, 'admin', 9, 1),
(28, 'admin', 10, 1),
(29, 'admin', 11, 1),
(30, 'admin', 12, 1),
(31, 'admin', 13, 1),
(32, 'admin', 14, 1),
(33, 'admin', 15, 1),
(34, 'admin', 16, 1),
(35, 'admin', 17, 1),
(36, 'admin', 18, 1),
(37, 'admin', 19, 1),
(38, 'admin', 32, 1),
(39, 'admin', 33, 1),
(40, 'admin', 34, 1),
(41, 'admin', 35, 1),
(42, 'admin', 24, 1),
(43, 'admin', 25, 1),
(44, 'admin', 26, 1),
(45, 'admin', 27, 1),
(64, 'teacher', 29, 1),
(65, 'teacher', 31, 0),
(66, 'teacher', 30, 1),
(67, 'teacher', 28, 1),
(68, 'teacher', 5, 0),
(69, 'teacher', 7, 0),
(70, 'teacher', 6, 0),
(71, 'teacher', 4, 0),
(72, 'teacher', 1, 1),
(73, 'teacher', 41, 0),
(74, 'teacher', 43, 0),
(75, 'teacher', 42, 0),
(76, 'teacher', 40, 1),
(77, 'teacher', 37, 1),
(78, 'teacher', 39, 0),
(79, 'teacher', 38, 0),
(80, 'teacher', 36, 1),
(81, 'teacher', 45, 0),
(82, 'teacher', 44, 0),
(83, 'teacher', 3, 1),
(84, 'teacher', 2, 1),
(85, 'teacher', 21, 0),
(86, 'teacher', 23, 0),
(87, 'teacher', 22, 0),
(88, 'teacher', 20, 0),
(89, 'teacher', 9, 0),
(90, 'teacher', 11, 0),
(91, 'teacher', 10, 0),
(92, 'teacher', 8, 0),
(93, 'teacher', 13, 0),
(94, 'teacher', 15, 0),
(95, 'teacher', 14, 0),
(96, 'teacher', 12, 1),
(97, 'teacher', 17, 0),
(98, 'teacher', 19, 0),
(99, 'teacher', 18, 0),
(100, 'teacher', 16, 0),
(101, 'teacher', 33, 0),
(102, 'teacher', 35, 0),
(103, 'teacher', 34, 0),
(104, 'teacher', 32, 1),
(105, 'teacher', 25, 0),
(106, 'teacher', 27, 0),
(107, 'teacher', 26, 0),
(108, 'teacher', 24, 1),
(127, 'student', 29, 0),
(128, 'student', 31, 0),
(129, 'student', 30, 0),
(130, 'student', 28, 1),
(131, 'student', 5, 0),
(132, 'student', 7, 0),
(133, 'student', 6, 0),
(134, 'student', 4, 0),
(135, 'student', 1, 1),
(136, 'student', 41, 0),
(137, 'student', 43, 0),
(138, 'student', 42, 0),
(139, 'student', 40, 1),
(140, 'student', 37, 0),
(141, 'student', 39, 0),
(142, 'student', 38, 0),
(143, 'student', 36, 1),
(144, 'student', 45, 0),
(145, 'student', 44, 0),
(146, 'student', 3, 1),
(147, 'student', 2, 1),
(148, 'student', 21, 0),
(149, 'student', 23, 0),
(150, 'student', 22, 0),
(151, 'student', 20, 0),
(152, 'student', 9, 0),
(153, 'student', 11, 0),
(154, 'student', 10, 0),
(155, 'student', 8, 0),
(156, 'student', 13, 0),
(157, 'student', 15, 0),
(158, 'student', 14, 0),
(159, 'student', 12, 0),
(160, 'student', 17, 0),
(161, 'student', 19, 0),
(162, 'student', 18, 0),
(163, 'student', 16, 0),
(164, 'student', 33, 0),
(165, 'student', 35, 0),
(166, 'student', 34, 0),
(167, 'student', 32, 0),
(168, 'student', 25, 0),
(169, 'student', 27, 0),
(170, 'student', 26, 0),
(171, 'student', 24, 1),
(190, 'parent', 29, 0),
(191, 'parent', 31, 0),
(192, 'parent', 30, 0),
(193, 'parent', 28, 1),
(194, 'parent', 5, 0),
(195, 'parent', 7, 0),
(196, 'parent', 6, 0),
(197, 'parent', 4, 0),
(198, 'parent', 1, 1),
(199, 'parent', 41, 0),
(200, 'parent', 43, 0),
(201, 'parent', 42, 0),
(202, 'parent', 40, 1),
(203, 'parent', 37, 0),
(204, 'parent', 39, 0),
(205, 'parent', 38, 0),
(206, 'parent', 36, 1),
(207, 'parent', 45, 0),
(208, 'parent', 44, 0),
(209, 'parent', 3, 1),
(210, 'parent', 2, 1),
(211, 'parent', 21, 0),
(212, 'parent', 23, 0),
(213, 'parent', 22, 0),
(214, 'parent', 20, 0),
(215, 'parent', 9, 0),
(216, 'parent', 11, 0),
(217, 'parent', 10, 0),
(218, 'parent', 8, 0),
(219, 'parent', 13, 0),
(220, 'parent', 15, 0),
(221, 'parent', 14, 0),
(222, 'parent', 12, 0),
(223, 'parent', 17, 0),
(224, 'parent', 19, 0),
(225, 'parent', 18, 0),
(226, 'parent', 16, 0),
(227, 'parent', 33, 0),
(228, 'parent', 35, 0),
(229, 'parent', 34, 0),
(230, 'parent', 32, 0),
(231, 'parent', 25, 0),
(232, 'parent', 27, 0),
(233, 'parent', 26, 0),
(234, 'parent', 24, 0),
(433, 'admin', 46, 1),
(434, 'admin', 47, 1),
(435, 'admin', 48, 1),
(436, 'admin', 49, 1),
(437, 'admin', 50, 1),
(438, 'admin', 51, 1),
(439, 'admin', 52, 1),
(440, 'admin', 53, 1),
(448, 'teacher', 47, 1),
(449, 'teacher', 49, 0),
(450, 'teacher', 48, 1),
(451, 'teacher', 46, 1),
(452, 'teacher', 51, 0),
(453, 'teacher', 53, 0),
(454, 'teacher', 52, 0),
(455, 'teacher', 50, 0),
(463, 'student', 47, 0),
(464, 'student', 49, 0),
(465, 'student', 48, 0),
(466, 'student', 46, 1),
(467, 'student', 51, 0),
(468, 'student', 53, 0),
(469, 'student', 52, 0),
(470, 'student', 50, 1),
(478, 'parent', 47, 0),
(479, 'parent', 49, 0),
(480, 'parent', 48, 0),
(481, 'parent', 46, 1),
(482, 'parent', 51, 0),
(483, 'parent', 53, 0),
(484, 'parent', 52, 0),
(485, 'parent', 50, 1);

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `route_id` int(11) NOT NULL,
  `route_code` varchar(50) NOT NULL,
  `route_name` varchar(120) NOT NULL,
  `start_point` varchar(150) DEFAULT NULL,
  `end_point` varchar(150) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `route_stops`
--

CREATE TABLE `route_stops` (
  `route_stop_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `stop_id` int(11) NOT NULL,
  `stop_order` int(11) NOT NULL,
  `arrival_time` time DEFAULT NULL,
  `departure_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_code` varchar(50) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `capacity` int(11) DEFAULT 50,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `class_id`, `section_code`, `section_name`, `capacity`, `status`, `created_at`) VALUES
(11, 12, 'A', 'A Section', 66, 'active', '2026-06-11 05:26:53'),
(12, 12, 'B', 'B Section', 66, 'active', '2026-06-11 05:27:05'),
(13, 13, 'A', 'A Section', 66, 'active', '2026-06-11 05:27:20'),
(14, 13, 'B', 'B Section', 66, 'active', '2026-06-11 05:28:26');

-- --------------------------------------------------------

--
-- Table structure for table `stops`
--

CREATE TABLE `stops` (
  `stop_id` int(11) NOT NULL,
  `stop_name` varchar(150) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `class_name` varchar(30) DEFAULT NULL,
  `section_name` varchar(30) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `class_id`, `section_id`, `class_name`, `section_name`, `dob`, `gender`, `address`, `parent_phone`, `created_at`) VALUES
(6, 16, 'STU00001', 12, 11, '', '', '2026-06-11', 'male', '', '9000322870', '2026-06-11 05:30:45'),
(7, 17, 'STU00002', 12, 11, 'Computer Science & Engineering', 'A Section', '2026-06-11', 'male', '', '', '2026-06-11 05:31:26'),
(8, 18, 'STU00003', 12, 12, 'Computer Science & Engineering', 'B Section', '2008-10-10', 'male', '', '9000322870', '2026-06-11 05:32:15'),
(9, 19, 'STU00004', 12, 12, 'Computer Science & Engineering', 'B Section', '2026-06-11', 'male', '', '8790844365', '2026-06-11 05:33:02'),
(10, 20, 'STU00005', 13, 13, 'Computer Science & Engineering', 'A Section', '2026-06-30', 'male', '9-87, nagoor colongy, kuntrapakam(post)\r\ntirupati rural', '9000322870', '2026-06-11 05:33:34'),
(11, 21, 'STU00006', 13, 14, 'Computer Science & Engineering', 'B Section', '2026-12-31', 'male', '', '8790844365', '2026-06-11 05:34:20'),
(12, 22, 'STU00007', 13, 14, 'Computer Science & Engineering', 'B Section', '2023-11-30', 'male', '', '997934', '2026-06-11 05:35:29');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(30) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `description`, `status`, `created_at`) VALUES
(7, 'CSE001', 'DBMS', '', 'active', '2026-06-11 05:29:09'),
(8, 'CSE002', 'OS', '', 'active', '2026-06-11 05:29:19'),
(9, 'CSE003', 'Java', '', 'active', '2026-06-11 05:29:31'),
(10, 'CSE004', 'Python', '', 'active', '2026-06-11 05:29:41');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `teacher_id` varchar(50) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `teacher_id`, `class_id`, `section_id`, `subject_name`, `qualification`, `specialization`, `salary`, `created_at`) VALUES
(7, 23, 'TCH00001', NULL, NULL, NULL, 'M.tech.,', NULL, NULL, '2026-06-11 05:36:14'),
(8, 24, 'TCH00002', NULL, NULL, NULL, 'M.Tech.,', NULL, NULL, '2026-06-11 05:36:55');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `assignment_role` enum('primary','co_primary','lab_incharge','lab_faculty','lab_assistant') NOT NULL DEFAULT 'primary',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`assignment_id`, `teacher_id`, `subject_id`, `class_id`, `section_id`, `assignment_role`, `created_at`) VALUES
(12, 7, 7, 12, 11, 'primary', '2026-06-11 05:37:14'),
(13, 8, 7, 12, 11, 'co_primary', '2026-06-11 05:37:25'),
(14, 7, 9, 12, 11, 'co_primary', '2026-06-11 05:37:39'),
(15, 8, 9, 12, 11, 'primary', '2026-06-11 05:38:46'),
(16, 7, 7, 12, 12, 'lab_incharge', '2026-06-11 05:39:07'),
(17, 7, 10, 13, 13, 'lab_incharge', '2026-06-11 05:39:20'),
(18, 7, 8, 13, 14, 'lab_faculty', '2026-06-11 05:39:36'),
(19, 8, 8, 12, 12, 'lab_incharge', '2026-06-11 05:39:50'),
(20, 8, 9, 13, 14, 'lab_faculty', '2026-06-11 05:40:12');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_attendance`
--

CREATE TABLE `teacher_attendance` (
  `attendance_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','leave','medical_leave','od','half_day') NOT NULL DEFAULT 'present',
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teacher_attendance`
--

INSERT INTO `teacher_attendance` (`attendance_id`, `teacher_id`, `attendance_date`, `status`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 7, '2026-06-12', 'present', '', 12, '2026-06-12 05:22:36', '2026-06-12 05:32:25'),
(2, 8, '2026-06-12', 'present', '', 12, '2026-06-12 05:22:36', '2026-06-12 05:32:25'),
(3, 7, '2026-06-11', 'od', '', 12, '2026-06-12 05:25:51', NULL),
(4, 8, '2026-06-11', 'absent', '', 12, '2026-06-12 05:25:51', NULL),
(5, 7, '2026-06-13', 'present', '', 26, '2026-06-13 05:33:22', NULL),
(6, 8, '2026-06-13', 'present', '', 26, '2026-06-13 05:33:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `timetable_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`timetable_id`, `class_id`, `section_id`, `template_id`, `academic_year`, `status`, `created_at`, `effective_from`, `effective_to`) VALUES
(12, 12, 11, 7, '2026-27', 'active', '2026-06-11 05:42:10', '2026-06-11', NULL),
(13, 13, 13, 7, '2025-26', 'active', '2026-06-11 13:58:34', '2026-06-11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetable_entries`
--

CREATE TABLE `timetable_entries` (
  `entry_id` int(11) NOT NULL,
  `timetable_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `period_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_assignment_id` int(11) NOT NULL,
  `room_no` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `timetable_entries`
--

INSERT INTO `timetable_entries` (`entry_id`, `timetable_id`, `day_of_week`, `period_id`, `subject_id`, `teacher_assignment_id`, `room_no`, `remarks`, `created_at`) VALUES
(17, 12, 'monday', 10, 7, 12, '', '', '2026-06-11 05:42:43'),
(18, 12, 'tuesday', 10, 9, 15, '', '', '2026-06-11 05:42:53'),
(19, 12, 'wednesday', 10, 7, 15, '', '', '2026-06-11 05:43:00'),
(20, 12, 'thursday', 12, 7, 12, '', '', '2026-06-11 05:43:06'),
(21, 12, 'thursday', 17, 7, 12, '', '', '2026-06-11 05:43:14'),
(22, 12, 'monday', 16, 7, 12, '', '', '2026-06-13 17:11:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student','parent') NOT NULL,
  `profile_photo` varchar(255) DEFAULT 'default.png',
  `theme_preference` varchar(20) DEFAULT 'light',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `profile_photo`, `theme_preference`, `created_at`, `admin_id`) VALUES
(16, 'Abdul Karim S', 'adbul@gmail.com', '900000001', '$2y$10$FdCqTIOuk8LagT9sj8MuyOrW1jyQcBU9I6Xrhp3QxkR2gh0tGm0Ze', 'student', 'default.png', 'light', '2026-06-11 05:30:45', NULL),
(17, 'Akash S', 'akash@gmail.com', '9704290782', '$2y$10$taXNmdTqTfpGljHuRwI37.bznLJB/w.9kluXVhRJvUCnOaLknVnDm', 'student', 'default.png', 'light', '2026-06-11 05:31:26', NULL),
(18, 'Akshaya G', 'akshaya@gmail.com', '9704290782', '$2y$10$VMxngb68vOnq0JdeZNKN1.RdzbHyvd19wW8ROpoBSh0Al8bxQIz92', 'student', 'default.png', 'light', '2026-06-11 05:32:15', NULL),
(19, 'Aruna N', 'aruna@gmail.com', '8790844365', '$2y$10$jJ3lhIZTOrr4uRlr1meWpuYFQ2YInTBO4ej2pDFvmix5rxgS1FJ7.', 'student', 'default.png', 'light', '2026-06-11 05:33:02', NULL),
(20, 'Baba', 'babafake2008@gmail.com', '9704290782', '$2y$10$.fCQxp33B10Axznqz8ZFLe3WTi9Hie/5hRltuGt03EKyKugETp3Mu', 'student', 'default.png', 'light', '2026-06-11 05:33:34', NULL),
(21, 'Sravanthi', 'swifttech000@gmail.com', '8790844365', '$2y$10$Kfe1Rm8rRpEwEEyuh4Fq5upSIvmTZhyAXw1xi4ivjHsULk21snl2e', 'student', 'default.png', 'light', '2026-06-11 05:34:20', NULL),
(22, 'Ashraf Vali', 'ashraf@gmail.com', '6281025228', '$2y$10$4rxxlZrJ472wPw29.HDTEOU91Mc9dCA/ISq/MmupjUpAQqTAOqn4.', 'student', 'default.png', 'light', '2026-06-11 05:35:29', NULL),
(23, 'E. Rajesh', 'rajeshsir@gmail.com', '6281025228', '$2y$10$m8uALctogvVtBU7G7tF/aOat9nys35bJJ26ydR94lAWOqtyu8yUsy', 'teacher', 'default.png', 'light', '2026-06-11 05:36:14', NULL),
(24, 'Gayatri Mam', 'gayatri@gmail.com', '9704290782', '$2y$10$XlIpngx6lyvjyOFQjkI2/OVGzisYUOO7aKlqRFwuLX13.0RlBoLwm', 'teacher', 'default.png', 'light', '2026-06-11 05:36:55', NULL),
(26, 'R.Baba', 'toearnviv@gmail.com', '99', '$2y$10$CgTFCNe2TTPjIbKRofQnIuPdFlGmxXXp5yjDJIJcuOT9Tdi2gRmk2', 'admin', 'default.png', 'light', '2026-06-13 00:55:33', 'ADM0001');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `user_permission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`class_id`,`section_id`,`attendance_date`,`attendance_mode`,`period_id`),
  ADD KEY `idx_date` (`attendance_date`),
  ADD KEY `idx_class` (`class_id`),
  ADD KEY `idx_section` (`section_id`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_period` (`period_id`),
  ADD KEY `idx_teacher_assignment` (`teacher_assignment_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `unique_student_attendance` (`attendance_id`,`student_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_attendance` (`attendance_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`bus_id`),
  ADD UNIQUE KEY `unique_bus_number` (`bus_number`),
  ADD UNIQUE KEY `unique_registration_number` (`registration_number`);

--
-- Indexes for table `bus_assignments`
--
ALTER TABLE `bus_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_student_transport` (`student_id`,`status`),
  ADD KEY `idx_bus_route` (`bus_id`,`route_id`),
  ADD KEY `fk_bus_assignments_route` (`route_id`),
  ADD KEY `fk_bus_assignments_driver` (`driver_id`),
  ADD KEY `fk_bus_assignments_pickup` (`pickup_stop_id`),
  ADD KEY `fk_bus_assignments_drop` (`drop_stop_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD UNIQUE KEY `unique_class_code` (`class_code`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`driver_id`),
  ADD UNIQUE KEY `unique_license_number` (`license_number`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `idx_exam_status` (`status`),
  ADD KEY `fk_exams_type` (`exam_type_id`);

--
-- Indexes for table `exam_halls`
--
ALTER TABLE `exam_halls`
  ADD PRIMARY KEY (`hall_id`);

--
-- Indexes for table `exam_hall_allocations`
--
ALTER TABLE `exam_hall_allocations`
  ADD PRIMARY KEY (`allocation_id`),
  ADD KEY `idx_hall_schedule` (`schedule_id`,`hall_id`),
  ADD KEY `fk_exam_hall_alloc_hall` (`hall_id`),
  ADD KEY `fk_exam_hall_alloc_student` (`student_id`);

--
-- Indexes for table `exam_invigilators`
--
ALTER TABLE `exam_invigilators`
  ADD PRIMARY KEY (`invigilator_id`),
  ADD UNIQUE KEY `unique_schedule_teacher` (`schedule_id`,`teacher_id`),
  ADD KEY `fk_exam_invigilators_teacher` (`teacher_id`);

--
-- Indexes for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_exam_schedule_lookup` (`exam_id`,`class_id`,`section_id`,`exam_date`),
  ADD KEY `fk_exam_schedule_class` (`class_id`),
  ADD KEY `fk_exam_schedule_section` (`section_id`),
  ADD KEY `fk_exam_schedule_subject` (`subject_id`);

--
-- Indexes for table `exam_types`
--
ALTER TABLE `exam_types`
  ADD PRIMARY KEY (`exam_type_id`),
  ADD UNIQUE KEY `unique_exam_type` (`type_name`);

--
-- Indexes for table `grading_system`
--
ALTER TABLE `grading_system`
  ADD PRIMARY KEY (`grading_id`),
  ADD KEY `idx_grade_range` (`min_marks`,`max_marks`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_type_created` (`type`,`created_at`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`read_id`),
  ADD UNIQUE KEY `unique_read` (`notification_id`,`user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_notification_id` (`notification_id`);

--
-- Indexes for table `notification_targets`
--
ALTER TABLE `notification_targets`
  ADD PRIMARY KEY (`target_id`),
  ADD KEY `idx_notification_id` (`notification_id`),
  ADD KEY `idx_target_lookup` (`target_type`,`target_value`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`template_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parent_id` (`parent_id`),
  ADD UNIQUE KEY `parent_id_2` (`parent_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `periods`
--
ALTER TABLE `periods`
  ADD PRIMARY KEY (`period_id`),
  ADD UNIQUE KEY `unique_template_order` (`template_id`,`sort_order`),
  ADD KEY `idx_period_template` (`template_id`);

--
-- Indexes for table `period_templates`
--
ALTER TABLE `period_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD UNIQUE KEY `template_code` (`template_code`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `unique_module_action` (`module_key`,`action_key`),
  ADD KEY `idx_module` (`module_key`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `idx_result_scope` (`class_id`,`section_id`,`academic_year`,`semester`),
  ADD KEY `idx_result_status` (`status`),
  ADD KEY `fk_results_section` (`section_id`);

--
-- Indexes for table `result_entries`
--
ALTER TABLE `result_entries`
  ADD PRIMARY KEY (`entry_id`),
  ADD UNIQUE KEY `unique_result_student_subject` (`result_id`,`student_id`,`subject_id`),
  ADD KEY `idx_result_student` (`student_id`),
  ADD KEY `idx_result_subject` (`subject_id`),
  ADD KEY `fk_result_entries_teacher` (`teacher_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_permission_id`),
  ADD UNIQUE KEY `unique_role_permission` (`role`,`permission_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_permission` (`permission_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`route_id`),
  ADD UNIQUE KEY `unique_route_code` (`route_code`);

--
-- Indexes for table `route_stops`
--
ALTER TABLE `route_stops`
  ADD PRIMARY KEY (`route_stop_id`),
  ADD UNIQUE KEY `unique_route_stop_order` (`route_id`,`stop_order`),
  ADD KEY `fk_route_stops_stop` (`stop_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `unique_section_per_class` (`class_id`,`section_code`);

--
-- Indexes for table `stops`
--
ALTER TABLE `stops`
  ADD PRIMARY KEY (`stop_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `student_id_2` (`student_id`),
  ADD UNIQUE KEY `student_id_3` (`student_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`),
  ADD UNIQUE KEY `teacher_id_2` (`teacher_id`),
  ADD UNIQUE KEY `teacher_id_3` (`teacher_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `idx_assignment_lookup` (`teacher_id`,`class_id`,`section_id`,`subject_id`);

--
-- Indexes for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`,`attendance_date`),
  ADD KEY `idx_teacher_created` (`created_at`),
  ADD KEY `idx_teacher_date` (`teacher_id`,`attendance_date`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`timetable_id`),
  ADD UNIQUE KEY `unique_class_section` (`class_id`,`section_id`);

--
-- Indexes for table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  ADD PRIMARY KEY (`entry_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`user_permission_id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_permission` (`permission_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bus_assignments`
--
ALTER TABLE `bus_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_halls`
--
ALTER TABLE `exam_halls`
  MODIFY `hall_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_hall_allocations`
--
ALTER TABLE `exam_hall_allocations`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_invigilators`
--
ALTER TABLE `exam_invigilators`
  MODIFY `invigilator_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_types`
--
ALTER TABLE `exam_types`
  MODIFY `exam_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grading_system`
--
ALTER TABLE `grading_system`
  MODIFY `grading_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `read_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notification_targets`
--
ALTER TABLE `notification_targets`
  MODIFY `target_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `periods`
--
ALTER TABLE `periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `period_templates`
--
ALTER TABLE `period_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `result_entries`
--
ALTER TABLE `result_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `role_permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=493;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `route_stop_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `stops`
--
ALTER TABLE `stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `user_permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `fk_attendance_period` FOREIGN KEY (`period_id`) REFERENCES `periods` (`period_id`),
  ADD CONSTRAINT `fk_attendance_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `fk_attendance_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`) ON DELETE CASCADE;

--
-- Constraints for table `bus_assignments`
--
ALTER TABLE `bus_assignments`
  ADD CONSTRAINT `fk_bus_assignments_bus` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`),
  ADD CONSTRAINT `fk_bus_assignments_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bus_assignments_drop` FOREIGN KEY (`drop_stop_id`) REFERENCES `stops` (`stop_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bus_assignments_pickup` FOREIGN KEY (`pickup_stop_id`) REFERENCES `stops` (`stop_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bus_assignments_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`),
  ADD CONSTRAINT `fk_bus_assignments_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `fk_exams_type` FOREIGN KEY (`exam_type_id`) REFERENCES `exam_types` (`exam_type_id`) ON DELETE SET NULL;

--
-- Constraints for table `exam_hall_allocations`
--
ALTER TABLE `exam_hall_allocations`
  ADD CONSTRAINT `fk_exam_hall_alloc_hall` FOREIGN KEY (`hall_id`) REFERENCES `exam_halls` (`hall_id`),
  ADD CONSTRAINT `fk_exam_hall_alloc_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `exam_schedule` (`schedule_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_hall_alloc_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_invigilators`
--
ALTER TABLE `exam_invigilators`
  ADD CONSTRAINT `fk_exam_invigilators_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `exam_schedule` (`schedule_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_invigilators_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD CONSTRAINT `fk_exam_schedule_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `fk_exam_schedule_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_schedule_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_exam_schedule_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `periods`
--
ALTER TABLE `periods`
  ADD CONSTRAINT `periods_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `period_templates` (`template_id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `fk_results_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `fk_results_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

--
-- Constraints for table `result_entries`
--
ALTER TABLE `result_entries`
  ADD CONSTRAINT `fk_result_entries_result` FOREIGN KEY (`result_id`) REFERENCES `results` (`result_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_result_entries_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `fk_result_entries_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `fk_result_entries_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE;

--
-- Constraints for table `route_stops`
--
ALTER TABLE `route_stops`
  ADD CONSTRAINT `fk_route_stops_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_route_stops_stop` FOREIGN KEY (`stop_id`) REFERENCES `stops` (`stop_id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD CONSTRAINT `fk_teacher_attendance_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `fk_user_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
