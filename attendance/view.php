<?php

include('../config/auth.php');
include('../config/db.php');

$attendance_id = intval(
    $_GET['attendance_id']
    ??
    $_GET['id']
    ??
    0
);

if($attendance_id <= 0){

    header(
        'Location:index.php'
    );

    exit();

}

$attendance_query = mysqli_query(

    $conn,

    "SELECT

        a.*,

        c.class_name,

        s.section_name,

        p.period_name,

        sub.subject_name,

        u.full_name AS created_by_name

    FROM attendance a

    LEFT JOIN classes c
    ON a.class_id = c.class_id

    LEFT JOIN sections s
    ON a.section_id = s.section_id

    LEFT JOIN periods p
    ON a.period_id = p.period_id

    LEFT JOIN subjects sub
    ON a.subject_id = sub.subject_id

    LEFT JOIN users u
    ON a.created_by = u.id

    WHERE

    a.attendance_id = '$attendance_id'

    LIMIT 1"

);

if(

    !$attendance_query

    ||

    mysqli_num_rows(
        $attendance_query
    ) == 0

){

    header(
        'Location:index.php'
    );

    exit();

}

$attendance = mysqli_fetch_assoc(
    $attendance_query
);

$records_query = mysqli_query(

    $conn,

    "SELECT

        ar.*,

        st.student_id,

        usr.full_name

    FROM attendance_records ar

    JOIN students st
    ON ar.student_id = st.id

    JOIN users usr
    ON st.user_id = usr.id

    WHERE

    ar.attendance_id='$attendance_id'

    ORDER BY usr.full_name ASC"

);

$total_students = 0;
$present_count = 0;
$absent_count = 0;
$late_count = 0;
$leave_count = 0;
$medical_leave_count = 0;
$od_count = 0;

$student_records = [];

while(
    $row = mysqli_fetch_assoc(
        $records_query
    )
){

    $student_records[] = $row;

    $total_students++;

    switch(
        $row['status']
    ){

        case 'present':
            $present_count++;
        break;

        case 'absent':
            $absent_count++;
        break;

        case 'late':
            $late_count++;
        break;

        case 'leave':
            $leave_count++;
        break;

        case 'medical_leave':
            $medical_leave_count++;
        break;

        case 'od':
            $od_count++;
        break;

    }

}

$attendance_percentage = 0;

if($total_students > 0){

    $attendance_percentage =
        round(
            ($present_count / $total_students)
            * 100,
            2
        );

}

$page_title =
'View Attendance';

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
View Attendance | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.info-box{

    background:var(--card);

    border-radius:16px;

    padding:20px;

    border:1px solid rgba(148,163,184,.15);

}

.info-grid{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(220px,1fr));

    gap:18px;

}

.info-item{

    display:flex;

    flex-direction:column;

    gap:6px;

}

.info-label{

    font-size:13px;

    color:var(--muted);

    font-weight:500;

}

.info-value{

    font-size:15px;

    font-weight:600;

}

.status{

    text-transform:capitalize;

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
Attendance Details
</h1>

<p>
View attendance session information and student records.
</p>

</div>

<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
">

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

<a
href="edit.php?attendance_id=<?php echo $attendance_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Attendance

</a>

</div>

</div>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-calendar-days"></i>

</div>

<h3>

<?php

echo date(
'd M',
strtotime(
$attendance['attendance_date']
)
);

?>

</h3>

</div>

<p>
Attendance Date
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-users"></i>

</div>

<h3>

<?php

echo $total_students;

?>

</h3>

</div>

<p>
Students
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

<i class="fa-solid fa-chart-pie"></i>

</div>

<h3>

<?php

echo $attendance_percentage;

?>

%

</h3>

</div>

<p>
Attendance %
</p>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Attendance Information
</h2>

</div>

<div class="info-grid">

<div class="info-item">

<span class="info-label">

Class

</span>

<span class="info-value">

<?php

echo htmlspecialchars(
$attendance['class_name']
);

?>

</span>

</div>

<div class="info-item">

<span class="info-label">

Section

</span>

<span class="info-value">

<?php

echo htmlspecialchars(
$attendance['section_name']
);

?>

</span>

</div>

<div class="info-item">

<span class="info-label">

Attendance Mode

</span>

<span class="info-value">

<?php

echo ucfirst(
$attendance['attendance_mode']
);

?>

</span>

</div>

<div class="info-item">

<span class="info-label">

Day

</span>

<span class="info-value">

<?php

echo ucfirst(
$attendance['attendance_day']
);

?>

</span>

</div>

<?php if(
!empty(
$attendance['period_name']
)
){ ?>

<div class="info-item">

<span class="info-label">

Period

</span>

<span class="info-value">

<?php

echo htmlspecialchars(
$attendance['period_name']
);

?>

</span>

</div>

<?php } ?>

<?php if(
!empty(
$attendance['subject_name']
)
){ ?>

<div class="info-item">

<span class="info-label">

Subject

</span>

<span class="info-value">

<?php

echo htmlspecialchars(
$attendance['subject_name']
);

?>

</span>

</div>

<?php } ?>

<div class="info-item">

<span class="info-label">

Created By

</span>

<span class="info-value">

<?php

echo htmlspecialchars(
$attendance['created_by_name']
);

?>

</span>

</div>

</div>

</div>

<?php if(
!empty(
$attendance['remarks']
)
){ ?>

<div class="dashboard-section">

<div class="section-header">

<h2>
Remarks
</h2>

</div>

<div class="info-box">

<?php

echo nl2br(
htmlspecialchars(
$attendance['remarks']
)
);

?>

</div>

</div>

<?php } ?>
<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Student Attendance Records
        </h2>

    </div>

    <div class="table-responsive">

        <table class="data-table sortable-table">

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
                        Status
                        <i class="fa-solid fa-sort"></i>
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php

            if(count($student_records) > 0){

                foreach(
                    $student_records
                    as
                    $record
                ){

                    $status_class = 'secondary';

                    switch(
                        $record['status']
                    ){

                        case 'present':
                            $status_class = 'success';
                        break;

                        case 'absent':
                            $status_class = 'danger';
                        break;

                        case 'late':
                            $status_class = 'warning';
                        break;

                        case 'leave':
                            $status_class = 'info';
                        break;

                        case 'medical_leave':
                            $status_class = 'primary';
                        break;

                        case 'od':
                            $status_class = 'secondary';
                        break;

                    }

            ?>

                <tr>

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $record['student_id']
                        );

                        ?>

                    </td>

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $record['full_name']
                        );

                        ?>

                    </td>

                    <td>

                        <span
                            class="status <?php echo $status_class; ?>"
                        >

                            <?php

                            echo ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $record['status']
                                )
                            );

                            ?>

                        </span>

                    </td>

                </tr>

            <?php

                }

            }
            else{

            ?>

                <tr>

                    <td
                        colspan="3"
                        style="
                            text-align:center;
                            padding:60px 20px;
                        "
                    >

                        <div>

                            <i
                                class="fa-solid fa-users"
                                style="
                                    font-size:48px;
                                    color:var(--muted);
                                    margin-bottom:16px;
                                "
                            ></i>

                            <h3>
                                No Student Records Found
                            </h3>

                            <p
                                style="
                                    margin-top:10px;
                                    color:var(--muted);
                                "
                            >
                                No attendance records are available
                                for this session.
                            </p>

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

<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Attendance Breakdown
        </h2>

    </div>

    <div class="dashboard-grid">

        <div class="dashboard-card">

            <h3>
                <?php echo $present_count; ?>
            </h3>

            <p>
                Present Students
            </p>

        </div>

        <div class="dashboard-card">

            <h3>
                <?php echo $absent_count; ?>
            </h3>

            <p>
                Absent Students
            </p>

        </div>

        <div class="dashboard-card">

            <h3>
                <?php echo $late_count; ?>
            </h3>

            <p>
                Late Students
            </p>

        </div>

        <div class="dashboard-card">

            <h3>
                <?php echo $leave_count; ?>
            </h3>

            <p>
                Leave
            </p>

        </div>

        <div class="dashboard-card">

            <h3>
                <?php echo $medical_leave_count; ?>
            </h3>

            <p>
                Medical Leave
            </p>

        </div>

        <div class="dashboard-card">

            <h3>
                <?php echo $od_count; ?>
            </h3>

            <p>
                On Duty
            </p>

        </div>

    </div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

<script>

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

</body>

</html>