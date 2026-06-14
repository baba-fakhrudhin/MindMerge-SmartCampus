<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/TeacherScopeService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);
requirePermission('timetables', 'view');

$scope = new TeacherScopeService($conn, (int) $_SESSION['user']['id']);
$tid = $scope->getTeacherId();
$day = strtolower(date('l'));
$entries = [];

if ($tid > 0) {
    $query = mysqli_query(
        $conn,
        "SELECT te.day_of_week, p.period_name, p.start_time, p.end_time,
                sub.subject_name, c.class_name, s.section_name, te.room_no
         FROM timetable_entries te
         INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
         INNER JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
         INNER JOIN periods p ON p.period_id = te.period_id
         INNER JOIN subjects sub ON sub.subject_id = te.subject_id
         INNER JOIN classes c ON c.class_id = tt.class_id
         INNER JOIN sections s ON s.section_id = tt.section_id
         WHERE ta.teacher_id = '$tid'
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
<div class="page-header"><div><h1>My Timetable</h1><p>Your weekly teaching schedule.</p></div></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Day</th><th>Period</th><th>Time</th><th>Subject</th><th>Class</th><th>Section</th><th>Room</th></tr></thead>
<tbody>
<?php if (empty($entries)) { ?>
<tr><td colspan="7">No timetable entries assigned to you.</td></tr>
<?php } else { foreach ($entries as $e) { ?>
<tr class="<?php echo $e['day_of_week'] === $day ? 'active-row' : ''; ?>">
<td><?php echo ucfirst($e['day_of_week']); ?></td>
<td><?php echo htmlspecialchars($e['period_name']); ?></td>
<td><?php echo date('g:i A', strtotime($e['start_time'])); ?> - <?php echo date('g:i A', strtotime($e['end_time'])); ?></td>
<td><?php echo htmlspecialchars($e['subject_name']); ?></td>
<td><?php echo htmlspecialchars($e['class_name']); ?></td>
<td><?php echo htmlspecialchars($e['section_name']); ?></td>
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
