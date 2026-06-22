<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ResultsService.php';

requirePermission('results', 'view');

$service = new ResultsService($conn);
$ready = $service->isReady();
$role = strtolower($_SESSION['user']['role'] ?? '');
$resultSets = $ready ? $service->getResultSets(['published_only' => $role !== 'admin']) : [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Results | MindMerge</title>
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
<div><h1>Results</h1><p>Create, publish, and review exam-linked academic results.</p></div>
<?php if ($ready && canCreate('results')) { ?><a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create Result</a><?php } ?>
</div>
<?php if (!$ready) { ?>
<div class="alert-banner warning"><i class="fa-solid fa-database"></i> Results tables need the latest migration. Import <strong>database/exam_results_migration.sql</strong>.</div>
<?php } ?>
<?php if (isset($_GET['success'])) { ?>
<div class="success-alert"><i class="fa-solid fa-circle-check"></i> Result updated successfully.</div>
<?php } ?>
<div class="dashboard-grid">
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon"><i class="fa-solid fa-square-poll-vertical"></i></div><h3><?php echo count($resultSets); ?></h3></div><p>Result Sets</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon green"><i class="fa-solid fa-chart-line"></i></div><h3><?php echo count(array_filter($resultSets, fn($r) => $r['status'] === 'published')); ?></h3></div><p>Published</p></div>
</div>
<div class="dashboard-section">
<div class="section-header"><h2>Result Sets</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Exam ID</th><th>Exam</th><th>Class</th><th>Section</th><th>Date</th><th>Status</th><th>Entries</th><th>Exports</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($resultSets)) { ?>
<tr><td colspan="9"><div class="empty-state"><i class="fa-solid fa-square-poll-vertical"></i><h3>No results available</h3><p>Create a result sheet from an exam to begin entering marks.</p></div></td></tr>
<?php } else { foreach ($resultSets as $result) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($result['exam_code'] ?? 'Legacy'); ?></strong></td>
<td><?php echo htmlspecialchars($result['exam_name'] ?? ucfirst(str_replace('_', ' ', $result['result_type']))); ?></td>
<td><?php echo htmlspecialchars($result['class_name']); ?></td>
<td><?php echo htmlspecialchars($result['section_name']); ?></td>
<td><?php echo !empty($result['exam_date']) ? date('M j, Y', strtotime($result['exam_date'])) : htmlspecialchars($result['academic_year']); ?></td>
<td><span class="status <?php echo $result['status'] === 'published' ? 'success' : 'warning'; ?>"><?php echo ucfirst($result['status']); ?></span></td>
<td><?php echo (int) ($result['entry_count'] ?? 0); ?></td>
<td>
<a href="export.php?result_id=<?php echo (int) $result['result_id']; ?>&format=csv" class="btn btn-sm">CSV</a>
<a href="export.php?result_id=<?php echo (int) $result['result_id']; ?>&format=xls" class="btn btn-sm">Excel</a>
<a href="export.php?result_id=<?php echo (int) $result['result_id']; ?>&format=pdf" class="btn btn-sm">PDF</a>
</td>
<td>
<a href="view.php?result_id=<?php echo (int) $result['result_id']; ?>" class="btn btn-sm"><i class="fa-solid fa-eye"></i></a>
<?php if (canEdit('results') || canCreate('results')) { ?>
<a href="entries.php?result_id=<?php echo (int) $result['result_id']; ?>" class="btn btn-sm"><i class="fa-solid fa-pen"></i></a>
<?php } ?>
</td>
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
