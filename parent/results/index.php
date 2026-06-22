<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ParentDashboardService.php';
require_once __DIR__ . '/../../shared/services/ResultsService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['parent']);
requirePermission('results', 'view');

$parentService = new ParentDashboardService($conn, (int) $_SESSION['user']['id']);
$resultsService = new ResultsService($conn);
$rows = [];

foreach ($parentService->getChildren() as $child) {
    foreach ($resultsService->getStudentResults((int) $child['id']) as $result) {
        $result['child_name'] = $child['full_name'];
        $rows[] = $result;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Child Results | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="app-layout">
<?php include('../../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../../partials/topbar.php'); ?>
<div class="page-content">
<div class="page-header"><div><h1>Child Results</h1><p>Published marksheets for your children.</p></div></div>
<div class="dashboard-section">
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Child</th><th>Exam ID</th><th>Exam</th><th>Date</th><th>Subject</th><th>Total</th><th>Grade</th><th>GPA</th></tr></thead>
<tbody>
<?php if (empty($rows)) { ?>
<tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-chart-column"></i><h3>No published results</h3><p>Published child exam results will appear here.</p></div></td></tr>
<?php } else { foreach ($rows as $row) { ?>
<tr>
<td><?php echo htmlspecialchars($row['child_name']); ?></td>
<td><strong><?php echo htmlspecialchars($row['exam_code'] ?? 'Legacy'); ?></strong></td>
<td><?php echo htmlspecialchars($row['exam_name'] ?? 'Result Sheet'); ?></td>
<td><?php echo !empty($row['exam_date']) ? date('M j, Y', strtotime($row['exam_date'])) : htmlspecialchars($row['academic_year']); ?></td>
<td><?php echo htmlspecialchars($row['subject_name']); ?></td>
<td><?php echo htmlspecialchars($row['total_marks']); ?></td>
<td><?php echo htmlspecialchars($row['grade'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($row['grade_point'] ?? '-'); ?></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
