<?php

include('../config/auth.php');
include('../config/db.php');

header('Content-Type: application/json');

$class_id = intval($_GET['class_id'] ?? 0);

$section_id = intval($_GET['section_id'] ?? 0);

$period_id = intval($_GET['period_id'] ?? 0);

$attendance_date =
$_GET['attendance_date']
??
date('Y-m-d');

$day = strtolower(

date(

'l',

strtotime($attendance_date)

)

);

$response = [

'success' => false,

'subject_id' => null,

'teacher_assignment_id' => null,

'subject_name' => '',

'teacher_name' => '',

'room_no' => '',

'remarks' => ''

];

if(

$class_id > 0

&&

$section_id > 0

&&

$period_id > 0

){

$query = mysqli_query(

$conn,

"SELECT

te.subject_id,
te.teacher_assignment_id,

te.room_no,
te.remarks,

sub.subject_name,

u.full_name

FROM timetable_entries te

JOIN timetables t
ON te.timetable_id = t.timetable_id

LEFT JOIN subjects sub
ON te.subject_id = sub.subject_id

LEFT JOIN teacher_assignments ta
ON te.teacher_assignment_id = ta.assignment_id

LEFT JOIN teachers tr
ON ta.teacher_id = tr.id

LEFT JOIN users u
ON tr.user_id = u.id

WHERE

t.class_id = '$class_id'

AND

t.section_id = '$section_id'

AND

te.period_id = '$period_id'

AND

te.day_of_week = '$day'

LIMIT 1"

);

if(

$query

&&

mysqli_num_rows($query) > 0

){

$row = mysqli_fetch_assoc($query);

$response = [

'success' => true,

'subject_id' =>
$row['subject_id'],

'teacher_assignment_id' =>
$row['teacher_assignment_id'],

'subject_name' =>
$row['subject_name'] ?? '',

'teacher_name' =>
$row['full_name'] ?? '',

'room_no' =>
$row['room_no'] ?? '',

'remarks' =>
$row['remarks'] ?? ''

];

}

}

echo json_encode($response);

?>