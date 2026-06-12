<?php

include('../config/auth.php');
include('../config/db.php');

header('Content-Type: application/json');

$class_id = intval($_GET['class_id'] ?? 0);

$section_id = intval($_GET['section_id'] ?? 0);

$data = [];

if(

$class_id > 0

&&

$section_id > 0

){

$query = mysqli_query(

$conn,

"SELECT

s.id,
s.student_id,

u.full_name

FROM students s

JOIN users u
ON s.user_id=u.id

WHERE

s.class_id='$class_id'

AND

s.section_id='$section_id'

ORDER BY u.full_name ASC"

);

while($row = mysqli_fetch_assoc($query)){

$data[] = [

'id' => $row['id'],

'student_id' => $row['student_id'],

'full_name' => $row['full_name']

];

}

}

echo json_encode($data);

?>