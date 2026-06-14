<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);
requirePermission('attendance', 'view');

header('Location: ' . BASE_URL . 'attendance/report.php');
exit();
