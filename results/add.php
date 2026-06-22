<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ExamService.php';
require_once __DIR__ . '/../shared/services/ResultsService.php';

requirePermission('results', 'create');

$examService = new ExamService($conn);
$resultService = new ResultsService($conn);
$ready = $examService->isReady() && $resultService->isReady();
$error = '';
$exams = $ready ? $examService->getAvailableForResult() : [];

if ($ready && isset($_POST['create_result'])) {
    $exam_id = (int) ($_POST['exam_id'] ?? 0);

    if ($exam_id <= 0) {
        $error = 'Please select an exam.';
    } else {
        $result_id = $resultService->createResultFromExam($exam_id, (int) $_SESSION['user']['id']);

        if ($result_id > 0) {
            header('Location: entries.php?result_id=' . $result_id . '&success=created');
            exit();
        }

        $error = 'Failed to create result sheet.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Result | MindMerge</title>
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
<div><h1>Create Result</h1><p>Select an existing active exam to publish marks for its class-section.</p></div>
<a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
<?php if (!$ready) { ?><div class="alert-banner warning"><i class="fa-solid fa-database"></i> Import <strong>database/exam_results_migration.sql</strong> before creating results.</div><?php } ?>
<?php if ($error) { ?><div class="alert-banner danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div><?php } ?>
<form method="POST" class="dashboard-section">
<div class="form-grid">
<div class="form-group">
<label class="form-label">Exam *</label>
<select name="exam_id" class="form-input" required>
<option value="">Select Exam</option>
<?php foreach ($exams as $exam) { ?>
<option value="<?php echo (int) $exam['exam_id']; ?>">
<?php echo htmlspecialchars($exam['exam_code'] . ' - ' . $exam['exam_name'] . ' (' . $exam['class_name'] . ' / ' . $exam['section_name'] . ')'); ?>
</option>
<?php } ?>
</select>
</div>
</div>
<?php if (empty($exams) && $ready) { ?><p>No active exams are waiting for result creation.</p><?php } ?>
<button type="submit" name="create_result" class="btn btn-primary" <?php echo !$ready || empty($exams) ? 'disabled' : ''; ?>><i class="fa-solid fa-plus"></i> Create Result Sheet</button>
</form>
</div></div></div>
<script src="../assets/js/common.js"></script>
</body>
</html>
