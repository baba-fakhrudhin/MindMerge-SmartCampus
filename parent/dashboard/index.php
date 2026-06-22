<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ParentDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['parent']);

$service = new ParentDashboardService($conn, (int) $_SESSION['user']['id']);
$stats = $service->getStats();
$insights = $service->getInsights();
$attendanceTrend = $service->getAttendanceTrend();
$user_name = portal_greeting_name();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Parent Dashboard | MindMerge</title>
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
<div>
<h1>Parent Portal</h1>
<p class="portal-greeting">Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong></p>
</div>
</div>
<div class="portal-stats-grid">
<div class="dashboard-card"><div class="card-icon blue"><i class="fa-solid fa-children"></i></div><h3>Children</h3><h1><?php echo $stats['children_count']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon green"><i class="fa-solid fa-calendar-check"></i></div><h3>Avg Attendance</h3><h1><?php echo $stats['average_attendance']; ?>%</h1></div>
<div class="dashboard-card"><div class="card-icon orange"><i class="fa-solid fa-chart-column"></i></div><h3>Avg Performance</h3><h1><?php echo $stats['average_performance']; ?>%</h1></div>
<div class="dashboard-card"><div class="card-icon red"><i class="fa-solid fa-bell"></i></div><h3>Notifications</h3><h1><?php echo $stats['unread_notifications']; ?></h1></div>
</div>
<div class="portal-charts-grid">
<div class="portal-chart-card"><h3>Attendance Trend</h3><canvas id="attendanceTrendChart"></canvas></div>
<div class="portal-chart-card"><h3>Performance Trend</h3><p style="color:var(--muted);padding:40px 0;text-align:center;">Published exam results are available in Child Results.</p></div>
</div>
<div class="portal-insights-grid">
<div class="insight-card">
<h4>Attendance Alerts</h4>
<?php if (empty($insights['attendance_alerts'])) { ?><p>All children meet attendance thresholds.</p><?php } else { ?>
<ul><?php foreach ($insights['attendance_alerts'] as $a) { ?><li><?php echo htmlspecialchars($a['name']); ?> — <?php echo $a['rate']; ?>%</li><?php } ?></ul>
<?php } ?>
</div>
</div>
<div class="dashboard-section">
<div class="section-header"><h2>Quick Actions</h2></div>
<div class="quick-actions">
<a href="../attendance/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-calendar-check"></i><h3>View Attendance</h3></a>
<a href="../../exams/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-file-lines"></i><h3>View Exams</h3></a>
<a href="../results/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-chart-column"></i><h3>View Results</h3></a>
<a href="../../notifications/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-bell"></i><h3>Notifications</h3></a>
</div>
</div>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/common.js"></script>
<script>
new Chart(document.getElementById('attendanceTrendChart'), { type:'line', data:{ labels:<?php echo json_encode($attendanceTrend['labels']); ?>, datasets:[{ data:<?php echo json_encode($attendanceTrend['values']); ?>, borderColor:'#2563eb', spanGaps:true, tension:0.3 }] }, options:{ responsive:true, maintainAspectRatio:false, scales:{ y:{ min:0, max:100 } } } });
</script>
</body>
</html>
