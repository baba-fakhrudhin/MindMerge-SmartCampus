<?php

include('../config/auth.php');
include('../config/db.php');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=results_report.xls");

echo "
<table border='1'>

<tr>
<th>Student</th>
<th>Student ID</th>
<th>Exam</th>
<th>Class</th>
<th>Section</th>
<th>Marks</th>
<th>Total Marks</th>
<th>Percentage</th>
<th>Remarks</th>
</tr>

";

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
ON rm.result_id=r.result_id

INNER JOIN exams e
ON r.exam_id=e.exam_id

INNER JOIN students st
ON rm.student_id=st.id

INNER JOIN users u
ON st.user_id=u.id

LEFT JOIN classes c
ON r.class_id=c.class_id

LEFT JOIN sections sec
ON r.section_id=sec.section_id

ORDER BY

e.exam_name,
c.class_name,
sec.section_name,
u.full_name"

);

while($row=mysqli_fetch_assoc($query)){

$percentage = 0;

if($row['total_marks'] > 0){

$percentage = round(

($row['marks_obtained'] /
$row['total_marks']) * 100,

2

);

}

echo "

<tr>

<td>{$row['full_name']}</td>

<td>{$row['student_id']}</td>

<td>{$row['exam_name']}</td>

<td>" .
(
!empty($row['class_name'])
? $row['class_name']
: 'School Wide'
)
. "</td>

<td>" .
(
!empty($row['section_name'])
? $row['section_name']
: 'All Sections'
)
. "</td>

<td>{$row['marks_obtained']}</td>

<td>{$row['total_marks']}</td>

<td>{$percentage}%</td>

<td>{$row['remarks']}</td>

</tr>

";

}

echo "</table>";

exit;
?>