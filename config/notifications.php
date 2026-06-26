<?php

/**
 * MindMerge SmartCampus — Notification Service
 *
 * Central configuration and helpers for the notifications module.
 * Other modules (attendance, exams, fees, etc.) can call notification_create()
 * to publish alerts without duplicating targeting logic.
 */

if (!defined('NOTIFICATION_TYPES')) {

    define('NOTIFICATION_TYPES', [

        'general' => [
            'label' => 'General',
            'icon'  => 'fa-bullhorn',
            'class' => 'primary',
            'color' => '#2563eb',
            'bg'    => 'rgba(37,99,235,0.08)',
        ],

        'exam' => [
            'label' => 'Exam',
            'icon'  => 'fa-file-lines',
            'class' => 'danger',
            'color' => '#ef4444',
            'bg'    => 'rgba(239,68,68,0.08)',
        ],

        'fee' => [
            'label' => 'Fee',
            'icon'  => 'fa-wallet',
            'class' => 'success',
            'color' => '#22c55e',
            'bg'    => 'rgba(34,197,94,0.08)',
        ],

        'attendance' => [
            'label' => 'Attendance',
            'icon'  => 'fa-calendar-check',
            'class' => 'warning',
            'color' => '#f59e0b',
            'bg'    => 'rgba(245,158,11,0.08)',
        ],

        'result' => [
            'label' => 'Result',
            'icon'  => 'fa-chart-column',
            'class' => 'info',
            'color' => '#06b6d4',
            'bg'    => 'rgba(6,182,212,0.08)',
        ],

        'announcement' => [
            'label' => 'Announcement',
            'icon'  => 'fa-megaphone',
            'class' => 'primary',
            'color' => '#7c3aed',
            'bg'    => 'rgba(124,58,237,0.08)',
        ],

        'holiday' => [
            'label' => 'Holiday',
            'icon'  => 'fa-umbrella-beach',
            'class' => 'success',
            'color' => '#10b981',
            'bg'    => 'rgba(16,185,129,0.08)',
        ],

        'event' => [
            'label' => 'Event',
            'icon'  => 'fa-calendar-days',
            'class' => 'warning',
            'color' => '#f97316',
            'bg'    => 'rgba(249,115,22,0.08)',
        ],

        'transport' => [
            'label' => 'Transport',
            'icon'  => 'fa-bus',
            'class' => 'info',
            'color' => '#0284c7',
            'bg'    => 'rgba(2,132,199,0.08)',
        ],

        'emergency' => [
            'label' => 'Emergency',
            'icon'  => 'fa-triangle-exclamation',
            'class' => 'danger',
            'color' => '#dc2626',
            'bg'    => 'rgba(220,38,38,0.12)',
        ],

    ]);

}

if (!defined('NOTIFICATION_QUICK_TEMPLATES')) {

    define('NOTIFICATION_QUICK_TEMPLATES', [

        'attendance_warning' => [
            'name'    => 'Attendance Warning',
            'icon'    => 'fa-calendar-check',
            'type'    => 'attendance',
            'title'   => 'Attendance Warning',
            'message' => 'Your attendance has fallen below the required threshold. Please meet your class advisor at the earliest to discuss improvement measures.',
        ],

        'fee_reminder' => [
            'name'    => 'Fee Reminder',
            'icon'    => 'fa-wallet',
            'type'    => 'fee',
            'title'   => 'Fee Payment Reminder',
            'message' => 'This is a reminder that your fee payment is pending. Please complete the payment before the due date to avoid late penalties.',
        ],

        'exam_schedule' => [
            'name'    => 'Exam Schedule',
            'icon'    => 'fa-file-lines',
            'type'    => 'exam',
            'title'   => 'Exam Schedule Published',
            'message' => 'The examination schedule has been published. Please check the exam dates, timings, and venue details on the portal.',
        ],

        'results_published' => [
            'name'    => 'Results Published',
            'icon'    => 'fa-chart-column',
            'type'    => 'result',
            'title'   => 'Results Published',
            'message' => 'Examination results are now available. Log in to the portal to view your detailed mark sheet and performance summary.',
        ],

        'holiday_notice' => [
            'name'    => 'Holiday Notice',
            'icon'    => 'fa-umbrella-beach',
            'type'    => 'holiday',
            'title'   => 'Holiday Announcement',
            'message' => 'The campus will remain closed on the announced holiday. Regular classes and activities will resume on the next working day.',
        ],

        'emergency_alert' => [
            'name'    => 'Emergency Alert',
            'icon'    => 'fa-triangle-exclamation',
            'type'    => 'emergency',
            'title'   => 'Emergency Alert',
            'message' => 'Important: An emergency situation requires immediate attention. Please follow official instructions and stay safe.',
        ],

    ]);

}


/**
 * Return type configuration with safe fallback.
 */
function notification_type_config(?string $type = null): array
{
    $types = NOTIFICATION_TYPES;

    if ($type === null) {
        return $types;
    }

    return $types[$type] ?? $types['general'];
}


/**
 * Validate notification type against allowed enum values.
 */
function notification_is_valid_type(string $type): bool
{
    return isset(NOTIFICATION_TYPES[$type]);
}


/**
 * Parse posted target strings (type:value) into structured rows.
 */
function notification_parse_targets(array $raw_targets): array
{
    $parsed = [];
    $seen   = [];

    foreach ($raw_targets as $raw) {
        $raw = trim((string) $raw);

        if ($raw === '' || !str_contains($raw, ':')) {
            continue;
        }

        [$target_type, $target_value] = explode(':', $raw, 2);
        $target_type  = trim($target_type);
        $target_value = trim($target_value);

        $allowed_types = ['role', 'class', 'section', 'student', 'teacher', 'driver'];

        if (
            !in_array($target_type, $allowed_types, true)
            || $target_value === ''
        ) {
            continue;
        }

        if ($target_type === 'role') {
            $allowed_roles = ['all', 'admin', 'teacher', 'student', 'parent', 'driver'];
            if (!in_array($target_value, $allowed_roles, true)) {
                continue;
            }
        }

        $key = $target_type . ':' . $target_value;

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $parsed[]   = [
            'target_type'  => $target_type,
            'target_value' => $target_value,
        ];
    }

    return $parsed;
}


/**
 * Build recipient context for the logged-in user (role-aware filtering).
 */
function notification_user_context(mysqli $conn, int $user_id, string $role): array
{
    $role = strtolower(trim($role));

    $context = [
        'user_id'           => $user_id,
        'role'              => $role,
        'is_admin'          => ($role === 'admin'),
        'student_id'        => null,
        'student_row_id'    => null,
        'class_id'          => null,
        'section_id'        => null,
        'teacher_id'        => null,
        'teacher_row_id'    => null,
        'parent_student_ids'=> [],
        'assigned_class_ids'=> [],
        'assigned_section_ids'=> [],
    ];

    if ($role === 'student') {
        $student = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT id, student_id, class_id, section_id
             FROM students
             WHERE user_id = '$user_id'
             LIMIT 1"
        ));

        if ($student) {
            $context['student_row_id'] = (int) $student['id'];
            $context['student_id']     = $student['student_id'];
            $context['class_id']       = (int) ($student['class_id'] ?? 0) ?: null;
            $context['section_id']     = (int) ($student['section_id'] ?? 0) ?: null;
        }
    }

    if ($role === 'teacher') {
        $teacher = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT id, teacher_id
             FROM teachers
             WHERE user_id = '$user_id'
             LIMIT 1"
        ));

        if ($teacher) {
            $context['teacher_row_id'] = (int) $teacher['id'];
            $context['teacher_id']     = $teacher['teacher_id'];

            $assignments = mysqli_query(
                $conn,
                "SELECT DISTINCT class_id, section_id
                 FROM teacher_assignments
                 WHERE teacher_id = '" . (int) $teacher['id'] . "'"
            );

            while ($row = mysqli_fetch_assoc($assignments)) {
                if (!empty($row['class_id'])) {
                    $context['assigned_class_ids'][] = (int) $row['class_id'];
                }
                if (!empty($row['section_id'])) {
                    $context['assigned_section_ids'][] = (int) $row['section_id'];
                }
            }
        }
    }

    if ($role === 'parent') {
        $parents = mysqli_query(
            $conn,
            "SELECT student_id
             FROM parents
             WHERE user_id = '$user_id'"
        );

        while ($row = mysqli_fetch_assoc($parents)) {
            if (!empty($row['student_id'])) {
                $context['parent_student_ids'][] = $row['student_id'];
            }
        }
    }

    return $context;
}


/**
 * Determine if a single target matches the user context.
 */
function notification_target_matches_user(array $target, array $context): bool
{
    $type  = $target['target_type'];
    $value = $target['target_value'];

    if ($type === 'role') {
        if ($value === 'all') {
            return true;
        }

        return $value === $context['role'];
    }

    if ($type === 'class') {
        $class_id = (int) $value;

        if ($context['class_id'] === $class_id) {
            return true;
        }

        if (in_array($class_id, $context['assigned_class_ids'], true)) {
            return true;
        }

        return false;
    }

    if ($type === 'section') {
        $section_id = (int) $value;

        if ($context['section_id'] === $section_id) {
            return true;
        }

        if (in_array($section_id, $context['assigned_section_ids'], true)) {
            return true;
        }

        return false;
    }

    if ($type === 'student') {
        if ($context['student_id'] === $value) {
            return true;
        }

        if (in_array($value, $context['parent_student_ids'], true)) {
            return true;
        }

        return false;
    }

    if ($type === 'teacher') {
        return $context['teacher_id'] === $value;
    }

    return false;
}


/**
 * Check whether a user should see a notification.
 */
function notification_user_can_see(
    mysqli $conn,
    int $notification_id,
    array $context
): bool {

    if ($context['is_admin']) {
        return true;
    }

    $notification = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT created_by
         FROM notifications
         WHERE id = '$notification_id'
         LIMIT 1"
    ));

    if (!$notification) {
        return false;
    }

    if ((int) $notification['created_by'] === (int) $context['user_id']) {
        return true;
    }

    $targets = mysqli_query(
        $conn,
        "SELECT target_type, target_value
         FROM notification_targets
         WHERE notification_id = '$notification_id'"
    );

    if (mysqli_num_rows($targets) === 0) {
        return $context['is_admin'];
    }

    while ($target = mysqli_fetch_assoc($targets)) {
        if (notification_target_matches_user($target, $context)) {
            return true;
        }
    }

    return false;
}


/**
 * SQL WHERE fragment limiting notifications visible to a user.
 */
function notification_visibility_where(mysqli $conn, array $context): string
{
    if ($context['is_admin']) {
        return '1=1';
    }

    $user_id = (int) $context['user_id'];
    $role    = mysqli_real_escape_string($conn, $context['role']);

    $conditions = ["n.created_by = '$user_id'"];

    $conditions[] = "EXISTS (
        SELECT 1 FROM notification_targets nt
        WHERE nt.notification_id = n.id
        AND nt.target_type = 'role'
        AND nt.target_value IN ('all', '$role')
    )";

    if ($context['class_id']) {
        $class_id = (int) $context['class_id'];
        $conditions[] = "EXISTS (
            SELECT 1 FROM notification_targets nt
            WHERE nt.notification_id = n.id
            AND nt.target_type = 'class'
            AND nt.target_value = '$class_id'
        )";
    }

    if ($context['section_id']) {
        $section_id = (int) $context['section_id'];
        $conditions[] = "EXISTS (
            SELECT 1 FROM notification_targets nt
            WHERE nt.notification_id = n.id
            AND nt.target_type = 'section'
            AND nt.target_value = '$section_id'
        )";
    }

    if (!empty($context['assigned_class_ids'])) {
        $class_list = implode(',', array_map('intval', $context['assigned_class_ids']));
        $conditions[] = "EXISTS (
            SELECT 1 FROM notification_targets nt
            WHERE nt.notification_id = n.id
            AND nt.target_type = 'class'
            AND nt.target_value IN ($class_list)
        )";
    }

    if (!empty($context['assigned_section_ids'])) {
        $section_list = implode(',', array_map('intval', $context['assigned_section_ids']));
        $conditions[] = "EXISTS (
            SELECT 1 FROM notification_targets nt
            WHERE nt.notification_id = n.id
            AND nt.target_type = 'section'
            AND nt.target_value IN ($section_list)
        )";
    }

    if ($context['student_id']) {
        $student_id = mysqli_real_escape_string($conn, $context['student_id']);
        $conditions[] = "EXISTS (
            SELECT 1 FROM notification_targets nt
            WHERE nt.notification_id = n.id
            AND nt.target_type = 'student'
            AND nt.target_value = '$student_id'
        )";
    }

    if ($context['teacher_id']) {
        $teacher_id = mysqli_real_escape_string($conn, $context['teacher_id']);
        $conditions[] = "EXISTS (
            SELECT 1 FROM notification_targets nt
            WHERE nt.notification_id = n.id
            AND nt.target_type = 'teacher'
            AND nt.target_value = '$teacher_id'
        )";
    }

    if (!empty($context['parent_student_ids'])) {
        $escaped = array_map(
            fn($id) => "'" . mysqli_real_escape_string($conn, $id) . "'",
            $context['parent_student_ids']
        );
        $student_list = implode(',', $escaped);
        $conditions[] = "EXISTS (
            SELECT 1 FROM notification_targets nt
            WHERE nt.notification_id = n.id
            AND nt.target_type = 'student'
            AND nt.target_value IN ($student_list)
        )";
    }

    return '(' . implode(' OR ', $conditions) . ')';
}


/**
 * Resolve human-readable labels for notification targets.
 */
function notification_resolve_target_labels(mysqli $conn, int $notification_id): array
{
    $labels = [];

    $query = mysqli_query(
        $conn,
        "SELECT target_type, target_value
         FROM notification_targets
         WHERE notification_id = '$notification_id'
         ORDER BY target_id ASC"
    );

    while ($row = mysqli_fetch_assoc($query)) {
        $labels[] = notification_format_target_label($conn, $row['target_type'], $row['target_value']);
    }

    return $labels;
}


/**
 * Format a single target as readable text.
 */
function notification_format_target_label(
    mysqli $conn,
    string $target_type,
    string $target_value
): string {

    if ($target_type === 'role') {
        $map = [
            'all'     => 'All Users',
            'admin'   => 'All Admins',
            'teacher' => 'All Teachers',
            'student' => 'All Students',
            'parent'  => 'All Parents',
            'driver'  => 'All Drivers',
        ];

        return $map[$target_value] ?? ucfirst($target_value);
    }

    if ($target_type === 'class') {
        $class_id = (int) $target_value;
        $class = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT class_name, class_code FROM classes WHERE class_id = '$class_id' LIMIT 1"
        ));

        if ($class) {
            $code = $class['class_code'] ? $class['class_code'] . ' — ' : '';
            return 'Class: ' . $code . $class['class_name'];
        }

        return 'Class #' . $class_id;
    }

    if ($target_type === 'section') {
        $section_id = (int) $target_value;
        $section = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT s.section_name, s.section_code, c.class_code, c.class_name
             FROM sections s
             LEFT JOIN classes c ON s.class_id = c.class_id
             WHERE s.section_id = '$section_id'
             LIMIT 1"
        ));

        if ($section) {
            $prefix = $section['class_code'] ?? $section['class_name'] ?? '';
            return 'Section: ' . trim($prefix . ' ' . ($section['section_code'] ?? $section['section_name']));
        }

        return 'Section #' . $section_id;
    }

    if ($target_type === 'student') {
        $student_id = mysqli_real_escape_string($conn, $target_value);
        $student = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT u.full_name, s.student_id
             FROM students s
             JOIN users u ON s.user_id = u.id
             WHERE s.student_id = '$student_id'
             LIMIT 1"
        ));

        if ($student) {
            return 'Student: ' . $student['full_name'] . ' (' . $student['student_id'] . ')';
        }

        return 'Student: ' . $target_value;
    }

    if ($target_type === 'teacher') {
        $teacher_id = mysqli_real_escape_string($conn, $target_value);
        $teacher = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT u.full_name, t.teacher_id
             FROM teachers t
             JOIN users u ON t.user_id = u.id
             WHERE t.teacher_id = '$teacher_id'
             LIMIT 1"
        ));

        if ($teacher) {
            return 'Teacher: ' . $teacher['full_name'] . ' (' . $teacher['teacher_id'] . ')';
        }

        return 'Teacher: ' . $target_value;
    }

    return ucfirst($target_type) . ': ' . $target_value;
}


/**
 * Create a notification with multiple targets (extensible for other modules).
 *
 * @param array $data Keys: title, message, type, created_by, targets (array of type:value strings),
 *                    optional source_module, source_ref
 * @return int|false Notification ID on success
 */
function notification_create(mysqli $conn, array $data)
{
    $title   = trim($data['title'] ?? '');
    $message = trim($data['message'] ?? '');
    $type    = trim($data['type'] ?? 'general');
    $created_by = (int) ($data['created_by'] ?? 0);
    $targets = $data['targets'] ?? [];
    $source_module = trim($data['source_module'] ?? '');
    $source_ref    = trim($data['source_ref'] ?? '');

    if ($title === '' || $message === '' || !notification_is_valid_type($type)) {
        return false;
    }

    $parsed_targets = is_array($targets) && isset($targets[0]) && is_array($targets[0])
        ? $targets
        : notification_parse_targets(is_array($targets) ? $targets : []);

    if (empty($parsed_targets)) {
        return false;
    }

    $title_esc   = mysqli_real_escape_string($conn, $title);
    $message_esc = mysqli_real_escape_string($conn, $message);
    $type_esc    = mysqli_real_escape_string($conn, $type);

    $source_cols = '';
    $source_vals = '';

    mysqli_begin_transaction($conn);

    try {
        $has_source = notification_table_has_column($conn, 'notifications', 'source_module');

        if ($has_source && $source_module !== '') {
            mysqli_query(
                $conn,
                "INSERT INTO notifications (title, message, type, created_by, source_module, source_ref)
                 VALUES ('$title_esc', '$message_esc', '$type_esc', '$created_by',
                         '" . mysqli_real_escape_string($conn, $source_module) . "',
                         '" . mysqli_real_escape_string($conn, $source_ref) . "')"
            );
        } else {
            mysqli_query(
                $conn,
                "INSERT INTO notifications (title, message, type, created_by)
                 VALUES ('$title_esc', '$message_esc', '$type_esc', '$created_by')"
            );
        }

        $notification_id = (int) mysqli_insert_id($conn);

        if ($notification_id <= 0) {
            throw new Exception('Insert failed');
        }

        foreach ($parsed_targets as $target) {
            $tt = mysqli_real_escape_string($conn, $target['target_type']);
            $tv = mysqli_real_escape_string($conn, $target['target_value']);

            mysqli_query(
                $conn,
                "INSERT INTO notification_targets (notification_id, target_type, target_value)
                 VALUES ('$notification_id', '$tt', '$tv')"
            );
        }

        mysqli_commit($conn);

        return $notification_id;

    } catch (Throwable $e) {
        mysqli_rollback($conn);
        return false;
    }
}


/**
 * Mark a notification as read for a user.
 */
function notification_mark_read(mysqli $conn, int $notification_id, int $user_id): bool
{
    if ($notification_id <= 0 || $user_id <= 0) {
        return false;
    }

    $existing = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT read_id FROM notification_reads
         WHERE notification_id = '$notification_id' AND user_id = '$user_id'
         LIMIT 1"
    ));

    if ($existing) {
        return true;
    }

    return (bool) mysqli_query(
        $conn,
        "INSERT INTO notification_reads (notification_id, user_id, read_at)
         VALUES ('$notification_id', '$user_id', NOW())"
    );
}


/**
 * Mark all visible unread notifications as read for a user.
 */
function notification_mark_all_read(mysqli $conn, array $context): int
{
    $user_id = (int) $context['user_id'];
    $where   = notification_visibility_where($conn, $context);

    $query = mysqli_query(
        $conn,
        "SELECT n.id
         FROM notifications n
         LEFT JOIN notification_reads nr
           ON nr.notification_id = n.id AND nr.user_id = '$user_id'
         WHERE $where AND nr.read_id IS NULL"
    );

    $count = 0;

    while ($row = mysqli_fetch_assoc($query)) {
        if (notification_mark_read($conn, (int) $row['id'], $user_id)) {
            $count++;
        }
    }

    return $count;
}


/**
 * Count unread notifications for the current user.
 */
function notification_unread_count(mysqli $conn, array $context): int
{
    $user_id = (int) $context['user_id'];
    $where   = notification_visibility_where($conn, $context);

    $row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM notifications n
         LEFT JOIN notification_reads nr
           ON nr.notification_id = n.id AND nr.user_id = '$user_id'
         WHERE $where AND nr.read_id IS NULL"
    ));

    return (int) ($row['total'] ?? 0);
}

/**
 * Recent notifications visible to a specific user context.
 */
function notification_recent_for_context(mysqli $conn, array $context, int $limit = 5): array
{
    $limit = max(1, min(25, $limit));
    $where = notification_visibility_where($conn, $context);
    $items = [];

    $query = mysqli_query(
        $conn,
        "SELECT n.id, n.title, n.message, n.type AS notification_type, n.created_at
         FROM notifications n
         WHERE $where
         ORDER BY n.created_at DESC
         LIMIT $limit"
    );

    while ($query && $row = mysqli_fetch_assoc($query)) {
        $items[] = $row;
    }

    return $items;
}


/**
 * Check if optional column exists (for gradual schema upgrades).
 */
function notification_table_has_column(mysqli $conn, string $table, string $column): bool
{
    $table  = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);

    $result = mysqli_query(
        $conn,
        "SHOW COLUMNS FROM `$table` LIKE '$column'"
    );

    return $result && mysqli_num_rows($result) > 0;
}
