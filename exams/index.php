<?php

include('../config/auth.php');
include('../config/db.php');

$search = '';

$where = '';

if(isset($_GET['search']) && trim($_GET['search']) != ''){

    $search = mysqli_real_escape_string(
        $conn,
        trim($_GET['search'])
    );

    $where = "

    WHERE

e.exam_name LIKE '%$search%'

OR

e.exam_type LIKE '%$search%'

OR

c.class_name LIKE '%$search%'

OR

s.section_name LIKE '%$search%'

OR

sub.subject_name LIKE '%$search%'

OR

e.custom_subject LIKE '%$search%'

OR

e.status LIKE '%$search%'

    ";

}

$query = mysqli_query(

$conn,

"SELECT

e.*,

c.class_name,

s.section_name,

sub.subject_name

FROM exams e

LEFT JOIN classes c
ON e.class_id = c.class_id

LEFT JOIN sections s
ON e.section_id = s.section_id

LEFT JOIN subjects sub
ON e.subject_id = sub.subject_id

$where

ORDER BY e.exam_id DESC"

);

/* Statistics */

$total_exams = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
        FROM exams"

    )

)['total'];

$upcoming_exams = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
        FROM exams
        WHERE status='upcoming'"

    )

)['total'];

$ongoing_exams = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
        FROM exams
        WHERE status='ongoing'"

    )

)['total'];

$completed_exams = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
        FROM exams
        WHERE status='completed'"

    )

)['total'];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Exam Management | MindMerge SmartCampus
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
Exam Management
</h1>

<p>
Create and manage examinations.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Exam

</a>

</div>

<?php if(isset($_GET['success'])){ ?>

<div
style="
background:#dcfce7;
color:#166534;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php

if($_GET['success']=='added'){

echo "Exam created successfully.";

}
elseif($_GET['success']=='updated'){

echo "Exam updated successfully.";

}
elseif($_GET['success']=='deleted'){

echo "Exam deleted successfully.";

}

?>

</div>

<?php } ?>

<?php if(isset($_GET['error'])){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php

if($_GET['error']=='not_found'){

echo "Exam not found.";

}
elseif($_GET['error']=='delete_failed'){

echo "Unable to delete exam.";

}

?>

</div>

<?php } ?>

<!-- Statistics -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Exams</h3>

<h2>
<?php echo $total_exams; ?>
</h2>

</div>

<div class="dashboard-card">

<h3>Upcoming Exams</h3>

<h2>
<?php echo $upcoming_exams; ?>
</h2>

</div>

<div class="dashboard-card">

<h3>Ongoing Exams</h3>

<h2>
<?php echo $ongoing_exams; ?>
</h2>

</div>

<div class="dashboard-card">

<h3>Completed Exams</h3>

<h2>
<?php echo $completed_exams; ?>
</h2>

</div>

</div>

<!-- Quick Actions -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Quick Actions
</h2>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Exam

</a>

<a
href="../results/index.php"
class="btn">

<i class="fa-solid fa-chart-column"></i>

Results

</a>

<a
href="../reports/report.php"
class="btn">

<i class="fa-solid fa-file-lines"></i>

Reports

</a>

</div>

</div>

<!-- Search -->

<div class="dashboard-section">

<form method="GET">

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<input

type="text"

name="search"

class="form-input"

placeholder="Search Exam, Type, Class, Section, Subject or Status"

value="<?php echo htmlspecialchars($search); ?>"

style="flex:1;min-width:250px;">

<button
type="submit"
class="btn btn-primary">

<i class="fa-solid fa-magnifying-glass"></i>

Search

</button>

<a
href="index.php"
class="btn">

Reset

</a>

</div>

</form>

</div>

<!-- Exams Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
All Exams
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Exam Name
</th>
<th data-sort="true">
Type
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Section
</th>

<th data-sort="true">
Subject
</th>

<th data-sort="true">
Total Marks
</th>

<th data-sort="true">
Exam Date
</th>

<th data-sort="true">
Time
</th>

<th data-sort="true">
Status
</th>

<th>
Actions
</th>

</tr>

</thead>

<tbody>
    <?php

if(mysqli_num_rows($query) > 0){

while($row = mysqli_fetch_assoc($query)){

?>

<tr>

<td>

<strong>
<?php echo htmlspecialchars($row['exam_name']); ?>
</strong>

<br>


</td>
<td>

<?php

echo ucwords(
str_replace(
'_',
' ',
$row['exam_type']
)
);

?>

</td>
<td>

<?php

echo !empty($row['class_name'])
? htmlspecialchars($row['class_name'])
: 'School Wide';

?>

</td>

<td>

<?php

echo htmlspecialchars(
$row['section_name'] ?? '-'
);

?>

</td>

<td>

<?php

echo htmlspecialchars(

!empty($row['custom_subject'])
? $row['custom_subject']
: ($row['subject_name'] ?? '-')

);

?>

</td>

<td>

<?php

echo (int)$row['total_marks'];

?>

</td>

<td>

<?php

echo !empty($row['exam_date'])

? date(
'd M Y',
strtotime($row['exam_date'])
)

: '-';

?>

</td>

<td>

<?php

$start =
!empty($row['start_time'])
? date('h:i A',strtotime($row['start_time']))
: '';

$end =
!empty($row['end_time'])
? date('h:i A',strtotime($row['end_time']))
: '';

echo ($start || $end)
? $start . ' - ' . $end
: '-';

?>

</td>

<td>

<?php

$statusClass = 'danger';

if($row['status'] == 'ongoing'){
    $statusClass = 'info';
}
elseif($row['status'] == 'completed'){
    $statusClass = 'success';
}

?>

<span class="status <?php echo $statusClass; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

</span>

</td>


<td>

<div
style="
display:flex;
gap:8px;
flex-wrap:wrap;
">

<a
href="view.php?id=<?php echo $row['exam_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

<i class="fa-solid fa-eye"></i>

View

</a>

<a
href="edit.php?id=<?php echo $row['exam_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['exam_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this exam?');">

<i class="fa-solid fa-trash"></i>

Delete

</a>

</div>

</td>

</tr>

<?php

}

}
else{

?>

<tr>

<td
colspan="10"
style="
text-align:center;
padding:40px;
">

<div
style="
display:flex;
flex-direction:column;
align-items:center;
gap:10px;
">

<i
class="fa-solid fa-file-signature"
style="
font-size:40px;
color:#94a3b8;
">
</i>

<p
style="
margin:0;
font-weight:500;
">

No exams found.

</p>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Create First Exam

</a>

</div>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>