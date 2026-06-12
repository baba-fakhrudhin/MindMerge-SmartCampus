<?php

include('../config/db.php');

$class_id = intval($_GET['class_id'] ?? 0);
$section_id = intval($_GET['section_id'] ?? 0);

$result = mysqli_query(

$conn,

"SELECT timetable_id
 FROM timetables
 WHERE class_id='$class_id'
 AND section_id='$section_id'
 LIMIT 1"

);

echo json_encode([
'success' => mysqli_num_rows($result) > 0
]);