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
<title>Teacher Profile — Print</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/portals.css">
</head>
<body onload="window.print()">
<div class="print-profile">
<h1>MindMerge SmartCampus — Teacher Profile</h1>
<hr>
<div style="display:flex;gap:30px;margin:20px 0;">
<?php if ($photoUrl) { ?><img src="<?php echo htmlspecialchars($photoUrl); ?>" style="width:100px;height:100px;border-radius:8px;"><?php } ?>
<div>
<h2><?php echo htmlspecialchars($data['user']['full_name']); ?></h2>
<p>Teacher ID: <?php echo htmlspecialchars($data['role_data']['teacher_id'] ?? '-'); ?></p>
<p>Email: <?php echo htmlspecialchars($data['user']['email']); ?></p>
<p>Phone: <?php echo htmlspecialchars($data['user']['phone'] ?? '-'); ?></p>
<p>Qualification: <?php echo htmlspecialchars($data['role_data']['qualification'] ?? '-'); ?></p>
</div>
</div>
<h3>Subjects</h3>
<p><?php echo htmlspecialchars(implode(', ', $data['extra']['subjects'] ?? []) ?: '-'); ?></p>
<h3>Assigned Classes & Sections</h3>
<ul><?php foreach ($scope->getAssignedClassSectionPairs() as $p) { ?><li><?php echo htmlspecialchars($p['class_name'] . ' - ' . $p['section_name']); ?></li><?php } ?></ul>
<p style="margin-top:40px;font-size:12px;color:#666;">Printed on <?php echo date('F j, Y'); ?></p>
</div>
</body>
</html>
