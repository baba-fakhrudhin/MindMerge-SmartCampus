<?php

include('../config/auth.php');
include('../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php');
exit;

}

$result_id = (int)$_GET['id'];

$resultQuery = mysqli_query(

$conn,

"SELECT

r.*,

e.exam_name,
e.total_marks,

c.class_name,
s.section_name

FROM results r

INNER JOIN exams e
ON r.exam_id=e.exam_id

LEFT JOIN classes c
ON r.class_id=c.class_id

LEFT JOIN sections s
ON r.section_id=s.section_id

WHERE r.result_id='$result_id'

LIMIT 1"

);

if(mysqli_num_rows($resultQuery) == 0){

header('Location:index.php');
exit;

}

$result =
mysqli_fetch_assoc(
$resultQuery
);

if(

!empty($result['class_id'])

&&

!empty($result['section_id'])

){

/*
Specific Class + Section
*/

$students = mysqli_query(

$conn,

"SELECT

s.id,
s.student_id,

u.full_name

FROM students s

INNER JOIN users u
ON s.user_id=u.id

WHERE

s.class_id='".$result['class_id']."'

AND

s.section_id='".$result['section_id']."'

ORDER BY u.full_name"

);

}
elseif(

!empty($result['class_id'])

){

/*
Entire Class
*/

$students = mysqli_query(

$conn,

"SELECT

s.id,
s.student_id,

u.full_name

FROM students s

INNER JOIN users u
ON s.user_id=u.id

WHERE

s.class_id='".$result['class_id']."'

ORDER BY u.full_name"

);

}
else{

/*
School Wide
*/

$students = mysqli_query(

$conn,

"SELECT

s.id,
s.student_id,

u.full_name

FROM students s

INNER JOIN users u
ON s.user_id=u.id

ORDER BY u.full_name"

);

}
if($_SERVER['REQUEST_METHOD']=='POST'){
if(
isset($_POST['marks'])
&&
is_array($_POST['marks'])
){

foreach($_POST['marks'] as $student_id=>$marks){

$student_id =
(int)$student_id;

$marks =
(float)$marks;
if($marks > $result['total_marks']){

$marks = $result['total_marks'];

}

if($marks < 0){

$marks = 0;

}

$remarks =
mysqli_real_escape_string(

$conn,

$_POST['remarks'][$student_id]
?? ''

);

$exists = mysqli_query(

$conn,

"SELECT mark_id

FROM result_marks

WHERE

result_id='$result_id'

AND

student_id='$student_id'

LIMIT 1"

);

if(mysqli_num_rows($exists)>0){

$row =
mysqli_fetch_assoc(
$exists
);

mysqli_query(

$conn,

"UPDATE result_marks

SET

marks_obtained='$marks',
remarks='$remarks'

WHERE mark_id='".$row['mark_id']."'"

);

}
else{

mysqli_query(

$conn,

"INSERT INTO result_marks
(
result_id,
student_id,
marks_obtained,
remarks
)

VALUES
(
'$result_id',
'$student_id',
'$marks',
'$remarks'
)"

);

}

}
}
header(
'Location:view.php?id=' .
$result_id .
'&success=saved'
);

exit;

}
?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Enter Marks | MindMerge SmartCampus
</title>

<link
rel="stylesheet"
href="../assets/css/global.css">

<link
rel="stylesheet"
href="../assets/css/layout.css">

<link
rel="stylesheet"
href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>

<h1>
Enter Student Marks
</h1>

<p>

<?php

echo htmlspecialchars(
$result['exam_name']
);

?>

</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>
Exam
</h3>

<h2>

<?php

echo htmlspecialchars(
$result['exam_name']
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Maximum Marks
</h3>

<h2>

<?php

echo (int)$result['total_marks'];

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Result Status
</h3>

<h2>

<?php

echo ucfirst(
$result['status']
);

?>

</h2>

</div>
<div class="dashboard-card">

<h3>
Target Group
</h3>

<h2>

<?php

if(
!empty($result['class_name'])
&&
!empty($result['section_name'])
){

echo htmlspecialchars(
$result['class_name']
)
.
' - '
.
htmlspecialchars(
$result['section_name']
);

}
elseif(
!empty($result['class_name'])
){

echo htmlspecialchars(
$result['class_name']
);

}
else{

echo 'School Wide';

}

?>

</h2>

</div>  

</div>

<div class="dashboard-section">

<form method="POST">

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>
Student ID
</th>

<th>
Student Name
</th>

<th>
Marks
</th>

<th>
Remarks
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($students) > 0){

while($student =
mysqli_fetch_assoc($students)){

$existing =
mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT

marks_obtained,
remarks

FROM result_marks

WHERE

result_id='$result_id'

AND

student_id='".$student['id']."'

LIMIT 1"

)

);

?>

<tr>

<td>

<?php

echo htmlspecialchars(
$student['student_id']
);

?>

</td>

<td>

<strong>

<?php

echo htmlspecialchars(
$student['full_name']
);

?>

</strong>

</td>

<td>

<input

type="number"

name="marks[<?php echo $student['id']; ?>]"

class="form-input"

min="0"

max="<?php echo (int)$result['total_marks']; ?>"

step="0.01"

value="<?php echo $existing['marks_obtained'] ?? ''; ?>"

required>

</td>

<td>

<input

type="text"

name="remarks[<?php echo $student['id']; ?>]"

class="form-input"

value="<?php echo htmlspecialchars($existing['remarks'] ?? ''); ?>"

placeholder="Optional">

</td>

</tr>

<?php

}

}
else{

?>

<tr>

<td
colspan="4"
style="
text-align:center;
padding:40px;
">

No students found for this result session.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

<div
style="
margin-top:20px;
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Marks

</button>

<a
href="view.php?id=<?php echo $result_id; ?>"
class="btn">

View Result

</a>

<a
href="index.php"
class="btn">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>
