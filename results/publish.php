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

$marksCheck = mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM result_marks

WHERE result_id='$result_id'"

);

$marksData =
mysqli_fetch_assoc(
$marksCheck
);

if($marksData['total'] == 0){

header(
'Location:index.php?error=no_marks'
);

exit;

}

mysqli_query(

$conn,

"UPDATE results

SET

status='published',
published_at=NOW()

WHERE result_id='$result_id'"

);
header('Location:index.php?success=published');

exit;

?>