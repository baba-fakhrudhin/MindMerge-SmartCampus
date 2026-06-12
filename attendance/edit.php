<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

$attendance_id = intval(
    $_GET['attendance_id']
    ??
    $_GET['id']
    ??
    0
);

if($attendance_id <= 0){

    header('Location:index.php');
    exit();

}

$attendance_query = mysqli_query(

    $conn,

    "SELECT *

    FROM attendance

    WHERE attendance_id='$attendance_id'

    LIMIT 1"

);

if(

    !$attendance_query

    ||

    mysqli_num_rows(
        $attendance_query
    ) == 0

){

    header('Location:index.php');
    exit();

}

$attendance =
mysqli_fetch_assoc(
    $attendance_query
);

if(isset($_POST['update_attendance'])){

    $remarks = mysqli_real_escape_string(

        $conn,

        trim(
            $_POST['remarks']
            ??
            ''
        )

    );

    mysqli_begin_transaction(
        $conn
    );

    try{

        $update_attendance = mysqli_query(

            $conn,

            "UPDATE attendance

            SET

            remarks='$remarks'

            WHERE

            attendance_id='$attendance_id'"

        );

        if(!$update_attendance){

            throw new Exception(
                mysqli_error($conn)
            );

        }

        if(

            isset(
                $_POST['student_ids']
            )

            &&

            count(
                $_POST['student_ids']
            ) > 0

        ){

            foreach(

                $_POST['student_ids']

                as

                $student_id

            ){

                $student_id =
                intval($student_id);

                $status =
                mysqli_real_escape_string(

                    $conn,

                    $_POST[
                        'status_' .
                        $student_id
                    ]

                    ??

                    'present'

                );

                $allowed_statuses = [

                    'present',
                    'absent',
                    'late',
                    'leave',
                    'medical_leave',
                    'od'

                ];

                if(

                    !in_array(
                        $status,
                        $allowed_statuses
                    )

                ){

                    $status = 'present';

                }

                $update_record =
                mysqli_query(

                    $conn,

                    "UPDATE attendance_records

                    SET

                    status='$status'

                    WHERE

                    attendance_id='$attendance_id'

                    AND

                    student_id='$student_id'"

                );

                if(!$update_record){

                    throw new Exception(
                        mysqli_error($conn)
                    );

                }

            }

        }

        mysqli_commit($conn);

        header(
            'Location:index.php?success=updated'
        );

        exit();

    }
    catch(Exception $e){

        mysqli_rollback($conn);

        $error =
        $e->getMessage();

    }

}

$records_query = mysqli_query(

    $conn,

    "SELECT

        ar.*,

        st.id AS student_pk,
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

$student_records = [];

$present_count = 0;
$absent_count = 0;
$late_count = 0;
$leave_count = 0;
$medical_leave_count = 0;
$od_count = 0;

while(

    $row = mysqli_fetch_assoc(
        $records_query
    )

){

    $student_records[] = $row;

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

$total_students =
count(
    $student_records
);

$page_title =
'Edit Attendance';

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Edit Attendance | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

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

    font-weight:500;

    color:var(--muted);

}

.info-value{

    font-size:15px;

    font-weight:600;

}

.student-row{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    padding:14px;

    border:1px solid rgba(148,163,184,.15);

    border-radius:14px;

    margin-bottom:12px;

}

.student-info{

    display:flex;

    flex-direction:column;

    gap:4px;

}

.student-name{

    font-weight:600;

}

.student-id{

    font-size:13px;

    color:var(--muted);

}

@media(max-width:768px){

    .student-row{

        flex-direction:column;

        align-items:flex-start;

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
            Edit Attendance
        </h1>

        <p>
            Update student attendance records and remarks.
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
            href="view.php?attendance_id=<?php echo $attendance_id; ?>"
            class="btn"
        >

            <i class="fa-solid fa-eye"></i>

            View

        </a>

        <a
            href="index.php"
            class="btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back

        </a>

    </div>

</div>

<?php if($error != ''){ ?>

<div
    style="
    background:#fee2e2;
    color:#991b1b;
    padding:14px 18px;
    border-radius:14px;
    margin-bottom:20px;
    "
>

    <i class="fa-solid fa-circle-exclamation"></i>

    <?php echo htmlspecialchars($error); ?>

</div>

<?php } ?>

<form method="POST">

<div class="dashboard-grid">

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <h3>

                <?php echo $total_students; ?>

            </h3>

        </div>

        <p>
            Total Students
        </p>

    </div>

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-user-check"></i>

            </div>

            <h3>

                <?php echo $present_count; ?>

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

                <?php echo $absent_count; ?>

            </h3>

        </div>

        <p>
            Absent
        </p>

    </div>

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-clock"></i>

            </div>

            <h3>

                <?php echo $late_count; ?>

            </h3>

        </div>

        <p>
            Late
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
                Attendance Date
            </span>

            <span class="info-value">

                <?php

                echo date(
                    'd M Y',
                    strtotime(
                        $attendance['attendance_date']
                    )
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
                Class ID
            </span>

            <span class="info-value">

                <?php echo $attendance['class_id']; ?>

            </span>

        </div>

        <div class="info-item">

            <span class="info-label">
                Section ID
            </span>

            <span class="info-value">

                <?php echo $attendance['section_id']; ?>

            </span>

        </div>

        <?php if(!empty($attendance['period_id'])){ ?>

        <div class="info-item">

            <span class="info-label">
                Period
            </span>

            <span class="info-value">

                <?php echo $attendance['period_id']; ?>

            </span>

        </div>

        <?php } ?>

    </div>

</div>

<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Attendance Remarks
        </h2>

    </div>

    <div class="form-group">

        <textarea
            name="remarks"
            class="form-textarea"
            placeholder="Enter attendance remarks..."
        ><?php

        echo htmlspecialchars(
            $attendance['remarks']
        );

        ?></textarea>

    </div>

</div>
<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Student Attendance
        </h2>

    </div>

    <div
        style="
        background:#dbeafe;
        color:#1e40af;
        padding:14px 18px;
        border-radius:12px;
        margin-bottom:20px;
        "
    >

        <i class="fa-solid fa-circle-info"></i>

        Update attendance status for students below.

    </div>

    <div
        style="
        display:flex;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:20px;
        "
    >

        <button
            type="button"
            class="btn"
            id="markAllPresent"
        >

            <i class="fa-solid fa-check"></i>

            Mark All Present

        </button>

        <button
            type="button"
            class="btn"
            id="markAllAbsent"
        >

            <i class="fa-solid fa-user-xmark"></i>

            Mark All Absent

        </button>

    </div>

    <div id="studentsContainer">

        <?php

        if(count($student_records) > 0){

            foreach(
                $student_records
                as
                $student
            ){

        ?>

        <div class="student-row">

            <div class="student-info">

                <div class="student-name">

                    <?php

                    echo htmlspecialchars(
                        $student['full_name']
                    );

                    ?>

                </div>

                <div class="student-id">

                    <?php

                    echo htmlspecialchars(
                        $student['student_pk']
                    );

                    ?>

                </div>

            </div>

            <div
                style="
                min-width:200px;
                "
            >

                <input
                    type="hidden"
                    name="student_ids[]"
                    value="<?php echo $student['student_pk']; ?>"
                >

                <select
                    name="status_<?php echo $student['student_pk']; ?>"
                    class="form-select attendance-status"
                >

                    <option
                        value="present"
                        <?php

                        if(
                            $student['status']
                            ==
                            'present'
                        ){
                            echo 'selected';
                        }

                        ?>
                    >
                        Present
                    </option>

                    <option
                        value="absent"
                        <?php

                        if(
                            $student['status']
                            ==
                            'absent'
                        ){
                            echo 'selected';
                        }

                        ?>
                    >
                        Absent
                    </option>

                    <option
                        value="late"
                        <?php

                        if(
                            $student['status']
                            ==
                            'late'
                        ){
                            echo 'selected';
                        }

                        ?>
                    >
                        Late
                    </option>

                    <option
                        value="leave"
                        <?php

                        if(
                            $student['status']
                            ==
                            'leave'
                        ){
                            echo 'selected';
                        }

                        ?>
                    >
                        Leave
                    </option>

                    <option
                        value="medical_leave"
                        <?php

                        if(
                            $student['status']
                            ==
                            'medical_leave'
                        ){
                            echo 'selected';
                        }

                        ?>
                    >
                        Medical Leave
                    </option>

                    <option
                        value="od"
                        <?php

                        if(
                            $student['status']
                            ==
                            'od'
                        ){
                            echo 'selected';
                        }

                        ?>
                    >
                        OD
                    </option>

                </select>

            </div>

        </div>

        <?php

            }

        }
        else{

        ?>

        <div
            style="
            text-align:center;
            padding:50px;
            color:var(--muted);
            "
        >

            <i
                class="fa-solid fa-users"
                style="
                font-size:48px;
                margin-bottom:15px;
                "
            ></i>

            <h3>
                No Student Records Found
            </h3>

            <p>
                Attendance records are unavailable.
            </p>

        </div>

        <?php } ?>

    </div>

</div>

<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Attendance Summary
        </h2>

    </div>

    <div class="dashboard-grid">

        <div class="dashboard-card">

            <h3 id="summaryPresent">

                <?php echo $present_count; ?>

            </h3>

            <p>
                Present
            </p>

        </div>

        <div class="dashboard-card">

            <h3 id="summaryAbsent">

                <?php echo $absent_count; ?>

            </h3>

            <p>
                Absent
            </p>

        </div>

        <div class="dashboard-card">

            <h3 id="summaryLate">

                <?php echo $late_count; ?>

            </h3>

            <p>
                Late
            </p>

        </div>

        <div class="dashboard-card">

            <h3 id="summaryLeave">

                <?php echo $leave_count; ?>

            </h3>

            <p>
                Leave
            </p>

        </div>

        <div class="dashboard-card">

            <h3 id="summaryMedical">

                <?php echo $medical_leave_count; ?>

            </h3>

            <p>
                Medical Leave
            </p>

        </div>

        <div class="dashboard-card">

            <h3 id="summaryOD">

                <?php echo $od_count; ?>

            </h3>

            <p>
                OD
            </p>

        </div>

    </div>

</div>

<div
    style="
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    margin-top:24px;
    "
>

    <button
        type="submit"
        name="update_attendance"
        class="btn btn-primary"
    >

        <i class="fa-solid fa-floppy-disk"></i>

        Update Attendance

    </button>

    <a
        href="view.php?attendance_id=<?php echo $attendance_id; ?>"
        class="btn"
    >

        Cancel

    </a>

</div>
<script>

function updateSummaryCards(){

    let present = 0;
    let absent = 0;
    let late = 0;
    let leave = 0;
    let medical = 0;
    let od = 0;

    document
        .querySelectorAll('.attendance-status')
        .forEach(function(select){

            switch(select.value){

                case 'present':
                    present++;
                break;

                case 'absent':
                    absent++;
                break;

                case 'late':
                    late++;
                break;

                case 'leave':
                    leave++;
                break;

                case 'medical_leave':
                    medical++;
                break;

                case 'od':
                    od++;
                break;

            }

        });

    document.getElementById(
        'summaryPresent'
    ).innerText = present;

    document.getElementById(
        'summaryAbsent'
    ).innerText = absent;

    document.getElementById(
        'summaryLate'
    ).innerText = late;

    document.getElementById(
        'summaryLeave'
    ).innerText = leave;

    document.getElementById(
        'summaryMedical'
    ).innerText = medical;

    document.getElementById(
        'summaryOD'
    ).innerText = od;

}

document
    .querySelectorAll(
        '.attendance-status'
    )
    .forEach(function(select){

        select.addEventListener(
            'change',
            updateSummaryCards
        );

    });

document.getElementById(
    'markAllPresent'
)?.addEventListener(
    'click',
    function(){

        document
            .querySelectorAll(
                '.attendance-status'
            )
            .forEach(function(select){

                select.value =
                'present';

            });

        updateSummaryCards();

    }
);

document.getElementById(
    'markAllAbsent'
)?.addEventListener(
    'click',
    function(){

        document
            .querySelectorAll(
                '.attendance-status'
            )
            .forEach(function(select){

                select.value =
                'absent';

            });

        updateSummaryCards();

    }
);

document.querySelector(
    'form'
)?.addEventListener(
    'submit',
    function(e){

        const students =
        document.querySelectorAll(
            'input[name="student_ids[]"]'
        );

        if(
            students.length === 0
        ){

            e.preventDefault();

            alert(
                'No students available to update.'
            );

            return false;

        }

        const confirmUpdate =
        confirm(
            'Update attendance records?'
        );

        if(
            !confirmUpdate
        ){

            e.preventDefault();

            return false;

        }

    }
);

document.addEventListener(
    'DOMContentLoaded',
    function(){

        updateSummaryCards();

    }
);

</script>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

<script>

document.addEventListener(

    'DOMContentLoaded',

    function(){

        if(
            typeof initializeSortableTables
            ===
            'function'
        ){

            initializeSortableTables();

        }

    }

);

</script>

</body>

</html>