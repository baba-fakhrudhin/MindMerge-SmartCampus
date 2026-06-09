
<?php

include('../config/auth.php');
include('../config/db.php');

if(!isset($_GET['id'])){

header("Location:index.php");
exit();

}

$id = intval($_GET['id']);

$check = mysqli_query(

$conn,

"SELECT *

FROM timetables

WHERE timetable_id='$id'"

);

if(mysqli_num_rows($check)==0){

header("Location:index.php");
exit();

}

/*
Delete timetable entries first
*/

mysqli_query(

$conn,

"DELETE

FROM timetable_entries

WHERE timetable_id='$id'"

);

/*
Delete timetable
*/

mysqli_query(

$conn,

"DELETE

FROM timetables

WHERE timetable_id='$id'"

);

header(
"Location:index.php?success=deleted"
);

exit();

?>
