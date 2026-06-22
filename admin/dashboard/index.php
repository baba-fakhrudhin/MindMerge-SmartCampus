<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/AdminDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['admin']);

$service = new AdminDashboardService($conn);
$stats = $service->getStats();
$insights = $service->getInsights();
$monthlyTrend = $service->getMonthlyAttendanceTrend();
$classComparison = $service->getClassAttendanceComparison();
$teacherTrend = $service->getTeacherAttendanceTrend();
$studentGrowth = $service->getStudentGrowthTrend();
$recentNotifications = $service->getRecentNotifications();
$user_name = portal_greeting_name();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | MindMerge SmartCampus</title>
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
<h1>Institution Overview</h1>
<p class="portal-greeting"><?php echo portal_time_greeting(); ?>, <strong><?php echo htmlspecialchars($user_name); ?></strong> — here is today's school analytics.</p>
</div>
</div>

<div class="portal-stats-grid">

<div class="dashboard-card">
<div class="card-icon blue"><i class="fa-solid fa-user-graduate"></i></div>
<h3>Total Students</h3>
<h1><?php echo number_format($stats['total_students']); ?></h1>
<p>Currently enrolled</p>
</div>

<div class="dashboard-card">
<div class="card-icon green"><i class="fa-solid fa-chalkboard-user"></i></div>
<h3>Total Teachers</h3>
<h1><?php echo number_format($stats['total_teachers']); ?></h1>
<p>Active teaching staff</p>
</div>

<div class="dashboard-card">
<div class="card-icon blue"><i class="fa-solid fa-school"></i></div>
<h3>Total Classes</h3>
<h1><?php echo number_format($stats['total_classes']); ?></h1>
<p>Active classes</p>
</div>

<div class="dashboard-card">
<div class="card-icon orange"><i class="fa-solid fa-layer-group"></i></div>
<h3>Total Sections</h3>
<h1><?php echo number_format($stats['total_sections']); ?></h1>
<p>Active sections</p>
</div>

<div class="dashboard-card">
<div class="card-icon green"><i class="fa-solid fa-calendar-check"></i></div>
<h3>Attendance Rate</h3>
<h1><?php echo $stats['attendance_rate']; ?>%</h1>
<p>Overall student attendance</p>
</div>

<div class="dashboard-card">
<div class="card-icon orange"><i class="fa-solid fa-user-check"></i></div>
<h3>Teacher Attendance</h3>
<h1><?php echo $stats['teacher_attendance_rate']; ?>%</h1>
<p>Staff attendance rate</p>
</div>

<div class="dashboard-card">
<div class="card-icon red"><i class="fa-solid fa-bell"></i></div>
<h3>Unread Notifications</h3>
<h1><?php echo number_format($stats['unread_notifications']); ?></h1>
<p>Pending alerts</p>
</div>

<div class="dashboard-card">
<div class="card-icon red"><i class="fa-solid fa-file-lines"></i></div>
<h3>Upcoming Exams</h3>
<h1><?php echo $stats['upcoming_exams']; ?></h1>
<p>Scheduled assessments</p>
</div>

</div>

<div class="portal-charts-grid">

<div class="portal-chart-card">
<h3><i class="fa-solid fa-chart-line"></i> Monthly Attendance Trend</h3>
<canvas id="monthlyAttendanceChart"></canvas>
</div>

<div class="portal-chart-card">
<h3><i class="fa-solid fa-chart-bar"></i> Class Attendance Comparison</h3>
<canvas id="classComparisonChart"></canvas>
</div>

<div class="portal-chart-card">
<h3><i class="fa-solid fa-chart-line"></i> Teacher Attendance Trend</h3>
<canvas id="teacherTrendChart"></canvas>
</div>

<div class="portal-chart-card">
<h3><i class="fa-solid fa-chart-area"></i> Student Growth Trend</h3>
<canvas id="studentGrowthChart"></canvas>
</div>

</div>

<div class="portal-insights-grid">

<div class="insight-card">
<h4><i class="fa-solid fa-trophy"></i> Best Performing Class</h4>
<?php if ($insights['best_class']) { ?>
<p><strong><?php echo htmlspecialchars($insights['best_class']['name']); ?></strong> — <?php echo $insights['best_class']['rate']; ?>%</p>
<?php } else { ?>
<p>No attendance data yet.</p>
<?php } ?>
</div>

<div class="insight-card">
<h4><i class="fa-solid fa-triangle-exclamation"></i> Lowest Performing Class</h4>
<?php if ($insights['lowest_class']) { ?>
<p><strong><?php echo htmlspecialchars($insights['lowest_class']['name']); ?></strong> — <?php echo $insights['lowest_class']['rate']; ?>%</p>
<?php } else { ?>
<p>No attendance data yet.</p>
<?php } ?>
</div>

<div class="insight-card">
<h4><i class="fa-solid fa-user-xmark"></i> Critical Students</h4>
<p><?php echo $insights['critical_students']; ?> students below 75% attendance threshold</p>
</div>

<div class="insight-card">
<h4><i class="fa-solid fa-clock"></i> Pending Attendance</h4>
<p><?php echo $insights['pending_sessions']; ?> class-sections without today's attendance</p>
</div>

</div>

<div class="dashboard-section">
<div class="section-header"><h2>Quick Actions</h2></div>
<div class="quick-actions">

<?php if (canCreate('students')) { ?>
<a href="../../students/add.php" class="action-card" style="text-decoration:none;color:inherit;">
<i class="fa-solid fa-user-plus"></i>
<h3>Add Student</h3>
<p>Register a new student</p>
</a>
<?php } ?>

<?php if (canEdit('students')) { ?>
<a href="../../students/assign-parent.php" class="action-card" style="text-decoration:none;color:inherit;">
<i class="fa-solid fa-people-roof"></i>
<h3>Assign Parent</h3>
<p>Link students to parent accounts</p>
</a>
<?php } ?>

<?php if (canCreate('teachers')) { ?>
<a href="../../teachers/add.php" class="action-card" style="text-decoration:none;color:inherit;">
<i class="fa-solid fa-user-tie"></i>
<h3>Add Teacher</h3>
<p>Create teacher profile</p>
</a>
<?php } ?>

<?php if (canCreate('timetables')) { ?>
<a href="../../timetables/add.php" class="action-card" style="text-decoration:none;color:inherit;">
<i class="fa-solid fa-calendar-days"></i>
<h3>Create Timetable</h3>
<p>Build class schedule</p>
</a>
<?php } ?>

<?php if (canCreate('notifications')) { ?>
<a href="../../notifications/create.php" class="action-card" style="text-decoration:none;color:inherit;">
<i class="fa-solid fa-bell"></i>
<h3>Send Notification</h3>
<p>Broadcast an alert</p>
</a>
<?php } ?>

<?php if (canCreate('exams')) { ?>
<a href="../../exams/add.php" class="action-card" style="text-decoration:none;color:inherit;">
<i class="fa-solid fa-file-lines"></i>
<h3>Add Exam</h3>
<p>Schedule exam and result flow</p>
</a>
<?php } ?>

</div>
</div>

<?php if (!empty($recentNotifications)) { ?>
<div class="dashboard-section">
<div class="section-header"><h2>Recent Notifications</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Title</th><th>Type</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($recentNotifications as $n) { ?>
<tr>
<td><?php echo htmlspecialchars($n['title']); ?></td>
<td><?php echo htmlspecialchars(ucfirst($n['notification_type'] ?? 'general')); ?></td>
<td><?php echo date('M j, Y', strtotime($n['created_at'])); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
<?php } ?>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/common.js"></script>
<script>
const chartDefaults = { responsive: true, maintainAspectRatio: false };

new Chart(document.getElementById('monthlyAttendanceChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($monthlyTrend['labels']); ?>,
        datasets: [{ label: 'Attendance %', data: <?php echo json_encode($monthlyTrend['values']); ?>, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.1)', fill: true, tension: 0.3 }]
    },
    options: { ...chartDefaults, scales: { y: { min: 0, max: 100 } } }
});

new Chart(document.getElementById('classComparisonChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($classComparison['labels']); ?>,
        datasets: [{ label: 'Attendance %', data: <?php echo json_encode($classComparison['values']); ?>, backgroundColor: '#22c55e' }]
    },
    options: { ...chartDefaults, scales: { y: { min: 0, max: 100 } } }
});

new Chart(document.getElementById('teacherTrendChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($teacherTrend['labels']); ?>,
        datasets: [{ label: 'Teacher Attendance %', data: <?php echo json_encode($teacherTrend['values']); ?>, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.3 }]
    },
    options: { ...chartDefaults, scales: { y: { min: 0, max: 100 } } }
});

new Chart(document.getElementById('studentGrowthChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($studentGrowth['labels']); ?>,
        datasets: [{ label: 'Total Students', data: <?php echo json_encode($studentGrowth['values']); ?>, borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.1)', fill: true, tension: 0.3 }]
    },
    options: chartDefaults
});
</script>
</body>
</html>
