<?php

include('../../config/auth.php');
include('../../config/db.php');

if(
    !isset($_GET['id'])
    ||
    !is_numeric($_GET['id'])
){

    header('Location:index.php');
    exit;

}

$bus_id = (int)$_GET['id'];

/*
|--------------------------------------------------------------------------
| Check Student Assignments
|--------------------------------------------------------------------------
*/

$student_check = mysqli_query(

    $conn,

    "SELECT assignment_id

     FROM transport_student_assignments

     WHERE bus_id = $bus_id

     LIMIT 1"

);

if(mysqli_num_rows($student_check) > 0){

    header(
        'Location:index.php?error=in_use'
    );

    exit;

}

/*
|--------------------------------------------------------------------------
| Check Route Assignment
|--------------------------------------------------------------------------
|
| Prevent deleting bus if route exists.
| Admin must delete route first.
|
*/

$route_check = mysqli_query(

    $conn,

    "SELECT route_id

     FROM transport_routes

     WHERE bus_id = $bus_id

     LIMIT 1"

);

if(mysqli_num_rows($route_check) > 0){

    header(
        'Location:index.php?error=route_exists'
    );

    exit;

}

/*
|--------------------------------------------------------------------------
| Remove Live Tracking Records
|--------------------------------------------------------------------------
*/

mysqli_query(

    $conn,

    "DELETE FROM transport_live_location

     WHERE bus_id = $bus_id"

);

/*
|--------------------------------------------------------------------------
| Delete Bus
|--------------------------------------------------------------------------
*/

$delete = mysqli_query(

    $conn,

    "DELETE FROM transport_buses

     WHERE bus_id = $bus_id"

);

if(!$delete){

    header(
        'Location:index.php?error=delete_failed'
    );

    exit;

}

header(
    'Location:index.php?success=deleted'
);

exit;

?>