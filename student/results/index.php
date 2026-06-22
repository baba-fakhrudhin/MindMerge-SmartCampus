<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../../shared/services/ResultsService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);
requirePermission('results', 'view');

$studentService = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
$resultsService = new ResultsService($conn);
$student_id = $studentService->getStudentDbId();
$results = $resultsService->getStudentResults($student_id);
$gpaInfo = $resultsService->getLatestGpa($student_id);
$trend = $resultsService->getPerformanceTrend($student_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Results | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/portals.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="app-layout">
<?php include('../../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../../partials/topbar.php'); ?>
<div class="page-content">
<div class="page-header">
<div><h1>My Results</h1><p>Published academic results and GPA.</p></div>
</div>
<div class="portal-stats-grid">
<div class="dashboard-card"><h3>Latest GPA</h3><h1><?php echo $gpaInfo['gpa'] ?? '—'; ?></h1></div>
<div class="dashboard-card"><h3>Overall GPA</h3><h1><?php echo $gpaInfo['overall_gpa'] ?? '—'; ?></h1></div>
<div class="dashboard-card"><h3>Subjects</h3><h1><?php echo count($results); ?></h1></div>
</div>
<div class="dashboard-section">
<div class="section-header"><h2>Performance Trend</h2></div>
<canvas id="gpaChart" style="max-height:260px;"></canvas>
</div>
<div class="dashboard-section">
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Exam ID</th><th>Exam</th><th>Date</th><th>Subject</th><th>Total</th><th>Grade</th><th>GPA</th></tr></thead>
<tbody>
<?php if (empty($results)) { ?>
<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-chart-column"></i><h3>No published results</h3><p>Your exam marksheets and GPA reports will appear here.</p></div></td></tr>
<?php } else { foreach ($results as $row) { ?>
<tr>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/common.js"></script>
<script>
const trend = <?php echo json_encode($trend); ?>;
if (trend.labels.length) {
new Chart(document.getElementById('gpaChart'), { type:'line', data:{ labels:trend.labels, datasets:[{ label:'GPA', data:trend.values, borderColor:'#7c3aed', tension:0.3 }] }, options:{ responsive:true, scales:{ y:{ min:0, max:4 } } } });
}
</script>
</body>
</html>
