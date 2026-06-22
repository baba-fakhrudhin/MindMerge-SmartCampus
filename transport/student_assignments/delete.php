    <?php

include('../../config/auth.php');
include('../../config/db.php');

$id = (int)($_GET['id'] ?? 0);

if($id <= 0){

header(
'Location:index.php?error=not_found'
);

exit;

}

/* =========================
   Verify Assignment Exists
========================= */

$check = mysqli_query(

$conn,

"SELECT assignment_id

FROM transport_student_assignments

WHERE assignment_id='$id'

LIMIT 1"

);

if(mysqli_num_rows($check) == 0){

header(
'Location:index.php?error=not_found'
);

exit;

}

/* =========================
   Delete Assignment
========================= */

mysqli_query(

$conn,

"DELETE FROM transport_student_assignments

WHERE assignment_id='$id'

LIMIT 1"

);

header(
'Location:index.php?success=deleted'
);

exit;

?>