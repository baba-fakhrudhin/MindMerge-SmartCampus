<?php

/**
 * Portal routing and path helpers for multi-role ERP.
 */

require_once __DIR__ . '/../../config/constants.php';

function portal_get_role(): string
{
    return strtolower($_SESSION['user']['role'] ?? $_SESSION['permission_role'] ?? '');
}

function portal_dashboard_path(string $role = ''): string
{
    $role = $role !== '' ? strtolower($role) : portal_get_role();

    $map = [
    'admin'   => 'admin/dashboard/index.php',
    'teacher' => 'teacher/dashboard/index.php',
    'student' => 'student/dashboard/index.php',
    'parent'  => 'parent/dashboard/index.php',

    'driver'  => 'driver/dashboard/index.php',
    'helper'  => 'driver/dashboard/index.php'
];

    return $map[$role] ?? 'admin/dashboard/index.php';
}

function portal_dashboard_url(string $role = ''): string
{
    return BASE_URL . portal_dashboard_path($role);
}

function portal_redirect_home(): void
{
    if (!headers_sent()) {
        header('Location: ' . portal_dashboard_url());
        exit();
    }

    echo '<script>window.location.href="' . htmlspecialchars(portal_dashboard_url()) . '";</script>';
    exit();
}

function portal_profile_path(string $role = ''): string
{
    return 'profile/index.php';
}

function portal_profile_url(string $role = ''): string
{
    return BASE_URL . portal_profile_path($role);
}

function portal_is_admin_context(): bool
{
    return portal_get_role() === 'admin';
}

function portal_greeting_name(): string
{
    return $_SESSION['user']['full_name'] ?? 'User';
}

function portal_time_greeting(): string
{
    $hour = (int) date('G');

    if ($hour < 12) {
        return 'Good Morning';
    }

    if ($hour < 17) {
        return 'Good Afternoon';
    }

    return 'Good Evening';
}

function portal_require_role(array $allowed_roles): void
{
    $role = portal_get_role();

    if (!in_array($role, $allowed_roles, true)) {
        require_once __DIR__ . '/../../config/permissions.php';
        permission_deny_and_exit();
    }
}

function portal_include_auth(int $depth = 2): void
{
    require_once dirname(__DIR__, $depth) . '/config/auth.php';
    require_once dirname(__DIR__, $depth) . '/config/db.php';
}

function portal_asset(string $path): string
{
    return BASE_URL . ltrim($path, '/');
}
