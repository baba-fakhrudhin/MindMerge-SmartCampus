<?php

class DriverDashboardService
{
    private mysqli $conn;
    private int $userId;

    public function __construct(mysqli $conn, int $userId)
    {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function getDriverBus(): ?array
    {
        $query = mysqli_query(

            $this->conn,

            "SELECT

            b.*,

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

        if(mysqli_num_rows($query) == 0){
            return null;
        }

        return mysqli_fetch_assoc($query);
    }

    public function getStats(): array
    {
        $bus = $this->getDriverBus();

        if(!$bus){

            return [

                'assigned_bus' => '-',
                'route_name' => '-',
                'status' => 'not_started'

            ];

        }

        return [

            'assigned_bus' =>
            $bus['bus_number'],

            'route_name' =>
            $bus['route_name'] ?? 'Not Assigned',

            'status' =>
            $bus['status'] ?? 'not_started'

        ];
    }
}
?>
