<?php

include('../config/auth.php');
include('../config/db.php');

$timetable_id = intval($_GET['id'] ?? 0);

$timetable_query = mysqli_query(

$conn,

"SELECT

t.*,

c.class_name,

s.section_name,

pt.template_name,

pt.template_code

FROM timetables t

JOIN classes c
ON t.class_id = c.class_id

JOIN sections s
ON t.section_id = s.section_id

JOIN period_templates pt
ON t.template_id = pt.template_id

WHERE t.timetable_id='$timetable_id'"

);

$timetable = mysqli_fetch_assoc(
$timetable_query
);

if(!$timetable){

header("Location:index.php");
exit();

}

$days = [

'monday',
'tuesday',
'wednesday',
'thursday',
'friday',
'saturday'

];

$period_query = mysqli_query(

$conn,

"SELECT *

FROM periods

WHERE template_id='".$timetable['template_id']."'

ORDER BY sort_order ASC"

);

$periods = [];

while($period = mysqli_fetch_assoc($period_query)){

$periods[] = $period;

}

$total_entries = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM timetable_entries

WHERE timetable_id='$timetable_id'"

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

<?php

echo htmlspecialchars(
$timetable['class_name']
);

?>

-

<?php

echo htmlspecialchars(
$timetable['section_name']
);

?>

Timetable

</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.timetable-table{

width:100%;

border-collapse:collapse;

min-width:1200px;

background:var(--card);

}

.timetable-table th,
.timetable-table td{

border:1px solid rgba(148,163,184,.15);

padding:12px;

vertical-align:top;

text-align:center;

}

.timetable-table th{

background:#f8fafc;

font-weight:600;

}

body.dark-mode .timetable-table th{

background:#081028;

}

.period-cell{

background:#f8fafc;

font-weight:600;

min-width:180px;

}

body.dark-mode .period-cell{

background:#081028;

}

.subject-box{

padding:10px;

border-radius:12px;

background:#eff6ff;

border:1px solid #bfdbfe;

min-height:90px;

display:flex;

flex-direction:column;

justify-content:center;

gap:6px;

}

body.dark-mode .subject-box{

background:#0f172a;

border-color:#29476d;

}

.empty-box{

padding:10px;

border-radius:12px;

background:#f9fafb;

color:#94a3b8;

min-height:90px;

display:flex;

align-items:center;

justify-content:center;

}

body.dark-mode .empty-box{

background:#111827;

}

.teacher-name{

font-size:12px;

color:var(--muted);

}

.room-name{

font-size:12px;

font-weight:600;

color:#2563eb;

}

.summary-grid{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(220px,1fr));

gap:20px;

}

</style>

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

<?php

echo htmlspecialchars(
$timetable['class_name']
);

?>

-

<?php

echo htmlspecialchars(
$timetable['section_name']
);

?>

</h1>
<p>

<?php

echo htmlspecialchars(
$timetable['template_name']
);

?>

•

<?php

echo ucfirst(
$timetable['timetable_type']
);

?>

Timetable

•

AY:

<?php

echo htmlspecialchars(
$timetable['academic_year']
);

?>

</p>
</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="entries.php?id=<?php echo $timetable_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen-to-square"></i>

Manage Entries

</a>

<a
href="../attendance/index.php?timetable_id=<?php echo $timetable_id; ?>"
class="btn">

<i class="fa-solid fa-calendar-check"></i>

Attendance

</a>
<a
href="edit.php?id=<?php echo $timetable_id; ?>"
class="btn">

<i class="fa-solid fa-gear"></i>

Edit Timetable

</a>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

</div>
<div class="dashboard-grid">
<div class="dashboard-section">

<div class="section-header">

<h2>
Timetable Information
</h2>

</div>

<div class="dashboard-grid">

<div class="dashboard-card">

<h4 style="margin-bottom:8px;">
Timetable Type
</h4>

<span class="status <?php

if($timetable['timetable_type']=='exam'){
echo 'warning';
}
elseif($timetable['timetable_type']=='special'){
echo 'info';
}
elseif($timetable['timetable_type']=='remedial'){
echo 'secondary';
}
else{
echo 'primary';
}

?>">

<?php echo ucfirst($timetable['timetable_type']); ?>

</span>

</div>

<div class="dashboard-card">

<h4 style="margin-bottom:8px;">
Schedule Template
</h4>

<strong>

<?php echo htmlspecialchars($timetable['template_name']); ?>

</strong>

<br>

<small>

<?php echo htmlspecialchars($timetable['template_code']); ?>

</small>

</div>

<div class="dashboard-card">

<h4 style="margin-bottom:8px;">
Effective From
</h4>

<strong>

<?php

echo !empty($timetable['effective_from'])

? date(
'd M Y',
strtotime(
$timetable['effective_from']
)
)

: 'Immediate';

?>

</strong>

</div>

<div class="dashboard-card">

<h4 style="margin-bottom:8px;">
Effective To
</h4>

<strong>

<?php

echo !empty($timetable['effective_to'])

? date(
'd M Y',
strtotime(
$timetable['effective_to']
)
)

: 'Until Changed';

?>

</strong>

</div>

</div>

</div>
<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-calendar-days"></i>
</div>

<h3>
<?php echo count($periods); ?>
</h3>

</div>

<p>
Total Periods
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-book"></i>
</div>

<h3>
<?php echo $total_entries; ?>
</h3>

</div>

<p>
Assigned Entries
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-layer-group"></i>
</div>

<h3>

<?php

echo count($days);

?>

</h3>

</div>

<p>
Working Days
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-circle-check"></i>
</div>

<h3>

<?php

$teaching_periods = 0;

foreach($periods as $p){

if(
$p['is_teaching_period']=='yes'
&&
$p['attendance_allowed']=='yes'
){

$teaching_periods++;

}

}

$total_slots = $teaching_periods * count($days);

$completion = 0;

if($total_slots > 0){

$completion = round(
($total_entries / $total_slots) * 100
);

if($completion > 100){
$completion = 100;
}

}

echo $completion;

?>%

</h3>

</div>

<p>
Timetable Completion
</p>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Weekly Timetable
</h2>

</div>

<div class="table-responsive">

<table class="timetable-table">

<thead>

<tr>

<th>
Period
</th>

<?php

foreach($days as $day){

?>

<th>

<?php

echo ucfirst($day);

?>

</th>

<?php

}

?>

</tr>

</thead>

<tbody>

<?php

foreach($periods as $period){

?>

<tr>

<td class="period-cell">

<div>

<strong>

<?php

echo htmlspecialchars(
$period['period_name']
);

?>

</strong>

</div>

<div
style="
font-size:12px;
margin-top:6px;
color:#6b7280;
">

<?php

echo date(
'h:i A',
strtotime(
$period['start_time']
)
);

?>

-

<?php

echo date(
'h:i A',
strtotime(
$period['end_time']
)
);

?>

</div>

</td>

<?php

foreach($days as $day){

$query = mysqli_query(

$conn,

"SELECT

te.*,

sub.subject_name,

u.full_name

FROM timetable_entries te

JOIN subjects sub
ON te.subject_id=sub.subject_id

JOIN teacher_assignments ta
ON te.teacher_assignment_id=ta.assignment_id

JOIN teachers t
ON ta.teacher_id=t.id

JOIN users u
ON t.user_id=u.id

WHERE

te.timetable_id='$timetable_id'

AND

te.day_of_week='$day'

AND

te.period_id='".$period['period_id']."'

LIMIT 1"

);

$entry = mysqli_fetch_assoc(
$query
);

?>

<td>

<?php

if($entry){

?>

<div class="subject-box">

<div
style="
font-weight:600;
">

<?php

echo htmlspecialchars(
$entry['subject_name']
);

?>

</div>

<div class="teacher-name">

<?php

echo htmlspecialchars(
$entry['full_name']
);

?>

</div>

<?php

if(!empty($entry['room_no'])){

?>

<div class="room-name">

Room:
<?php echo htmlspecialchars($entry['room_no']); ?>

</div>

<?php

}

?>

</div>

<?php

}
else{

?>

<div class="empty-box">

Unassigned

</div>

<?php

}

?>

</td>

<?php

}

?>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Timetable Status
</h2>

</div>

<?php

if($completion < 100){

?>

<div
style="
padding:16px;
border-radius:12px;
background:#fef3c7;
color:#92400e;
font-weight:500;
">

<i class="fa-solid fa-triangle-exclamation"></i>

This timetable is incomplete.

<?php

echo ($total_slots - $total_entries);

?>

teaching slot(s) still require subject allocation.

</div>

<?php

}
else{

?>

<div
style="
padding:16px;
border-radius:12px;
background:#dcfce7;
color:#166534;
font-weight:500;
">

<i class="fa-solid fa-circle-check"></i>

Timetable is fully configured.

</div>

<?php

}

?>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>