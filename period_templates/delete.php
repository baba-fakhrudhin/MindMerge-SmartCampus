
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$check = mysqli_query(

$conn,

"SELECT *

FROM period_templates

WHERE template_id='$id'"

);

if(mysqli_num_rows($check)==0){

header("Location:index.php");
exit();

}

$periods = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM periods

WHERE template_id='$id'"

)

);

if($periods['total'] > 0){

header(
"Location:index.php?error=template_has_periods"
);

exit();

}

mysqli_query(

$conn,

"DELETE FROM period_templates

WHERE template_id='$id'"

);

header(
"Location:index.php?success=deleted"
);

exit();

?>
