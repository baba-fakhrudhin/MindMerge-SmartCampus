<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);
requirePermission('teacher_attendance', 'view');

header('Location: ' . BASE_URL . 'attendance/teacher/index.php');
exit();
