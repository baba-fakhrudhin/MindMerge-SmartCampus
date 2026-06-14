<?php

include('../config/auth.php');
requirePermission('exams', 'view');

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exams | MindMerge</title>
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
<div class="page-header"><div><h1>Exams</h1><p>Exam management module — coming soon.</p></div></div>
<div class="dashboard-section" style="text-align:center;padding:60px 20px;">
<i class="fa-solid fa-file-lines" style="font-size:48px;color:var(--primary);margin-bottom:16px;"></i>
<h3>Exams Module</h3>
<p style="color:var(--muted);margin-top:8px;">Create exams, manage marks, and publish results from this module.</p>
</div>
</div></div></div>
<script src="../assets/js/common.js"></script>
</body>
</html>
