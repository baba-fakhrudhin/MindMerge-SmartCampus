
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$assignment = mysqli_query(

$conn,

"SELECT *

FROM teacher_assignments

WHERE assignment_id='$id'"

);

if(mysqli_num_rows($assignment) == 0){

header("Location:index.php");
exit();

}

mysqli_query(

$conn,

"DELETE FROM teacher_assignments

WHERE assignment_id='$id'"

);

header("Location:index.php?success=deleted");
exit();

?>
