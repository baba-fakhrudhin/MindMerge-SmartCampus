-- MindMerge SmartCampus Transport Schema
-- Canonical schema for the simplified school transport ERP.

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+00:00';

CREATE TABLE `drivers` (
  `driver_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `license_number` varchar(80) NOT NULL,
  `license_expiry` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `helpers` (
  `helper_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `buses` (
  `bus_id` int(11) NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `driver_id` int(11) DEFAULT NULL,
  `helper_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `routes` (
  `route_id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `route_name` varchar(120) NOT NULL,
  `start_location` varchar(150) DEFAULT NULL,
  `end_location` varchar(150) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stops` (
  `stop_id` int(11) NOT NULL,
  `stop_name` varchar(150) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `route_stops` (
  `route_stop_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `stop_id` int(11) NOT NULL,
  `arrival_time` time DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `stop_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bus_assignments` (
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `pickup_stop_id` int(11) DEFAULT NULL,
  `drop_stop_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `drivers`
  ADD PRIMARY KEY (`driver_id`),
  ADD KEY `idx_drivers_status` (`status`),
  ADD KEY `idx_drivers_name` (`name`);

ALTER TABLE `helpers`
  ADD PRIMARY KEY (`helper_id`),
  ADD KEY `idx_helpers_status` (`status`),
  ADD KEY `idx_helpers_name` (`name`);

ALTER TABLE `buses`
  ADD PRIMARY KEY (`bus_id`),
  ADD UNIQUE KEY `unique_bus_number` (`bus_number`),
  ADD UNIQUE KEY `unique_registration_number` (`registration_number`),
  ADD KEY `idx_buses_status` (`status`),
  ADD KEY `idx_buses_driver` (`driver_id`),
  ADD KEY `idx_buses_helper` (`helper_id`);

ALTER TABLE `routes`
  ADD PRIMARY KEY (`route_id`),
  ADD KEY `idx_routes_bus` (`bus_id`),
  ADD KEY `idx_routes_status` (`status`),
  ADD KEY `idx_routes_name` (`route_name`);

ALTER TABLE `stops`
  ADD PRIMARY KEY (`stop_id`),
  ADD KEY `idx_stops_status` (`status`),
  ADD KEY `idx_stops_name` (`stop_name`);

ALTER TABLE `route_stops`
  ADD PRIMARY KEY (`route_stop_id`),
  ADD UNIQUE KEY `unique_route_stop_order` (`route_id`,`stop_order`),
  ADD UNIQUE KEY `unique_route_stop_pair` (`route_id`,`stop_id`),
  ADD KEY `idx_route_stops_route` (`route_id`),
  ADD KEY `idx_route_stops_stop` (`stop_id`),
  ADD KEY `idx_route_stops_order` (`route_id`,`stop_order`);

ALTER TABLE `bus_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `idx_bus_assignments_bus` (`bus_id`),
  ADD KEY `idx_bus_assignments_pickup` (`pickup_stop_id`),
  ADD KEY `idx_bus_assignments_drop` (`drop_stop_id`),
  ADD KEY `idx_bus_assignments_status` (`status`);

ALTER TABLE `drivers`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `helpers`
  MODIFY `helper_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `route_stops`
  MODIFY `route_stop_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bus_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `buses`
  ADD CONSTRAINT `fk_buses_driver`
    FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_buses_helper`
    FOREIGN KEY (`helper_id`) REFERENCES `helpers` (`helper_id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `routes`
  ADD CONSTRAINT `fk_routes_bus`
    FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `route_stops`
  ADD CONSTRAINT `fk_route_stops_route`
    FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_route_stops_stop`
    FOREIGN KEY (`stop_id`) REFERENCES `stops` (`stop_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `bus_assignments`
  ADD CONSTRAINT `fk_bus_assignments_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bus_assignments_bus`
    FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bus_assignments_pickup_stop`
    FOREIGN KEY (`pickup_stop_id`) REFERENCES `stops` (`stop_id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bus_assignments_drop_stop`
    FOREIGN KEY (`drop_stop_id`) REFERENCES `stops` (`stop_id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;
