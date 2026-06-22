<?php

include('../../config/auth.php');
include('../../config/db.php');

$assignment_id =
(int)($_POST['assignment_id'] ?? 0);

$bus_id =
(int)($_POST['bus_id'] ?? 0);

$stop_id =
(int)($_POST['stop_id'] ?? 0);

if(
$assignment_id <= 0
||
$bus_id <= 0
||
$stop_id <= 0
){

header(
'Location:index.php?error=invalid'
);

exit;

}

mysqli_query(

$conn,

"UPDATE transport_student_assignments

SET

bus_id='$bus_id',
stop_id='$stop_id'

WHERE assignment_id='$assignment_id'

LIMIT 1"

);

header(
'Location:index.php?success=updated'
);

exit;
?>