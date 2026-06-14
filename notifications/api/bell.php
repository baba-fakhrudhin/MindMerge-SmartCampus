<?php

include('../../config/auth.php');
include('../../config/db.php');
require_once('../../config/notifications.php');

header('Content-Type: application/json');

$user_id = (int) ($_SESSION['user']['id'] ?? 0);
$user_role = $_SESSION['user']['role'] ?? '';

$context = notification_user_context($conn, $user_id, $user_role);
$where = notification_visibility_where($conn, $context);

$unread = notification_unread_count($conn, $context);

$query = mysqli_query(
    $conn,
    "SELECT
        n.id,
        n.title,
        n.type,
        n.created_at,
        CASE WHEN nr.read_id IS NULL THEN 0 ELSE 1 END AS is_read
     FROM notifications n
     LEFT JOIN notification_reads nr
       ON nr.notification_id = n.id AND nr.user_id = '$user_id'
     WHERE $where
     ORDER BY n.created_at DESC
     LIMIT 8"
);

$notifications = [];

while ($row = mysqli_fetch_assoc($query)) {
    $config = notification_type_config($row['type']);
    $created = strtotime($row['created_at']);
    $diff = time() - $created;

    if ($diff < 60) {
        $time_ago = 'Just now';
    } elseif ($diff < 3600) {
        $time_ago = floor($diff / 60) . 'm ago';
    } elseif ($diff < 86400) {
        $time_ago = floor($diff / 3600) . 'h ago';
    } else {
        $time_ago = date('d M Y', $created);
    }

    $notifications[] = [
        'id'         => (int) $row['id'],
        'title'      => $row['title'],
        'type'       => $row['type'],
        'type_label' => $config['label'],
        'icon'       => $config['icon'],
        'color'      => $config['color'],
        'bg'         => $config['bg'],
        'is_read'    => (bool) $row['is_read'],
        'time_ago'   => $time_ago,
    ];
}

echo json_encode([
    'success'       => true,
    'unread_count'  => $unread,
    'notifications' => $notifications,
]);
