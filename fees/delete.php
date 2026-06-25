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

$fee_structure_id = (int)$_GET['id'];

/*
Check Fee Structure Exists
*/

$check = mysqli_query(

$conn,

"SELECT fee_structure_id

FROM fee_structures

WHERE fee_structure_id='$fee_structure_id'

LIMIT 1"

);

if(mysqli_num_rows($check) == 0){

header('Location:index.php?error=not_found');
exit;

}

/*
Prevent deletion if assigned
*/

$assigned = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM student_fees

WHERE fee_structure_id='$fee_structure_id'"

)

);

if($assigned['total'] > 0){

header('Location:index.php?error=in_use');
exit;

}

/*
Delete Fee Structure
*/

$deleted = mysqli_query(

$conn,

"DELETE

FROM fee_structures

WHERE fee_structure_id='$fee_structure_id'"

);

if($deleted){

header('Location:index.php?success=deleted');

}
else{

header('Location:index.php?error=delete_failed');

}

exit;

?>