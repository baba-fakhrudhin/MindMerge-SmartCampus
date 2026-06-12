<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

/* CHECK STUDENTS */

$student_count = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
        FROM students
        WHERE class_id = $id"

    )

)['total'];

/* CHECK TIMETABLE */

$timetable_count = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
        FROM timetables
        WHERE class_id = $id"

    )

)['total'];

/* CHECK ATTENDANCE */

$attendance_count = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
        FROM attendance
        WHERE class_id = $id"

    )

)['total'];

if(

    $student_count > 0 ||

    $timetable_count > 0 ||

    $attendance_count > 0

){

    header(
        "Location:index.php?error=in_use"
    );

    exit();

}

/* DELETE CLASS */

mysqli_query(

    $conn,

    "DELETE FROM classes
    WHERE class_id = $id"

);

header(
    "Location:index.php?success=deleted"
);

exit();

?>