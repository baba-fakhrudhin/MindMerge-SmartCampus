<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$period_query = mysqli_query(

$conn,

"SELECT *

FROM periods

WHERE period_id='$id'"

);

if(mysqli_num_rows($period_query) == 0){

header("Location:index.php");
exit();

}

$period = mysqli_fetch_assoc(
$period_query
);

$template_id = $period['template_id'];

mysqli_query(

$conn,

"DELETE FROM periods

WHERE period_id='$id'"

);

header(

"Location:../period_templates/view.php?id=$template_id&success=period_deleted"

);

exit();

?>