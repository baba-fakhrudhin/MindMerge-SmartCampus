<?php

session_start();

if (isset($_SESSION['user'])) {
    require_once __DIR__ . '/shared/helpers/portal.php';
    header('Location: ' . portal_dashboard_url());
} else {
    header('Location: auth/login.php');
}

exit();
