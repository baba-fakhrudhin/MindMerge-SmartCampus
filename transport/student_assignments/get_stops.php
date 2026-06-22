<?php

include('../../config/auth.php');
include('../../config/db.php');

header('Content-Type: application/json');

$bus_id = (int)($_GET['bus_id'] ?? 0);

if($bus_id <= 0){

echo json_encode([

'success' => false,
'data' => []

]);

exit;

}

$query = mysqli_query(

$conn,

"SELECT

ts.stop_id,
ts.stop_name

FROM transport_routes tr

INNER JOIN transport_stops ts
ON ts.route_id = tr.route_id

WHERE tr.bus_id='$bus_id'

ORDER BY ts.stop_order"

);

$stops = [];

while($row=mysqli_fetch_assoc($query)){

$stops[] = [

'stop_id' => (int)$row['stop_id'],

'stop_name' => $row['stop_name']

];

}

echo json_encode([

'success' => true,

'data' => $stops

]);

exit;
?>