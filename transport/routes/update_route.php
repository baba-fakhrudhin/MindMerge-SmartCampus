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

$route_id = intval(
$input['route_id'] ?? 0
);

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

if($route_id <= 0){

echo json_encode([

'success' => false,

'message' => 'Invalid route.'

]);

exit;

}

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
   Route Exists
========================= */

$route_check = mysqli_query(

$conn,

"SELECT route_id

FROM transport_routes

WHERE route_id='$route_id'

LIMIT 1"

);

if(mysqli_num_rows($route_check) == 0){

echo json_encode([

'success' => false,

'message' => 'Route not found.'

]);

exit;

}
/* =========================
   Prevent Duplicate Bus Route
========================= */

$duplicate_route = mysqli_query(

$conn,

"SELECT route_id

FROM transport_routes

WHERE bus_id='$bus_id'

AND route_id != '$route_id'

LIMIT 1"

);

if(mysqli_num_rows($duplicate_route) > 0){

echo json_encode([

'success' => false,

'message' => 'Selected bus is already assigned to another route.'

]);

exit;

}
/* =========================
   Start / End Validation
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

'message' => 'Route must contain one start point.'

]);

exit;

}

if($end_count != 1){

echo json_encode([

'success' => false,

'message' => 'Route must contain one end point.'

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
   Escape Values
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

/* =========================
   Update Route
========================= */

$update = mysqli_query(

$conn,

"UPDATE transport_routes

SET

bus_id='$bus_id',

route_name='$route_name',

route_description='$route_description',

route_color='$route_color',

start_time='$start_time',

end_time='$end_time',

status='$status'

WHERE route_id='$route_id'"

);

if(!$update){

throw new Exception(
mysqli_error($conn)
);

}

/* =========================
   Remove Old Stops
========================= */

$deleteStops = mysqli_query(

$conn,

"DELETE FROM transport_stops

WHERE route_id='$route_id'"

);

if(!$deleteStops){

throw new Exception(
mysqli_error($conn)
);

}

/* =========================
   Insert New Stops
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

$insertStop = mysqli_query(

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

if(!$insertStop){

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

'message' => 'Route updated successfully.'

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
