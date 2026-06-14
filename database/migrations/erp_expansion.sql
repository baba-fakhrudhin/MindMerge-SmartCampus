-- MindMerge SmartCampus ERP expansion schema
-- Run after schema.sql and permissions_system.sql.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

CREATE TABLE IF NOT EXISTS grading_system (
  grading_id INT(11) NOT NULL AUTO_INCREMENT,
  grade_name VARCHAR(20) NOT NULL,
  min_marks DECIMAL(6,2) NOT NULL,
  max_marks DECIMAL(6,2) NOT NULL,
  grade_point DECIMAL(4,2) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (grading_id),
  KEY idx_grade_range (min_marks, max_marks)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS results (
  result_id INT(11) NOT NULL AUTO_INCREMENT,
  class_id INT(11) NOT NULL,
  section_id INT(11) NOT NULL,
  academic_year VARCHAR(20) NOT NULL,
  semester VARCHAR(50) DEFAULT NULL,
  result_type VARCHAR(50) NOT NULL DEFAULT 'semester',
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  created_by INT(11) DEFAULT NULL,
  published_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (result_id),
  KEY idx_result_scope (class_id, section_id, academic_year, semester),
  KEY idx_result_status (status),
  CONSTRAINT fk_results_class FOREIGN KEY (class_id) REFERENCES classes(class_id),
  CONSTRAINT fk_results_section FOREIGN KEY (section_id) REFERENCES sections(section_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS result_entries (
  entry_id INT(11) NOT NULL AUTO_INCREMENT,
  result_id INT(11) NOT NULL,
  student_id INT(11) NOT NULL,
  subject_id INT(11) NOT NULL,
  teacher_id INT(11) DEFAULT NULL,
  internal_marks DECIMAL(6,2) NOT NULL DEFAULT 0,
  external_marks DECIMAL(6,2) NOT NULL DEFAULT 0,
  lab_marks DECIMAL(6,2) NOT NULL DEFAULT 0,
  attendance_marks DECIMAL(6,2) NOT NULL DEFAULT 0,
  total_marks DECIMAL(6,2) NOT NULL DEFAULT 0,
  grade VARCHAR(20) DEFAULT NULL,
  grade_point DECIMAL(4,2) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (entry_id),
  UNIQUE KEY unique_result_student_subject (result_id, student_id, subject_id),
  KEY idx_result_student (student_id),
  KEY idx_result_subject (subject_id),
  CONSTRAINT fk_result_entries_result FOREIGN KEY (result_id) REFERENCES results(result_id) ON DELETE CASCADE,
  CONSTRAINT fk_result_entries_student FOREIGN KEY (student_id) REFERENCES students(id),
  CONSTRAINT fk_result_entries_subject FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
  CONSTRAINT fk_result_entries_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exam_types (
  exam_type_id INT(11) NOT NULL AUTO_INCREMENT,
  type_name VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (exam_type_id),
  UNIQUE KEY unique_exam_type (type_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exams (
  exam_id INT(11) NOT NULL AUTO_INCREMENT,
  exam_type_id INT(11) DEFAULT NULL,
  exam_name VARCHAR(150) NOT NULL,
  academic_year VARCHAR(20) NOT NULL,
  status ENUM('draft','published','completed','archived') NOT NULL DEFAULT 'draft',
  created_by INT(11) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (exam_id),
  KEY idx_exam_status (status),
  CONSTRAINT fk_exams_type FOREIGN KEY (exam_type_id) REFERENCES exam_types(exam_type_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exam_schedule (
  schedule_id INT(11) NOT NULL AUTO_INCREMENT,
  exam_id INT(11) NOT NULL,
  class_id INT(11) NOT NULL,
  section_id INT(11) DEFAULT NULL,
  subject_id INT(11) NOT NULL,
  exam_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  room_no VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (schedule_id),
  KEY idx_exam_schedule_lookup (exam_id, class_id, section_id, exam_date),
  CONSTRAINT fk_exam_schedule_exam FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
  CONSTRAINT fk_exam_schedule_class FOREIGN KEY (class_id) REFERENCES classes(class_id),
  CONSTRAINT fk_exam_schedule_section FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE SET NULL,
  CONSTRAINT fk_exam_schedule_subject FOREIGN KEY (subject_id) REFERENCES subjects(subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exam_halls (
  hall_id INT(11) NOT NULL AUTO_INCREMENT,
  hall_name VARCHAR(100) NOT NULL,
  capacity INT(11) NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (hall_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exam_hall_allocations (
  allocation_id INT(11) NOT NULL AUTO_INCREMENT,
  schedule_id INT(11) NOT NULL,
  hall_id INT(11) NOT NULL,
  student_id INT(11) DEFAULT NULL,
  seat_no VARCHAR(30) DEFAULT NULL,
  PRIMARY KEY (allocation_id),
  KEY idx_hall_schedule (schedule_id, hall_id),
  CONSTRAINT fk_exam_hall_alloc_schedule FOREIGN KEY (schedule_id) REFERENCES exam_schedule(schedule_id) ON DELETE CASCADE,
  CONSTRAINT fk_exam_hall_alloc_hall FOREIGN KEY (hall_id) REFERENCES exam_halls(hall_id),
  CONSTRAINT fk_exam_hall_alloc_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exam_invigilators (
  invigilator_id INT(11) NOT NULL AUTO_INCREMENT,
  schedule_id INT(11) NOT NULL,
  teacher_id INT(11) NOT NULL,
  duty_role VARCHAR(50) DEFAULT 'invigilator',
  PRIMARY KEY (invigilator_id),
  UNIQUE KEY unique_schedule_teacher (schedule_id, teacher_id),
  CONSTRAINT fk_exam_invigilators_schedule FOREIGN KEY (schedule_id) REFERENCES exam_schedule(schedule_id) ON DELETE CASCADE,
  CONSTRAINT fk_exam_invigilators_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS buses (
  bus_id INT(11) NOT NULL AUTO_INCREMENT,
  bus_number VARCHAR(50) NOT NULL,
  registration_number VARCHAR(50) NOT NULL,
  capacity INT(11) NOT NULL DEFAULT 0,
  status ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  latitude DECIMAL(10,7) DEFAULT NULL,
  longitude DECIMAL(10,7) DEFAULT NULL,
  last_updated DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (bus_id),
  UNIQUE KEY unique_bus_number (bus_number),
  UNIQUE KEY unique_registration_number (registration_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS drivers (
  driver_id INT(11) NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  license_number VARCHAR(80) NOT NULL,
  address TEXT DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (driver_id),
  UNIQUE KEY unique_license_number (license_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS routes (
  route_id INT(11) NOT NULL AUTO_INCREMENT,
  route_code VARCHAR(50) NOT NULL,
  route_name VARCHAR(120) NOT NULL,
  start_point VARCHAR(150) DEFAULT NULL,
  end_point VARCHAR(150) DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (route_id),
  UNIQUE KEY unique_route_code (route_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stops (
  stop_id INT(11) NOT NULL AUTO_INCREMENT,
  stop_name VARCHAR(150) NOT NULL,
  latitude DECIMAL(10,7) DEFAULT NULL,
  longitude DECIMAL(10,7) DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (stop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS route_stops (
  route_stop_id INT(11) NOT NULL AUTO_INCREMENT,
  route_id INT(11) NOT NULL,
  stop_id INT(11) NOT NULL,
  stop_order INT(11) NOT NULL,
  arrival_time TIME DEFAULT NULL,
  departure_time TIME DEFAULT NULL,
  PRIMARY KEY (route_stop_id),
  UNIQUE KEY unique_route_stop_order (route_id, stop_order),
  CONSTRAINT fk_route_stops_route FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
  CONSTRAINT fk_route_stops_stop FOREIGN KEY (stop_id) REFERENCES stops(stop_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bus_assignments (
  assignment_id INT(11) NOT NULL AUTO_INCREMENT,
  bus_id INT(11) NOT NULL,
  route_id INT(11) NOT NULL,
  driver_id INT(11) DEFAULT NULL,
  student_id INT(11) NOT NULL,
  pickup_stop_id INT(11) DEFAULT NULL,
  drop_stop_id INT(11) DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (assignment_id),
  UNIQUE KEY unique_student_transport (student_id, status),
  KEY idx_bus_route (bus_id, route_id),
  CONSTRAINT fk_bus_assignments_bus FOREIGN KEY (bus_id) REFERENCES buses(bus_id),
  CONSTRAINT fk_bus_assignments_route FOREIGN KEY (route_id) REFERENCES routes(route_id),
  CONSTRAINT fk_bus_assignments_driver FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE SET NULL,
  CONSTRAINT fk_bus_assignments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_bus_assignments_pickup FOREIGN KEY (pickup_stop_id) REFERENCES stops(stop_id) ON DELETE SET NULL,
  CONSTRAINT fk_bus_assignments_drop FOREIGN KEY (drop_stop_id) REFERENCES stops(stop_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE attendance
  ADD COLUMN IF NOT EXISTS teacher_id INT(11) NULL AFTER created_by,
  ADD COLUMN IF NOT EXISTS teacher_name VARCHAR(120) NULL AFTER teacher_id,
  ADD COLUMN IF NOT EXISTS attendance_time TIME NULL AFTER attendance_date;

COMMIT;
