
<?php

include('../../config/auth.php');
include('../../config/db.php');

$page_title = 'Mark Teacher Attendance';

$selected_date =
$_GET['attendance_date']
??
date('Y-m-d');

$success = '';
$error = '';

/*
|--------------------------------------------------------------------------
| SAVE ATTENDANCE
|--------------------------------------------------------------------------
*/

if(

$_SERVER['REQUEST_METHOD']
=== 'POST'

){

$selected_date =
$_POST['attendance_date']
??
date('Y-m-d');

$teacher_ids =
$_POST['teacher_id']
??
[];

$statuses =
$_POST['status']
??
[];

$remarks =
$_POST['remarks']
??
[];

if(
count($teacher_ids) == 0
){

$error =
'No teachers found to mark attendance.';

}
else{

mysqli_begin_transaction($conn);

try{

foreach(
$teacher_ids
as
$index => $teacher_id
){

$teacher_id =
intval($teacher_id);

$status =
mysqli_real_escape_string(

$conn,

$statuses[$index]
??
'present'

);

$remark =
mysqli_real_escape_string(

$conn,

trim(
$remarks[$index]
??
''
)

);

/*
|--------------------------------------------------------------------------
| CHECK EXISTING RECORD
|--------------------------------------------------------------------------
*/

$existing =
mysqli_query(

$conn,

"SELECT attendance_id

FROM teacher_attendance

WHERE

teacher_id='$teacher_id'

AND

attendance_date='$selected_date'

LIMIT 1"

);

if(
mysqli_num_rows($existing) > 0
){

$row =
mysqli_fetch_assoc(
$existing
);

$attendance_id =
intval(
$row['attendance_id']
);

mysqli_query(

$conn,

"UPDATE teacher_attendance

SET

status='$status',

remarks='$remark',

created_by='".intval(
$_SESSION['user']['id']
)."'

WHERE

attendance_id='$attendance_id'"

);

}
else{

mysqli_query(

$conn,

"INSERT INTO teacher_attendance(

teacher_id,
attendance_date,
status,
remarks,
created_by

)

VALUES(

'$teacher_id',

'$selected_date',

'$status',

'$remark',

'".intval(
$_SESSION['user']['id']
)."'

)"

);

}

}

mysqli_commit($conn);
$success =
'Teacher attendance saved successfully for '
.
date(
'd M Y',
strtotime(
$selected_date
)
);

}
catch(Exception $e){

mysqli_rollback($conn);

$error =
'Failed to save attendance.';

}

}

}

/*
|--------------------------------------------------------------------------
| TEACHERS LIST
|--------------------------------------------------------------------------
*/

$teachers_query = mysqli_query(

$conn,

"SELECT

t.id,

t.teacher_id,

u.full_name,

t.qualification,

t.specialization

FROM teachers t

JOIN users u
ON t.user_id=u.id

ORDER BY u.full_name ASC"

);

/*
|--------------------------------------------------------------------------
| EXISTING ATTENDANCE
|--------------------------------------------------------------------------
*/

$attendance_map = [];

$existing_query = mysqli_query(

$conn,

"SELECT

teacher_id,

status,

remarks

FROM teacher_attendance

WHERE

attendance_date='$selected_date'"

);

while(
$row =
mysqli_fetch_assoc(
$existing_query
)
){

$attendance_map[
$row['teacher_id']
] = [

'status' =>
$row['status'],

'remarks' =>
$row['remarks']

];

}

/*
|--------------------------------------------------------------------------
| SUMMARY CARDS
|--------------------------------------------------------------------------
*/

$total_teachers =
mysqli_num_rows(
$teachers_query
);

mysqli_data_seek(
$teachers_query,
0
);

$present_count = 0;

$absent_count = 0;

$leave_count = 0;

foreach(
$attendance_map
as
$item
){

if(
$item['status']
==
'present'
){

$present_count++;

}
elseif(
$item['status']
==
'absent'
){

$absent_count++;

}
else{

$leave_count++;

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

Teacher Attendance |
MindMerge SmartCampus

</title>

<link
rel="stylesheet"
href="../../assets/css/global.css">

<link
rel="stylesheet"
href="../../assets/css/layout.css">

<link
rel="stylesheet"
href="../../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.teacher-search{

margin-bottom:20px;

}

.teacher-search input{

width:100%;

}

.status-select{

min-width:170px;

}

</style>

</head>

<body>

<div class="app-layout">

<?php include('../../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../../partials/topbar.php'); ?>

<div class="page-content">


<div class="page-header">

<div>

<h1>
Teacher Attendance
</h1>

<p>
Mark and manage teacher attendance records.
</p>

</div>

<div>

<a
href="index.php"
class="btn"
>

<i class="fa-solid fa-arrow-left"></i>

Back to Dashboard

</a>

</div>

</div>

<?php

if($success != ''){

?>

<div
class="dashboard-section"
style="
border-left:4px solid #22c55e;
margin-bottom:20px;
">

<?php
echo htmlspecialchars(
$success
);
?>

</div>

<?php

}

?>

<?php

if($error != ''){

?>

<div
class="dashboard-section"
style="
border-left:4px solid #ef4444;
margin-bottom:20px;
">

<?php
echo htmlspecialchars(
$error
);
?>

</div>

<?php

}

?>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-users"></i>

</div>

<h3>

<?php
echo $total_teachers;
?>

</h3>

</div>

<p>
Total Teachers
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-user-check"></i>

</div>

<h3>

<?php
echo $present_count;
?>

</h3>

</div>

<p>
Present
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-user-xmark"></i>

</div>

<h3>

<?php
echo $absent_count;
?>

</h3>

</div>

<p>
Absent
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-calendar-minus"></i>

</div>

<h3>

<?php
echo $leave_count;
?>

</h3>

</div>

<p>
Leave / Medical / OD
</p>

</div>

</div>

<form
method="POST"
action=""
>

<div class="dashboard-section">

<div class="section-header">

<h2>
Attendance Details
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">

Attendance Date

</label>

<input

type="date"

name="attendance_date"

class="form-input"

value="<?php echo htmlspecialchars($selected_date); ?>"

required

>

</div>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Quick Actions
</h2>

</div>

<div class="quick-actions">

<button

type="button"

id="markAllPresent"

class="btn btn-primary"

>

<i class="fa-solid fa-user-check"></i>

Mark All Present

</button>

<button

type="button"

id="markAllAbsent"

class="btn"

>

<i class="fa-solid fa-user-xmark"></i>

Mark All Absent

</button>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Teacher Attendance List
</h2>

</div>

<div class="teacher-search">

<input

type="text"

id="teacherSearch"

class="form-input"

placeholder="Search teacher by ID or name..."

>

</div>

<div class="table-responsive">

<table
class="custom-table"
id="teacherTable"
>

<thead>

<tr>

<th data-sort="true">
Teacher ID
</th>

<th data-sort="true">
Teacher Name
</th>

<th>
Status
</th>

<th>
Remarks
</th>

</tr>

</thead>

<tbody>

<?php

while(
$teacher =
mysqli_fetch_assoc(
$teachers_query
)
){

$current_status =
$attendance_map[
$teacher['id']
]['status']
??
'present';

$current_remark =
$attendance_map[
$teacher['id']
]['remarks']
??
'';

?>

<tr>

<td>

<?php
echo htmlspecialchars(
$teacher['teacher_id']
);
?>

<input

type="hidden"

name="teacher_id[]"

value="<?php echo $teacher['id']; ?>"

>

</td>

<td>

<?php
echo htmlspecialchars(
$teacher['full_name']
);
?>

</td>

<td>

<select

name="status[]"

class="form-select status-select"

>

<option
value="present"
<?php
echo
$current_status=='present'
?
'selected'
:
'';
?>
>
Present
</option>

<option
value="absent"
<?php
echo
$current_status=='absent'
?
'selected'
:
'';
?>
>
Absent
</option>

<option
value="leave"
<?php
echo
$current_status=='leave'
?
'selected'
:
'';
?>
>
Leave
</option>

<option
value="medical_leave"
<?php
echo
$current_status=='medical_leave'
?
'selected'
:
'';
?>
>
Medical Leave
</option>

<option
value="od"
<?php
echo
$current_status=='od'
?
'selected'
:
'';
?>
>
OD
</option>

</select>

</td>

<td>

<input

type="text"

name="remarks[]"

class="form-input"

value="<?php echo htmlspecialchars($current_remark); ?>"

placeholder="Optional remarks"

>

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
justify-content:flex-end;
"
>

<button
type="submit"
class="btn btn-primary"
>

<i class="fa-solid fa-floppy-disk"></i>

Save Attendance

</button>

</div>

</div>

</form>


<script>

/* =========================================
   MARK ALL PRESENT
========================================= */

document
.getElementById(
'markAllPresent'
)
.addEventListener(
'click',
()=>{

document
.querySelectorAll(
'select[name="status[]"]'
)
.forEach(select=>{

select.value =
'present';

});

}
);

/* =========================================
   MARK ALL ABSENT
========================================= */

document
.getElementById(
'markAllAbsent'
)
.addEventListener(
'click',
()=>{

document
.querySelectorAll(
'select[name="status[]"]'
)
.forEach(select=>{

select.value =
'absent';

});

}
);

/* =========================================
   TEACHER SEARCH
========================================= */

document
.getElementById(
'teacherSearch'
)
.addEventListener(
'keyup',
function(){

const value =
this.value
.toLowerCase()
.trim();

document
.querySelectorAll(
'#teacherTable tbody tr'
)
.forEach(row=>{

const teacherId =

row.children[0]
.innerText
.toLowerCase();

const teacherName =

row.children[1]
.innerText
.toLowerCase();

if(

teacherId.includes(value)

||

teacherName.includes(value)

){

row.style.display =
'';

}
else{

row.style.display =
'none';

}

});

}
);

</script>

<script src="../../assets/js/common.js"></script>

<script src="../../assets/js/sidebar.js"></script>

</div>

</div>

</div>

</body>

</html>
