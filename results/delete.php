<?php

include('../config/auth.php');
include('../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php?error=not_found');
exit;

}

$result_id = (int)$_GET['id'];

$check = mysqli_query(

$conn,

"SELECT result_id

FROM results

WHERE result_id='$result_id'

LIMIT 1"

);

if(mysqli_num_rows($check) == 0){

header('Location:index.php?error=not_found');
exit;

}

mysqli_begin_transaction($conn);

try{

mysqli_query(

$conn,

"DELETE

FROM result_marks

WHERE result_id='$result_id'"

);

mysqli_query(

$conn,

"DELETE

FROM results

WHERE result_id='$result_id'"

);

mysqli_commit($conn);

header('Location:index.php?success=deleted');
exit;

}
catch(Exception $e){

mysqli_rollback($conn);

header('Location:index.php?error=delete_failed');
exit;

}
?>