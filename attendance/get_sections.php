<?php

include('../config/auth.php');
include('../config/db.php');

header('Content-Type: application/json');

$class_id = intval($_GET['class_id'] ?? 0);

$data = [];

if($class_id > 0){

$query = mysqli_query(

$conn,

"SELECT

section_id,
section_name

FROM sections

WHERE

class_id='$class_id'

AND

status='active'

ORDER BY section_name ASC"

);

while($row = mysqli_fetch_assoc($query)){

$data[] = $row;

}

}

echo json_encode($data);

?>