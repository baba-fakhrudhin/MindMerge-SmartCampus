<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ProfileService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['parent']);

$profileService = new ProfileService($conn);
$data = $profileService->getProfileData((int) $_SESSION['user']['id'], 'parent');

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parent Profile — Print</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/portals.css">
</head>
<body onload="window.print()">
<div class="print-profile">
<h1>MindMerge SmartCampus — Parent Profile</h1>
<hr>
<h2><?php echo htmlspecialchars($data['user']['full_name']); ?></h2>
<p>Email: <?php echo htmlspecialchars($data['user']['email']); ?></p>
<p>Phone: <?php echo htmlspecialchars($data['user']['phone'] ?? '-'); ?></p>
<p>Relationship: <?php echo htmlspecialchars($data['role_data']['relationship_name'] ?? '-'); ?></p>
<h3>Children Information</h3>
<ul><?php foreach ($data['extra']['children'] ?? [] as $c) { ?><li><?php echo htmlspecialchars($c['full_name'] . ' — ' . $c['class_name'] . ' ' . $c['section_name']); ?></li><?php } ?></ul>
<p style="margin-top:40px;font-size:12px;color:#666;">Printed on <?php echo date('F j, Y'); ?></p>
</div>
</body>
</html>
