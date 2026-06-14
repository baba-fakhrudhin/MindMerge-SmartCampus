<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/TransportService.php';
require_once __DIR__ . '/../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../shared/services/ParentDashboardService.php';

requirePermission('transport', 'view');

$role = strtolower($_SESSION['user']['role'] ?? '');
$service = new TransportService($conn);
$ready = $service->isReady();
$stats = $service->getStats();
$assignment = null;

if ($role === 'student') {
    $studentService = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
    $assignment = $service->getStudentAssignment($studentService->getStudentDbId());
}

if ($role === 'parent') {
    $parentService = new ParentDashboardService($conn, (int) $_SESSION['user']['id']);
    $children = $parentService->getChildren();
    if (!empty($children)) {
        $assignment = $service->getStudentAssignment((int) $children[0]['id']);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transport | MindMerge</title>
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
<div><h1>Transport</h1><p>Bus, route, driver, stop, and assignment management.</p></div>
<?php if ($role === 'admin' && $ready && canCreate('transport')) { ?><a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Transport Record</a><?php } ?>
</div>
<?php if (!$ready) { ?><div class="alert alert-warning"><i class="fa-solid fa-database"></i> Run <strong>database/migrations/erp_expansion.sql</strong> to enable Transport.</div><?php } ?>
<?php if ($role === 'admin') { ?>
<div class="dashboard-grid">
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon"><i class="fa-solid fa-bus"></i></div><h3><?php echo $stats['buses']; ?></h3></div><p>Buses</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon green"><i class="fa-solid fa-id-card"></i></div><h3><?php echo $stats['drivers']; ?></h3></div><p>Drivers</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon orange"><i class="fa-solid fa-route"></i></div><h3><?php echo $stats['routes']; ?></h3></div><p>Routes</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon red"><i class="fa-solid fa-user-graduate"></i></div><h3><?php echo $stats['assignments']; ?></h3></div><p>Assignments</p></div>
</div>
<div class="dashboard-section">
<div class="section-header"><h2>Transport Operations</h2></div>
<div class="quick-actions">
<a class="action-card" href="#"><i class="fa-solid fa-bus"></i><h3>Bus Management</h3><p>Manage vehicle capacity and GPS-ready fields.</p></a>
<a class="action-card" href="#"><i class="fa-solid fa-id-card"></i><h3>Driver Management</h3><p>Manage driver identity and license records.</p></a>
<a class="action-card" href="#"><i class="fa-solid fa-route"></i><h3>Route Management</h3><p>Configure routes, stops, and timings.</p></a>
<a class="action-card" href="#"><i class="fa-solid fa-users"></i><h3>Assign Students</h3><p>Link students to buses, routes, and stops.</p></a>
</div>
</div>
<?php } else { ?>
<div class="dashboard-section">
<div class="section-header"><h2><?php echo $role === 'parent' ? 'Child Transport Details' : 'My Transport Details'; ?></h2></div>
<?php if (!$assignment) { ?>
<div class="empty-state"><i class="fa-solid fa-bus"></i><h3>No active bus assignment</h3><p>Transport details will appear after admin assignment.</p></div>
<?php } else { ?>
<div class="dashboard-grid">
<div class="dashboard-card"><h3>Bus</h3><p><?php echo htmlspecialchars($assignment['bus_number']); ?> (<?php echo htmlspecialchars($assignment['registration_number']); ?>)</p></div>
<div class="dashboard-card"><h3>Route</h3><p><?php echo htmlspecialchars($assignment['route_code'] . ' - ' . $assignment['route_name']); ?></p></div>
<div class="dashboard-card"><h3>Driver</h3><p><?php echo htmlspecialchars(($assignment['driver_name'] ?? '-') . ' ' . ($assignment['driver_phone'] ?? '')); ?></p></div>
<div class="dashboard-card"><h3>Stops</h3><p>Pickup: <?php echo htmlspecialchars($assignment['pickup_stop'] ?? '-'); ?><br>Drop: <?php echo htmlspecialchars($assignment['drop_stop'] ?? '-'); ?></p></div>
</div>
<?php } ?>
</div>
<?php } ?>
</div>
</div>
</div>
<script src="../assets/js/common.js"></script>
</body>
</html>
