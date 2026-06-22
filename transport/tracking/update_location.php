<?php

include('../../config/auth.php');
include('../../config/db.php');

header('Content-Type: application/json');

if(
!isset($_SESSION['user'])
||
strtolower($_SESSION['user']['role']) !== 'driver'
){

http_response_code(403);

echo json_encode([
'success' => false,
'message' => 'Unauthorized'
]);

exit;

}

$user_id = (int)$_SESSION['user']['id'];

$latitude = isset($_POST['latitude'])
? (float)$_POST['latitude']
: 0;

$longitude = isset($_POST['longitude'])
? (float)$_POST['longitude']
: 0;

$status = mysqli_real_escape_string(

$conn,

$_POST['status']
?? 'running'

);

$latitude =
isset($_POST['latitude'])
? (float)$_POST['latitude']
: null;

$longitude =
isset($_POST['longitude'])
? (float)$_POST['longitude']
: null;

if(
$latitude == 0
&&
$longitude == 0
&&
$status == 'running'
)
{

echo json_encode([
'success'=>false,
'message'=>'Waiting for GPS'
]);

exit;

}


/*
Find driver's assigned bus
*/

$busQuery = mysqli_query(

$conn,

"SELECT

b.bus_id

FROM transport_staff ts

INNER JOIN transport_buses b
ON b.driver_id = ts.staff_id

WHERE ts.user_id='$user_id'

LIMIT 1"

);

if(mysqli_num_rows($busQuery) == 0){

echo json_encode([
'success' => false,
'message' => 'No bus assigned'
]);

exit;

}

$bus = mysqli_fetch_assoc($busQuery);

$bus_id = (int)$bus['bus_id'];

/*
Insert or update live location
*/
mysqli_query(

$conn,

"INSERT INTO transport_live_location
(
bus_id,
latitude,
longitude,
status
)

VALUES
(
'$bus_id',
'$latitude',
'$longitude',
'$status'
)

ON DUPLICATE KEY UPDATE

latitude='$latitude',

longitude='$longitude',

status='$status',

updated_at=NOW()"

);

echo json_encode([

'success' => true,

'bus_id' => $bus_id,

'latitude' => $latitude,

'longitude' => $longitude,

'status' => $status,

'timestamp' => date('Y-m-d H:i:s')

]);
?>
