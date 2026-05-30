<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

mysqli_query(

$conn,

"DELETE FROM classes

WHERE class_id='$id'"

);

header("Location:index.php?success=deleted");
exit();