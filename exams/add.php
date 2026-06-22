<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ExamService.php';

requirePermission('exams', 'create');

$service = new ExamService($conn);
$ready = $service->isReady();
$error = '';
$class_query = mysqli_query($conn, "SELECT * FROM classes WHERE status='active' ORDER BY class_name ASC");

if ($ready && isset($_POST['save_exam'])) {
    $class_id = (int) ($_POST['class_id'] ?? 0);
    $section_id = (int) ($_POST['section_id'] ?? 0);
    $exam_name = trim($_POST['exam_name'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $exam_date = trim($_POST['exam_date'] ?? '');
    $exam_time = trim($_POST['exam_time'] ?? '');

    if ($class_id <= 0 || $section_id <= 0 || $exam_name === '' || $academic_year === '' || $exam_date === '' || $exam_time === '') {
        $error = 'Please fill all required fields.';
    } else {
        $exam_id = $service->createExam($_POST, (int) $_SESSION['user']['id']);
        if ($exam_id > 0) {
            header('Location: view.php?exam_id=' . $exam_id . '&success=created');
            exit();
        }
        $error = 'Failed to create exam.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Exam | MindMerge</title>
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
<div class="page-header"><div><h1>Add Exam</h1><p>Schedule an exam for one class and section.</p></div><a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a></div>
<?php if (!$ready) { ?><div class="alert-banner warning"><i class="fa-solid fa-database"></i> Import <strong>database/exam_results_migration.sql</strong> before adding exams.</div><?php } ?>
<?php if ($error) { ?><div class="alert-banner danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div><?php } ?>
<form method="POST" class="dashboard-section">
<div class="form-grid">
<div class="form-group"><label class="form-label">Exam Name *</label><input type="text" name="exam_name" class="form-input" required></div>
<div class="form-group"><label class="form-label">Academic Year *</label><input type="text" name="academic_year" class="form-input" placeholder="2026-2027" required></div>
<div class="form-group"><label class="form-label">Class *</label><select name="class_id" id="class_id" class="form-input" required><option value="">Select Class</option><?php while ($c = mysqli_fetch_assoc($class_query)) { ?><option value="<?php echo (int) $c['class_id']; ?>"><?php echo htmlspecialchars($c['class_name']); ?></option><?php } ?></select></div>
<div class="form-group"><label class="form-label">Section *</label><select name="section_id" id="section_id" class="form-input" required><option value="">Select Section</option></select></div>
<div class="form-group"><label class="form-label">Date *</label><input type="date" name="exam_date" class="form-input" required></div>
<div class="form-group"><label class="form-label">Time *</label><input type="time" name="exam_time" class="form-input" required></div>
<div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
</div>
<button type="submit" name="save_exam" class="btn btn-primary" <?php echo !$ready ? 'disabled' : ''; ?>><i class="fa-solid fa-floppy-disk"></i> Save Exam</button>
</form>
</div></div></div>
<script src="../assets/js/common.js"></script>
<script>
document.getElementById('class_id').addEventListener('change', function () {
    const sectionSelect = document.getElementById('section_id');
    sectionSelect.innerHTML = '<option value="">Loading...</option>';
    if (!this.value) {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        return;
    }
    fetch('../attendance/get_sections.php?class_id=' + this.value)
        .then(r => r.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            (Array.isArray(data) ? data : (data.sections || [])).forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.section_id;
                opt.textContent = s.section_name;
                sectionSelect.appendChild(opt);
            });
        });
});
</script>
</body>
</html>
