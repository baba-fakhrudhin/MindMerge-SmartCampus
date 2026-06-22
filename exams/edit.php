<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ExamService.php';

requirePermission('exams', 'edit');

$exam_id = (int) ($_GET['exam_id'] ?? 0);
$service = new ExamService($conn);
$exam = $service->getExamById($exam_id);
$error = '';

if (!$exam) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['save_exam'])) {
    if ((int) ($_POST['class_id'] ?? 0) <= 0 || (int) ($_POST['section_id'] ?? 0) <= 0 || trim($_POST['exam_name'] ?? '') === '') {
        $error = 'Please fill all required fields.';
    } elseif ($service->updateExam($exam_id, $_POST)) {
        header('Location: view.php?exam_id=' . $exam_id . '&success=updated');
        exit();
    } else {
        $error = 'Failed to update exam.';
    }
}

$class_query = mysqli_query($conn, "SELECT * FROM classes WHERE status='active' ORDER BY class_name ASC");
$section_query = mysqli_query($conn, "SELECT * FROM sections WHERE class_id='" . (int) $exam['class_id'] . "' AND status='active' ORDER BY section_name ASC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Exam | MindMerge</title>
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
<div class="page-header"><div><h1>Edit Exam</h1><p><?php echo htmlspecialchars($exam['exam_code']); ?></p></div><a href="view.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a></div>
<?php if ($error) { ?><div class="alert-banner danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div><?php } ?>
<form method="POST" class="dashboard-section">
<div class="form-grid">
<div class="form-group"><label class="form-label">Exam Name *</label><input type="text" name="exam_name" class="form-input" value="<?php echo htmlspecialchars($exam['exam_name']); ?>" required></div>
<div class="form-group"><label class="form-label">Academic Year *</label><input type="text" name="academic_year" class="form-input" value="<?php echo htmlspecialchars($exam['academic_year']); ?>" required></div>
<div class="form-group"><label class="form-label">Class *</label><select name="class_id" id="class_id" class="form-input" required><?php while ($c = mysqli_fetch_assoc($class_query)) { ?><option value="<?php echo (int) $c['class_id']; ?>" <?php echo (int) $exam['class_id'] === (int) $c['class_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['class_name']); ?></option><?php } ?></select></div>
<div class="form-group"><label class="form-label">Section *</label><select name="section_id" id="section_id" class="form-input" required><?php while ($s = mysqli_fetch_assoc($section_query)) { ?><option value="<?php echo (int) $s['section_id']; ?>" <?php echo (int) $exam['section_id'] === (int) $s['section_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['section_name']); ?></option><?php } ?></select></div>
<div class="form-group"><label class="form-label">Date *</label><input type="date" name="exam_date" class="form-input" value="<?php echo htmlspecialchars($exam['exam_date']); ?>" required></div>
<div class="form-group"><label class="form-label">Time *</label><input type="time" name="exam_time" class="form-input" value="<?php echo htmlspecialchars(substr($exam['exam_time'], 0, 5)); ?>" required></div>
<div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active" <?php echo $exam['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo $exam['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div>
</div>
<button type="submit" name="save_exam" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
</form>
</div></div></div>
<script src="../assets/js/common.js"></script>
<script>
document.getElementById('class_id').addEventListener('change', function () {
    const sectionSelect = document.getElementById('section_id');
    sectionSelect.innerHTML = '<option value="">Loading...</option>';
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
