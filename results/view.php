<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ResultsService.php';

$result_id = (int) ($_GET['result_id'] ?? 0);
$service = new ResultsService($conn);
$result = $service->getResultById($result_id);

if (!$result) {
    header('Location: index.php');
    exit();
}

requirePermission('results', 'view');

$entries = $service->getEntries($result_id);
$role = strtolower($_SESSION['user']['role'] ?? '');

if ($role !== 'admin' && $result['status'] !== 'published') {
    if (!canEdit('results')) {
        permission_deny_and_exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Result | MindMerge</title>
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
<div>
<h1>Result Sheet</h1>
<p><?php echo htmlspecialchars(($result['exam_code'] ?? 'Legacy Result') . ' - ' . ($result['exam_name'] ?? $result['class_name'])); ?></p>
</div>
<div class="quick-actions">
<?php if (canEdit('results') || canCreate('results')) { ?>
<a href="entries.php?result_id=<?php echo $result_id; ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Enter Marks</a>
<?php } ?>
<?php if (canView('results')) { ?>
<a href="export.php?result_id=<?php echo $result_id; ?>&format=csv" class="btn btn-secondary"><i class="fa-solid fa-file-csv"></i> CSV</a>
<a href="export.php?result_id=<?php echo $result_id; ?>&format=xls" class="btn btn-secondary"><i class="fa-solid fa-file-excel"></i> Excel</a>
<a href="export.php?result_id=<?php echo $result_id; ?>&format=pdf" class="btn btn-secondary"><i class="fa-solid fa-file-pdf"></i> PDF</a>
<?php } ?>
<a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
</div>

<div class="portal-stats-grid">
<div class="dashboard-card"><h3>Exam ID</h3><h1 style="font-size:1.4rem;"><?php echo htmlspecialchars($result['exam_code'] ?? 'Legacy'); ?></h1></div>
<div class="dashboard-card"><h3>Class</h3><h1 style="font-size:1.4rem;"><?php echo htmlspecialchars($result['class_name'] . ' / ' . $result['section_name']); ?></h1></div>
<div class="dashboard-card"><h3>Exam Date</h3><h1 style="font-size:1.4rem;"><?php echo !empty($result['exam_date']) ? date('M j, Y', strtotime($result['exam_date'])) : htmlspecialchars($result['academic_year']); ?></h1></div>
<div class="dashboard-card"><h3>Status</h3><h1 style="font-size:1.4rem;"><span class="status <?php echo $result['status'] === 'published' ? 'success' : 'warning'; ?>"><?php echo ucfirst($result['status']); ?></span></h1></div>
</div>

<?php if ($role === 'admin') { ?>
<div class="quick-actions" style="margin-bottom:20px;">
<?php if ($result['status'] !== 'published' && canEdit('results')) { ?>
<a href="publish.php?result_id=<?php echo $result_id; ?>&action=publish" class="btn btn-primary"><i class="fa-solid fa-bullhorn"></i> Publish</a>
<?php } elseif ($result['status'] === 'published' && canEdit('results')) { ?>
<a href="publish.php?result_id=<?php echo $result_id; ?>&action=unpublish" class="btn btn-secondary"><i class="fa-solid fa-eye-slash"></i> Unpublish</a>
<?php } ?>
<?php if (canDelete('results')) { ?>
<a href="delete.php?result_id=<?php echo $result_id; ?>" class="btn btn-danger" onclick="return confirm('Delete this result set?');"><i class="fa-solid fa-trash"></i> Delete</a>
<?php } ?>
</div>
<?php } ?>

<div class="dashboard-section">
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Student</th><th>Subject</th><th>Internal</th><th>External</th><th>Lab</th><th>Attendance</th><th>Total</th><th>Grade</th><th>GPA</th></tr></thead>
<tbody>
<?php if (empty($entries)) { ?>
<tr><td colspan="9"><div class="empty-state"><i class="fa-solid fa-square-poll-vertical"></i><h3>No marks entered</h3><p>Enter marks to populate this result sheet.</p></div></td></tr>
<?php } else { foreach ($entries as $e) { ?>
<tr>
<td><?php echo htmlspecialchars($e['student_name'] . ' (' . $e['student_code'] . ')'); ?></td>
<td><?php echo htmlspecialchars($e['subject_name']); ?></td>
<td><?php echo htmlspecialchars($e['internal_marks']); ?></td>
<td><?php echo htmlspecialchars($e['external_marks']); ?></td>
<td><?php echo htmlspecialchars($e['lab_marks']); ?></td>
<td><?php echo htmlspecialchars($e['attendance_marks']); ?></td>
<td><?php echo htmlspecialchars($e['total_marks']); ?></td>
<td><?php echo htmlspecialchars($e['grade'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($e['grade_point'] ?? '-'); ?></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
<script src="../assets/js/common.js"></script>
</body>
</html>
