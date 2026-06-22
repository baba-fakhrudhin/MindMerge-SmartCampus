<?php

include('../../config/auth.php');
include('../../config/db.php');

header('Content-Type: application/json');

try{

$input = json_decode(

file_get_contents(
'php://input'
),

true

);

if(!$input){

echo json_encode([

'success' => false,

'message' => 'Invalid request data.'

]);

exit;

}

/* =========================
   Route Data
========================= */

$bus_id = intval(
$input['bus_id'] ?? 0
);

$route_name = trim(
$input['route_name'] ?? ''
);

$route_description = trim(
$input['route_description'] ?? ''
);

$route_color = trim(
$input['route_color'] ?? '#2563eb'
);

$start_time = trim(
$input['start_time'] ?? ''
);

$end_time = trim(
$input['end_time'] ?? ''
);

$status = trim(
$input['status'] ?? 'active'
);

$stops = $input['stops'] ?? [];

/* =========================
   Validation
========================= */

if($bus_id <= 0){

echo json_encode([

'success' => false,

'message' => 'Please select a bus.'

]);

exit;

}

if($route_name == ''){

echo json_encode([

'success' => false,

'message' => 'Route name is required.'

]);

exit;

}

if(count($stops) < 2){

echo json_encode([

'success' => false,

'message' => 'At least 2 stops are required.'

]);

exit;

}

/* =========================
   Validate Start / End
========================= */

$start_count = 0;

$end_count = 0;

foreach($stops as $stop){

if(
isset($stop['is_start'])
&&
$stop['is_start'] == 1
){

$start_count++;

}

if(
isset($stop['is_end'])
&&
$stop['is_end'] == 1
){

$end_count++;

}

}

if($start_count != 1){

echo json_encode([

'success' => false,

'message' => 'Route must contain exactly one start point.'

]);

exit;

}

if($end_count != 1){

echo json_encode([

'success' => false,

'message' => 'Route must contain exactly one end point.'

]);

exit;

}

/* =========================
   Verify Bus Exists
========================= */

$bus_check = mysqli_query(

$conn,

"SELECT bus_id

FROM transport_buses

WHERE bus_id='$bus_id'

LIMIT 1"

);

if(mysqli_num_rows($bus_check) == 0){

echo json_encode([

'success' => false,

'message' => 'Selected bus does not exist.'

]);

exit;

}

/* =========================
   One Bus = One Route
========================= */

$route_exists = mysqli_query(

$conn,

"SELECT route_id

FROM transport_routes

WHERE bus_id='$bus_id'

LIMIT 1"

);

if(mysqli_num_rows($route_exists) > 0){

echo json_encode([

'success' => false,

'message' => 'This bus already has a route assigned.'

]);

exit;

}

/* =========================
   Begin Transaction
========================= */

mysqli_begin_transaction(
$conn
);

/* =========================
   Insert Route
========================= */

$route_name =
mysqli_real_escape_string(
$conn,
$route_name
);

$route_description =
mysqli_real_escape_string(
$conn,
$route_description
);

$route_color =
mysqli_real_escape_string(
$conn,
$route_color
);

$start_time =
mysqli_real_escape_string(
$conn,
$start_time
);

$end_time =
mysqli_real_escape_string(
$conn,
$end_time
);

$status =
mysqli_real_escape_string(
$conn,
$status
);

$route_insert = mysqli_query(

$conn,

"INSERT INTO transport_routes
(
bus_id,
route_name,
route_description,
route_color,
start_time,
end_time,
status
)

VALUES
(
'$bus_id',
'$route_name',
'$route_description',
'$route_color',
'$start_time',
'$end_time',
'$status'
)"

);

if(!$route_insert){

throw new Exception(
mysqli_error($conn)
);

}

$route_id =
mysqli_insert_id(
$conn
);

/* =========================
   Insert Stops
========================= */

$order = 1;

foreach($stops as $stop){

$stop_name = mysqli_real_escape_string(

$conn,

trim(
$stop['stop_name'] ?? ''
)

);

$latitude = floatval(
$stop['latitude'] ?? 0
);

$longitude = floatval(
$stop['longitude'] ?? 0
);

$arrival_time = mysqli_real_escape_string(

$conn,

trim(
$stop['arrival_time'] ?? ''
)

);

$is_start = intval(
$stop['is_start'] ?? 0
);

$is_end = intval(
$stop['is_end'] ?? 0
);

if($stop_name == ''){

continue;

}

$stop_insert = mysqli_query(

$conn,

"INSERT INTO transport_stops
(
route_id,
stop_name,
latitude,
longitude,
stop_order,
is_start,
is_end,
arrival_time
)

VALUES
(
'$route_id',
'$stop_name',
'$latitude',
'$longitude',
'$order',
'$is_start',
'$is_end',
'$arrival_time'
)"

);

if(!$stop_insert){

throw new Exception(
mysqli_error($conn)
);

}

$order++;

}

/* =========================
   Commit
========================= */

mysqli_commit(
$conn
);

echo json_encode([

'success' => true,

'route_id' => $route_id,

'message' => 'Route created successfully.'

]);

}
catch(Exception $e){

mysqli_rollback(
$conn
);

echo json_encode([

'success' => false,

'message' => $e->getMessage()

]);

}
?>
