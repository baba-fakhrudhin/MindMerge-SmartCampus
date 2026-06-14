-- MindMerge SmartCampus — Role & Permission Management (Phase 1)
-- Run against mindmerge database (MariaDB 10.4+ / MySQL 8+)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- ---------------------------------------------------------------------------
-- 1. Core permission tables
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_key` varchar(50) NOT NULL,
  `action_key` varchar(20) NOT NULL,
  `label` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `unique_module_action` (`module_key`, `action_key`),
  KEY `idx_module` (`module_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('admin','teacher','student','parent') NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`role_permission_id`),
  UNIQUE KEY `unique_role_permission` (`role`, `permission_id`),
  KEY `idx_role` (`role`),
  KEY `idx_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_permissions` (
  `user_permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_permission_id`),
  UNIQUE KEY `unique_user_permission` (`user_id`, `permission_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_permission` (`permission_id`),
  CONSTRAINT `fk_user_permissions_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_permissions_permission`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- 2. Seed permissions (module × action)
-- ---------------------------------------------------------------------------

INSERT INTO `permissions` (`module_key`, `action_key`, `label`, `sort_order`) VALUES
('dashboard',       'view',   'View Dashboard',              10),
('profile',         'view',   'View Profile',                20),
('profile',         'edit',   'Edit Profile',                21),
('classes',         'view',   'View Classes',                100),
('classes',         'create', 'Create Classes',              101),
('classes',         'edit',   'Edit Classes',                102),
('classes',         'delete', 'Delete Classes',              103),
('sections',        'view',   'View Sections',               110),
('sections',        'create', 'Create Sections',             111),
('sections',        'edit',   'Edit Sections',               112),
('sections',        'delete', 'Delete Sections',             113),
('students',        'view',   'View Students',               120),
('students',        'create', 'Create Students',             121),
('students',        'edit',   'Edit Students',               122),
('students',        'delete', 'Delete Students',             123),
('teachers',        'view',   'View Teachers',               130),
('teachers',        'create', 'Create Teachers',             131),
('teachers',        'edit',   'Edit Teachers',               132),
('teachers',        'delete', 'Delete Teachers',             133),
('schedules',       'view',   'View Schedules',              140),
('schedules',       'create', 'Create Schedules',            141),
('schedules',       'edit',   'Edit Schedules',              142),
('schedules',       'delete', 'Delete Schedules',            143),
('timetables',      'view',   'View Timetables',             150),
('timetables',      'create', 'Create Timetables',           151),
('timetables',      'edit',   'Edit Timetables',             152),
('timetables',      'delete', 'Delete Timetables',           153),
('attendance',      'view',   'View Attendance',             160),
('attendance',      'create', 'Create Attendance',           161),
('attendance',      'edit',   'Edit Attendance',             162),
('attendance',      'delete', 'Delete Attendance',           163),
('teacher_attendance', 'view',   'View Teacher Attendance',  170),
('teacher_attendance', 'create', 'Create Teacher Attendance',171),
('teacher_attendance', 'edit',   'Edit Teacher Attendance', 172),
('teacher_attendance', 'delete', 'Delete Teacher Attendance',173),
('notifications',   'view',   'View Notifications',          180),
('notifications',   'create', 'Create Notifications',        181),
('notifications',   'edit',   'Edit Notifications',          182),
('notifications',   'delete', 'Delete Notifications',        183),
('results',         'view',   'View Results',                185),
('results',         'create', 'Create Results',              186),
('results',         'edit',   'Edit Results',                187),
('results',         'delete', 'Delete Results',              188),
('exams',           'view',   'View Exams',                  190),
('exams',           'create', 'Create Exams',                191),
('exams',           'edit',   'Edit Exams',                  192),
('exams',           'delete', 'Delete Exams',                193),
('transport',       'view',   'View Transport',              195),
('transport',       'create', 'Create Transport',            196),
('transport',       'edit',   'Edit Transport',              197),
('transport',       'delete', 'Delete Transport',            198),
('permissions',     'view',   'View Permission Settings',    200),
('permissions',     'edit',   'Edit Permission Settings',    201)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `sort_order` = VALUES(`sort_order`);

-- ---------------------------------------------------------------------------
-- 3. Default role permissions
-- ---------------------------------------------------------------------------

-- Admin: all permissions granted
INSERT INTO `role_permissions` (`role`, `permission_id`, `granted`)
SELECT 'admin', p.permission_id, 1
FROM `permissions` p
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);

-- Teacher defaults
INSERT INTO `role_permissions` (`role`, `permission_id`, `granted`)
SELECT 'teacher', p.permission_id,
  CASE
    WHEN p.module_key = 'dashboard' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'profile' THEN 1
    WHEN p.module_key = 'students' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'attendance' AND p.action_key IN ('view','create','edit') THEN 1
    WHEN p.module_key = 'teacher_attendance' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'timetables' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'notifications' AND p.action_key IN ('view','create') THEN 1
    WHEN p.module_key = 'results' AND p.action_key IN ('view','create','edit') THEN 1
    WHEN p.module_key = 'exams' AND p.action_key = 'view' THEN 1
    ELSE 0
  END
FROM `permissions` p
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);

-- Student defaults
INSERT INTO `role_permissions` (`role`, `permission_id`, `granted`)
SELECT 'student', p.permission_id,
  CASE
    WHEN p.module_key = 'dashboard' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'profile' THEN 1
    WHEN p.module_key = 'attendance' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'timetables' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'notifications' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'results' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'exams' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'transport' AND p.action_key = 'view' THEN 1
    ELSE 0
  END
FROM `permissions` p
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);

-- Parent defaults
INSERT INTO `role_permissions` (`role`, `permission_id`, `granted`)
SELECT 'parent', p.permission_id,
  CASE
    WHEN p.module_key = 'dashboard' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'profile' THEN 1
    WHEN p.module_key = 'attendance' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'notifications' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'results' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'exams' AND p.action_key = 'view' THEN 1
    WHEN p.module_key = 'transport' AND p.action_key = 'view' THEN 1
    ELSE 0
  END
FROM `permissions` p
ON DUPLICATE KEY UPDATE `granted` = VALUES(`granted`);

COMMIT;
