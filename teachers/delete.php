
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$teacher = mysqli_query(
$conn,
"SELECT *
FROM teachers
WHERE id='$id'"
);

$row = mysqli_fetch_assoc($teacher);

if(!$row){

header("Location:index.php");
exit();

}

$user_id = $row['user_id'];

mysqli_query(
$conn,
"DELETE FROM teachers
WHERE id='$id'"
);

mysqli_query(
$conn,
"DELETE FROM users
WHERE id='$user_id'"
);

header("Location:index.php?success=deleted");
exit();

?>
