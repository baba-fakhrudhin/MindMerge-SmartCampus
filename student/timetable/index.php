<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);
requirePermission('timetables', 'view');

$service = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
$student = $service->getStudent();
$class_id = (int) ($student['class_id'] ?? 0);
$section_id = (int) ($student['section_id'] ?? 0);
$entries = [];

if ($class_id > 0) {
    $query = mysqli_query(
        $conn,
        "SELECT te.day_of_week, p.period_name, p.start_time, p.end_time,
                sub.subject_name, u.full_name AS teacher_name, te.room_no
         FROM timetable_entries te
         INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
         INNER JOIN periods p ON p.period_id = te.period_id
         INNER JOIN subjects sub ON sub.subject_id = te.subject_id
         LEFT JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
         LEFT JOIN teachers t ON t.id = ta.teacher_id
         LEFT JOIN users u ON u.id = t.user_id
         WHERE tt.class_id = '$class_id' AND tt.section_id = '$section_id'
         ORDER BY FIELD(te.day_of_week,'monday','tuesday','wednesday','thursday','friday','saturday'), p.start_time"
    );

    while ($row = mysqli_fetch_assoc($query)) {
        $entries[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Timetable | MindMerge</title>
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
<div class="page-header"><div><h1>My Timetable</h1><p><?php echo htmlspecialchars(($student['class_name'] ?? '') . ' - ' . ($student['section_name'] ?? '')); ?></p></div></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Day</th><th>Period</th><th>Time</th><th>Subject</th><th>Teacher</th><th>Room</th></tr></thead>
<tbody>
<?php if (empty($entries)) { ?><tr><td colspan="6">No timetable available.</td></tr><?php } else { foreach ($entries as $e) { ?>
<tr>
<td><?php echo ucfirst($e['day_of_week']); ?></td>
<td><?php echo htmlspecialchars($e['period_name']); ?></td>
<td><?php echo date('g:i A', strtotime($e['start_time'])); ?> - <?php echo date('g:i A', strtotime($e['end_time'])); ?></td>
<td><?php echo htmlspecialchars($e['subject_name']); ?></td>
<td><?php echo htmlspecialchars($e['teacher_name'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($e['room_no'] ?? '-'); ?></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
