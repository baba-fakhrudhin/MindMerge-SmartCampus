<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ExamService.php';
require_once __DIR__ . '/../shared/services/ResultsService.php';

requirePermission('exams', 'view');

$exam_id = (int) ($_GET['exam_id'] ?? 0);
$examService = new ExamService($conn);
$resultService = new ResultsService($conn);
$exam = $examService->getExamById($exam_id);

if (!$exam) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['create_result']) && canCreate('results')) {
    $result_id = $resultService->createResultFromExam($exam_id, (int) $_SESSION['user']['id']);
    if ($result_id > 0) {
        header('Location: ../results/entries.php?result_id=' . $result_id . '&success=created');
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Exam | MindMerge</title>
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
<div><h1><?php echo htmlspecialchars($exam['exam_name']); ?></h1><p><?php echo htmlspecialchars($exam['exam_code']); ?></p></div>
<div class="quick-actions">
<?php if (canEdit('exams')) { ?><a href="edit.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit</a><?php } ?>
<a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
</div>

<div class="portal-stats-grid">
<div class="dashboard-card"><h3>Class</h3><h1 style="font-size:1.4rem;"><?php echo htmlspecialchars($exam['class_name']); ?></h1></div>
<div class="dashboard-card"><h3>Section</h3><h1 style="font-size:1.4rem;"><?php echo htmlspecialchars($exam['section_name']); ?></h1></div>
<div class="dashboard-card"><h3>Date</h3><h1 style="font-size:1.4rem;"><?php echo date('M j, Y', strtotime($exam['exam_date'])); ?></h1></div>
<div class="dashboard-card"><h3>Time</h3><h1 style="font-size:1.4rem;"><?php echo date('g:i A', strtotime($exam['exam_time'])); ?></h1></div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Result Sheet</h2></div>
<?php if (!empty($exam['result_id'])) { ?>
<p>This exam already has a result sheet.</p>
<div class="form-actions"><a href="../results/view.php?result_id=<?php echo (int) $exam['result_id']; ?>" class="btn btn-primary"><i class="fa-solid fa-square-poll-vertical"></i> Open Result</a></div>
<?php } else { ?>
<p>Create a result sheet for this exam. Students will be loaded only from <?php echo htmlspecialchars($exam['class_name'] . ' - ' . $exam['section_name']); ?>.</p>
<?php if (canCreate('results')) { ?><form method="POST" class="form-actions"><button type="submit" name="create_result" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create Result Sheet</button></form><?php } ?>
<?php } ?>
</div>
</div></div></div>
<script src="../assets/js/common.js"></script>
</body>
</html>
