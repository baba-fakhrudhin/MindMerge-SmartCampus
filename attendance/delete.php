<?php

include('../config/auth.php');
include('../config/db.php');

$attendance_id = intval(
    $_GET['attendance_id'] ?? 0
);

if($attendance_id <= 0){

    header(
        "Location:index.php?error=invalid_attendance"
    );

    exit();

}

/* START TRANSACTION */

mysqli_begin_transaction($conn);

try{

    /* DELETE ATTENDANCE RECORDS */

    mysqli_query(

        $conn,

        "DELETE FROM attendance_records
        WHERE attendance_id = $attendance_id"

    );

    /* DELETE ATTENDANCE SESSION */

    mysqli_query(

        $conn,

        "DELETE FROM attendance
        WHERE attendance_id = $attendance_id"

    );

    mysqli_commit($conn);

    header(
        "Location:index.php?success=deleted"
    );

    exit();

}
catch(Exception $e){

    mysqli_rollback($conn);

    header(
        "Location:index.php?error=delete_failed"
    );

    exit();

}

?>