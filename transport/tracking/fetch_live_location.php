<?php

include('../../config/auth.php');
include('../../config/db.php');

header('Content-Type: application/json');

/*
Optional bus filter
*/

$bus_id = isset($_GET['bus_id'])
? (int)$_GET['bus_id']
: 0;

$where = '';

if($bus_id > 0){

$where = "WHERE b.bus_id='$bus_id'";

}

$query = mysqli_query(

$conn,

"SELECT

b.bus_id,
b.bus_number,
b.bus_name,

ll.latitude,
ll.longitude,
ll.status,
ll.updated_at,

d.staff_id,
d.full_name AS driver_name,
d.phone AS driver_phone,
d.profile_photo,

r.route_id,
r.route_name,
r.route_color,
r.start_time,
r.end_time

FROM transport_buses b

LEFT JOIN transport_live_location ll
ON ll.bus_id=b.bus_id

LEFT JOIN transport_staff d
ON d.staff_id=b.driver_id

LEFT JOIN transport_routes r
ON r.bus_id=b.bus_id

$where

ORDER BY b.bus_name ASC"

);

$buses = [];

while($row = mysqli_fetch_assoc($query)){
$stops = [];

if(!empty($row['route_id'])){

    $stopQuery = mysqli_query(

        $conn,

        "SELECT

            stop_id,
            stop_name,
            latitude,
            longitude,
            is_start,
            is_end,
            stop_order

         FROM transport_stops

         WHERE route_id='".(int)$row['route_id']."'

         ORDER BY stop_order ASC"

    );

    while($stop = mysqli_fetch_assoc($stopQuery)){

        $stops[] = [

            'stop_id' => (int)$stop['stop_id'],

            'stop_name' => $stop['stop_name'],

            'latitude' => (float)$stop['latitude'],

            'longitude' => (float)$stop['longitude'],

            'is_start' => (int)$stop['is_start'],

            'is_end' => (int)$stop['is_end']

        ];

    }

}
$buses[] = [

'bus_id' => (int)$row['bus_id'],

'bus_number' => $row['bus_number'],

'bus_name' => $row['bus_name'],

'latitude' => $row['latitude']
? (float)$row['latitude']
: null,

'longitude' => $row['longitude']
? (float)$row['longitude']
: null,

'status' => $row['status']
?? 'not_started',

'updated_at' => $row['updated_at'],

'driver' => [

'staff_id' => $row['staff_id'],

'name' => $row['driver_name'],

'phone' => $row['driver_phone'],

'profile_photo' => $row['profile_photo']

],

'route' => [

'route_id' => $row['route_id'],

'route_name' => $row['route_name'],

'route_color' => $row['route_color'],

'start_time' => $row['start_time'],

'end_time' => $row['end_time']

],
'stops' => $stops

];

}

if($bus_id > 0){

echo json_encode([

'success' => true,

'data' => $buses[0] ?? null,

'timestamp' => date('Y-m-d H:i:s')

]);

exit;

}

echo json_encode([

'success' => true,

'count' => count($buses),

'data' => $buses,

'timestamp' => date('Y-m-d H:i:s')

]);

exit;
?>
