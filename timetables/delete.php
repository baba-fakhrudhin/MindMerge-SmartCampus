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

"SELECT timetable_id

FROM timetables

WHERE timetable_id='$id'"

);

if(mysqli_num_rows($check)==0){

header("Location:index.php");
exit();

}

mysqli_begin_transaction($conn);

try{

mysqli_query(

$conn,

"DELETE

FROM timetable_entries

WHERE timetable_id='$id'"

);

mysqli_query(

$conn,

"DELETE

FROM timetables

WHERE timetable_id='$id'"

);

mysqli_commit($conn);

header(
"Location:index.php?success=deleted"
);

exit();

}
catch(Exception $e){

mysqli_rollback($conn);

header(
"Location:index.php?error=delete_failed"
);

exit();

}