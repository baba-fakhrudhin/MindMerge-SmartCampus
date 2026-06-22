<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ExamService.php';

requirePermission('exams', 'delete');

$exam_id = (int) ($_GET['exam_id'] ?? 0);
$service = new ExamService($conn);

if ($exam_id > 0 && !$service->deleteExam($exam_id)) {
    header('Location: index.php?error=has_result');
    exit();
}

header('Location: index.php?success=deleted');
exit();
