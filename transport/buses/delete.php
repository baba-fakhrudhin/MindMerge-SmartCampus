<?php

include('../../config/auth.php');
include('../../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php');
exit;

}

$bus_id = (int)$_GET['id'];

/*
Check student assignments
*/

$student_check = mysqli_query(

$conn,

"SELECT assignment_id

FROM transport_student_assignments

WHERE bus_id = $bus_id

LIMIT 1"

);

if(mysqli_num_rows($student_check) > 0){

header(
'Location:index.php?error=in_use'
);

exit;

}

/*
Stops are ON DELETE CASCADE
so they will remove automatically.
*/
mysqli_query(
$conn,
"DELETE FROM transport_live_location
WHERE bus_id=$bus_id"
);

mysqli_query(
$conn,
"DELETE FROM transport_bus_routes
WHERE bus_id=$bus_id"
);
mysqli_query(

$conn,

"DELETE FROM transport_buses
WHERE bus_id = $bus_id"

);

header(
'Location:index.php?success=deleted'
);

exit;
?>
