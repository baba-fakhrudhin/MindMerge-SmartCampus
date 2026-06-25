<?php

include('../config/auth.php');
include('../config/db.php');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=result_report.csv');

$output = fopen('php://output', 'w');

fputcsv(

$output,

[
'Student Name',
'Student ID',
'Exam',
'Class',
'Section',
'Marks Obtained',
'Total Marks',
'Percentage',
'Remarks'
]

);

$query = mysqli_query(

$conn,

"SELECT

u.full_name,

st.student_id,

e.exam_name,

c.class_name,

sec.section_name,

rm.marks_obtained,

e.total_marks,

rm.remarks

FROM result_marks rm

INNER JOIN results r
ON rm.result_id = r.result_id

INNER JOIN exams e
ON r.exam_id = e.exam_id

INNER JOIN students st
ON rm.student_id = st.id

INNER JOIN users u
ON st.user_id = u.id

INNER JOIN classes c
ON r.class_id = c.class_id

INNER JOIN sections sec
ON r.section_id = sec.section_id

ORDER BY

e.exam_name,
c.class_name,
sec.section_name,
u.full_name"

);

while($row = mysqli_fetch_assoc($query)){

$percentage = 0;

if($row['total_marks'] > 0){

$percentage = round(

($row['marks_obtained'] /
$row['total_marks']) * 100,

2

);

}

fputcsv(

$output,

[

$row['full_name'],
$row['student_id'],
$row['exam_name'],
!empty($row['class_name'])
? $row['class_name']
: 'School Wide',

!empty($row['section_name'])
? $row['section_name']
: 'All Sections',
$row['marks_obtained'],
$row['total_marks'],
$percentage . '%',
$row['remarks']

]

);

}

fclose($output);

exit;
?>