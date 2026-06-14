<?php

session_start();

require_once __DIR__ . '/constants.php';

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/permissions.php';

if (empty($_SESSION['permission_map'])) {
    permission_load_user($conn, $_SESSION['user']);
}

permission_guard_request($conn);
