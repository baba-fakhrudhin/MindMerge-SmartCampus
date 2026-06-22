<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ExamService.php';

requirePermission('exams', 'view');

$service = new ExamService($conn);
$ready = $service->isReady();
$role = strtolower($_SESSION['user']['role'] ?? '');
$scope = [];

if ($role === 'teacher') {
    $teacher = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT id FROM teachers WHERE user_id = '" . (int) $_SESSION['user']['id'] . "' LIMIT 1"
    ));
    $scope['teacher_id'] = (int) ($teacher['id'] ?? 0);
}

if ($role === 'student') {
    $student = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT class_id, section_id FROM students WHERE user_id = '" . (int) $_SESSION['user']['id'] . "' LIMIT 1"
    ));
    $scope['class_id'] = (int) ($student['class_id'] ?? 0);
    $scope['section_id'] = (int) ($student['section_id'] ?? 0);
}

$exams = $ready ? $service->getExams($scope) : [];

if ($role === 'parent' && $ready) {
    require_once __DIR__ . '/../shared/services/ParentDashboardService.php';
    $parentService = new ParentDashboardService($conn, (int) $_SESSION['user']['id']);
    $allowed = [];
    foreach ($parentService->getChildren() as $child) {
        $allowed[(int) $child['class_id'] . '-' . (int) $child['section_id']] = true;
    }
    $exams = array_values(array_filter($exams, fn($exam) => isset($allowed[(int) $exam['class_id'] . '-' . (int) $exam['section_id']])));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exams | MindMerge</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="app-layout">
<?php include('../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../partials/topbar.php'); ?>
<div class="page-content">
<div class="page-header">
<div><h1>Exams</h1><p>Create exams and connect each exam to one controlled result sheet.</p></div>
<?php if ($ready && canCreate('exams')) { ?><a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Exam</a><?php } ?>
</div>

<?php if (!$ready) { ?>
<div class="alert-banner warning"><i class="fa-solid fa-database"></i> Exam tables need the latest migration. Import <strong>database/exam_results_migration.sql</strong>.</div>
<?php } ?>
<?php if (isset($_GET['success'])) { ?>
<div class="success-alert"><i class="fa-solid fa-circle-check"></i> Exam saved successfully.</div>
<?php } ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'has_result') { ?>
<div class="alert-banner warning"><i class="fa-solid fa-circle-info"></i> This exam already has a result sheet, so it cannot be deleted.</div>
<?php } ?>

<div class="dashboard-grid">
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon"><i class="fa-solid fa-file-lines"></i></div><h3><?php echo count($exams); ?></h3></div><p>Total Exams</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon green"><i class="fa-solid fa-circle-check"></i></div><h3><?php echo count(array_filter($exams, fn($e) => $e['status'] === 'active')); ?></h3></div><p>Active Exams</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon orange"><i class="fa-solid fa-square-poll-vertical"></i></div><h3><?php echo count(array_filter($exams, fn($e) => !empty($e['result_id']))); ?></h3></div><p>With Results</p></div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Exam List</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Exam ID</th><th>Name</th><th>Class</th><th>Section</th><th>Date</th><th>Time</th><th>Status</th><th>Result</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($exams)) { ?>
<tr><td colspan="9"><div class="empty-state"><i class="fa-solid fa-file-lines"></i><h3>No exams found</h3><p>Add an exam to start publishing class-section results.</p></div></td></tr>
<?php } else { foreach ($exams as $exam) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($exam['exam_code']); ?></strong></td>
<td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
<td><?php echo htmlspecialchars($exam['class_name']); ?></td>
<td><?php echo htmlspecialchars($exam['section_name']); ?></td>
<td><?php echo $exam['exam_date'] ? date('M j, Y', strtotime($exam['exam_date'])) : '-'; ?></td>
<td><?php echo $exam['exam_time'] ? date('g:i A', strtotime($exam['exam_time'])) : '-'; ?></td>
<td><span class="status <?php echo $exam['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($exam['status']); ?></span></td>
<td><?php if (!empty($exam['result_id'])) { ?><a href="../results/view.php?result_id=<?php echo (int) $exam['result_id']; ?>" class="status primary">Open</a><?php } else { ?><span class="status warning">Pending</span><?php } ?></td>
<td>
<a href="view.php?exam_id=<?php echo (int) $exam['exam_id']; ?>" class="btn btn-sm"><i class="fa-solid fa-eye"></i></a>
<?php if (canEdit('exams')) { ?><a href="edit.php?exam_id=<?php echo (int) $exam['exam_id']; ?>" class="btn btn-sm"><i class="fa-solid fa-pen"></i></a><?php } ?>
<?php if (canDelete('exams')) { ?><a href="delete.php?exam_id=<?php echo (int) $exam['exam_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this exam?');"><i class="fa-solid fa-trash"></i></a><?php } ?>
</td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>
</div></div></div>
<script src="../assets/js/common.js"></script>
</body>
</html>
