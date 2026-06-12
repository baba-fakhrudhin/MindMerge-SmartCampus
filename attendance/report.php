<?php

include('../config/auth.php');
include('../config/db.php');

$page_title = 'Attendance Reports';

$class_id = intval(
    $_GET['class_id']
    ??
    0
);

$section_id = intval(
    $_GET['section_id']
    ??
    0
);

$attendance_mode = mysqli_real_escape_string(
    $conn,
    $_GET['attendance_mode']
    ??
    ''
);
$attendance_filter = mysqli_real_escape_string(
    $conn,
    $_GET['attendance_filter']
    ??
    ''
);


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

if(
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $date_from
    )
){
    $date_from =
    date('Y-m-01');
}

if(
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $date_to
    )
){
    $date_to =
    date('Y-m-d');
}

$class_query = mysqli_query(

    $conn,

    "SELECT *

    FROM classes

    WHERE status='active'

    ORDER BY class_name ASC"

);

$section_query = null;

if($class_id > 0){

    $section_query = mysqli_query(

        $conn,

        "SELECT *

        FROM sections

        WHERE class_id='$class_id'

        ORDER BY section_name ASC"

    );

}

$where = [];

$where[] =
"a.attendance_date BETWEEN
'$date_from'
AND
'$date_to'";

if($class_id > 0){

    $where[] =
    "a.class_id='$class_id'";

}

if($section_id > 0){

    $where[] =
    "a.section_id='$section_id'";

}

if(
    $attendance_mode != ''
){

    $where[] =
    "a.attendance_mode='$attendance_mode'";

}

$where_sql =
implode(
    ' AND ',
    $where
);
if(
    empty($where_sql)
){
    $where_sql = '1=1';
}

/*
|--------------------------------------------------------------------------
| STUDENT SUMMARY
|--------------------------------------------------------------------------
*/

$student_summary_query = mysqli_query(

$conn,

"SELECT

st.id,

MAX(st.student_id) AS student_id,

MAX(usr.full_name) AS full_name,

MAX(c.class_name) AS class_name,

MAX(sec.section_name) AS section_name,

SUM(
CASE
WHEN ar.status='present'
THEN 1
ELSE 0
END
) AS present_count,

SUM(
CASE
WHEN ar.status='absent'
THEN 1
ELSE 0
END
) AS absent_count,

SUM(
CASE
WHEN ar.status='late'
THEN 1
ELSE 0
END
) AS late_count,

SUM(
CASE
WHEN ar.status='leave'
THEN 1
ELSE 0
END
) AS leave_count,

SUM(
CASE
WHEN ar.status='medical_leave'
THEN 1
ELSE 0
END
) AS medical_leave_count,

SUM(
CASE
WHEN ar.status='od'
THEN 1
ELSE 0
END
) AS od_count,

COUNT(*) AS total_sessions

FROM attendance_records ar

JOIN attendance a
ON ar.attendance_id=a.attendance_id

JOIN students st
ON ar.student_id=st.id

JOIN users usr
ON st.user_id=usr.id

LEFT JOIN classes c
ON st.class_id=c.class_id

LEFT JOIN sections sec
ON st.section_id=sec.section_id

WHERE

$where_sql

GROUP BY st.id

ORDER BY usr.full_name ASC"

);


$total_sessions = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total

        FROM attendance a

        WHERE

        $where_sql"

    )

)['total'];

/*
|--------------------------------------------------------------------------
| DASHBOARD STATS
|--------------------------------------------------------------------------
*/

$stats = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT

        COUNT(*) AS total_records,

        SUM(
            CASE
            WHEN ar.status!='absent'
            THEN 1
            ELSE 0
            END
            ) AS total_present,

        SUM(
            CASE
            WHEN ar.status='absent'
            THEN 1
            ELSE 0
            END
        ) AS total_absent

        FROM attendance_records ar

        JOIN attendance a
        ON ar.attendance_id=a.attendance_id

        WHERE

        $where_sql"

    )

);

$total_records =
intval(
    $stats['total_records']
);

$total_present =
intval(
    $stats['total_present']
);

$total_absent =
intval(
    $stats['total_absent']
);

$attendance_percentage = 0;
$critical_students = 0;
$warning_students = 0;
$safe_students = 0;
$excellent_students = 0;

$class_summary = [];

$class_summary_query = mysqli_query(

$conn,

"SELECT

c.class_name,
s.section_name,

COUNT(DISTINCT st.id) total_students,

SUM(
CASE
WHEN ar.status!='absent'
THEN 1
ELSE 0
END
) present_count,

COUNT(ar.record_id) total_records

FROM attendance_records ar

JOIN attendance a
ON ar.attendance_id=a.attendance_id

JOIN students st
ON ar.student_id=st.id

LEFT JOIN classes c
ON st.class_id=c.class_id

LEFT JOIN sections s
ON st.section_id=s.section_id

WHERE

$where_sql

GROUP BY
st.class_id,
st.section_id

ORDER BY
c.class_name,
s.section_name"

);
$no_attendance_query = mysqli_query(

$conn,

"SELECT

st.student_id,

u.full_name,

c.class_name,

s.section_name

FROM students st

JOIN users u
ON st.user_id=u.id

LEFT JOIN classes c
ON st.class_id=c.class_id

LEFT JOIN sections s
ON st.section_id=s.section_id

LEFT JOIN attendance_records ar
ON st.id=ar.student_id

WHERE ar.student_id IS NULL

ORDER BY u.full_name ASC"

);

$faculty_summary_query = mysqli_query(

$conn,

"SELECT

u.full_name,

COUNT(
DISTINCT a.attendance_id
) AS sessions_taken,

COUNT(
ar.record_id
) AS total_records,

SUM(

CASE

WHEN ar.status!='absent'
THEN 1

ELSE 0

END

) AS effective_present

FROM attendance a

LEFT JOIN attendance_records ar
ON a.attendance_id=ar.attendance_id

LEFT JOIN teacher_assignments ta
ON a.teacher_assignment_id=ta.assignment_id

LEFT JOIN teachers t
ON ta.teacher_id=t.id

LEFT JOIN users u
ON t.user_id=u.id

WHERE

$where_sql

GROUP BY t.id

ORDER BY u.full_name ASC"
);

while(
$row =
mysqli_fetch_assoc(
$class_summary_query
)
){

$percentage = 0;

if(
$row['total_records'] > 0
){

$percentage = round(

(
$row['present_count']
/
$row['total_records']
) * 100,

2

);

}

$row['attendance_percentage'] =
$percentage;

$class_summary[] = $row;

}
$best_class = '-';
$best_percentage = -1;

$worst_class = '-';
$worst_percentage = 101;

foreach($class_summary as $item){

    if(
        $item['total_records'] <= 0
    ){
        continue;
    }

    if(
        $item['attendance_percentage']
        >
        $best_percentage
    ){

        $best_percentage =
        $item['attendance_percentage'];

        $best_class =
        $item['class_name']
        .
        ' - '
        .
        $item['section_name'];

    }

    if(
        $item['attendance_percentage']
        <
        $worst_percentage
    ){

        $worst_percentage =
        $item['attendance_percentage'];

        $worst_class =
        $item['class_name']
        .
        ' - '
        .
        $item['section_name'];

    }

}
$students_without_attendance =
mysqli_num_rows(
$no_attendance_query
);
if($total_records > 0){

    $attendance_percentage = round(

        (
            $total_present
            /
            $total_records
        ) * 100,

        2

    );

}
    $attendance_trend_query = mysqli_query(

$conn,

"SELECT

attendance_date,

COUNT(*) total_records,

SUM(

CASE

WHEN ar.status!='absent'
THEN 1

ELSE 0

END

) total_present

FROM attendance a

JOIN attendance_records ar
ON a.attendance_id=ar.attendance_id

WHERE

$where_sql

GROUP BY attendance_date

ORDER BY attendance_date ASC"

);

$trend_labels = [];

$trend_values = [];

while(
$row =
mysqli_fetch_assoc(
$attendance_trend_query
)
){

$percent = 0;

if(
$row['total_records'] > 0
){

$percent = round(

(
$row['total_present']
/
$row['total_records']
)
*
100,

2

);

}

$trend_labels[] =
date(
'd M',
strtotime(
$row['attendance_date']
)
);

$trend_values[] =
$percent;
}
$records_trend_query = mysqli_query(

$conn,

"SELECT

attendance_date,

COUNT(ar.record_id)
AS records_count

FROM attendance a

LEFT JOIN attendance_records ar
ON a.attendance_id=ar.attendance_id

WHERE

$where_sql

GROUP BY attendance_date

ORDER BY attendance_date ASC"

);

$record_labels = [];

$record_values = [];

while(
$row =
mysqli_fetch_assoc(
$records_trend_query
)
){

$record_labels[] =
date(
'd M',
strtotime(
$row['attendance_date']
)
);

$record_values[] =
$row['records_count'];

}

$has_trend_data =
count($trend_labels) > 0;

$has_record_data =
count($record_labels) > 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Attendance Reports | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<style>

.filter-grid{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(220px,1fr));

    gap:16px;

}



.report-card{

    background:var(--card);

    padding:22px;

    border-radius:16px;

    border:1px solid rgba(148,163,184,.15);

    transition:.3s;

}

.report-card:hover{

    transform:translateY(-4px);

}

.report-card i{

    font-size:28px;

    margin-bottom:14px;

    color:var(--primary);

}

.report-card h3{

    margin-bottom:8px;

}
.table-responsive{

overflow-x:auto;

}

.data-table{

min-width:1100px;

}
.dashboard-grid{

margin-top:24px;

}

.analytics-section{

margin-top:24px;

}


.analytics-grid{

display:grid;

grid-template-columns:
repeat(
auto-fit,
minmax(500px,1fr)
);

gap:24px;

}

.chart-card{

height:520px;

padding:24px;

}

.chart-card canvas{

width:100% !important;

height:100% !important;

}
.chart-title{

font-size:16px;
font-weight:600;
margin-bottom:12px;
text-align:center;

}
.student-table{

min-width:1200px;

}
.student-table thead th{

position:sticky;

top:0;

background:var(--card);

z-index:2;

}
th i{

margin-left:6px;
opacity:.6;

}
.analytics-grid{

display:grid;

grid-template-columns:
repeat(
auto-fit,
minmax(
450px,
1fr
)
);

gap:20px;

}

.chart-card{

padding:20px;

height:400px;

}

.chart-card canvas{

width:100% !important;

height:100% !important;

}
@media(max-width:768px){

.analytics-grid{

grid-template-columns:1fr;

}

.chart-card{

height:300px;

}

.data-table{

min-width:1400px;

}

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
            Attendance Reports
        </h1>

        <p>
            Analyze attendance trends, sessions and student performance.
        </p>

    </div>

    <div
        style="
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        "
    >

        <a
            href="index.php"
            class="btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back

        </a>

        <a
            href="mark.php"
            class="btn btn-primary"
        >

            <i class="fa-solid fa-clipboard-check"></i>

            Mark Attendance

        </a>

    </div>

</div>

<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Filters
        </h2>

    </div>

    <form
        method="GET"
    >

        <div class="filter-grid">

            <div class="form-group">

                <label class="form-label">
                    Class
                </label>

                <select
                    name="class_id"
                    class="form-select"
                >

                    <option value="">
                        All Classes
                    </option>

                    <?php

                    while(
                        $class =
                        mysqli_fetch_assoc(
                            $class_query
                        )
                    ){

                    ?>

                    <option
                        value="<?php echo $class['class_id']; ?>"
                        <?php

                        if(
                            $class_id ==
                            $class['class_id']
                        ){
                            echo 'selected';
                        }

                        ?>
                    >

                        <?php

                        echo htmlspecialchars(
                            $class['class_name']
                        );

                        ?>

                    </option>

                    <?php } ?>

                </select>

            </div>

            <div class="form-group">

                <label class="form-label">
                    Section
                </label>

                <select
                    name="section_id"
                    class="form-select"
                >

                    <option value="">
                        All Sections
                    </option>

                    <?php

                    if(
                        $section_query
                    ){

                        while(
                            $section =
                            mysqli_fetch_assoc(
                                $section_query
                            )
                        ){

                    ?>

                    <option
                        value="<?php echo $section['section_id']; ?>"
                        <?php

                        if(
                            $section_id ==
                            $section['section_id']
                        ){
                            echo 'selected';
                        }

                        ?>
                    >

                        <?php

                        echo htmlspecialchars(
                            $section['section_name']
                        );

                        ?>

                    </option>

                    <?php

                        }

                    }

                    ?>

                </select>

            </div>

            <div class="form-group">

                <label class="form-label">
                    Attendance Mode
                </label>

                <select
                    name="attendance_mode"
                    class="form-select"
                >

                    <option value="">
                        All Modes
                    </option>

                    <option
                        value="daily"
                        <?php
                        if(
                            $attendance_mode ==
                            'daily'
                        ){
                            echo 'selected';
                        }
                        ?>
                    >
                        Daily
                    </option>

                    <option
                        value="period"
                        <?php
                        if(
                            $attendance_mode ==
                            'period'
                        ){
                            echo 'selected';
                        }
                        ?>
                    >
                        Period
                    </option>

                </select>

            </div>
            <div class="form-group">

    <label class="form-label">
        Attendance Status
    </label>

    <select
        name="attendance_filter"
        class="form-select"
    >

        <option value="">
            All Students
        </option>

        <option
            value="below75"
            <?php if($attendance_filter=='below75') echo 'selected'; ?>
        >
            Below 75%
        </option>

        <option
            value="below50"
            <?php if($attendance_filter=='below50') echo 'selected'; ?>
        >
            Below 50%
        </option>

        <option
            value="above75"
            <?php if($attendance_filter=='above75') echo 'selected'; ?>
        >
            75% And Above
        </option>

        <option
            value="above90"
            <?php if($attendance_filter=='above90') echo 'selected'; ?>
        >
            90% And Above
        </option>

    </select>

</div>

            <div class="form-group">

                <label class="form-label">
                    From Date
                </label>

                <input
                    type="date"
                    name="date_from"
                    value="<?php echo $date_from; ?>"
                    class="form-input"
                >

            </div>

            <div class="form-group">

                <label class="form-label">
                    To Date
                </label>

                <input
                    type="date"
                    name="date_to"
                    value="<?php echo $date_to; ?>"
                    class="form-input"
                >

            </div>

        </div>

        <div
            style="
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            margin-top:12px;
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

                Reset

            </a>

        </div>

    </form>

</div>

<div class="dashboard-grid">

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-calendar-check"></i>

            </div>

            <h3>

                <?php echo $total_sessions; ?>

            </h3>

        </div>

        <p>
            Total Sessions
        </p>

    </div>

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <h3>

                <?php echo $total_records; ?>

            </h3>

        </div>

        <p>
            Attendance Records
        </p>

    </div>

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-user-check"></i>

            </div>

            <h3>

                <?php echo number_format($attendance_percentage,2); ?>%

            </h3>

        </div>

        <p>
            Overall Attendance
        </p>

    </div>

</div>
<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Attendance Analytics
        </h2>

    </div>

    <div
    class="analytics-grid"
    >

        <div
        class="
        dashboard-card
        chart-card
        "
        >

            <canvas
            id="attendanceTrendChart"
            ></canvas>

        </div>

        <div
        class="
        dashboard-card
        chart-card
        "
        >

            <canvas
            id="recordsTrendChart"
            ></canvas>

        </div>

    </div>

</div>
<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Attendance Insights
        </h2>

    </div>

    <div class="dashboard-grid">

        <div class="dashboard-card">

            <h4>
                Best Performing Class
            </h4>

            <p>
                <?php echo htmlspecialchars($best_class); ?>
            </p>

        </div>

        <div class="dashboard-card">

            <h4>
                Lowest Performing Class
            </h4>

            <p>
                <?php echo htmlspecialchars($worst_class); ?>
            </p>

        </div>     

</div>
<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Class Attendance Summary
        </h2>

    </div>

    <div class="table-responsive">

        <table class="data-table sortable-table custom-table">

            <thead>

                <tr>

                    <th data-sort="true">Class</th>

                    <th data-sort="true">Section</th>

                    <th data-sort="true">Students</th>

                    <th data-sort="true">Attendance %</th>

                    <th data-sort="true">Status</th>

                </tr>

            </thead>

            <tbody>
            <?php $class_rows = 0; ?>

            <?php foreach($class_summary as $row){ 
                $class_rows++;
                ?>
                <tr>

                    <td>
                        <?php echo htmlspecialchars(
$row['class_name'] ?? '-'
); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars(
$row['section_name'] ?? '-'
); ?>
                    </td>

                    <td>
                        <?php echo $row['total_students']; ?>
                    </td>

                    <td>
                        <?php echo number_format(
$row['attendance_percentage'],
2
); ?>%
                    </td>

                    <td>

                        <?php

if(
$row['attendance_percentage'] >= 90
){

$status='Excellent';
$class='success';

}
elseif(
$row['attendance_percentage'] >= 75
){

$status='Safe';
$class='success';

}
else{

$status='Warning';
$class='warning';

}
                        ?>

                        <span
                            class="status <?php echo $class; ?>"
                        >

                            <?php echo $status; ?>

                        </span>

                    </td>

                </tr>

            <?php } if($class_rows == 0){?>
            <tr>

<td colspan="5">

No class summary data found.

</td>

</tr>

<?php } ?>
            </tbody>

        </table>

    </div>

</div>
<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Student Attendance Summary
        </h2>

    </div>

    <div class="table-responsive">

        <table
class="data-table sortable-table student-table">

            <thead>

                <tr>

                    <th data-sort="true">
                        Student ID
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        Student Name
                        <i class="fa-solid fa-sort"></i>
                    </th>
                    <th data-sort="true">
                        Class
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        Section
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        Present
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        Absent
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        Late
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        Leave
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        Medical
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        OD
                        <i class="fa-solid fa-sort"></i>
                    </th>

                    <th data-sort="true">
                        Attendance %
                        <i class="fa-solid fa-sort"></i>
                    </th>
                    <th data-sort="true">
                        Eligibility
                    </th>

                    <th data-sort="true">
                        Performance
                        <i class="fa-solid fa-sort"></i>
                    </th>
                    

                </tr>

            </thead>

            <tbody>
                

            <?php
            
                $student_rows = [];
            if(
                mysqli_num_rows(
                    $student_summary_query
                ) > 0
            ){
                while(
                    $student =
                    mysqli_fetch_assoc(
                        $student_summary_query
                    )
                ){

                    $total_sessions =
                    intval(
                        $student['total_sessions']
                    );

                    $present =
                    intval(
                        $student['present_count']
                    );

                    $attendance_percent = 0;
                $eligibility_class='danger';
$eligibility_text='Not Eligible';

$performance_class='danger';
$performance_text='Critical';
                    if(
                        $total_sessions > 0
                    ){

                       $effective_present =

intval($student['present_count'])

+

intval($student['late_count'])

+

intval($student['leave_count'])

+

intval($student['medical_leave_count'])

+

intval($student['od_count']);

$attendance_percent = round(

(
$effective_present
/
$total_sessions
)

* 100,

2

);


$skip_student = false;

switch($attendance_filter){

    case 'below75':

        $skip_student =
        $attendance_percent >= 75;

    break;

    case 'below50':

        $skip_student =
        $attendance_percent >= 50;

    break;

    case 'above75':

        $skip_student =
        $attendance_percent < 75;

    break;

    case 'above90':

        $skip_student =
        $attendance_percent < 90;

    break;

}

if($skip_student){

    continue;

}


$eligibility_class = 'danger';
$eligibility_text = 'Not Eligible';

if($attendance_percent >= 75){

    $eligibility_class = 'success';
    $eligibility_text = 'Eligible';

}

$performance_class = 'danger';
$performance_text = 'Critical';

if($attendance_percent >= 90){

    $performance_class = 'success';
    $performance_text = 'Excellent';

}
elseif($attendance_percent >= 75){

    $performance_class = 'primary';
    $performance_text = 'Good';

}
elseif($attendance_percent >= 65){

    $performance_class = 'warning';
    $performance_text = 'Warning';

}
                    }  

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

                        <?php

                        echo htmlspecialchars(
                            $student['full_name']
                        );

                        ?>

                    </td>
                    <td>

                        <?php

                        echo htmlspecialchars(
                            $student['class_name']
                        );

                        ?>

                        </td>

                        <td>

                        <?php

                        echo htmlspecialchars(
                            $student['section_name']
                        );

                        ?>

                        </td>

                    <td>

                        <span class="status success">

                            <?php

                            echo
                            $student['present_count'];

                            ?>

                        </span>

                    </td>

                    <td>

                        <span class="status danger">

                            <?php

                            echo
                            $student['absent_count'];

                            ?>

                        </span>

                    </td>

                    <td>

                        <?php

                        echo
                        $student['late_count'];

                        ?>

                    </td>

                    <td>

                        <?php

                        echo
                        $student['leave_count'];

                        ?>

                    </td>

                    <td>

                        <?php

                        echo
                        $student['medical_leave_count'];

                        ?>

                    </td>

                    <td>

                        <?php

                        echo
                        $student['od_count'];

                        ?>

                    </td>

                    <td>

                        <strong>

                            <?php

                            echo
                            $attendance_percent;

                            ?>

                            %

                        </strong>

                    </td>
                    <td>

                        <span
                        class="status <?php echo $eligibility_class; ?>"
                        >

                        <?php

                        echo
                        $eligibility_text;

                        ?>

                        </span>

                        </td>
                    <td>

                        <span
                            class="status <?php echo $performance_class; ?>"
                        >

                            <?php

                            echo
                            $performance_text;

                            ?>

                        </span>

                    </td>

                </tr>

            <?php
                $student_rows[] = $student;
                }

            }
            else{

            ?>

                <tr>

                    <td
                        colspan="13"
                        style="
                        text-align:center;
                        padding:60px 20px;
                        "
                    >

                        <div>

                            <i
                                class="fa-solid fa-user-slash"
                                style="
                                font-size:48px;
                                color:var(--muted);
                                margin-bottom:16px;
                                "
                            ></i>

                            <h3>
                                No Student Data Found
                            </h3>

                            <p
                                style="
                                margin-top:10px;
                                color:var(--muted);
                                "
                            >

                                No attendance statistics are available
                                for the selected filters.

                            </p>

                        </div>

                    </td>

                </tr>

<?php

}
$displayed_students =

is_array($student_rows)

?

count($student_rows)

:

0;
if($displayed_students == 0){

?>

<tr>

<td
colspan="13"
style="
text-align:center;
padding:40px;
color:var(--text-muted);
"
>

<i
class="fa-solid fa-circle-info"
style="
font-size:20px;
margin-bottom:10px;
display:block;
"
></i>

No students found for the selected filters.

</td>

</tr>

<?php

}else{
?>
<p
style="
margin-top:6px;
color:var(--text-muted);
font-size:14px;
"
>

Showing

<strong>

<?php
echo $displayed_students;
?>

</strong>

students

</p>
<?php
}

?>


            </tbody>

        </table>

    </div>

</div>
<script>

const classSelect =
document.querySelector(
    'select[name="class_id"]'
);

const sectionSelect =
document.querySelector(
    'select[name="section_id"]'
);

if(
    classSelect &&
    sectionSelect
){

    classSelect.addEventListener(

        'change',

        function(){

            const classId =
            this.value;

            sectionSelect.innerHTML =
            '<option value="">Loading...</option>';

            if(
                classId === ''
            ){

                sectionSelect.innerHTML =
                '<option value="">All Sections</option>';

                return;

            }

            fetch(
                'get_sections.php?class_id='
                +
                classId
            )

            .then(
                response =>
                response.json()
            )

            .then(
                data => {

                    let html =
                    '<option value="">All Sections</option>';

                    data.forEach(
                        function(section){

                            html +=
                            '<option value="' +
                            section.section_id +
                            '" ' +

                            (
                                section.section_id ==
                                '<?php echo $section_id; ?>'
                                ?
                                'selected'
                                :
                                ''
                            ) +

                            '>' +

                            section.section_name +

                            '</option>';
                        }
                    );

                    sectionSelect.innerHTML =
                    html;

                }
            )

            .catch(
                function(){

                    sectionSelect.innerHTML =
                    '<option value="">All Sections</option>';

                }
            );

        }

    );
    if(
    classSelect &&
    classSelect.value !== ''
){

    classSelect.dispatchEvent(
        new Event('change')
    );

}

}

document.addEventListener(
'DOMContentLoaded',
function(){

document
.querySelectorAll(
'.sortable-table'
)
.forEach(function(table){

table
.querySelectorAll(
'th[data-sort="true"]'
)
.forEach(function(header,index){

let asc = true;

header.addEventListener(
'click',
function(){

const rows =
Array.from(
table.querySelectorAll(
'tbody tr'
)
);

rows.sort(function(a,b){

const aText =
a.children[index]
?.innerText
.trim();

const bText =
b.children[index]
?.innerText
.trim();

return asc
?
aText.localeCompare(
bText,
undefined,
{
numeric:true,
sensitivity:'base'
}
)
:
bText.localeCompare(
aText,
undefined,
{
numeric:true,
sensitivity:'base'
}
);

});

asc = !asc;

rows.forEach(function(row){

table
.querySelector('tbody')
.appendChild(row);

});

});

});

});

});

</script>

</div>

</div>

</div>

<?php

if(
$has_trend_data
){

?>

<script>

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
?>

}]

},

options:{

responsive:true,

maintainAspectRatio:false

}

}

);

</script>

<?php

}
else{

?>

<script>

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
flex-direction:column;
justify-content:center;
align-items:center;
text-align:center;
"
>

<i
class="fa-solid fa-chart-line"
style="
font-size:48px;
opacity:.3;
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

</script>

<?php

}

?>

<?php

if($has_record_data){

?>

<script>

new Chart(

document.getElementById(
'recordsTrendChart'
),

{

type:'line',

data:{

labels:

<?php
echo json_encode(
$record_labels
);
?>,

datasets:[{

label:
'Attendance Records',

data:

<?php
echo json_encode(
$record_values
);
?>

}]

},

options:{

responsive:true,

maintainAspectRatio:false

}

}

);

</script>

<?php

}
else{

?>

<script>

document
.getElementById(
'recordsTrendChart'
)
.parentElement
.innerHTML =

`
<div
style="
height:100%;
display:flex;
flex-direction:column;
justify-content:center;
align-items:center;
text-align:center;
"
>

<i
class="fa-solid fa-chart-bar"
style="
font-size:48px;
opacity:.3;
margin-bottom:15px;
"
></i>

<h3>
No Data Available
</h3>

<p>
No attendance records found.
</p>

</div>
`;

</script>

<?php

}

?>
<script src="../assets/js/common.js"></script>

</body>

</html>