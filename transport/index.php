<?php

include('../config/auth.php');

$role = strtolower(
    $_SESSION['user']['role'] ?? ''
);

switch($role){

    case 'admin':

        header(
            'Location:buses/index.php'
        );
        exit;

    case 'driver':

        header(
            'Location:tracking/index.php'
        );
        exit;

    case 'student':

        header(
            'Location:tracking/index.php'
        );
        exit;

    case 'parent':

        header(
            'Location:tracking/index.php'
        );
        exit;

    default:

        header(
            'Location:../dashboard/index.php'
        );
        exit;

}
?>