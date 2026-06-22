<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/TeacherScopeService.php';
require_once __DIR__ . '/../../shared/services/TeacherDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);

$scope = new TeacherScopeService($conn, (int) $_SESSION['user']['id']);
$service = new TeacherDashboardService($conn, $scope);
$stats = $service->getStats();
$insights = $service->getInsights();
$schedule = $service->getTodaySchedule();
$periods = $service->getCurrentAndNextPeriod();
$markingTrend = $service->getAttendanceMarkingTrend();
$studentTrend = $service->getStudentAttendanceTrend();
$user_name = portal_greeting_name();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Dashboard | MindMerge</title>
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
<h1>Teacher Console</h1>
<p class="portal-greeting"><?php echo portal_time_greeting(); ?>, <strong><?php echo htmlspecialchars($user_name); ?></strong></p>
</div>
<?php if (canCreate('attendance')) { ?>
<a href="../attendance/index.php" class="btn btn-primary no-print"><i class="fa-solid fa-clipboard-check"></i> Take Attendance</a>
<?php } ?>
</div>

<div class="portal-stats-grid">
<div class="dashboard-card"><div class="card-icon blue"><i class="fa-solid fa-calendar-day"></i></div><h3>Today's Classes</h3><h1><?php echo $stats['today_classes']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon orange"><i class="fa-solid fa-hourglass-half"></i></div><h3>Pending Attendance</h3><h1><?php echo $stats['pending_attendance']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon green"><i class="fa-solid fa-circle-check"></i></div><h3>Completed</h3><h1><?php echo $stats['completed_attendance']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon blue"><i class="fa-solid fa-user-graduate"></i></div><h3>Assigned Students</h3><h1><?php echo $stats['assigned_students']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon red"><i class="fa-solid fa-bell"></i></div><h3>Unread Notifications</h3><h1><?php echo $stats['unread_notifications']; ?></h1></div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Today's Schedule</h2></div>
<?php if ($periods['current']) { ?>
<p><strong>Current Period:</strong> <?php echo htmlspecialchars($periods['current']['subject_name']); ?> — <?php echo htmlspecialchars($periods['current']['class_name'] . ' ' . $periods['current']['section_name']); ?></p>
<?php } ?>
<?php if ($periods['next']) { ?>
<p><strong>Next Period:</strong> <?php echo htmlspecialchars($periods['next']['subject_name']); ?> at <?php echo date('g:i A', strtotime($periods['next']['start_time'])); ?></p>
<?php } ?>
<div class="schedule-list" style="margin-top:16px;">
<?php if (empty($schedule)) { ?>
<p class="text-muted">No classes scheduled for today.</p>
<?php } else { foreach ($schedule as $item) {
    $now = date('H:i:s');
    $is_current = $now >= $item['start_time'] && $now <= $item['end_time'];
?>
<div class="schedule-item <?php echo $is_current ? 'current' : ''; ?>">
<div><strong><?php echo htmlspecialchars($item['subject_name']); ?></strong> — <?php echo htmlspecialchars($item['class_name'] . ' - ' . $item['section_name']); ?></div>
<div><?php echo date('g:i A', strtotime($item['start_time'])); ?> - <?php echo date('g:i A', strtotime($item['end_time'])); ?></div>
</div>
<?php } } ?>
</div>
</div>

<div class="portal-charts-grid">
<div class="portal-chart-card"><h3>Attendance Marking Trend</h3><canvas id="markingTrendChart"></canvas></div>
<div class="portal-chart-card"><h3>Student Attendance Trend</h3><canvas id="studentTrendChart"></canvas></div>
</div>

<div class="portal-insights-grid">
<div class="insight-card">
<h4>Classes Requiring Attention</h4>
<?php if (empty($insights['classes_requiring_attention'])) { ?>
<p>All assigned classes have today's attendance marked.</p>
<?php } else { ?>
<ul><?php foreach ($insights['classes_requiring_attention'] as $c) { ?><li><?php echo htmlspecialchars($c); ?></li><?php } ?></ul>
<?php } ?>
</div>
<div class="insight-card">
<h4>Attendance Completion</h4>
<p><strong><?php echo $insights['attendance_completion_pct']; ?>%</strong> of assigned classes marked today</p>
</div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Quick Actions</h2></div>
<div class="quick-actions">
<?php if (canCreate('attendance')) { ?><a href="../attendance/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-clipboard-check"></i><h3>Take Attendance</h3></a><?php } ?>
<?php if (canView('timetables')) { ?><a href="../timetable/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-calendar-days"></i><h3>View Timetable</h3></a><?php } ?>
<?php if (canView('students')) { ?><a href="../students/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-user-graduate"></i><h3>View Students</h3></a><?php } ?>
<?php if (canView('exams')) { ?><a href="../../exams/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-file-lines"></i><h3>View Exams</h3></a><?php } ?>
<?php if (canView('results')) { ?><a href="../results/index.php" class="action-card" style="text-decoration:none;color:inherit;"><i class="fa-solid fa-square-poll-vertical"></i><h3>View Results</h3></a><?php } ?>
</div>
</div>

</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/common.js"></script>
<script>
new Chart(document.getElementById('markingTrendChart'), { type:'line', data:{ labels:<?php echo json_encode($markingTrend['labels']); ?>, datasets:[{ data:<?php echo json_encode($markingTrend['values']); ?>, borderColor:'#2563eb', tension:0.3 }] }, options:{ responsive:true, maintainAspectRatio:false } });
new Chart(document.getElementById('studentTrendChart'), { type:'line', data:{ labels:<?php echo json_encode($studentTrend['labels']); ?>, datasets:[{ data:<?php echo json_encode($studentTrend['values']); ?>, borderColor:'#22c55e', tension:0.3 }] }, options:{ responsive:true, maintainAspectRatio:false, scales:{ y:{ min:0, max:100 } } } });
</script>
</body>
</html>
