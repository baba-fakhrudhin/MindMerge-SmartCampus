<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

$exams = mysqli_query(

$conn,

"SELECT

e.exam_id,
e.exam_name,
e.exam_date,
e.class_id,
e.section_id,

c.class_name,
s.section_name,

sub.subject_name,

e.custom_subject

FROM exams e

LEFT JOIN classes c
ON e.class_id = c.class_id

LEFT JOIN sections s
ON e.section_id = s.section_id

LEFT JOIN subjects sub
ON e.subject_id = sub.subject_id

ORDER BY e.exam_date DESC,
e.exam_name ASC"

);

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$exam_id = (int)$_POST['exam_id'];

$examQuery = mysqli_query(

$conn,

"SELECT *

FROM exams

WHERE exam_id='$exam_id'

LIMIT 1"

);

if(mysqli_num_rows($examQuery) == 0){

$error =
'Selected exam not found.';

}
else{

$exam =
mysqli_fetch_assoc(
$examQuery
);

$existing = mysqli_query(

$conn,

"SELECT result_id

FROM results

WHERE exam_id='$exam_id'

LIMIT 1"

);

if(mysqli_num_rows($existing) > 0){

$result =
mysqli_fetch_assoc(
$existing
);

header(
'Location:mark.php?id=' .
$result['result_id']
);

exit;

}

$class_id = !empty($exam['class_id'])
? $exam['class_id']
: "NULL";

$section_id = !empty($exam['section_id'])
? $exam['section_id']
: "NULL";

mysqli_query(

$conn,

"INSERT INTO results
(
exam_id,
class_id,
section_id,
status
)

VALUES
(
".$exam['exam_id'].",
$class_id,
$section_id,
'draft'
)"

);

$result_id =
mysqli_insert_id($conn);

header(
'Location:mark.php?id=' .
$result_id
);

exit;

}

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
Create Result Session
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
Create Result Session
</h1>

<p>
Select an exam and begin entering marks.
</p>

</div>

<a
href="index.php"
class="btn">

Back

</a>

</div>

<?php if(!empty($error)){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="POST">

<div class="form-group">

<label>
Select Exam
</label>

<select
name="exam_id"
class="form-input"
required>

<option value="">
Select Exam
</option>

<?php

while($exam =
mysqli_fetch_assoc($exams)){

?>
<option
value="<?php echo $exam['exam_id']; ?>">
<?php

$classLabel =
!empty($exam['class_name'])
? $exam['class_name']
: 'School Wide';

$sectionLabel =
!empty($exam['section_name'])
? $exam['section_name']
: 'All Sections';

echo htmlspecialchars(
$exam['exam_name']
);

echo ' | ';

echo !empty($exam['exam_date'])
? date(
'd M Y',
strtotime($exam['exam_date'])
)
: '-';

echo ' | ';

echo 'Class: ';

echo htmlspecialchars(
$classLabel
);

echo ' | Section: ';

echo htmlspecialchars(
$sectionLabel
);
?>
</option>

<?php } ?>

</select>

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

Create & Enter Marks

</button>

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