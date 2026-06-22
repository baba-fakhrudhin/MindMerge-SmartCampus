<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/permissions.php';
require_once __DIR__ . '/../shared/helpers/portal.php';

/**
 * Authenticate against the existing users table and preserve the application's
 * current session contract. A required role can be supplied for dedicated
 * portal entry points such as the admin login.
 */
function auth_attempt_login(
    mysqli $conn,
    string $email,
    string $password,
    ?string $required_role = null
): array {
    $email = trim($email);

    if ($email === '' || $password === '') {
        return ['success' => false, 'error' => 'Please enter your email and password.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Please enter a valid email address.'];
    }

    $email_escaped = mysqli_real_escape_string($conn, $email);
    $result = mysqli_query(
        $conn,
        "SELECT * FROM users WHERE email = '$email_escaped' LIMIT 1"
    );
    $user = $result ? mysqli_fetch_assoc($result) : null;

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'The email or password is incorrect.'];
    }

    $role = strtolower($user['role'] ?? '');
    $required_role = $required_role !== null ? strtolower($required_role) : null;

    if ($required_role !== null && $role !== $required_role) {
        return [
            'success' => false,
            'error' => $required_role === 'admin'
                ? 'This sign-in page is reserved for administrators.'
                : 'This account cannot use this sign-in page.',
        ];
    }

    if ($required_role === null && $role === 'admin') {
        return [
            'success' => false,
            'error' => 'Administrator accounts must use the dedicated Admin Login.',
            'admin_account' => true,
        ];
    }

    session_regenerate_id(true);
    $_SESSION['user'] = $user;
    permission_load_user($conn, $user);

    return [
        'success' => true,
        'user' => $user,
        'redirect' => portal_dashboard_url($role),
    ];
}
