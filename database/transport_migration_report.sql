-- MindMerge SmartCampus Transport Migration Report
-- Run these queries before applying the final transport migration constraints.

-- Stops with missing coordinates
SELECT stop_id, stop_name, latitude, longitude, status, created_at
FROM stops
WHERE latitude IS NULL OR longitude IS NULL
ORDER BY stop_name ASC;

-- Routes without bus ownership
SELECT route_id, route_name, bus_id, start_location, end_location, status, created_at
FROM routes
WHERE bus_id IS NULL
ORDER BY route_name ASC;

-- Historical assignments by student/bus/status to review before removing duplicates
SELECT student_id, bus_id, status, COUNT(*) AS assignment_count
FROM bus_assignments
GROUP BY student_id, bus_id, status
HAVING COUNT(*) > 1
ORDER BY assignment_count DESC, student_id ASC, bus_id ASC;
