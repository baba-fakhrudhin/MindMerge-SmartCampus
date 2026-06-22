<?php

include('../../config/auth.php');
include('../../config/db.php');

$route_id = intval($_GET['id'] ?? 0);

if($route_id <= 0){

header('Location:index.php');
exit;

}

/* Verify Route Exists */

$check = mysqli_query(

$conn,

"SELECT route_id

FROM transport_routes

WHERE route_id='$route_id'

LIMIT 1"

);

if(mysqli_num_rows($check) == 0){

header('Location:index.php?error=not_found');
exit;

}

/* Delete Route */

$delete = mysqli_query(

$conn,

"DELETE FROM transport_routes

WHERE route_id='$route_id'"

);

if($delete){

header('Location:index.php?success=deleted');
exit;

}
else{

header('Location:index.php?error=delete_failed');
exit;

}
?>
