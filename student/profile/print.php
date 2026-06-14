<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ProfileService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);

$profileService = new ProfileService($conn);
$data = $profileService->getProfileData((int) $_SESSION['user']['id'], 'student');
$photoUrl = $profileService->getPhotoUrl($data['user']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Profile — Print</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/portals.css">
</head>
<body onload="window.print()">
<div class="print-profile">
<h1>MindMerge SmartCampus — Student Profile</h1>
<hr>
<div style="display:flex;gap:30px;margin:20px 0;">
<?php if ($photoUrl) { ?><img src="<?php echo htmlspecialchars($photoUrl); ?>" style="width:100px;height:100px;border-radius:8px;"><?php } ?>
<div>
<h2><?php echo htmlspecialchars($data['user']['full_name']); ?></h2>
<p>Student ID: <?php echo htmlspecialchars($data['role_data']['student_id'] ?? '-'); ?></p>
<p>Class: <?php echo htmlspecialchars($data['role_data']['class_name'] ?? '-'); ?></p>
<p>Section: <?php echo htmlspecialchars($data['role_data']['section_name'] ?? '-'); ?></p>
<p>Department: <?php echo htmlspecialchars($data['role_data']['class_name'] ?? '-'); ?></p>
</div>
</div>
<p style="margin-top:40px;font-size:12px;color:#666;">Printed on <?php echo date('F j, Y'); ?></p>
</div>
</body>
</html>
