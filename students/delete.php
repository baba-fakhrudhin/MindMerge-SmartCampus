
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$student = mysqli_query(
$conn,
"SELECT *
FROM students
WHERE id='$id'"
);

$row = mysqli_fetch_assoc($student);

if(!$row){

header("Location:index.php");
exit();

}

$user_id = $row['user_id'];

mysqli_query(
$conn,
"DELETE FROM students
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
