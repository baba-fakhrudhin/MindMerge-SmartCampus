<?php

/**
 * MindMerge SmartCampus — Permission Management (Phase 1)
 *
 * Resolution order:
 *   1. Admin role bypass (all granted)
 *   2. User-specific override (user_permissions)
 *   3. Role default (role_permissions)
 *   4. Deny
 */

if (!defined('PERMISSION_ACTIONS')) {
    define('PERMISSION_ACTIONS', ['view', 'create', 'edit', 'delete']);
}

if (!defined('PERMISSION_ROLES')) {
    define('PERMISSION_ROLES', ['admin', 'teacher', 'student', 'parent','driver']);
}


/**
 * Sidebar / module registry (module_key => config).
 */
function permission_module_registry(): array
{
    return [
        'profile' => [
            'label'   => 'Profile',
            'icon'    => 'fa-user',
            'url'     => 'profile/index.php',
            'action'  => 'view',
            'match'   => '/profile/',
            'always'  => true,
        ],
        'dashboard' => [
            'label'  => 'Dashboard',
            'icon'   => 'fa-house',
            'url'    => 'admin/dashboard/index.php',
            'action' => 'view',
            'match'  => '/admin/dashboard/|/dashboard/',
        ],
        'classes' => [
            'label'  => 'Classes',
            'icon'   => 'fa-school',
            'url'    => 'classes/index.php',
            'action' => 'view',
            'match'  => '/classes/',
        ],
        'sections' => [
            'label'  => 'Sections',
            'icon'   => 'fa-layer-group',
            'url'    => 'sections/index.php',
            'action' => 'view',
            'match'  => '/sections/',
        ],
        'students' => [
            'label'  => 'Students',
            'icon'   => 'fa-user-graduate',
            'url'    => 'students/index.php',
            'action' => 'view',
            'match'  => '/students/',
        ],
        'teachers' => [
            'label'  => 'Teachers',
            'icon'   => 'fa-chalkboard-user',
            'url'    => 'teachers/index.php',
            'action' => 'view',
            'match'  => '/teachers/|/subjects/|/teacher_assignments/',
        ],
        'schedules' => [
            'label'  => 'Schedules',
            'icon'   => 'fa-clock',
            'url'    => 'period_templates/index.php',
            'action' => 'view',
            'match'  => '/period_templates/|/periods/',
        ],
        'timetables' => [
            'label'  => 'Timetables',
            'icon'   => 'fa-calendar-days',
            'url'    => 'timetables/index.php',
            'action' => 'view',
            'match'  => '/timetables/',
        ],
        'attendance' => [
            'label'  => 'Attendance',
            'icon'   => 'fa-calendar-check',
            'url'    => 'attendance/index.php',
            'action' => 'view',
            'match'  => '/attendance/',
        ],
        'teacher_student_attendance' => [
            'label'      => 'Student Attendance',
            'icon'       => 'fa-calendar-check',
            'url'        => 'teacher/attendance/index.php',
            'action'     => 'view',
            'permission' => 'attendance',
            'match'      => '/teacher/attendance/|/attendance/mark|/attendance/edit|/attendance/view|/attendance/delete|/attendance/report|/attendance/get_|/attendance/check_',
        ],
        'teacher_my_attendance' => [
            'label'      => 'My Attendance',
            'icon'       => 'fa-user-check',
            'url'        => 'teacher/my-attendance/index.php',
            'action'     => 'view',
            'permission' => 'my_teacher_attendance',
            'match'      => '/teacher/my-attendance/',
        ],
        'teacher_students' => [
            'label'      => 'Students',
            'icon'       => 'fa-user-graduate',
            'url'        => 'teacher/students/index.php',
            'action'     => 'view',
            'permission' => 'students',
            'match'      => '/teacher/students/',
        ],
        'teacher_timetable' => [
            'label'      => 'My Timetable',
            'icon'       => 'fa-calendar-days',
            'url'        => 'teacher/timetable/index.php',
            'action'     => 'view',
            'permission' => 'timetables',
            'match'      => '/teacher/timetable/',
        ],
        'teacher_reports' => [
            'label'      => 'Reports',
            'icon'       => 'fa-chart-column',
            'url'        => 'teacher/reports/index.php',
            'action'     => 'view',
            'permission' => 'attendance',
            'match'      => '/teacher/reports/',
        ],
        'teacher_results' => [
            'label'      => 'Results',
            'icon'       => 'fa-square-poll-vertical',
            'url'        => 'teacher/results/index.php',
            'action'     => 'view',
            'permission' => 'results',
            'match'      => '/teacher/results/',
        ],
        'student_attendance' => [
            'label'      => 'Attendance',
            'icon'       => 'fa-calendar-check',
            'url'        => 'student/attendance/index.php',
            'action'     => 'view',
            'permission' => 'attendance',
            'match'      => '/student/attendance/',
        ],
        'student_timetable' => [
            'label'      => 'Timetable',
            'icon'       => 'fa-calendar-days',
            'url'        => 'student/timetable/index.php',
            'action'     => 'view',
            'permission' => 'timetables',
            'match'      => '/student/timetable/',
        ],
        'student_results' => [
            'label'      => 'Results',
            'icon'       => 'fa-chart-column',
            'url'        => 'student/results/index.php',
            'action'     => 'view',
            'permission' => 'results',
            'match'      => '/student/results/',
        ],
        'digital_id' => [
            'label'      => 'Digital ID',
            'icon'       => 'fa-id-card',
            'url'        => 'student/digital-id/index.php',
            'action'     => 'view',
            'permission' => 'profile',
            'match'      => '/student/digital-id/',
            'always'     => true,
        ],
        'parent_children' => [
            'label'      => 'Children',
            'icon'       => 'fa-children',
            'url'        => 'parent/children/index.php',
            'action'     => 'view',
            'permission' => 'students',
            'match'      => '/parent/children/',
        ],
        'parent_results' => [
            'label'      => 'Results',
            'icon'       => 'fa-chart-column',
            'url'        => 'parent/results/index.php',
            'action'     => 'view',
            'permission' => 'results',
            'match'      => '/parent/results/',
        ],
        'parent_attendance' => [
            'label'      => 'Attendance',
            'icon'       => 'fa-calendar-check',
            'url'        => 'parent/attendance/index.php',
            'action'     => 'view',
            'permission' => 'attendance',
            'match'      => '/parent/attendance/',
        ],
        'notifications' => [
            'label'  => 'Notifications',
            'icon'   => 'fa-bell',
            'url'    => 'notifications/index.php',
            'action' => 'view',
            'match'  => '/notifications/',
        ],
        'exams' => [
            'label'  => 'Exams',
            'icon'   => 'fa-file-lines',
            'url'    => 'exams/index.php',
            'action' => 'view',
            'match'  => '/exams/',
        ],
        'results' => [
            'label'  => 'Results',
            'icon'   => 'fa-square-poll-vertical',
            'url'    => 'results/index.php',
            'action' => 'view',
            'match'  => '/results/',
        ],
        'transport' => [
            'label'  => 'Transport',
            'icon'   => 'fa-bus',
            'url'    => 'transport/index.php',
            'action' => 'view',
            'match'  => '/transport/',
        ],
        'permissions' => [
            'label'  => 'Permissions',
            'icon'   => 'fa-shield-halved',
            'url'    => 'settings/permissions/index.php',
            'action' => 'view',
            'match'  => '/settings/permissions/',
        ],
        'driver_tracking' => [
            'label'      => 'My Bus',
            'icon'       => 'fa-location-dot',
            'url'        => 'transport/tracking/index.php',
            'action'     => 'view',
            'permission' => 'transport',
            'match'      => '/transport/tracking/',
        ],

        'driver_notifications' => [
            'label'      => 'Notifications',
            'icon'       => 'fa-bell',
            'url'        => 'notifications/index.php',
            'action'     => 'view',
            'permission' => 'notifications',
            'match'      => '/notifications/',
        ],
    ];
}


/**
 * Check whether permission tables exist (graceful before migration).
 */
function permission_tables_ready(mysqli $conn): bool
{
    static $ready = null;

    if ($ready !== null) {
        return $ready;
    }

    $result = mysqli_query(
        $conn,
        "SHOW TABLES LIKE 'permissions'"
    );

    $ready = $result && mysqli_num_rows($result) > 0;

    return $ready;
}


/**
 * Load all permissions for the current user into the session.
 */
function permission_load_user(mysqli $conn, array $user): void
{
    $user_id = (int) ($user['id'] ?? 0);
    $role    = strtolower(trim($user['role'] ?? ''));

    $_SESSION['permission_role'] = $role;
    $_SESSION['permission_map']  = [];

    if (!permission_tables_ready($conn)) {
        return;
    }

    if ($role === 'admin') {
        $query = mysqli_query(
            $conn,
            "SELECT module_key, action_key
             FROM permissions
             ORDER BY sort_order ASC"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $key = $row['module_key'] . '.' . $row['action_key'];
            $_SESSION['permission_map'][$key] = true;
        }

        return;
    }

    $query = mysqli_query(
        $conn,
        "SELECT
            p.module_key,
            p.action_key,
            COALESCE(up.granted, rp.granted, 0) AS granted
         FROM permissions p
         LEFT JOIN role_permissions rp
           ON rp.permission_id = p.permission_id
          AND rp.role = '" . mysqli_real_escape_string($conn, $role) . "'
         LEFT JOIN user_permissions up
           ON up.permission_id = p.permission_id
          AND up.user_id = '$user_id'
         ORDER BY p.sort_order ASC"
    );

    while ($row = mysqli_fetch_assoc($query)) {
        $key = $row['module_key'] . '.' . $row['action_key'];

        if ((int) $row['granted'] === 1) {
            $_SESSION['permission_map'][$key] = true;
        }
    }
}


/**
 * Refresh permissions for the logged-in user (after admin changes).
 */
function permission_refresh_current_user(mysqli $conn): void
{
    if (!isset($_SESSION['user'])) {
        return;
    }

    permission_load_user($conn, $_SESSION['user']);
}


/**
 * Core permission check.
 */
function hasPermission(string $module, string $action): bool
{
    $role = strtolower($_SESSION['permission_role'] ?? ($_SESSION['user']['role'] ?? ''));

    if ($role === 'admin') {
        return true;
    }

    $key = strtolower(trim($module)) . '.' . strtolower(trim($action));

    return !empty($_SESSION['permission_map'][$key]);
}


function canView(string $module): bool
{
    return hasPermission($module, 'view');
}

function canCreate(string $module): bool
{
    return hasPermission($module, 'create');
}

function canEdit(string $module): bool
{
    return hasPermission($module, 'edit');
}

function canDelete(string $module): bool
{
    return hasPermission($module, 'delete');
}


function isAdminUser(): bool
{
    $role = strtolower($_SESSION['user']['role'] ?? '');

    return $role === 'admin';
}


/**
 * Role-specific sidebar menu groups (portal-aware).
 */
function permission_portal_menu_groups(): array
{
    $role = strtolower($_SESSION['user']['role'] ?? 'admin');

    $menus = [
        'admin' => [
            ['label' => 'General', 'items' => ['profile', 'dashboard']],
            ['label' => 'Academics', 'items' => ['classes', 'sections', 'students', 'teachers']],
            ['label' => 'Scheduling', 'items' => ['schedules', 'timetables']],
            ['label' => 'Operations', 'items' => ['attendance', 'notifications', 'results', 'exams', 'transport']],
            ['label' => 'Administration', 'items' => ['permissions']],
        ],
        'teacher' => [
            ['label' => 'General', 'items' => ['profile', 'dashboard']],
            ['label' => 'Academics', 'items' => ['teacher_students']],
            ['label' => 'Scheduling', 'items' => ['teacher_timetable']],
            ['label' => 'Operations', 'items' => ['teacher_student_attendance', 'teacher_my_attendance', 'notifications', 'teacher_results', 'exams', 'transport']],
        ],
        'student' => [
            ['label' => 'General', 'items' => ['profile', 'dashboard']],
            ['label' => 'Academics', 'items' => ['student_results']],
            ['label' => 'Scheduling', 'items' => ['student_timetable']],
            ['label' => 'Operations', 'items' => ['student_attendance', 'notifications', 'exams', 'transport']],
            ['label' => 'Utilities', 'items' => ['digital_id']],
        ],
        'parent' => [
            ['label' => 'General', 'items' => ['profile', 'dashboard']],
            ['label' => 'Academics', 'items' => ['parent_children', 'parent_results']],
            ['label' => 'Operations', 'items' => ['parent_attendance', 'notifications', 'exams', 'transport']],
        ],'driver' => [

                [
                    'label' => 'General',
                    'items' => [
                        'profile',
                        'dashboard'
                    ]
                ],

                [
                    'label' => 'Transport',
                    'items' => [
                        'driver_tracking'
                    ]
                ],

                [
                    'label' => 'Communication',
                    'items' => [
                        'driver_notifications'
                    ]
                ]

            ],
    ];

    $registry = permission_module_registry();
    $groups   = $menus[$role] ?? $menus['admin'];
    $visible  = [];
    $represented_permissions = [];

    foreach ($groups as $group) {
        $items = [];

        foreach ($group['items'] as $module_key) {
            if (!isset($registry[$module_key])) {
                continue;
            }

            $config = $registry[$module_key];
            $perm   = $config['permission'] ?? $module_key;
            $represented_permissions[$perm] = true;

            if (!empty($config['always']) || canView($perm)) {
                $items[$module_key] = $config;
            }
        }

        if (!empty($items)) {
            $visible[] = [
                'label' => $group['label'],
                'items' => $items,
            ];
        }
    }

    /*
     * Role menus provide the best portal-specific destination first. If an
     * administrator grants an additional root module, expose it rather than
     * leaving authorization active but navigation invisible.
     */
    if ($role !== 'admin') {
        $additional = [];
        $canonical_modules = [
            'classes',
            'sections',
            'students',
            'teachers',
            'schedules',
            'timetables',
            'attendance',
            'notifications',
            'results',
            'exams',
            'transport',
            'permissions',
        ];

        foreach ($canonical_modules as $module_key) {
            if (
                !isset($registry[$module_key])
                || isset($represented_permissions[$module_key])
                || !canView($module_key)
            ) {
                continue;
            }

            $additional[$module_key] = $registry[$module_key];
        }

        if (!empty($additional)) {
            $visible[] = [
                'label' => 'Additional Access',
                'items' => $additional,
            ];
        }
    }

    return $visible;
}


/**
 * Modules visible in sidebar (view permission on module).
 * @deprecated Use permission_portal_menu_groups() for grouped navigation.
 */
function getVisibleModules(): array
{
    $visible = [];

    foreach (permission_portal_menu_groups() as $group) {
        foreach ($group['items'] as $module_key => $config) {
            $visible[$module_key] = $config;
        }
    }

    return $visible;
}


/**
 * Dashboard URL for the current user's role.
 */
function permission_role_dashboard_url(): string
{
    require_once __DIR__ . '/constants.php';

    $role = strtolower($_SESSION['user']['role'] ?? 'admin');

    $paths = [
        'admin'   => 'admin/dashboard/index.php',
        'teacher' => 'teacher/dashboard/index.php',
        'student' => 'student/dashboard/index.php',
        'parent'  => 'parent/dashboard/index.php',
        'driver'  => 'driver/dashboard/index.php'
    ];

    return BASE_URL . ($paths[$role] ?? $paths['admin']);
}


/**
 * Path groups used to keep parent sidebar items active on sub-pages.
 */
function permission_menu_path_groups(): array
{
    return [
        'attendance' => [
            '#/attendance/#',
        ],
        'teacher_student_attendance' => [
            '#/teacher/attendance/#',
            '#/attendance/mark#',
            '#/attendance/edit#',
            '#/attendance/view#',
            '#/attendance/delete#',
            '#/attendance/report#',
            '#/attendance/get_#',
            '#/attendance/check_#',
        ],
        'teacher_my_attendance' => [
            '#/teacher/my-attendance/#',
        ],
        'student_attendance' => [
            '#/student/attendance/#',
        ],
        'parent_attendance' => [
            '#/parent/attendance/#',
        ],
        'notifications' => [
            '#/notifications/#',
        ],
        'results' => [
            '#/results/#',
            '#/student/results/#',
            '#/parent/results/#',
        ],
        'timetables' => [
            '#/timetables/#',
        ],
        'teacher_timetable' => [
            '#/teacher/timetable/#',
        ],
        'student_timetable' => [
            '#/student/timetable/#',
        ],
    ];
}


/**
 * Determine if a sidebar item should appear active.
 */
function permission_menu_is_active(array $config, string $current_page, string $uri, string $module_key = ''): bool
{
    $path = strtolower(parse_url($uri, PHP_URL_PATH) ?? $uri);
    $groups = permission_menu_path_groups();

    if ($module_key !== '' && isset($groups[$module_key])) {
        foreach ($groups[$module_key] as $pattern) {
            if (preg_match($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    if (!empty($config['match']) && preg_match('#' . $config['match'] . '#', $uri)) {
        if (!empty($config['exclude_match']) && preg_match('#' . $config['exclude_match'] . '#', $uri)) {
            return false;
        }

        return true;
    }

    if (!empty($config['url']) && str_contains($uri, $config['url'])) {
        return true;
    }

    return false;
}


/**
 * Redirect unauthorized users.
 */
function permission_deny_and_exit(): void
{
    require_once __DIR__ . '/constants.php';

    $base = BASE_URL;

    if (!headers_sent()) {
        header('Location: ' . $base . 'settings/access-denied.php');
        exit();
    }

    echo '<script>window.location.href="' . htmlspecialchars($base . 'settings/access-denied.php') . '";</script>';
    exit();
}


function requirePermission(string $module, string $action): void
{
    if (!hasPermission($module, $action)) {
        permission_deny_and_exit();
    }
}


/**
 * Map request URI to module + action for automatic page guarding.
 */
function permission_resolve_route(string $uri, string $script): ?array
{
    $path = strtolower(parse_url($uri, PHP_URL_PATH) ?? '');
    $file = strtolower(basename($script));

    $rules = [
        ['pattern' => '#/admin/dashboard/#',              'module' => 'dashboard', 'action' => 'view'],
        ['pattern' => '#/teacher/dashboard/#',            'module' => 'dashboard', 'action' => 'view'],
        ['pattern' => '#/student/dashboard/#',           'module' => 'dashboard', 'action' => 'view'],
        ['pattern' => '#/parent/dashboard/#',             'module' => 'dashboard', 'action' => 'view'],

        ['pattern' => '#/teacher/students/#',            'module' => 'students', 'action' => 'view'],
        ['pattern' => '#/teacher/attendance/#',          'module' => 'attendance', 'action' => 'view'],
        ['pattern' => '#/teacher/my-attendance/#',      'module' => 'my_teacher_attendance', 'action' => 'view'],
        ['pattern' => '#/teacher/timetable/#',           'module' => 'timetables', 'action' => 'view'],
        ['pattern' => '#/teacher/reports/#',             'module' => 'attendance', 'action' => 'view'],
        ['pattern' => '#/teacher/profile/print#',       'module' => 'profile', 'action' => 'view', 'redirect' => 'profile/print.php'],
        ['pattern' => '#/teacher/profile/#',             'module' => 'profile', 'action' => 'view', 'redirect' => 'profile/index.php'],

        ['pattern' => '#/student/attendance/#',          'module' => 'attendance', 'action' => 'view'],
        ['pattern' => '#/student/timetable/#',          'module' => 'timetables', 'action' => 'view'],
        ['pattern' => '#/student/results/#',            'module' => 'results', 'action' => 'view'],
        ['pattern' => '#/student/digital-id/#',         'module' => 'profile', 'action' => 'view'],
        ['pattern' => '#/student/profile/print#',       'module' => 'profile', 'action' => 'view', 'redirect' => 'profile/print.php'],
        ['pattern' => '#/student/profile/#',             'module' => 'profile', 'action' => 'view', 'redirect' => 'profile/index.php'],

        ['pattern' => '#/parent/children/#',            'module' => 'students', 'action' => 'view'],
        ['pattern' => '#/parent/attendance/#',          'module' => 'attendance', 'action' => 'view'],
        ['pattern' => '#/parent/results/#',              'module' => 'results', 'action' => 'view'],
        ['pattern' => '#/parent/profile/print#',         'module' => 'profile', 'action' => 'view', 'redirect' => 'profile/print.php'],
        ['pattern' => '#/parent/profile/#',              'module' => 'profile', 'action' => 'view', 'redirect' => 'profile/index.php'],

        ['pattern' => '#/settings/permissions/save#', 'module' => 'permissions', 'action' => 'edit'],
        ['pattern' => '#/settings/permissions/#',     'module' => 'permissions', 'action' => 'view'],

        ['pattern' => '#/dashboard/#',                 'module' => 'dashboard', 'action' => 'view'],

        ['pattern' => '#/profile/print#',              'module' => 'profile',   'action' => 'view'],
        ['pattern' => '#/profile/#',                  'module' => 'profile',   'action' => 'view'],

        ['pattern' => '#/classes/add#',               'module' => 'classes', 'action' => 'create'],
        ['pattern' => '#/classes/edit#',              'module' => 'classes', 'action' => 'edit'],
        ['pattern' => '#/classes/delete#',            'module' => 'classes', 'action' => 'delete'],
        ['pattern' => '#/classes/#',                   'module' => 'classes', 'action' => 'view'],

        ['pattern' => '#/sections/add#',              'module' => 'sections', 'action' => 'create'],
        ['pattern' => '#/sections/edit#',             'module' => 'sections', 'action' => 'edit'],
        ['pattern' => '#/sections/delete#',           'module' => 'sections', 'action' => 'delete'],
        ['pattern' => '#/sections/#',                 'module' => 'sections', 'action' => 'view'],

        ['pattern' => '#/students/add#',              'module' => 'students', 'action' => 'create'],
        ['pattern' => '#/students/assign-parent#',    'module' => 'students', 'action' => 'edit'],
        ['pattern' => '#/students/edit#',            'module' => 'students', 'action' => 'edit'],
        ['pattern' => '#/students/delete#',           'module' => 'students', 'action' => 'delete'],
        ['pattern' => '#/students/#',                 'module' => 'students', 'action' => 'view'],

        ['pattern' => '#/teachers/add#',              'module' => 'teachers', 'action' => 'create'],
        ['pattern' => '#/teachers/edit#',             'module' => 'teachers', 'action' => 'edit'],
        ['pattern' => '#/teachers/delete#',           'module' => 'teachers', 'action' => 'delete'],
        ['pattern' => '#/teachers/#',                 'module' => 'teachers', 'action' => 'view'],

        ['pattern' => '#/subjects/add#',              'module' => 'teachers', 'action' => 'create'],
        ['pattern' => '#/subjects/edit#',            'module' => 'teachers', 'action' => 'edit'],
        ['pattern' => '#/subjects/delete#',           'module' => 'teachers', 'action' => 'delete'],
        ['pattern' => '#/subjects/#',                 'module' => 'teachers', 'action' => 'view'],

        ['pattern' => '#/teacher_assignments/add#',   'module' => 'teachers', 'action' => 'create'],
        ['pattern' => '#/teacher_assignments/edit#',  'module' => 'teachers', 'action' => 'edit'],
        ['pattern' => '#/teacher_assignments/delete#','module' => 'teachers', 'action' => 'delete'],
        ['pattern' => '#/teacher_assignments/#',      'module' => 'teachers', 'action' => 'view'],

        ['pattern' => '#/period_templates/add#',      'module' => 'schedules', 'action' => 'create'],
        ['pattern' => '#/period_templates/edit#',     'module' => 'schedules', 'action' => 'edit'],
        ['pattern' => '#/period_templates/delete#',   'module' => 'schedules', 'action' => 'delete'],
        ['pattern' => '#/period_templates/#',         'module' => 'schedules', 'action' => 'view'],
        ['pattern' => '#/periods/add#',              'module' => 'schedules', 'action' => 'create'],
        ['pattern' => '#/periods/edit#',             'module' => 'schedules', 'action' => 'edit'],
        ['pattern' => '#/periods/delete#',           'module' => 'schedules', 'action' => 'delete'],
        ['pattern' => '#/periods/#',                  'module' => 'schedules', 'action' => 'view'],

        ['pattern' => '#/timetables/add#',           'module' => 'timetables', 'action' => 'create'],
        ['pattern' => '#/timetables/edit#',           'module' => 'timetables', 'action' => 'edit'],
        ['pattern' => '#/timetables/delete#',         'module' => 'timetables', 'action' => 'delete'],
        ['pattern' => '#/timetables/entries#',        'module' => 'timetables', 'action' => 'edit'],
        ['pattern' => '#/timetables/#',               'module' => 'timetables', 'action' => 'view'],

        ['pattern' => '#/attendance/teacher/mark#',  'module' => 'teacher_attendance', 'action' => 'create', 'roles' => ['admin']],
        ['pattern' => '#/attendance/teacher/report#', 'module' => 'teacher_attendance', 'action' => 'view', 'roles' => ['admin']],
        ['pattern' => '#/attendance/teacher/#',       'module' => 'teacher_attendance', 'action' => 'view', 'roles' => ['admin']],

        ['pattern' => '#/attendance/mark#',            'module' => 'attendance', 'action' => 'create'],
        ['pattern' => '#/attendance/edit#',            'module' => 'attendance', 'action' => 'edit'],
        ['pattern' => '#/attendance/delete#',          'module' => 'attendance', 'action' => 'delete'],
        ['pattern' => '#/attendance/report#',         'module' => 'attendance', 'action' => 'view'],
        ['pattern' => '#/attendance/get_#',            'module' => 'attendance', 'action' => 'view'],
        ['pattern' => '#/attendance/check_#',          'module' => 'attendance', 'action' => 'view'],
        ['pattern' => '#/attendance/#',               'module' => 'attendance', 'action' => 'view'],

        ['pattern' => '#/notifications/create#',      'module' => 'notifications', 'action' => 'create'],
        ['pattern' => '#/notifications/reports#',      'module' => 'notifications', 'action' => 'view'],
        ['pattern' => '#/notifications/send-alert#',  'module' => 'notifications', 'action' => 'create'],
        ['pattern' => '#/notifications/get_sections#','module' => 'notifications', 'action' => 'view'],
        ['pattern' => '#/notifications/api/#',         'module' => 'notifications', 'action' => 'view'],
        ['pattern' => '#/notifications/#',             'module' => 'notifications', 'action' => 'view'],

        ['pattern' => '#/results/add#',                'module' => 'results', 'action' => 'create'],
        ['pattern' => '#/results/entries#',             'module' => 'results', 'action' => 'edit'],
        ['pattern' => '#/results/publish#',           'module' => 'results', 'action' => 'edit'],
        ['pattern' => '#/results/export#',            'module' => 'results', 'action' => 'view'],
        ['pattern' => '#/results/view#',              'module' => 'results', 'action' => 'view'],
        ['pattern' => '#/results/edit#',               'module' => 'results', 'action' => 'edit'],
        ['pattern' => '#/results/delete#',             'module' => 'results', 'action' => 'delete'],
        ['pattern' => '#/teacher/results/entries#',    'module' => 'results', 'action' => 'edit'],
        ['pattern' => '#/teacher/results/#',           'module' => 'results', 'action' => 'view'],
        ['pattern' => '#/results/#',                   'module' => 'results', 'action' => 'view'],

        ['pattern' => '#/exams/add#',                 'module' => 'exams', 'action' => 'create'],
        ['pattern' => '#/exams/edit#',                'module' => 'exams', 'action' => 'edit'],
        ['pattern' => '#/exams/delete#',              'module' => 'exams', 'action' => 'delete'],
        ['pattern' => '#/exams/view#',                'module' => 'exams', 'action' => 'view'],
        ['pattern' => '#/exams/#',                    'module' => 'exams', 'action' => 'view'],

        ['pattern' => '#/transport/.+/add#',           'module' => 'transport', 'action' => 'create'],
        ['pattern' => '#/transport/.+/edit#',          'module' => 'transport', 'action' => 'edit'],
        ['pattern' => '#/transport/.+/delete#',        'module' => 'transport', 'action' => 'delete'],
        ['pattern' => '#/transport/add#',              'module' => 'transport', 'action' => 'create'],
        ['pattern' => '#/transport/edit#',             'module' => 'transport', 'action' => 'edit'],
        ['pattern' => '#/transport/delete#',           'module' => 'transport', 'action' => 'delete'],
        ['pattern' => '#/transport/#',                 'module' => 'transport', 'action' => 'view'],
    ];

    foreach ($rules as $rule) {
        if (preg_match($rule['pattern'], $path)) {
            $resolved = [
                'module' => $rule['module'],
                'action' => $rule['action'],
            ];

            if (!empty($rule['roles'])) {
                $resolved['roles'] = $rule['roles'];
            }

            if (!empty($rule['redirect'])) {
                $resolved['redirect'] = $rule['redirect'];
            }

            return $resolved;
        }
    }

    if ($file === 'index.php' && str_contains($path, '/mindmerge smartcampus')) {
        return ['module' => 'dashboard', 'action' => 'view'];
    }

    return null;
}


/**
 * Apply automatic route guard (called from auth.php).
 */
function permission_guard_request(mysqli $conn): void
{
    if (!permission_tables_ready($conn)) {
        if (strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
            permission_deny_and_exit();
        }

        return;
    }

    if (empty($_SESSION['permission_map']) && isset($_SESSION['user'])) {
        permission_load_user($conn, $_SESSION['user']);
    }

    $uri    = $_SERVER['REQUEST_URI'] ?? '';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $role   = strtolower($_SESSION['user']['role'] ?? '');
    $path   = strtolower(parse_url($uri, PHP_URL_PATH) ?? '');

    if (str_contains(strtolower($uri), '/auth/')) {
        return;
    }

    if (str_contains(strtolower($uri), '/settings/access-denied')) {
        return;
    }

            $portal_restrictions = [
            '#/admin/#'   => ['admin'],
            '#/teacher/#' => ['teacher'],
            '#/student/#' => ['student'],
            '#/parent/#'  => ['parent'],
            '#/driver/#'  => ['driver'],
        ];

    foreach ($portal_restrictions as $pattern => $allowed_roles) {
        if (preg_match($pattern, $path) && !in_array($role, $allowed_roles, true)) {
            permission_deny_and_exit();
        }
    }

    $route = permission_resolve_route($uri, $script);

    if ($route === null) {
        return;
    }

    if (!empty($route['roles']) && !in_array($role, $route['roles'], true)) {
        permission_deny_and_exit();
    }

    requirePermission($route['module'], $route['action']);

    if (!empty($route['redirect'])) {
        require_once __DIR__ . '/constants.php';
        header('Location: ' . BASE_URL . $route['redirect']);
        exit();
    }
}


/**
 * Fetch all permissions grouped by module for admin UI.
 */
function permission_fetch_all_grouped(mysqli $conn): array
{
    $grouped = [];

    $query = mysqli_query(
        $conn,
        "SELECT permission_id, module_key, action_key, label, sort_order
         FROM permissions
         ORDER BY sort_order ASC, permission_id ASC"
    );

    while ($row = mysqli_fetch_assoc($query)) {
        $module = $row['module_key'];

        if (!isset($grouped[$module])) {
            $grouped[$module] = [
                'module_key' => $module,
                'label'      => permission_module_label($module),
                'permissions'=> [],
            ];
        }

        $grouped[$module]['permissions'][] = $row;
    }

    return $grouped;
}


function permission_module_label(string $module_key): string
{
    $admin_labels = [
        'teacher_attendance' => 'Teacher Attendance (Admin)',
        'my_teacher_attendance' => 'My Attendance (Teacher View)',
        'teacher_student_attendance' => 'Student Attendance (Teacher Portal)',
        'teacher_my_attendance' => 'My Attendance (Teacher Portal)',
        'teacher_students' => 'Students (Teacher Portal)',
        'teacher_timetable' => 'Timetable (Teacher Portal)',
        'teacher_reports' => 'Reports (Teacher Portal)',
        'student_attendance' => 'Attendance (Student Portal)',
        'student_timetable' => 'Timetable (Student Portal)',
        'student_results' => 'Results (Student Portal)',
        'digital_id' => 'Digital ID',
        'parent_children' => 'Children (Parent Portal)',
        'parent_results' => 'Results (Parent Portal)',
        'parent_attendance' => 'Attendance (Parent Portal)',
    ];

    if (isset($admin_labels[$module_key])) {
        return $admin_labels[$module_key];
    }

    $registry = permission_module_registry();

    if (isset($registry[$module_key]['label'])) {
        return $registry[$module_key]['label'];
    }

    return ucwords(str_replace('_', ' ', $module_key));
}


/**
 * Role permission matrix for admin UI.
 */
function permission_fetch_role_matrix(mysqli $conn): array
{
    $matrix = [];

    $query = mysqli_query(
        $conn,
        "SELECT rp.role, p.permission_id, rp.granted
         FROM role_permissions rp
         INNER JOIN permissions p ON p.permission_id = rp.permission_id"
    );

    while ($row = mysqli_fetch_assoc($query)) {
        $matrix[$row['role']][(int) $row['permission_id']] = (int) $row['granted'] === 1;
    }

    return $matrix;
}


/**
 * User override matrix for admin UI.
 */
function permission_fetch_user_overrides(mysqli $conn, int $user_id): array
{
    $overrides = [];

    if ($user_id <= 0) {
        return $overrides;
    }

    $query = mysqli_query(
        $conn,
        "SELECT permission_id, granted
         FROM user_permissions
         WHERE user_id = '$user_id'"
    );

    while ($row = mysqli_fetch_assoc($query)) {
        $overrides[(int) $row['permission_id']] = (int) $row['granted'] === 1;
    }

    return $overrides;
}


/**
 * Save role permissions from admin form.
 */
function permission_save_role_matrix(mysqli $conn, string $role, array $granted_ids): bool
{
    $role = strtolower(trim($role));

    if (!in_array($role, PERMISSION_ROLES, true) || $role === 'admin') {
        return false;
    }

    $all = mysqli_query($conn, "SELECT permission_id FROM permissions");
    $granted_map = array_flip(array_map('intval', $granted_ids));

    mysqli_begin_transaction($conn);

    try {
        while ($row = mysqli_fetch_assoc($all)) {
            $pid     = (int) $row['permission_id'];
            $granted = isset($granted_map[$pid]) ? 1 : 0;

            mysqli_query(
                $conn,
                "INSERT INTO role_permissions (role, permission_id, granted)
                 VALUES ('" . mysqli_real_escape_string($conn, $role) . "', '$pid', '$granted')
                 ON DUPLICATE KEY UPDATE granted = VALUES(granted)"
            );
        }

        mysqli_commit($conn);

        return true;
    } catch (Throwable $e) {
        mysqli_rollback($conn);

        return false;
    }
}


/**
 * Save user permission overrides (empty = remove all overrides).
 */
function permission_save_user_overrides(mysqli $conn, int $user_id, array $overrides): bool
{
    if ($user_id <= 0) {
        return false;
    }

    mysqli_begin_transaction($conn);

    try {
        mysqli_query(
            $conn,
            "DELETE FROM user_permissions WHERE user_id = '$user_id'"
        );

        foreach ($overrides as $permission_id => $granted) {
            $pid = (int) $permission_id;
            $val = (int) $granted === 1 ? 1 : 0;

            mysqli_query(
                $conn,
                "INSERT INTO user_permissions (user_id, permission_id, granted)
                 VALUES ('$user_id', '$pid', '$val')"
            );
        }

        mysqli_commit($conn);

        return true;
    } catch (Throwable $e) {
        mysqli_rollback($conn);

        return false;
    }
}


/**
 * Effective permission for a user (for admin preview).
 */
function permission_effective_for_user(
    mysqli $conn,
    int $user_id,
    string $role,
    string $module,
    string $action
): bool {
    $role = strtolower(trim($role));

    if ($role === 'admin') {
        return true;
    }

    $module_esc = mysqli_real_escape_string($conn, $module);
    $action_esc = mysqli_real_escape_string($conn, $action);

    $row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT
            p.permission_id,
            COALESCE(up.granted, rp.granted, 0) AS granted
         FROM permissions p
         LEFT JOIN role_permissions rp
           ON rp.permission_id = p.permission_id
          AND rp.role = '" . mysqli_real_escape_string($conn, $role) . "'
         LEFT JOIN user_permissions up
           ON up.permission_id = p.permission_id
          AND up.user_id = '$user_id'
         WHERE p.module_key = '$module_esc'
           AND p.action_key = '$action_esc'
         LIMIT 1"
    ));

    return $row && (int) $row['granted'] === 1;
}
