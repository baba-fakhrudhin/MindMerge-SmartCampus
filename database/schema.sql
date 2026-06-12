-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 08:15 AM
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
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
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

INSERT INTO `attendance` (`attendance_id`, `class_id`, `section_id`, `attendance_date`, `remarks`, `created_by`, `created_at`, `period_id`, `subject_id`, `teacher_assignment_id`, `attendance_day`, `updated_at`, `attendance_mode`) VALUES
(10, 12, 11, '2026-06-11', 'All present', 12, '2026-06-11 06:38:40', NULL, NULL, NULL, 'thursday', NULL, 'daily'),
(11, 12, 11, '2026-06-10', '', 12, '2026-06-11 13:54:48', NULL, NULL, NULL, 'wednesday', NULL, 'daily'),
(12, 12, 11, '2026-06-09', '', 12, '2026-06-11 13:55:03', NULL, NULL, NULL, 'tuesday', NULL, 'daily'),
(13, 12, 11, '2026-06-08', '', 12, '2026-06-11 13:55:23', NULL, NULL, NULL, 'monday', NULL, 'daily'),
(14, 12, 11, '2026-06-07', '', 12, '2026-06-11 13:55:44', NULL, NULL, NULL, 'sunday', NULL, 'daily'),
(15, 12, 11, '2026-06-06', '', 12, '2026-06-11 13:56:24', NULL, NULL, NULL, 'saturday', NULL, 'daily'),
(16, 13, 13, '2026-06-11', '', 12, '2026-06-11 13:59:03', NULL, NULL, NULL, 'thursday', NULL, 'daily'),
(17, 13, 13, '2026-06-10', '', 12, '2026-06-11 14:05:53', NULL, NULL, NULL, 'wednesday', NULL, 'daily');

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
(6, 16, 'STU00001', 12, 11, 'Computer Science & Engineering', 'A Section', '2026-06-11', 'male', '', '9000322870', '2026-06-11 05:30:45'),
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
(4, 8, '2026-06-11', 'absent', '', 12, '2026-06-12 05:25:51', NULL);

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
(21, 12, 'thursday', 17, 7, 12, '', '', '2026-06-11 05:43:14');

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
(24, 'Gayatri Mam', 'gayatri@gmail.com', '9704290782', '$2y$10$XlIpngx6lyvjyOFQjkI2/OVGzisYUOO7aKlqRFwuLX13.0RlBoLwm', 'teacher', 'default.png', 'light', '2026-06-11 05:36:55', NULL);

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
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD UNIQUE KEY `unique_class_code` (`class_code`);

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
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `unique_section_per_class` (`class_id`,`section_code`);

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
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
