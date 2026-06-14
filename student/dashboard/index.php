<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);

$service = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
$stats = $service->getStats();
$attendanceTrend = $service->getAttendanceTrend();
$todaySchedule = $service->getTodayTimetable();
$attendanceStatus = $service->getAttendanceStatus();
$user_name = portal_greeting_name();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard | MindMerge</title>
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
<h1>My Portal</h1>
<p class="portal-greeting">Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong></p>
</div>
</div>
<div class="portal-stats-grid">
<div class="dashboard-card"><div class="card-icon green"><i class="fa-solid fa-calendar-check"></i></div><h3>Attendance</h3><h1><?php echo $stats['attendance_pct']; ?>%</h1><p><?php echo htmlspecialchars($attendanceStatus); ?></p></div>
<div class="dashboard-card"><div class="card-icon blue"><i class="fa-solid fa-book"></i></div><h3>Subjects</h3><h1><?php echo $stats['subjects_count']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon red"><i class="fa-solid fa-file-lines"></i></div><h3>Upcoming Exams</h3><h1><?php echo $stats['upcoming_exams']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon orange"><i class="fa-solid fa-bell"></i></div><h3>Notifications</h3><h1><?php echo $stats['unread_notifications']; ?></h1></div>
</div>
<div class="dashboard-section">
<div class="section-header"><h2>Today's Classes</h2></div>
<div class="schedule-list">
<?php if (empty($todaySchedule)) { ?><p>No classes today.</p><?php } else { foreach ($todaySchedule as $item) { ?>
<div class="schedule-item"><div><strong><?php echo htmlspecialchars($item['subject_name']); ?></strong></div><div><?php echo date('g:i A', strtotime($item['start_time'])); ?></div></div>
<?php } } ?>
</div>
</div>
<div class="portal-charts-grid">
<div class="portal-chart-card"><h3>Attendance Trend</h3><canvas id="attendanceTrendChart"></canvas></div>
<div class="portal-chart-card"><h3>Academic Trend</h3><p style="color:var(--muted);padding:40px 0;text-align:center;">Results module coming soon.</p></div>
</div>
<div class="dashboard-section">
<div class="section-header"><h2>Quick Actions</h2></div>
<div class="quick-actions">
<a href="../timetable/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-calendar-days"></i><h3>View Timetable</h3></a>
<a href="../results/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-chart-column"></i><h3>View Results</h3></a>
<a href="../digital-id/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-id-card"></i><h3>Digital ID</h3></a>
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
