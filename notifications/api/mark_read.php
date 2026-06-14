<?php

include('../../config/auth.php');
include('../../config/db.php');
require_once('../../config/notifications.php');

header('Content-Type: application/json');

$user_id = (int) ($_SESSION['user']['id'] ?? 0);
$user_role = $_SESSION['user']['role'] ?? '';
$notification_id = (int) ($_POST['notification_id'] ?? $_GET['notification_id'] ?? 0);

$context = notification_user_context($conn, $user_id, $user_role);

if ($notification_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification']);
    exit;
}

if (!notification_user_can_see($conn, $notification_id, $context)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$ok = notification_mark_read($conn, $notification_id, $user_id);

echo json_encode([
    'success'      => $ok,
    'unread_count' => notification_unread_count($conn, $context),
]);
