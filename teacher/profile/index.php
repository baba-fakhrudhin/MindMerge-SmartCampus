<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ProfileService.php';
require_once __DIR__ . '/../../shared/services/TeacherScopeService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);

$profileService = new ProfileService($conn);
$data = $profileService->getProfileData((int) $_SESSION['user']['id'], 'teacher');
$scope = new TeacherScopeService($conn, (int) $_SESSION['user']['id']);
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
<div><h1>My Profile</h1><p>Teacher profile and assignments.</p></div>
<div style="display:flex;gap:10px;">
<a href="print.php" class="btn btn-primary" target="_blank"><i class="fa-solid fa-print"></i> Print Profile</a>
<a href="../../profile/index.php" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit Profile</a>
</div>
</div>
<div class="dashboard-grid" style="grid-template-columns:1fr 2fr;">
<div class="dashboard-card" style="text-align:center;">
<?php if ($photoUrl) { ?><img src="<?php echo htmlspecialchars($photoUrl); ?>" style="width:120px;height:120px;border-radius:50%;object-fit:cover;"><?php } else { ?><div class="profile-letter" style="width:80px;height:80px;margin:0 auto;font-size:32px;"><?php echo strtoupper(substr($data['user']['full_name'],0,1)); ?></div><?php } ?>
<h3 style="margin-top:16px;"><?php echo htmlspecialchars($data['user']['full_name']); ?></h3>
<p><?php echo htmlspecialchars($data['role_data']['teacher_id'] ?? ''); ?></p>
</div>
<div class="dashboard-card">
<h3>Teacher Information</h3>
<p><strong>Email:</strong> <?php echo htmlspecialchars($data['user']['email']); ?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($data['user']['phone'] ?? '-'); ?></p>
<p><strong>Qualification:</strong> <?php echo htmlspecialchars($data['role_data']['qualification'] ?? '-'); ?></p>
<h4 style="margin-top:20px;">Subjects</h4>
<p><?php echo htmlspecialchars(implode(', ', $data['extra']['subjects'] ?? []) ?: '-'); ?></p>
<h4 style="margin-top:20px;">Assigned Classes & Sections</h4>
<ul><?php foreach ($scope->getAssignedClassSectionPairs() as $p) { ?><li><?php echo htmlspecialchars($p['class_name'] . ' - ' . $p['section_name']); ?></li><?php } ?></ul>
</div>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
