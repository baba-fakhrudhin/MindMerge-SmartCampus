<?php

require_once __DIR__ . '/../../config/notifications.php';

class DriverDashboardService
{
    private mysqli $conn;
    private int $userId;
    private array $tableCache = [];

    public function __construct(mysqli $conn, int $userId)
    {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function getDriverBus(): ?array
    {
        if (!$this->tablesExist(['transport_staff', 'transport_buses'])) {
            return null;
        }

        $query = mysqli_query(

            $this->conn,

            "SELECT

            b.*,
            b.status AS bus_status,

            r.route_name,
            r.route_id,

            ts.staff_id,

            ll.status,
            ll.updated_at

            FROM transport_staff ts

            INNER JOIN transport_buses b
            ON ts.staff_id=b.driver_id

            LEFT JOIN transport_routes r
            ON b.bus_id=r.bus_id

            LEFT JOIN transport_live_location ll
            ON b.bus_id=ll.bus_id

            WHERE ts.user_id='{$this->userId}'

            LIMIT 1"

        );

        if(!$query || mysqli_num_rows($query) == 0){
            return null;
        }

        return mysqli_fetch_assoc($query);
    }

    public function getDriverProfile(): ?array
    {
        if (!$this->tablesExist(['transport_staff', 'users'])) {
            return null;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT ts.*, u.email, u.profile_photo
             FROM transport_staff ts
             LEFT JOIN users u ON u.id = ts.user_id
             WHERE ts.user_id = '{$this->userId}' AND ts.staff_type = 'driver'
             LIMIT 1"
        );

        return $query && mysqli_num_rows($query) > 0 ? mysqli_fetch_assoc($query) : null;
    }

    public function getStats(): array
    {
        $bus = $this->getDriverBus();

        if(!$bus){

            return [

                'assigned_bus' => '-',
                'route_name' => '-',
                'status' => 'not_started',
                'student_count' => 0,
                'stop_count' => 0,
                'unread_notifications' => $this->unreadNotifications()

            ];

        }

        return [

            'assigned_bus' =>
            $bus['bus_number'],

            'route_name' =>
            $bus['route_name'] ?? 'Not Assigned',

            'status' =>
            $bus['status'] ?? 'not_started',

            'student_count' => $this->getStudentCount((int) $bus['bus_id']),

            'stop_count' => $this->getStopCount((int) ($bus['route_id'] ?? 0)),

            'unread_notifications' => $this->unreadNotifications()

        ];
    }

    public function getRouteStops(): array
    {
        $bus = $this->getDriverBus();
        $routeId = (int) ($bus['route_id'] ?? 0);
        $items = [];

        if ($routeId <= 0 || !$this->tableExists('transport_stops')) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT stop_name, arrival_time, stop_order, is_start, is_end
             FROM transport_stops
             WHERE route_id = '$routeId'
             ORDER BY stop_order ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getAssignedStudents(): array
    {
        $bus = $this->getDriverBus();
        $busId = (int) ($bus['bus_id'] ?? 0);
        $items = [];

        if ($busId <= 0 || !$this->tableExists('transport_student_assignments')) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT u.full_name, s.class_name, s.section_name,
                    COALESCE(st.stop_name,'Not Assigned') AS stop_name
             FROM transport_student_assignments tsa
             LEFT JOIN students s ON tsa.student_id = s.id
             LEFT JOIN users u ON s.user_id = u.id
             LEFT JOIN transport_stops st ON tsa.stop_id = st.stop_id
             WHERE tsa.bus_id = '$busId'
             ORDER BY u.full_name"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getLiveLocation(): ?array
    {
        $bus = $this->getDriverBus();
        $busId = (int) ($bus['bus_id'] ?? 0);

        if ($busId <= 0 || !$this->tableExists('transport_live_location')) {
            return null;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT latitude, longitude, updated_at, status
             FROM transport_live_location
             WHERE bus_id = '$busId'
             LIMIT 1"
        );

        return $query && mysqli_num_rows($query) > 0 ? mysqli_fetch_assoc($query) : null;
    }

    public function getRecentNotifications(int $limit = 5): array
    {
        if (!$this->tableExists('notifications')) {
            return [];
        }

        $uid = (int) ($_SESSION['user']['id'] ?? $this->userId);
        $role = $_SESSION['user']['role'] ?? 'driver';
        $context = notification_user_context($this->conn, $uid, $role);

        return notification_recent_for_context($this->conn, $context, $limit);
    }

    private function getStudentCount(int $busId): int
    {
        if ($busId <= 0) {
            return 0;
        }

        return $this->tableExists('transport_student_assignments') ? $this->scalar("SELECT COUNT(*) FROM transport_student_assignments WHERE bus_id = '$busId'") : 0;
    }

    private function getStopCount(int $routeId): int
    {
        if ($routeId <= 0) {
            return 0;
        }

        return $this->tableExists('transport_stops') ? $this->scalar("SELECT COUNT(*) FROM transport_stops WHERE route_id = '$routeId'") : 0;
    }

    private function unreadNotifications(): int
    {
        $uid = (int) ($_SESSION['user']['id'] ?? $this->userId);
        $role = $_SESSION['user']['role'] ?? 'driver';
        $context = notification_user_context($this->conn, $uid, $role);

        return notification_unread_count($this->conn, $context);
    }

    private function scalar(string $sql): int
    {
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            return 0;
        }

        $row = mysqli_fetch_row($result);

        return (int) ($row[0] ?? 0);
    }

    private function tableExists(string $table): bool
    {
        if (isset($this->tableCache[$table])) {
            return $this->tableCache[$table];
        }

        $safe = mysqli_real_escape_string($this->conn, $table);
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE '$safe'");

        return $this->tableCache[$table] = ($result && mysqli_num_rows($result) > 0);
    }

    private function tablesExist(array $tables): bool
    {
        foreach ($tables as $table) {
            if (!$this->tableExists($table)) {
                return false;
            }
        }

        return true;
    }
}
