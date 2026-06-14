<?php

class TransportService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function isReady(): bool
    {
        return $this->tableExists('buses') && $this->tableExists('bus_assignments');
    }

    public function getStats(): array
    {
        if (!$this->isReady()) {
            return ['buses' => 0, 'drivers' => 0, 'routes' => 0, 'assignments' => 0];
        }

        return [
            'buses' => $this->scalar('SELECT COUNT(*) FROM buses'),
            'drivers' => $this->scalar('SELECT COUNT(*) FROM drivers'),
            'routes' => $this->scalar('SELECT COUNT(*) FROM routes'),
            'assignments' => $this->scalar("SELECT COUNT(*) FROM bus_assignments WHERE status='active'"),
        ];
    }

    public function getStudentAssignment(int $student_db_id): ?array
    {
        if (!$this->isReady() || $student_db_id <= 0) {
            return null;
        }

        $row = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT ba.*, b.bus_number, b.registration_number,
                    d.full_name AS driver_name, d.phone AS driver_phone,
                    r.route_name, r.route_code,
                    ps.stop_name AS pickup_stop,
                    ds.stop_name AS drop_stop
             FROM bus_assignments ba
             INNER JOIN buses b ON b.bus_id = ba.bus_id
             INNER JOIN routes r ON r.route_id = ba.route_id
             LEFT JOIN drivers d ON d.driver_id = ba.driver_id
             LEFT JOIN stops ps ON ps.stop_id = ba.pickup_stop_id
             LEFT JOIN stops ds ON ds.stop_id = ba.drop_stop_id
             WHERE ba.student_id = '$student_db_id'
               AND ba.status = 'active'
             LIMIT 1"
        ));

        return $row ?: null;
    }

    private function scalar(string $sql): int
    {
        $result = mysqli_query($this->conn, $sql);
        $row = $result ? mysqli_fetch_row($result) : [0];

        return (int) ($row[0] ?? 0);
    }

    private function tableExists(string $table): bool
    {
        $table = mysqli_real_escape_string($this->conn, $table);
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE '$table'");

        return $result && mysqli_num_rows($result) > 0;
    }
}
