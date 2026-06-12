
<?php

include('../../config/auth.php');
include('../../config/db.php');

$page_title = 'Teacher Attendance';

/*
|--------------------------------------------------------------------------
| DASHBOARD STATS
|--------------------------------------------------------------------------
*/

$total_teachers = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM teachers"

)

)['total'];

$present_today = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM teacher_attendance

WHERE

attendance_date = CURDATE()

AND

status='present'"

)

)['total'];

$absent_today = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM teacher_attendance

WHERE

attendance_date = CURDATE()

AND

status='absent'"

)

)['total'];

$leave_today = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM teacher_attendance

WHERE

attendance_date = CURDATE()

AND

status IN
(
'leave',
'medical_leave',
'od'
)"

)

)['total'];
$total_records = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM teacher_attendance"

)

)['total'];
$attendance_today = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT

COUNT(*) total_records,
COALESCE(

SUM(

CASE

WHEN status!='absent'

THEN 1

ELSE 0

END

),

0

) AS present_count

FROM teacher_attendance

WHERE

attendance_date = CURDATE()"

)

);

$today_percentage = 0;

if(
intval(
$attendance_today['total_records']
) > 0
){

$today_percentage = round(

(
intval(
$attendance_today['present_count']
)
/
intval(
$attendance_today['total_records']
)
)

* 100,

2

);

}

/*
|--------------------------------------------------------------------------
| RECENT ATTENDANCE
|--------------------------------------------------------------------------
*/

$recent_attendance = mysqli_query(

$conn,

"SELECT

ta.attendance_id,

ta.attendance_date,

ta.status,

ta.remarks,

t.teacher_id,

u.full_name,

creator.full_name AS marked_by

FROM teacher_attendance ta

JOIN teachers t
ON ta.teacher_id=t.id

JOIN users u
ON t.user_id=u.id

LEFT JOIN users creator
ON ta.created_by=creator.id

ORDER BY ta.created_at DESC

LIMIT 20"

);

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

    <meta charset="UTF-8">

    <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>
    Attendance Management | MindMerge SmartCampus
    </title>


    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">

    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </head>
    <style>
.page-header h1{

margin:0;

font-size:28px;

font-weight:700;

}

.page-header p{

margin-top:6px;

color:var(--muted);

}
        </style>

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
            Manage faculty attendance records, reports and daily attendance activities.
        </p>

    </div>

</div>

<div class="dashboard-grid">

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <h3>

                <?php echo $total_teachers; ?>

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

                <?php echo $present_today; ?>

            </h3>

        </div>

        <p>
            Present Today
        </p>

    </div>

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-user-xmark"></i>

            </div>

            <h3>

                <?php echo $absent_today; ?>

            </h3>

        </div>

        <p>
            Absent Today
        </p>

    </div>

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-calendar-minus"></i>

            </div>

            <h3>

                <?php echo $leave_today; ?>

            </h3>

        </div>

        <p>
            On Leave Today
        </p>

    </div>

    <div class="dashboard-card stat-card">

        <div class="stat-top">

            <div class="card-icon">

                <i class="fa-solid fa-chart-line"></i>

            </div>

            <h3>

                <?php

echo number_format(

floatval(
$today_percentage
),

2

);

?>%

            </h3>

        </div>

        <p>
            Attendance Rate
        </p>

    </div>
    <div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-clipboard-list"></i>

</div>

<h3>

<?php echo $total_records; ?>

</h3>

</div>

<p>
Attendance Records
</p>

</div>

</div>

<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Quick Actions
        </h2>

    </div>

    <div class="quick-actions">

        <a
        href="mark.php"
        class="action-card"
        >

            <i class="fa-solid fa-clipboard-check"></i>

            <h3>
                Mark Attendance
            </h3>

            <p>
                Record teacher attendance for a selected date.
            </p>

        </a>

        <a
        href="report.php"
        class="action-card"
        >

            <i class="fa-solid fa-chart-column"></i>

            <h3>
                Attendance Reports
            </h3>

            <p>
                View attendance reports, summaries and analytics.
            </p>

        </a>

        <a
        href="report.php?date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>"
        class="action-card"
        >

            <i class="fa-solid fa-calendar-day"></i>

            <h3>
                Today's Attendance
            </h3>

            <p>
                View attendance records marked today.
            </p>

        </a>
    <a
href="index.php"
class="action-card"
>

<i class="fa-solid fa-rotate-right"></i>

<h3>
Refresh Dashboard
</h3>

<p>
Reload teacher attendance statistics.
</p>

</a>
    </div>

</div>

<div class="dashboard-section">

    <div class="section-header">

        <h2>
            Recent Attendance Activity
        </h2>

    </div>

    <div class="table-responsive">

        <table
        class="data-table custom-table"
        >

            <thead>

                <tr>

                    <th data-sort="true">
                        Date
                    </th>

                    <th data-sort="true">
                        Teacher ID
                    </th>

                    <th data-sort="true">
                        Teacher Name
                    </th>

                    <th data-sort="true">
                        Status
                    </th>

                    <th>
                        Remarks
                    </th>

                    <th data-sort="true">
                        Marked By
                    </th>

                </tr>

            </thead>

            <tbody>

                <?php

                if(
                mysqli_num_rows(
                $recent_attendance
                ) > 0
                ){

                while(
                $row =
                mysqli_fetch_assoc(
                $recent_attendance
                )
                ){

                ?>

                <tr>

                    <td>

                        <?php
                        echo date(

                        'd M Y',

                        strtotime(
                        $row['attendance_date']
                        )

                        );
                        ?>

                    </td>

                    <td>

                        <?php
                        echo htmlspecialchars(
                        $row['teacher_id']
                        );
                        ?>

                    </td>

                    <td>

                        <?php
                        echo htmlspecialchars(
                        $row['full_name']
                        );
                        ?>

                    </td>

                    <td>

                        <?php

                        $badge_class =
                        'secondary';

                        if(
                        $row['status']
                        ==
                        'present'
                        ){

                        $badge_class =
                        'success';

                        }
                        elseif(
                        $row['status']
                        ==
                        'absent'
                        ){

                        $badge_class =
                        'danger';

                        }
                        else{

                        $badge_class =
                        'warning';

                        }

                        ?>

                        <span
class="status <?php echo $badge_class; ?>"
>
                            <?php

                            echo ucwords(

                            str_replace(

                            '_',
                            ' ',

                            $row['status']

                            )

                            );

                            ?>

                        </span>

                    </td>

                    <td>

                        <?php

                        echo !empty(
                        $row['remarks']
                        )

                        ?

                        htmlspecialchars(
                        $row['remarks']
                        )

                        :

                        '-';

                        ?>

                    </td>

                    <td>

                        <?php

                        echo !empty(
                        $row['marked_by']
                        )

                        ?

                        htmlspecialchars(
                        $row['marked_by']
                        )

                        :

                        '-';

                        ?>

                    </td>

                </tr>

                <?php

                }

                }
                else{

                ?>

                <tr>

                    <td
                    colspan="6"
                    style="
                    text-align:center;
                    padding:40px;
                    "
                    >

                        <i
                        class="
                        fa-solid
                        fa-calendar-xmark
                        "
                        style="
                        font-size:40px;
                        opacity:.5;
                        margin-bottom:10px;
                        display:block;
                        "
                        ></i>

                        No teacher attendance records found.

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
<?php include('../../partials/footer.php'); ?>

<script src="../../assets/js/common.js"></script>

</body>
</html>