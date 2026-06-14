<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ProfileService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['parent']);

$profileService = new ProfileService($conn);
$data = $profileService->getProfileData((int) $_SESSION['user']['id'], 'parent');
$photoUrl = $profileService->getPhotoUrl($data['user']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | MindMerge</title>
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
<div><h1>My Profile</h1></div>
<div style="display:flex;gap:10px;">
<a href="print.php" class="btn btn-primary" target="_blank"><i class="fa-solid fa-print"></i> Print Profile</a>
<a href="../../profile/index.php" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit Profile</a>
</div>
</div>
<div class="dashboard-card">
<p><strong>Name:</strong> <?php echo htmlspecialchars($data['user']['full_name']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($data['user']['email']); ?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($data['user']['phone'] ?? '-'); ?></p>
<p><strong>Relationship:</strong> <?php echo htmlspecialchars($data['role_data']['relationship_name'] ?? '-'); ?></p>
<h4 style="margin-top:20px;">Children</h4>
<ul><?php foreach ($data['extra']['children'] ?? [] as $c) { ?><li><?php echo htmlspecialchars($c['full_name'] . ' (' . $c['student_id'] . ') — ' . $c['class_name'] . ' ' . $c['section_name']); ?></li><?php } ?></ul>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
