<?php

include('../../config/auth.php');
include('../../config/db.php');

requirePermission('permissions', 'edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$save_type = trim($_POST['save_type'] ?? '');

if ($save_type === 'role') {
    $role = strtolower(trim($_POST['role'] ?? ''));

    if (
    !in_array(
        $role,
        ['teacher', 'student', 'parent', 'driver'],
        true
    )
) {
        header('Location: index.php?tab=roles&error=invalid_role');
        exit();
    }

    $granted = array_map('intval', $_POST['permissions'] ?? []);

    if (permission_save_role_matrix($conn, $role, $granted)) {
        permission_refresh_current_user($conn);
        header('Location: index.php?tab=roles&role=' . urlencode($role) . '&saved=1');
        exit();
    }

    header('Location: index.php?tab=roles&role=' . urlencode($role) . '&error=save_failed');
    exit();
}

if ($save_type === 'user') {
    $user_id = (int) ($_POST['user_id'] ?? 0);

    if ($user_id <= 0) {
        header('Location: index.php?tab=users&error=invalid_user');
        exit();
    }

    $user_perm = $_POST['user_perm'] ?? [];
    $overrides = [];

    foreach ($user_perm as $permission_id => $value) {
        $pid = (int) $permission_id;

        if ($pid <= 0 || $value === '') {
            continue;
        }

        $overrides[$pid] = (int) $value === 1 ? 1 : 0;
    }

    if (permission_save_user_overrides($conn, $user_id, $overrides)) {
        if ((int) ($_SESSION['user']['id'] ?? 0) === $user_id) {
            permission_refresh_current_user($conn);
        }

        header('Location: index.php?tab=users&user_id=' . $user_id . '&saved=1');
        exit();
    }

    header('Location: index.php?tab=users&user_id=' . $user_id . '&error=save_failed');
    exit();
}

header('Location: index.php?error=invalid_request');
exit();
