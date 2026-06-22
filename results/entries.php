<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ResultsService.php';

$result_id = (int) ($_GET['result_id'] ?? 0);
$service = new ResultsService($conn);
$result = $service->getResultById($result_id);

if (!$result) {
    header('Location: index.php');
    exit();
}

$can_edit = canEdit('results') || (canCreate('results') && strtolower($_SESSION['user']['role'] ?? '') === 'teacher');

if (!$can_edit) {
    requirePermission('results', 'edit');
}

$subject_id = (int) ($_GET['subject_id'] ?? 0);
$students = $service->getStudentsForResult($result_id);
$subjects = $service->getSubjectsForResult($result_id);
$entries = $service->getEntries($result_id, $subject_id > 0 ? $subject_id : null);
$entry_map = [];

foreach ($entries as $entry) {
    $entry_map[$entry['student_id'] . '-' . $entry['subject_id']] = $entry;
}

$teacher_db_id = 0;

if (strtolower($_SESSION['user']['role'] ?? '') === 'teacher') {
    $tid = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT id FROM teachers WHERE user_id = '" . (int) $_SESSION['user']['id'] . "' LIMIT 1"
    ));
    $teacher_db_id = (int) ($tid['id'] ?? 0);
}

if (isset($_POST['save_marks'])) {
    $rows = [];
    $active_subject = (int) ($_POST['subject_id'] ?? 0);

    foreach ($students as $student) {
        $sid = (int) $student['id'];
        $rows[] = [
            'student_id'       => $sid,
            'subject_id'       => $active_subject,
            'internal_marks'   => $_POST['internal_' . $sid] ?? 0,
            'external_marks'   => $_POST['external_' . $sid] ?? 0,
            'lab_marks'        => $_POST['lab_' . $sid] ?? 0,
            'attendance_marks' => $_POST['attendance_' . $sid] ?? 0,
        ];
    }

    $service->bulkSaveEntries($result_id, $rows, $teacher_db_id ?: null);
    header('Location: entries.php?result_id=' . $result_id . '&subject_id=' . $active_subject . '&success=saved');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enter Marks | MindMerge</title>
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
<div>
<h1>Enter Marks</h1>
<p><?php echo htmlspecialchars(($result['exam_code'] ?? 'Legacy Result') . ' - ' . ($result['exam_name'] ?? $result['class_name']) . ' | ' . $result['class_name'] . ' - ' . $result['section_name']); ?></p>
</div>
<a href="view.php?result_id=<?php echo $result_id; ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<?php if (isset($_GET['success'])) { ?>
<div class="success-alert"><i class="fa-solid fa-circle-check"></i> Marks saved successfully.</div>
<?php } ?>

<div class="dashboard-section">
<form method="GET" class="filter-row" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
<input type="hidden" name="result_id" value="<?php echo $result_id; ?>">
<div class="form-group">
<label>Subject</label>
<select name="subject_id" class="form-input" onchange="this.form.submit()">
<option value="">All Subjects</option>
<?php foreach ($subjects as $sub) { ?>
<option value="<?php echo (int) $sub['subject_id']; ?>" <?php echo $subject_id === (int) $sub['subject_id'] ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($sub['subject_name']); ?>
</option>
<?php } ?>
</select>
</div>
</form>
</div>

<?php if ($subject_id <= 0) { ?>
<div class="alert-banner warning"><i class="fa-solid fa-circle-info"></i> Select a subject to enter marks.</div>
<?php } else { ?>
<form method="POST">
<input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
<div class="dashboard-section">
<div class="table-responsive">
<table class="custom-table">
<thead>
<tr>
<th>Student</th>
<th>Internal</th>
<th>External</th>
<th>Lab</th>
<th>Attendance</th>
<th>Total</th>
<th>Grade</th>
</tr>
</thead>
<tbody>
<?php foreach ($students as $student) {
    $key = $student['id'] . '-' . $subject_id;
    $entry = $entry_map[$key] ?? null;
?>
<tr>
<td><?php echo htmlspecialchars($student['full_name'] . ' (' . $student['student_id'] . ')'); ?></td>
<td><input type="number" step="0.01" min="0" name="internal_<?php echo (int) $student['id']; ?>" class="form-input" value="<?php echo htmlspecialchars($entry['internal_marks'] ?? '0'); ?>"></td>
<td><input type="number" step="0.01" min="0" name="external_<?php echo (int) $student['id']; ?>" class="form-input" value="<?php echo htmlspecialchars($entry['external_marks'] ?? '0'); ?>"></td>
<td><input type="number" step="0.01" min="0" name="lab_<?php echo (int) $student['id']; ?>" class="form-input" value="<?php echo htmlspecialchars($entry['lab_marks'] ?? '0'); ?>"></td>
<td><input type="number" step="0.01" min="0" name="attendance_<?php echo (int) $student['id']; ?>" class="form-input" value="<?php echo htmlspecialchars($entry['attendance_marks'] ?? '0'); ?>"></td>
<td><?php echo htmlspecialchars($entry['total_marks'] ?? '0'); ?></td>
<td><?php echo htmlspecialchars($entry['grade'] ?? '-'); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<button type="submit" name="save_marks" class="btn btn-primary" style="margin-top:16px;"><i class="fa-solid fa-floppy-disk"></i> Save Marks</button>
</div>
</form>
<?php } ?>
</div>
</div>
</div>
<script src="../assets/js/common.js"></script>
</body>
</html>
