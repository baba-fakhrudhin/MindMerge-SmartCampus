
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$subject = mysqli_query(
$conn,
"SELECT *
FROM subjects
WHERE subject_id='$id'"
);

if(mysqli_num_rows($subject) == 0){

header("Location:index.php");
exit();

}

mysqli_query(
$conn,
"DELETE FROM subjects
WHERE subject_id='$id'"
);

header("Location:index.php?success=deleted");
exit();

?>
