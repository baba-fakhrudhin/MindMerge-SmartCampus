<?php

include('../config/auth.php');
include('../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php');
exit;

}

$exam_id = (int)$_GET['id'];

$check = mysqli_query(

$conn,

"SELECT exam_id

FROM exams

WHERE exam_id='$exam_id'

LIMIT 1"

);

if(mysqli_num_rows($check) == 0){

header('Location:index.php?error=not_found');
exit;

}

$delete = mysqli_query(

$conn,

"DELETE FROM exams

WHERE exam_id='$exam_id'"

);

if($delete){

header('Location:index.php?success=deleted');

}
else{

header('Location:index.php?error=delete_failed');

}

exit;

?>