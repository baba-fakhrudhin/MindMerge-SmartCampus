
<?php

include('../../config/auth.php');
include('../../config/db.php');

$page_title = 'Teacher Attendance Reports';

/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$date_from =
$_GET['date_from']
??
date(
'Y-m-01'
);

$date_to =
$_GET['date_to']
??
date(
'Y-m-d'
);

$teacher_filter =
intval(
$_GET['teacher_id']
??
0
);

/*
|--------------------------------------------------------------------------
| TEACHERS DROPDOWN
|--------------------------------------------------------------------------
*/

$teachers_dropdown =
mysqli_query(

$conn,

"SELECT

t.id,
t.teacher_id,

u.full_name

FROM teachers t

JOIN users u
ON t.user_id=u.id

ORDER BY u.full_name ASC"

);

/*
|--------------------------------------------------------------------------
| FILTER CONDITION
|--------------------------------------------------------------------------
*/

$where =

"WHERE

ta.attendance_date
BETWEEN
'$date_from'
AND
'$date_to'";

if(
$teacher_filter > 0
){

$where .=

" AND
ta.teacher_id='$teacher_filter'";

}

/*
|--------------------------------------------------------------------------
| DASHBOARD STATS
|--------------------------------------------------------------------------
*/

$stats_query =
mysqli_query(

$conn,

"SELECT

COUNT(*) total_records,

SUM(
CASE
WHEN ta.status='present'
THEN 1
ELSE 0
END
) present_count,

SUM(
CASE
WHEN ta.status='absent'
THEN 1
ELSE 0
END
) absent_count,

SUM(
CASE
WHEN ta.status='leave'
THEN 1
ELSE 0
END
) leave_count,

SUM(
CASE
WHEN ta.status='medical_leave'
THEN 1
ELSE 0
END
) medical_leave_count,

SUM(
CASE
WHEN ta.status='od'
THEN 1
ELSE 0
END
) od_count

FROM teacher_attendance ta

$where"

);

$stats =
mysqli_fetch_assoc(
$stats_query
);

$total_records =
intval(
$stats['total_records']
);

$effective_present =

intval(
$stats['present_count']
)

+

intval(
$stats['leave_count']
)

+

intval(
$stats['medical_leave_count']
)

+

intval(
$stats['od_count']
);

$attendance_percentage = 0;

if(
$total_records > 0
){

$attendance_percentage = round(

(
$effective_present
/
$total_records
)

* 100,

2

);

}

/*
|--------------------------------------------------------------------------
| TEACHER SUMMARY
|--------------------------------------------------------------------------
*/

$teacher_summary_query =
mysqli_query(

$conn,

"SELECT

t.id,

t.teacher_id,

u.full_name,

SUM(
CASE
WHEN ta.status='present'
THEN 1
ELSE 0
END
) present_count,

SUM(
CASE
WHEN ta.status='absent'
THEN 1
ELSE 0
END
) absent_count,

SUM(
CASE
WHEN ta.status='leave'
THEN 1
ELSE 0
END
) leave_count,

SUM(
CASE
WHEN ta.status='medical_leave'
THEN 1
ELSE 0
END
) medical_leave_count,

SUM(
CASE
WHEN ta.status='od'
THEN 1
ELSE 0
END
) od_count,

COUNT(*) total_days

FROM teacher_attendance ta

JOIN teachers t
ON ta.teacher_id=t.id

JOIN users u
ON t.user_id=u.id

$where

GROUP BY t.id

ORDER BY u.full_name ASC"

);

/*
|--------------------------------------------------------------------------
| MONTHLY TREND
|--------------------------------------------------------------------------
*/

$trend_query =
mysqli_query(

$conn,

"SELECT

attendance_date,

COUNT(*) total_records,

SUM(

CASE

WHEN status!='absent'

THEN 1

ELSE 0

END

) present_records

FROM teacher_attendance

WHERE

attendance_date
BETWEEN
'$date_from'
AND
'$date_to'

GROUP BY attendance_date

ORDER BY attendance_date ASC"

);

$trend_labels = [];

$trend_values = [];

while(
$row =
mysqli_fetch_assoc(
$trend_query
)
){

$trend_labels[] =
date(
'd M',
strtotime(
$row['attendance_date']
)
);

$percent = 0;

if(
$row['total_records'] > 0
){

$percent = round(

(
$row['present_records']
/
$row['total_records']
)

* 100,

2

);

}

$trend_values[] =
$percent;

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
Teacher Attendance Reports |
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
Teacher Attendance Reports
</h1>

<p>
Analyze teacher attendance records and attendance performance.
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


<div class="dashboard-section">

<form
method="GET"
action=""
>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
From Date
</label>

<input

type="date"

name="date_from"

class="form-input"

value="<?php echo htmlspecialchars($date_from); ?>"

>

</div>

<div class="form-group">

<label class="form-label">
To Date
</label>

<input

type="date"

name="date_to"

class="form-input"

value="<?php echo htmlspecialchars($date_to); ?>"

>

</div>

<div class="form-group">

<label class="form-label">
Teacher
</label>

<select
name="teacher_id"
class="form-select"
>

<option value="0">
All Teachers
</option>

<?php

mysqli_data_seek(
$teachers_dropdown,
0
);

while(
$teacher =
mysqli_fetch_assoc(
$teachers_dropdown
)
){

?>

<option

value="<?php echo $teacher['id']; ?>"

<?php

echo
$teacher_filter ==
$teacher['id']
?
'selected'
:
'';

?>

>

<?php

echo htmlspecialchars(

$teacher['teacher_id']

.
' - '

.
$teacher['full_name']

);

?>

</option>

<?php

}

?>

</select>

</div>

<div class="form-group">

<label class="form-label">
&nbsp;
</label>


<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
"
>

<button
type="submit"
class="btn btn-primary"
>

<i class="fa-solid fa-filter"></i>

Apply Filters

</button>

<a
href="report.php"
class="btn"
>

<i class="fa-solid fa-rotate-left"></i>

Reset

</a>

</div>


</div>

</div>

</form>

</div>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-chart-line"></i>

</div>

<h3>

<?php
echo $attendance_percentage;
?>%

</h3>

</div>

<p>
Attendance %
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-clipboard-list"></i>

</div>

<h3>

<?php
echo $total_records;
?>

</h3>

</div>

<p>
Total Records
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-user-check"></i>

</div>

<h3>

<?php
echo intval(
$stats['present_count']
);
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
echo intval(
$stats['absent_count']
);
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

echo

intval(
$stats['leave_count']
)

+

intval(
$stats['medical_leave_count']
)

+

intval(
$stats['od_count']
);

?>

</h3>

</div>

<p>
Leave / Medical / OD
</p>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Attendance Trend
</h2>

</div>

<div
style="
height:350px;
"
>

<canvas
id="attendanceTrendChart"
></canvas>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Teacher Attendance Summary
</h2>

</div>

<div class="table-responsive">

<table
class="custom-table"
>

<thead>

<tr>

<th data-sort="true">
Teacher ID
</th>

<th data-sort="true">
Teacher Name
</th>

<th data-sort="true">
Present
</th>

<th data-sort="true">
Absent
</th>

<th data-sort="true">
Leave
</th>

<th data-sort="true">
Medical
</th>

<th data-sort="true">
OD
</th>

<th data-sort="true">
Attendance %
</th>

</tr>

</thead>

<tbody>

<?php

$summary_found = false;

while(
$teacher =
mysqli_fetch_assoc(
$teacher_summary_query
)
){

$summary_found = true;

$effective_present =

intval(
$teacher['present_count']
)

+

intval(
$teacher['leave_count']
)

+

intval(
$teacher['medical_leave_count']
)

+

intval(
$teacher['od_count']
);

$attendance_percent = 0;

if(
intval(
$teacher['total_days']
) > 0
){

$attendance_percent = round(

(
$effective_present
/
intval(
$teacher['total_days']
)
)

* 100,

2

);

}

?>

<tr>

<td>

<?php
echo htmlspecialchars(
$teacher['teacher_id']
);
?>

</td>

<td>

<?php
echo htmlspecialchars(
$teacher['full_name']
);
?>

</td>

<td>

<?php
echo intval(
$teacher['present_count']
);
?>

</td>

<td>

<?php
echo intval(
$teacher['absent_count']
);
?>

</td>

<td>

<?php
echo intval(
$teacher['leave_count']
);
?>

</td>

<td>

<?php
echo intval(
$teacher['medical_leave_count']
);
?>

</td>

<td>

<?php
echo intval(
$teacher['od_count']
);
?>

</td>

<td>

<?php
echo $attendance_percent;
?>%

</td>

</tr>

<?php

}

if(
!$summary_found
){

?>

<tr>

<td
colspan="8"
style="
text-align:center;
padding:40px;
"
>

<i
class="
fa-solid
fa-chart-line
"
style="
font-size:42px;
opacity:.4;
display:block;
margin-bottom:12px;
"
></i>

No attendance records found for selected filters.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<script>

<?php

if(
count(
$trend_labels
) > 0
){

?>

new Chart(

document.getElementById(
'attendanceTrendChart'
),

{

type:'line',

data:{

labels:

<?php
echo json_encode(
$trend_labels
);
?>,

datasets:[{

label:
'Attendance %',

data:

<?php
echo json_encode(
$trend_values
);
?>,

fill:false,

tension:0.35

}]

},

options:{

responsive:true,

maintainAspectRatio:false,

plugins:{

legend:{

display:true

}

},

scales:{

y:{

beginAtZero:true,

max:100

}

}

}

}

);

<?php

}
else{

?>

document
.getElementById(
'attendanceTrendChart'
)
.parentElement
.innerHTML =

`
<div
style="
height:100%;
display:flex;
justify-content:center;
align-items:center;
flex-direction:column;
text-align:center;
"
>

<i
class="fa-solid fa-chart-line"
style="
font-size:50px;
opacity:.4;
margin-bottom:15px;
"
></i>

<h3>
No Data Available
</h3>

<p>
No attendance trend data found.
</p>

</div>
`;

<?php

}

?>

</script>

<script src="../../assets/js/common.js"></script>

<script src="../../assets/js/sidebar.js"></script>

</div>

</div>

</div>

</body>

</html>
