<?php

include('../../config/auth.php');
include('../../config/db.php');
require_once('../../config/notifications.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$user_id = (int) ($_SESSION['user']['id'] ?? 0);
$user_role = $_SESSION['user']['role'] ?? '';

$context = notification_user_context($conn, $user_id, $user_role);
$marked = notification_mark_all_read($conn, $context);

echo json_encode([
    'success'      => true,
    'marked_count' => $marked,
    'unread_count' => notification_unread_count($conn, $context),
]);
