-- MindMerge SmartCampus Transport Migration
-- Targets the current transport tables in database/mindmerge.sql.
-- Run after backing up the database.

START TRANSACTION;

-- -----------------------------------------------------------------------------
-- 1) Normalize drivers
-- -----------------------------------------------------------------------------
ALTER TABLE `drivers`
  ADD COLUMN `photo` varchar(255) DEFAULT NULL AFTER `license_expiry`;

UPDATE `drivers`
SET `name` = CASE
    WHEN `name` IS NULL OR TRIM(`name`) = '' THEN `full_name`
    ELSE `name`
  END;

ALTER TABLE `drivers`
  DROP COLUMN `full_name`;

-- -----------------------------------------------------------------------------
-- 2) Normalize helpers
-- -----------------------------------------------------------------------------
ALTER TABLE `helpers`
  ADD COLUMN `photo` varchar(255) DEFAULT NULL AFTER `phone`;

-- -----------------------------------------------------------------------------
-- 3) Normalize buses and move driver/helper ownership to the bus
-- -----------------------------------------------------------------------------
ALTER TABLE `buses`
  ADD COLUMN `driver_id` int(11) DEFAULT NULL AFTER `capacity`,
  ADD COLUMN `helper_id` int(11) DEFAULT NULL AFTER `driver_id`;

UPDATE `buses` b
LEFT JOIN (
  SELECT ba.bus_id, ba.driver_id, ba.helper_id
  FROM `bus_assignments` ba
  INNER JOIN (
    SELECT bus_id, MAX(assignment_id) AS assignment_id
    FROM `bus_assignments`
    GROUP BY bus_id
  ) latest ON latest.bus_id = ba.bus_id AND latest.assignment_id = ba.assignment_id
) src ON src.bus_id = b.bus_id
SET b.driver_id = src.driver_id,
    b.helper_id = src.helper_id;

ALTER TABLE `buses`
  DROP COLUMN `latitude`,
  DROP COLUMN `longitude`,
  DROP COLUMN `last_updated`;

-- -----------------------------------------------------------------------------
-- 4) Normalize routes
-- -----------------------------------------------------------------------------
ALTER TABLE `routes`
  ADD COLUMN `bus_id` int(11) DEFAULT NULL AFTER `route_id`,
  CHANGE COLUMN `start_point` `start_location` varchar(150) DEFAULT NULL,
  CHANGE COLUMN `end_point` `end_location` varchar(150) DEFAULT NULL;

UPDATE `routes` r
LEFT JOIN (
  SELECT ba.route_id, ba.bus_id
  FROM `bus_assignments` ba
  INNER JOIN (
    SELECT route_id, MAX(assignment_id) AS assignment_id
    FROM `bus_assignments`
    GROUP BY route_id
  ) latest ON latest.route_id = ba.route_id AND latest.assignment_id = ba.assignment_id
) src ON src.route_id = r.route_id
SET r.bus_id = src.bus_id;

ALTER TABLE `routes`
  DROP COLUMN `route_code`;

-- -----------------------------------------------------------------------------
-- 5) Normalize route_stops
-- -----------------------------------------------------------------------------
UPDATE `route_stops`
SET `stop_order` = CASE
    WHEN (`stop_order` IS NULL OR `stop_order` = 0) AND `sort_order` IS NOT NULL THEN `sort_order`
    ELSE `stop_order`
  END;

ALTER TABLE `route_stops`
  DROP COLUMN `sort_order`;

-- -----------------------------------------------------------------------------
-- 6) Normalize bus_assignments
-- -----------------------------------------------------------------------------
ALTER TABLE `bus_assignments`
  DROP FOREIGN KEY `fk_bus_assignments_driver`;

ALTER TABLE `bus_assignments`
  DROP FOREIGN KEY `fk_bus_assignments_route`;

ALTER TABLE `bus_assignments`
  DROP COLUMN `driver_id`,
  DROP COLUMN `helper_id`,
  DROP COLUMN `route_id`;

-- -----------------------------------------------------------------------------
-- 7) Rebuild/align transport indexes and constraints
-- -----------------------------------------------------------------------------
ALTER TABLE `buses`
  ADD KEY `idx_buses_status` (`status`),
  ADD KEY `idx_buses_driver` (`driver_id`),
  ADD KEY `idx_buses_helper` (`helper_id`);

ALTER TABLE `routes`
  ADD KEY `idx_routes_bus` (`bus_id`),
  ADD KEY `idx_routes_status` (`status`),
  ADD KEY `idx_routes_name` (`route_name`);

ALTER TABLE `route_stops`
  ADD UNIQUE KEY `unique_route_stop_pair` (`route_id`,`stop_id`),
  ADD KEY `idx_route_stops_route` (`route_id`),
  ADD KEY `idx_route_stops_stop` (`stop_id`),
  ADD KEY `idx_route_stops_order` (`route_id`,`stop_order`);

ALTER TABLE `bus_assignments`
  DROP INDEX `unique_student_transport`,
  ADD KEY `idx_bus_assignments_bus` (`bus_id`),
  ADD KEY `idx_bus_assignments_pickup` (`pickup_stop_id`),
  ADD KEY `idx_bus_assignments_drop` (`drop_stop_id`),
  ADD KEY `idx_bus_assignments_status` (`status`);

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
