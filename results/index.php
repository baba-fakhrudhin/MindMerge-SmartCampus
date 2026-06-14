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
<div><h1>Results</h1><p>Create, publish, and review academic results.</p></div>
<?php if ($ready && canCreate('results')) { ?><a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create Result</a><?php } ?>
</div>
<?php if (!$ready) { ?>
<div class="alert alert-warning"><i class="fa-solid fa-database"></i> Run <strong>database/migrations/erp_expansion.sql</strong> to enable the Results module.</div>
<?php } ?>
<div class="dashboard-grid">
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon"><i class="fa-solid fa-square-poll-vertical"></i></div><h3><?php echo count($resultSets); ?></h3></div><p>Result Sets</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon green"><i class="fa-solid fa-eye"></i></div><h3><?php echo $role === 'admin' ? 'Admin' : 'Portal'; ?></h3></div><p>Access Scope</p></div>
</div>
<div class="dashboard-section">
<div class="section-header"><h2>Result Sets</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Class</th><th>Section</th><th>Academic Year</th><th>Semester</th><th>Type</th><th>Status</th><th>Published</th></tr></thead>
<tbody>
<?php if (empty($resultSets)) { ?>
<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-square-poll-vertical"></i><h3>No results available</h3><p>Published results will appear here.</p></div></td></tr>
<?php } else { foreach ($resultSets as $result) { ?>
<tr>
<td><?php echo htmlspecialchars($result['class_name']); ?></td>
<td><?php echo htmlspecialchars($result['section_name']); ?></td>
<td><?php echo htmlspecialchars($result['academic_year']); ?></td>
<td><?php echo htmlspecialchars($result['semester'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars(ucfirst($result['result_type'])); ?></td>
<td><span class="status <?php echo $result['status'] === 'published' ? 'success' : 'warning'; ?>"><?php echo ucfirst($result['status']); ?></span></td>
<td><?php echo $result['published_at'] ? date('d M Y', strtotime($result['published_at'])) : '-'; ?></td>
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
